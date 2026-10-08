<?php
/**
 * Yhteiset apufunktiot ja vakiot.
 */

/** Tapahtumatyyppien suomenkieliset nimet. */
const TYPE_LABELS = [
    'income'  => 'Tulo',
    'expense' => 'Meno',
];

/** Kategorioiden suomenkieliset nimet (sama lähde lomakkeelle ja listauksille). */
const CATEGORY_LABELS = [
    'income'          => 'Tulo',
    'general_expense' => 'Yleinen meno',
    'travel'          => 'Matkalasku',
    'phone_data'      => 'Puhelin ja tietoliikenne',
];

/**
 * Koodaa arvon turvallisesti HTML:ään.
 * Kaikki tietokannasta tai käyttäjältä tuleva teksti tulostetaan tämän kautta (XSS-suojaus).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Muotoilee summan suomalaiseen tapaan, esim. 5000 -> "5 000,00 €".
 * Kovat välilyönnit estävät summan katkeamisen kahdelle riville.
 */
function format_eur(float|int|string|null $amount): string
{
    return number_format((float) $amount, 2, ',', "\u{00A0}") . "\u{00A0}€";
}

/** Muotoilee ISO-päivämäärän (2023-01-15) suomalaiseen muotoon (15.1.2023). */
function format_date(string $isoDate): string
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $isoDate);

    return $date ? $date->format('j.n.Y') : $isoDate;
}

/**
 * Ohjaa selaimen toiseen osoitteeseen ja lopettaa suorituksen.
 * Käytä vain sovelluksen omia, kiinteitä osoitteita (ei käyttäjän antamia arvoja).
 */
function redirect(string $location, int $status = 302): never
{
    header('Location: ' . $location, true, $status);
    exit;
}

/** Palauttaa POST-kentän merkkijonona. Puuttuva tai taulukkomuotoinen arvo antaa tyhjän merkkijonon. */
function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? $value : '';
}

/**
 * Onko pyyntö tullut HTTPS-yhteydellä? Ympäristömuuttuja APP_FORCE_HTTPS=1 pakottaa arvon todeksi,
 * kun TLS päätetään sovelluksen edessä olevassa välityspalvelimessa.
 */
function is_https(): bool
{
    if (getenv('APP_FORCE_HTTPS') === '1') {
        return true;
    }

    return !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
}

/** Muotoilee Unix-aikaleiman suomalaiseksi päiväykseksi ja kellonajaksi Suomen aikavyöhykkeellä. */
function format_datetime(int $timestamp): string
{
    $time = (new DateTimeImmutable('@' . $timestamp))->setTimezone(new DateTimeZone('Europe/Helsinki'));

    return $time->format('j.n.Y G:i');
}

/** Tallentaa kertaluonteisen ilmoituksen, joka näytetään seuraavalla sivulatauksella (istuntoon). */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type === 'error' ? 'error' : 'success', 'message' => $message];
}

/** Palauttaa ja poistaa tallennetun ilmoituksen. */
function flash_pull(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}
