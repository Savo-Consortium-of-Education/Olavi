<?php
require __DIR__ . '/lib/bootstrap.php';
// Jokainen kirjautunut käyttäjä saa vaihtaa oman salasanansa, joten erillistä oikeutta ei vaadita.

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $current = post_string('current_password');
    $new = post_string('new_password');
    $confirm = post_string('new_password_confirm');
    $ip = client_ip();

    if (login_is_throttled($pdo, $user['username'], $ip)) {
        // Sama rajoitus kuin kirjautumisessa: varastetulla istunnolla ei voi arvailla nykyistä salasanaa.
        http_response_code(429);
        $errors[] = 'Liian monta epäonnistunutta yritystä. Yritä uudelleen myöhemmin.';
    } else {
        $row = find_user_with_hash($pdo, $user['username']);

        if ($row === null || !password_verify($current, $row['password_hash'])) {
            record_failed_login($pdo, $user['username'], $ip);
            auth_log('password_change_failed', $user['username']);
            $errors[] = 'Nykyinen salasana on väärin.';
        } else {
            $policyError = password_policy_error($new, $user['username']);
            if ($policyError !== null) {
                $errors[] = $policyError;
            }
            if ($new !== $confirm) {
                $errors[] = 'Uudet salasanat eivät täsmää.';
            }
            if ($new === $current) {
                $errors[] = 'Uuden salasanan on erottava nykyisestä.';
            }

            if (!$errors) {
                // Muut tilin istunnot (esim. toinen laite) päättyvät, tämä jatkuu uudella istuntotunnisteella.
                $_SESSION['session_version'] = set_user_password($pdo, $user['id'], $new);
                session_regenerate_id(true);
                clear_failed_logins($pdo, $user['username'], $ip);
                auth_log('password_changed', $user['username']);
                flash_set('success', 'Salasana vaihdettu.');
                redirect('account.php', 303);
            }
        }
    }
}

render_header('Oma tili', 'account');
?>
    <section class="panel" aria-labelledby="info-heading">
        <h2 id="info-heading">Tilin tiedot</h2>
        <dl class="card-details">
            <div><dt>Käyttäjätunnus</dt><dd><?= e($user['username']) ?></dd></div>
            <div><dt>Nimi</dt><dd><?= e($user['display_name']) ?></dd></div>
            <div><dt>Rooli</dt><dd><?= e(ROLE_LABELS[$user['role']] ?? $user['role']) ?></dd></div>
        </dl>
    </section>

    <section class="panel" aria-labelledby="password-heading">
        <h2 id="password-heading">Vaihda salasana</h2>

        <?php if ($errors): ?>
        <div class="alert alert--error" role="alert">
            <ul class="alert-list">
                <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="post" action="account.php" class="form-grid">
            <?= csrf_field() ?>
            <div class="field field--wide">
                <label for="current_password">Nykyinen salasana</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
            </div>

            <div class="field">
                <label for="new_password">Uusi salasana</label>
                <input type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
            </div>

            <div class="field">
                <label for="new_password_confirm">Uusi salasana uudelleen</label>
                <input type="password" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password" required>
            </div>

            <p class="form-hint field--wide">Salasanan pituus on vähintään <?= PASSWORD_MIN_LENGTH ?> merkkiä (enintään <?= PASSWORD_MAX_BYTES ?> tavua). Käytä salasanaa, jota et käytä muualla.</p>

            <div class="form-actions field--wide">
                <button type="submit" class="btn">Vaihda salasana</button>
            </div>
        </form>
    </section>
<?php
render_footer();
