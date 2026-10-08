<?php
// Kirjautumissivu on ainoa sivu, joka on avoin kirjautumattomille (ks. lib/bootstrap.php).
define('PUBLIC_PAGE', true);
require __DIR__ . '/lib/bootstrap.php';

if (!auth_schema_ready($pdo)) {
    render_error_page(
        503,
        'Tietokanta vaatii päivityksen',
        'Kirjautumisen tietokantataulut puuttuvat. Päivitä tietokanta README.md:n ohjeen mukaan (kohta "Päivitys olemassa olevaan asennukseen").'
    );
}

if (current_user() !== null) {
    redirect('index.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = normalize_username(post_string('username'));
    $result = attempt_login($pdo, $username, post_string('password'));

    if ($result === LOGIN_OK) {
        redirect('index.php', 303);
    }

    if ($result === LOGIN_THROTTLED) {
        http_response_code(429);
        header('Retry-After: ' . LOGIN_WINDOW_SECONDS);
        $error = 'Liian monta epäonnistunutta kirjautumisyritystä. Yritä uudelleen myöhemmin.';
    } else {
        // Sama viesti tuntemattomalle tunnukselle, väärälle salasanalle ja poistetulle tilille.
        $error = 'Väärä käyttäjätunnus tai salasana.';
    }
}

render_header('Kirjaudu sisään');
?>
    <?php if ($error !== ''): ?>
    <div class="alert alert--error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="panel auth-card" aria-label="Kirjautumislomake">
        <form method="post" action="login.php" class="form-stack">
            <?= csrf_field() ?>
            <div class="field">
                <label for="username">Käyttäjätunnus</label>
                <input type="text" id="username" name="username" value="<?= e($username) ?>" maxlength="50"
                       autocomplete="username" autocapitalize="none" spellcheck="false" required>
            </div>

            <div class="field">
                <label for="password">Salasana</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Kirjaudu sisään</button>
            </div>
        </form>
        <p class="muted">Tunnukset luo sovelluksen omistaja.</p>
    </section>
<?php
render_footer();
