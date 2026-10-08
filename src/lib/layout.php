<?php
/**
 * Jaettu sivupohja: yläpalkki, navigaatio ja alapalkki.
 * Rakenne noudattaa design/wireframe_allpages.svg-tiedostoa.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/accounts.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

const APP_NAME = 'Pienyrityksen Taloushallinto';

/**
 * Päävalikon kohteet: avain => [tiedosto, nimi, tarvittava oikeus].
 * Valikossa näkyvät vain ne kohteet, joihin kirjautuneella käyttäjällä on oikeus (ks. ROLE_PERMISSIONS).
 */
const NAV_ITEMS = [
    'home'    => ['index.php', 'Koti', 'view'],
    'add'     => ['add_transaction.php', 'Lisää tapahtuma', 'add_transaction'],
    'reports' => ['reports.php', 'Raportit', 'view'],
    'tax'     => ['tax_reports.php', 'Veroilmoitukset', 'view'],
    'users'   => ['users.php', 'Käyttäjät', 'manage_users'],
];

/**
 * Tulostaa sivun alun (head, yläpalkki, navigaatio ja pääotsikon).
 * Kirjautuneelle käyttäjälle näytetään käyttäjävalikko ja navigaatio; kirjautumissivulla vain logo.
 *
 * @param string $title  Sivun otsikko (näkyy välilehdellä ja h1-otsikkona)
 * @param string $active Aktiivisen valikkokohdan avain (ks. NAV_ITEMS) tai 'account'
 */
function render_header(string $title, string $active = ''): void
{
    $user = current_user();
    ?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <?php if ($user !== null): ?>
        <div class="user-menu">
            <span class="user-name"><?= e($user['display_name']) ?> <span class="badge badge--role"><?= e(ROLE_LABELS[$user['role']] ?? $user['role']) ?></span></span>
            <a href="account.php"<?= $active === 'account' ? ' aria-current="page"' : '' ?>>Oma tili</a>
            <form method="post" action="logout.php" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--secondary">Kirjaudu ulos</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <?php if ($user !== null): ?>
    <div class="container">
        <nav class="main-nav" aria-label="Päävalikko">
            <ul>
                <?php foreach (NAV_ITEMS as $key => [$file, $label, $permission]): ?>
                    <?php if (!user_can($permission)) { continue; } ?>
                <li><a href="<?= e($file) ?>"<?= $key === $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</header>

<main id="main" class="container">
    <h1><?= e($title) ?></h1>
<?php
    render_flash();
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

/** Tulostaa mahdollisen kertaluonteisen ilmoituksen (ks. flash_set). */
function render_flash(): void
{
    $flash = flash_pull();
    if ($flash === null) {
        return;
    }

    $isError = $flash['type'] === 'error';
    ?>
    <div class="alert alert--<?= $isError ? 'error' : 'success' ?>" role="<?= $isError ? 'alert' : 'status' ?>"><?= e($flash['message']) ?></div>
<?php
}

/**
 * Tulostaa virhesivun annetulla HTTP-tilakoodilla ja lopettaa suorituksen.
 * Viesti on aina kiinteä, sivu ei koskaan näytä sisäisiä virhetietoja.
 */
function render_error_page(int $status, string $title, string $message): never
{
    http_response_code($status);
    render_header($title);
    ?>
    <section class="panel">
        <p><?= e($message) ?></p>
        <p><a class="btn btn--secondary" href="index.php">Takaisin etusivulle</a></p>
    </section>
<?php
    render_footer();
    exit;
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
