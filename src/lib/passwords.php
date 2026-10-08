<?php
/**
 * Salasanojen käytäntö ja tiivistäminen.
 *
 * Salasanaa ei tallenneta eikä kirjata lokiin selkotekstinä: tietokantaan menee vain bcrypt-tiiviste, jonka
 * kustannus (PASSWORD_BCRYPT_OPTIONS) tekee arvaamisesta hidasta. bcrypt käsittelee enintään 72 tavua, joten
 * pidempiä salasanoja ei sallita: muuten loppuosa jäisi huomiotta ilman että käyttäjä tietää sen.
 */

const PASSWORD_MIN_LENGTH = 10;
const PASSWORD_MAX_BYTES = 72;
const PASSWORD_BCRYPT_OPTIONS = ['cost' => 12];

/**
 * Valmiiksi laskettu tiiviste (sama kustannus kuin oikeilla tunnuksilla). Tuntemattoman käyttäjätunnuksen
 * kohdalla salasana tarkistetaan tätä vasten, jotta vastausaika ei paljasta, onko tunnus olemassa.
 */
const DUMMY_PASSWORD_HASH = '$2y$12$iSlvN8qZKmC/w99.BcA6/ueeFIApGofBnQ35PNhfoPwS.FL40t/zG';

/** Palauttaa salasanan bcrypt-tiivisteen. */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, PASSWORD_BCRYPT_OPTIONS);
}

/**
 * Tarkistaa uuden salasanan käytännön mukaisuuden.
 *
 * @return string|null Virheilmoitus tai null, jos salasana kelpaa
 */
function password_policy_error(string $password, string $username = ''): ?string
{
    if (str_contains($password, "\0")) {
        return 'Salasana sisältää kiellettyjä merkkejä.';
    }
    if (mb_strlen($password) < PASSWORD_MIN_LENGTH) {
        return 'Salasanan on oltava vähintään ' . PASSWORD_MIN_LENGTH . ' merkkiä pitkä.';
    }
    if (strlen($password) > PASSWORD_MAX_BYTES) {
        return 'Salasana on liian pitkä (enintään ' . PASSWORD_MAX_BYTES . ' tavua; ääkköset vievät kaksi tavua).';
    }
    if ($username !== '' && mb_strtolower($password) === mb_strtolower($username)) {
        return 'Salasana ei saa olla sama kuin käyttäjätunnus.';
    }

    return null;
}
