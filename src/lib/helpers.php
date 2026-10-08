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
