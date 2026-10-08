<?php
/**
 * Käyttäjätilit: roolit, oikeudet, syötteiden tarkistus ja tietokantakyselyt.
 *
 * Tiedosto ei käytä istuntoa, joten myös komentoriviskripti (src/cli) voi käyttää sitä.
 * Kaikki kyselyt ovat PDO-valmisteltuja lauseita.
 */

require_once __DIR__ . '/passwords.php';

/** Roolit ja niiden suomenkieliset nimet (suunnitelma plan.md: omistaja vs. kirjanpitäjä). */
const ROLE_LABELS = [
    'owner'      => 'Omistaja',
    'accountant' => 'Kirjanpitäjä',
];

/**
 * Rooleille myönnetyt oikeudet. Sivut tarkistavat oikeuden (user_can / require_permission), eivät roolin nimeä:
 * uusi rooli vaatii vain tämän taulukon, ROLE_LABELS:n ja users-taulun ENUM-määrityksen päivityksen.
 */
const ROLE_PERMISSIONS = [
    'owner'      => ['view', 'add_transaction', 'export', 'manage_users'],
    'accountant' => ['view', 'add_transaction', 'export'],
];

/** Käyttäjärivin kentät ilman salasanatiivistettä. Aikaleimat annetaan Unix-aikana (aikavyöhykeriippumaton). */
const USER_COLUMNS = 'id, username, display_name, role, is_active, session_version,
    UNIX_TIMESTAMP(last_login_at) AS last_login_ts';

/** Onko roolilla annettu oikeus? Tuntematon rooli ei saa mitään oikeuksia. */
function role_has_permission(string $role, string $permission): bool
{
    return in_array($permission, ROLE_PERMISSIONS[$role] ?? [], true);
}

/** Käyttäjätunnus tallennetaan ja haetaan aina pieninä kirjaimina ilman reunavälilyöntejä. */
function normalize_username(string $username): string
{
    return strtolower(trim($username));
}

/** @return string|null Virheilmoitus tai null, jos tunnus kelpaa (odottaa normalisoitua tunnusta) */
function username_error(string $username): ?string
{
    // \z (ei $): $ hyväksyisi myös rivinvaihdon merkkijonon lopussa.
    if (!preg_match('/^[a-z0-9][a-z0-9._-]{2,49}\z/', $username)) {
        return 'Käyttäjätunnuksessa saa olla 3–50 merkkiä: pieniä kirjaimia (a–z), numeroita sekä merkit . _ ja -.';
    }

    return null;
}

/** @return string|null Virheilmoitus tai null, jos nimi kelpaa */
function display_name_error(string $name): ?string
{
    $length = mb_strlen($name);
    if ($length < 1 || $length > 100 || !mb_check_encoding($name, 'UTF-8') || preg_match('/[\x00-\x1F\x7F]/', $name)) {
        return 'Nimen on oltava 1–100 merkkiä pitkä, eikä se saa sisältää ohjausmerkkejä.';
    }

    return null;
}

/** @return string|null Virheilmoitus tai null, jos rooli on tunnettu */
function role_error(string $role): ?string
{
    return array_key_exists($role, ROLE_LABELS) ? null : 'Valitse kelvollinen rooli.';
}

/** Muuttaa tietokannasta tulleen rivin kentät oikeisiin tyyppeihin. */
function normalize_user_row(array $row): array
{
    $row['id'] = (int) $row['id'];
    $row['is_active'] = (bool) $row['is_active'];
    $row['session_version'] = (int) $row['session_version'];
    $row['last_login_ts'] = $row['last_login_ts'] !== null ? (int) $row['last_login_ts'] : null;

    return $row;
}

/** Hakee käyttäjän tunnisteella (ei sisällä salasanatiivistettä). */
function find_user_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT ' . USER_COLUMNS . ' FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row === false ? null : normalize_user_row($row);
}

/** Hakee käyttäjän käyttäjätunnuksella salasanatiivisteineen (vain salasanan tarkistukseen). */
function find_user_with_hash(PDO $pdo, string $username): ?array
{
    $stmt = $pdo->prepare('SELECT ' . USER_COLUMNS . ', password_hash FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row === false ? null : normalize_user_row($row);
}

/** Kaikki käyttäjät käyttäjätunnuksen mukaan järjestettyinä (ei salasanatiivisteitä). */
function list_users(PDO $pdo): array
{
    $rows = $pdo->query('SELECT ' . USER_COLUMNS . ' FROM users ORDER BY username')->fetchAll(PDO::FETCH_ASSOC);

    return array_map('normalize_user_row', $rows);
}

/**
 * Luo käyttäjän. Syötteet on tarkistettava etukäteen (username_error ym.).
 * Jos käyttäjätunnus on jo käytössä, PDOException (SQLSTATE 23000) kertoo siitä kutsujalle.
 *
 * @return int Uuden käyttäjän tunniste
 */
function create_user(PDO $pdo, string $username, string $displayName, string $role, string $password): int
{
    $stmt = $pdo->prepare('INSERT INTO users (username, display_name, role, password_hash) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $displayName, $role, hash_password($password)]);

    return (int) $pdo->lastInsertId();
}

/** Päivittää käyttäjän nimen, roolin ja tilan (käytössä / poistettu käytöstä). */
function update_user(PDO $pdo, int $id, string $displayName, string $role, bool $isActive): void
{
    $stmt = $pdo->prepare('UPDATE users SET display_name = ?, role = ?, is_active = ? WHERE id = ?');
    $stmt->execute([$displayName, $role, $isActive ? 1 : 0, $id]);
}

/**
 * Asettaa uuden salasanan ja kasvattaa session_version-arvoa, jolloin tilin kaikki aiemmat istunnot
 * lakkaavat toimimasta (kutsuja päivittää omaan istuntoonsa palautetun uuden arvon).
 *
 * @return int Uusi session_version
 */
function set_user_password(PDO $pdo, int $id, string $password): int
{
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, session_version = session_version + 1 WHERE id = ?');
    $stmt->execute([hash_password($password), $id]);

    return find_user_by_id($pdo, $id)['session_version'] ?? 0;
}

/** Päivittää tiivisteen uudella kustannuksella (ei vaikuta istuntoihin). */
function upgrade_password_hash(PDO $pdo, int $id, string $password): void
{
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $stmt->execute([hash_password($password), $id]);
}

/** Merkitsee onnistuneen kirjautumisen ajan. */
function record_login(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
    $stmt->execute([$id]);
}
