<?php
/**
 * Käyttäjien hallinta komentoriviltä: ensimmäisen omistajan luonti, salasanan palautus ja käyttäjälista.
 *
 * Skripti ei toimi selaimesta (PHP_SAPI-tarkistus), joten tunnuksia ei voi luoda verkon yli. Ilman sitä
 * sovellukseen ei pääse sisään ennen kuin ensimmäinen omistaja on luotu, eikä repossa ole oletussalasanaa.
 *
 * Käyttö (Docker Compose, projektin juuressa):
 *   docker compose exec web php cli/manage_users.php create --username=olavi --name="Olavi Esimerkki" --role=owner
 *   docker compose exec web php cli/manage_users.php reset-password --username=olavi
 *   docker compose exec web php cli/manage_users.php list
 *
 * Salasana kysytään piilotettuna kahdesti. Ilman päätettä (esim. skriptistä) sen voi antaa vakiosyötteestä:
 *   printf '%s\n' "$SALASANA" | docker compose exec -T web php cli/manage_users.php create ... --password-stdin
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config.php';
require __DIR__ . '/../lib/accounts.php';

/** Tulostaa virheen virhevirtaan ja lopettaa annetulla poistumiskoodilla. */
function cli_fail(string $message, int $code = 1): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

function cli_usage(): never
{
    cli_fail(
        "Käyttö:\n"
        . "  php cli/manage_users.php create --username=TUNNUS --role=owner|accountant [--name=\"Nimi\"] [--password-stdin]\n"
        . "  php cli/manage_users.php reset-password --username=TUNNUS [--password-stdin]\n"
        . "  php cli/manage_users.php list"
    );
}

/**
 * Jäsentää valitsimet muotoa --avain=arvo tai --lippu. Tuntematon valitsin keskeyttää ajon, jotta
 * kirjoitusvirhe (esim. --passwrd-stdin) ei jää huomaamatta.
 *
 * @param string[] $allowed Komennolle sallitut valitsimet
 */
function parse_cli_options(array $args, array $allowed): array
{
    $options = [];
    foreach ($args as $arg) {
        if (!preg_match('/^--([a-z-]+)(?:=(.*))?$/s', $arg, $match) || !in_array($match[1], $allowed, true)) {
            cli_fail("Tuntematon argumentti: $arg", 1);
        }
        $options[$match[1]] = $match[2] ?? true;
    }

    return $options;
}

/** Lukee rivin vakiosyötteestä; päätteessä syöte piilotetaan (stty), jotta salasana ei näy ruudulla. */
function read_line(string $prompt, bool $hidden): string
{
    $hide = $hidden && DIRECTORY_SEPARATOR === '/' && stream_isatty(STDIN);

    fwrite(STDERR, $prompt);
    if ($hide) {
        shell_exec('stty -echo');
    }
    $line = fgets(STDIN);
    if ($hide) {
        shell_exec('stty echo');
        fwrite(STDERR, PHP_EOL);
    }

    return $line === false ? '' : rtrim($line, "\r\n");
}

/** Hakee salasanan: vakiosyötteestä (--password-stdin) tai kysymällä kahdesti. */
function read_password(array $options): string
{
    if (isset($options['password-stdin'])) {
        return read_line('', false);
    }
    if (!stream_isatty(STDIN)) {
        cli_fail('Salasanaa ei voi kysyä ilman päätettä. Käytä valitsinta --password-stdin tai aja komento '
            . 'interaktiivisesti (docker compose exec ilman -T).');
    }

    $password = read_line('Salasana: ', true);
    if ($password !== read_line('Salasana uudelleen: ', true)) {
        cli_fail('Salasanat eivät täsmää.', 2);
    }

    return $password;
}

function cli_create_user(PDO $pdo, array $options): void
{
    $username = normalize_username((string) ($options['username'] ?? ''));
    $name = trim((string) ($options['name'] ?? ''));
    $role = (string) ($options['role'] ?? '');
    if ($name === '') {
        $name = $username;
    }

    $errors = array_filter([username_error($username), display_name_error($name), role_error($role)]);
    if ($errors) {
        cli_fail(implode(PHP_EOL, $errors), 2);
    }

    $password = read_password($options);
    $passwordError = password_policy_error($password, $username);
    if ($passwordError !== null) {
        cli_fail($passwordError, 2);
    }

    try {
        create_user($pdo, $username, $name, $role, $password);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            cli_fail("Käyttäjätunnus '$username' on jo käytössä.", 2);
        }
        throw $e;
    }

    echo "Käyttäjä '$username' luotu (rooli: " . ROLE_LABELS[$role] . ")." . PHP_EOL;
}

function cli_reset_password(PDO $pdo, array $options): void
{
    $username = normalize_username((string) ($options['username'] ?? ''));
    $user = find_user_with_hash($pdo, $username);
    if ($user === null) {
        cli_fail("Käyttäjää '$username' ei löytynyt.", 2);
    }

    $password = read_password($options);
    $passwordError = password_policy_error($password, $username);
    if ($passwordError !== null) {
        cli_fail($passwordError, 2);
    }

    // Palautus ottaa tilin myös käyttöön, jotta lukittu omistaja pääsee takaisin sisään.
    update_user($pdo, $user['id'], $user['display_name'], $user['role'], true);
    set_user_password($pdo, $user['id'], $password);

    echo "Käyttäjän '$username' salasana vaihdettu ja tili otettu käyttöön. Tilin avoimet istunnot on päätetty." . PHP_EOL;
}

function cli_list_users(PDO $pdo): void
{
    foreach (list_users($pdo) as $user) {
        printf(
            "%-20s %-14s %-10s %s\n",
            $user['username'],
            ROLE_LABELS[$user['role']] ?? $user['role'],
            $user['is_active'] ? 'käytössä' : 'poistettu',
            $user['display_name']
        );
    }
}

$command = $argv[1] ?? '';
$options = parse_cli_options(array_slice($argv, 2), match ($command) {
    'create'         => ['username', 'name', 'role', 'password-stdin'],
    'reset-password' => ['username', 'password-stdin'],
    'list'           => [],
    default          => cli_usage(),
});

try {
    match ($command) {
        'create'         => cli_create_user($pdo, $options),
        'reset-password' => cli_reset_password($pdo, $options),
        'list'           => cli_list_users($pdo),
        default          => cli_usage(),
    };
} catch (PDOException $e) {
    if ($e->getCode() === '42S02') { // SQLSTATE 42S02: taulua ei ole
        cli_fail('Käyttäjätaulua ei löydy. Päivitä tietokanta ensin README.md:n ohjeen mukaan '
            . '(kohta "Päivitys olemassa olevaan asennukseen").', 3);
    }
    throw $e;
}
