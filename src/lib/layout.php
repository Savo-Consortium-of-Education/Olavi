<?php
/**
 * Jaettu sivupohja: yläpalkki, navigaatio ja alapalkki.
 * Rakenne noudattaa design/wireframe_allpages.svg-tiedostoa.
 */

require_once __DIR__ . '/helpers.php';

const APP_NAME = 'Pienyrityksen Taloushallinto';

/** Päävalikon kohteet: avain => [tiedosto, nimi]. */
const NAV_ITEMS = [
    'home'    => ['index.php', 'Koti'],
    'add'     => ['add_transaction.php', 'Lisää tapahtuma'],
    'reports' => ['reports.php', 'Raportit'],
    'tax'     => ['tax_reports.php', 'Veroilmoitukset'],
];

/**
 * Tulostaa sivun alun (head, yläpalkki, navigaatio ja pääotsikon).
 *
 * @param string $title  Sivun otsikko (näkyy välilehdellä ja h1-otsikkona)
 * @param string $active Aktiivisen valikkokohdan avain (ks. NAV_ITEMS)
 */
function render_header(string $title, string $active = ''): void
{
    ?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="assets/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<a class="skip-link" href="#main">Siirry sisältöön</a>

<header class="site-header">
    <div class="container header-bar">
        <a class="brand" href="index.php">
            <img class="brand-logo" src="assets/logo.svg" alt="" width="48" height="48">
            <span class="brand-name"><?= e(APP_NAME) ?></span>
        </a>
    </div>
    <div class="container">
        <nav class="main-nav" aria-label="Päävalikko">
            <ul>
                <?php foreach (NAV_ITEMS as $key => [$file, $label]): ?>
                <li><a href="<?= e($file) ?>"<?= $key === $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>

<main id="main" class="container">
    <h1><?= e($title) ?></h1>
<?php
}

/** Tulostaa sivun lopun (sulkee main-alueen ja tulostaa alapalkin). */
function render_footer(): void
{
    ?>
</main>

<footer class="site-footer">
    <div class="container"><?= e(APP_NAME) ?></div>
</footer>
</body>
</html>
<?php
}

/**
 * Tulostaa tunnuslukukortin (label + arvo).
 *
 * @param string $modifier Valinnainen lisäluokka arvolle, esim. 'negative'
 */
function render_stat_card(string $label, string $value, string $modifier = ''): void
{
    $class = 'card-value' . ($modifier !== '' ? ' card-value--' . $modifier : '');
    ?>
<div class="card">
    <div class="card-label"><?= e($label) ?></div>
    <div class="<?= e($class) ?>"><?= e($value) ?></div>
</div>
<?php
}
