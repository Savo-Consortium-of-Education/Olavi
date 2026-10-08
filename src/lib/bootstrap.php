<?php
/**
 * Yhteinen alustus kaikille sivuille: tietoturvaotsikot, tietokantayhteys, istunto ja kirjautumisvaatimus.
 *
 * Jokainen sivu lataa tämän tiedoston ensimmäisenä. Kirjautuminen vaaditaan OLETUKSENA: sivu on julkinen vain,
 * jos se määrittelee vakion PUBLIC_PAGE ennen tämän tiedoston lataamista (vain login.php tekee niin).
 * Uusi sivu on siis suojattu, vaikka tekijä unohtaisi erillisen tarkistuksen. Tarkistus tehdään ennen sivun
 * omaa koodia, joten kirjautumaton käyttäjä ei saa suoritettua mitään (esim. lomakkeen tallennusta).
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';

/** Lähettää tietoturvaotsikot jokaisessa vastauksessa. */
function send_security_headers(): void
{
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    // Sivut käyttävät vain omia tyylitiedostoja ja kuvia: skriptit, upotukset ja ulkopuoliset lataukset estetään.
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
    // Kirjautuneen käyttäjän sivuja ei tallenneta välimuistiin (esim. "Takaisin"-painike uloskirjautumisen jälkeen).
    header('Cache-Control: no-store');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

send_security_headers();

// Ilman istuntoevästettä käyttäjä ei voi olla kirjautunut: ohjataan suoraan, jotta tunnistamattomista
// pyynnöistä ei synny turhia istuntoja palvelimelle.
if (!defined('PUBLIC_PAGE') && !isset($_COOKIE[SESSION_NAME])) {
    redirect('login.php');
}

start_secure_session();

if (defined('PUBLIC_PAGE')) {
    $GLOBALS['auth_user'] = load_session_user($pdo);
} else {
    require_login($pdo);
}
