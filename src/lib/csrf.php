<?php
/**
 * CSRF-suojaus: jokaisella istunnolla on oma satunnainen tunniste, joka liitetään lomakkeisiin piilokenttänä
 * ja tarkistetaan palvelimella kaikissa tilaa muuttavissa (POST) pyynnöissä. Ulkopuolinen sivu ei voi arvata
 * tunnistetta, joten se ei voi lähettää lomaketta käyttäjän puolesta, vaikka selain liittäisi evästeen mukaan.
 * Evästeen SameSite=Lax-asetus on lisäsuoja, mutta ei korvaa tunnistetta.
 */

/** Istunnon CSRF-tunniste (luodaan tarvittaessa). */
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/** Piilokenttä, joka lisätään jokaiseen POST-lomakkeeseen. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Tarkistaa POST-pyynnön tunnisteen. Puuttuva tai väärä tunniste keskeyttää pyynnön (403) ennen mitään muutoksia. */
function csrf_verify(): void
{
    $sent = post_string('csrf_token');

    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        auth_log('csrf_failed');
        render_error_page(
            403,
            'Lomaketta ei voitu vahvistaa',
            'Lomakkeen suojaustunniste puuttuu tai on vanhentunut. Lataa sivu uudelleen ja yritä uudestaan.'
        );
    }
}
