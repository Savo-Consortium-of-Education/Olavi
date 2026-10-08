<?php
/**
 * Kirjautuminen, istunnot ja käyttöoikeudet.
 *
 * - Salasana tarkistetaan password_verify():llä bcrypt-tiivistettä vasten (ks. passwords.php).
 * - Istunto: HttpOnly- ja SameSite=Lax-eväste (Secure HTTPS-yhteydellä), uusi istuntotunniste kirjautuessa
 *   (estää istuntoon kiinnittämisen), toimettomuus- ja enimmäisaikaraja sekä tilin tarkistus tietokannasta
 *   joka pyynnöllä: poistettu käyttäjä tai vaihtunut salasana päättää istunnon heti.
 * - Epäonnistuneet kirjautumisyritykset tallennetaan tauluun login_attempts, ja ylimääräiset yritykset estetään.
 * - Käyttöoikeudet tarkistetaan oikeuksina (user_can), ei roolin nimenä.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/accounts.php';

const SESSION_NAME = 'OLAVISESSID';
const SESSION_IDLE_TIMEOUT = 1800;      // istunto päättyy 30 minuutin toimettomuuteen
const SESSION_ABSOLUTE_TIMEOUT = 28800; // ...ja viimeistään 8 tunnin kuluttua kirjautumisesta

const LOGIN_WINDOW_SECONDS = 900;       // epäonnistuneet yritykset lasketaan 15 minuutin ajalta
const LOGIN_MAX_FAILURES_PER_USER = 5;  // saman tunnuksen ja IP-osoitteen yhdistelmälle
const LOGIN_MAX_FAILURES_PER_IP = 20;   // yhdelle IP-osoitteelle kaikilla tunnuksilla yhteensä

const LOGIN_OK = 'ok';
const LOGIN_FAILED = 'failed';
const LOGIN_THROTTLED = 'throttled';

/** Käynnistää istunnon turvallisin asetuksin (ei tee mitään, jos istunto on jo käynnissä). */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');   // palvelin ei hyväksy itse keksittyä istuntotunnistetta
    ini_set('session.use_only_cookies', '1');  // tunniste ei kulje osoitteessa
    ini_set('session.use_trans_sid', '0');
    ini_set('session.gc_maxlifetime', (string) (SESSION_IDLE_TIMEOUT + 300));

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,           // istuntoeväste poistuu selaimen sulkeutuessa
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,        // JavaScript ei näe evästettä
        'samesite' => 'Lax',       // eväste ei lähde ulkopuolisen sivun lähettämän lomakkeen mukana
    ]);
    session_cache_limiter('');     // välimuistiotsakkeet asetetaan itse (ks. bootstrap.php)
    session_start();
}

/**
 * Onko kirjautumisen tietokantataulut (users, login_attempts) olemassa? Vanhasta tietokannasta ne puuttuvat,
 * koska schema.sql ajetaan vain tietokannan ensimmäisellä alustuksella (päivitys: migrations/, ks. README.md).
 */
function auth_schema_ready(PDO $pdo): bool
{
    try {
        $pdo->query('SELECT 1 FROM users LIMIT 1');
        $pdo->query('SELECT 1 FROM login_attempts LIMIT 1');

        return true;
    } catch (PDOException $e) {
        if ($e->getCode() === '42S02') { // SQLSTATE 42S02: taulua ei ole
            return false;
        }
        throw $e;
    }
}

/** Palauttaa kirjautuneen käyttäjän (asetetaan bootstrap.php:ssa) tai null. */
function current_user(): ?array
{
    return $GLOBALS['auth_user'] ?? null;
}

/**
 * Tyhjentää istunnon ja vaihtaa istuntotunnisteen. Vanha tunniste mitätöidään palvelimella,
 * joten sitä ei voi enää käyttää, ja selain saa uuden, tyhjän istunnon.
 */
function end_session(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
    $GLOBALS['auth_user'] = null;
}

/**
 * Lukee istunnon käyttäjän ja tarkistaa, että istunto on yhä voimassa. Palauttaa null, jos käyttäjä ei ole
 * kirjautunut, istunto on vanhentunut, tili on poistettu käytöstä tai salasana on vaihtunut istunnon jälkeen.
 */
function load_session_user(PDO $pdo): ?array
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!is_int($userId)) {
        return null;
    }

    $now = time();
    $lastActivity = $_SESSION['last_activity'] ?? null;
    $loginAt = $_SESSION['login_at'] ?? null;
    if (!is_int($lastActivity) || !is_int($loginAt)
        || $now - $lastActivity > SESSION_IDLE_TIMEOUT
        || $now - $loginAt > SESSION_ABSOLUTE_TIMEOUT) {
        end_session();
        flash_set('error', 'Istunto on vanhentunut. Kirjaudu sisään uudelleen.');

        return null;
    }

    $user = find_user_by_id($pdo, $userId);
    if ($user === null || !$user['is_active'] || $user['session_version'] !== ($_SESSION['session_version'] ?? null)) {
        end_session();

        return null;
    }

    $_SESSION['last_activity'] = $now;

    return $user;
}

/** Vaatii kirjautumisen: ohjaa kirjautumissivulle, jos käyttäjä ei ole kirjautunut. */
function require_login(PDO $pdo): array
{
    $user = load_session_user($pdo);
    if ($user === null) {
        redirect('login.php');
    }

    $GLOBALS['auth_user'] = $user;

    return $user;
}

/** Onko kirjautuneella käyttäjällä annettu oikeus? */
function user_can(string $permission): bool
{
    $user = current_user();

    return $user !== null && role_has_permission($user['role'], $permission);
}

/** Vaatii oikeuden: kirjautumaton ohjataan kirjautumiseen, oikeudeton saa 403-sivun. */
function require_permission(string $permission): void
{
    $user = current_user();
    if ($user === null) {
        redirect('login.php');
    }

    if (!role_has_permission($user['role'], $permission)) {
        auth_log('forbidden permission=' . $permission, $user['username']);
        render_error_page(403, 'Ei käyttöoikeutta', 'Roolisi ei oikeuta tämän sivun käyttöön.');
    }
}

/** Asiakkaan IP-osoite. X-Forwarded-For-otsaketta ei käytetä, koska asiakas voi väärentää sen. */
function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '-'), 0, 45);
}

/**
 * Kirjaa tietoturvatapahtuman palvelimen lokiin (Dockerissa: docker compose logs web). Salasanoja ei kirjata.
 * Kirjoitus tehdään suoraan stderriin: error_log() kirjaa Apachessa "notice"-tasolla, jonka oletusasetus (warn)
 * suodattaisi pois.
 */
function auth_log(string $event, string $username = ''): void
{
    $safeName = preg_replace('/[^A-Za-z0-9._@-]/', '?', substr($username, 0, 64));
    $line = sprintf('%s [auth] %s user=%s ip=%s', gmdate('Y-m-d\TH:i:s\Z'), $event, $safeName, client_ip());
    file_put_contents('php://stderr', $line . PHP_EOL);
}

/** Onko tunnuksella tai IP-osoitteella liikaa epäonnistuneita yrityksiä viime aikoina? */
function login_is_throttled(PDO $pdo, string $username, string $ip): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS by_ip, COALESCE(SUM(username = ?), 0) AS by_user
         FROM login_attempts
         WHERE ip_address = ? AND attempted_at > (NOW() - INTERVAL ? SECOND)'
    );
    $stmt->execute([$username, $ip, LOGIN_WINDOW_SECONDS]);
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    return (int) $counts['by_user'] >= LOGIN_MAX_FAILURES_PER_USER
        || (int) $counts['by_ip'] >= LOGIN_MAX_FAILURES_PER_IP;
}

/** Tallentaa epäonnistuneen yrityksen ja siivoaa yli vuorokauden vanhat rivit. */
function record_failed_login(PDO $pdo, string $username, string $ip): void
{
    $stmt = $pdo->prepare('INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)');
    $stmt->execute([substr($username, 0, 64), $ip]);
    $pdo->exec('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
}

/** Nollaa tunnuksen ja IP-osoitteen epäonnistuneet yritykset onnistuneen kirjautumisen jälkeen. */
function clear_failed_logins(PDO $pdo, string $username, string $ip): void
{
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE username = ? AND ip_address = ?');
    $stmt->execute([$username, $ip]);
}

/**
 * Yrittää kirjata käyttäjän sisään. Virheen syytä (tuntematon tunnus, väärä salasana, poistettu tili)
 * ei paljasteta: kaikista seuraa sama LOGIN_FAILED.
 *
 * @return string LOGIN_OK, LOGIN_FAILED tai LOGIN_THROTTLED
 */
function attempt_login(PDO $pdo, string $username, string $password): string
{
    $ip = client_ip();

    if (login_is_throttled($pdo, $username, $ip)) {
        auth_log('login_throttled', $username);

        return LOGIN_THROTTLED;
    }

    $user = $username !== '' ? find_user_with_hash($pdo, $username) : null;

    // Salasana tarkistetaan aina (tuntemattomalla tunnuksella vertailutiivistettä vasten), jotta vastausaika
    // ei paljasta, onko käyttäjätunnus olemassa.
    $valid = password_verify($password, $user['password_hash'] ?? DUMMY_PASSWORD_HASH);

    if ($user === null || !$valid || !$user['is_active']) {
        record_failed_login($pdo, $username, $ip);
        auth_log('login_failed', $username);

        return LOGIN_FAILED;
    }

    clear_failed_logins($pdo, $username, $ip);
    if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, PASSWORD_BCRYPT_OPTIONS)) {
        upgrade_password_hash($pdo, $user['id'], $password);
    }

    login_user($pdo, $user);
    auth_log('login_ok', $username);

    return LOGIN_OK;
}

/** Aloittaa kirjautuneen istunnon: uusi istuntotunniste (istuntoon kiinnittämisen esto) ja tyhjä istuntodata. */
function login_user(PDO $pdo, array $user): void
{
    session_regenerate_id(true);
    $_SESSION = [
        'user_id'         => $user['id'],
        'session_version' => $user['session_version'],
        'login_at'        => time(),
        'last_activity'   => time(),
    ];
    $GLOBALS['auth_user'] = $user;
    record_login($pdo, $user['id']);
}
