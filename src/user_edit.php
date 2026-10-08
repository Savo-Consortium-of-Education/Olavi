<?php
require __DIR__ . '/lib/bootstrap.php';
require_permission('manage_users');

$self = current_user();

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$user = is_int($id) ? find_user_by_id($pdo, $id) : null;

if ($user === null) {
    render_error_page(404, 'Käyttäjää ei löytynyt', 'Pyydettyä käyttäjää ei ole olemassa.');
}

// Omaa tiliä ei muokata tällä sivulla: näin omistaja ei voi vahingossa poistaa itseltään oikeuksia tai lukita
// itseään ulos. Oman salasanan voi vaihtaa tilisivulla.
if ($user['id'] === $self['id']) {
    redirect('account.php');
}

$errors = [];
$form = ['display_name' => $user['display_name'], 'role' => $user['role'], 'is_active' => $user['is_active']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $form['display_name'] = trim(post_string('display_name'));
    $form['role'] = post_string('role');
    $form['is_active'] = post_string('is_active') === '1';
    $newPassword = post_string('new_password');

    $errors = array_values(array_filter([
        display_name_error($form['display_name']),
        role_error($form['role']),
        $newPassword !== '' ? password_policy_error($newPassword, $user['username']) : null,
        $newPassword !== post_string('new_password_confirm') ? 'Salasanat eivät täsmää.' : null,
    ]));

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            update_user($pdo, $user['id'], $form['display_name'], $form['role'], $form['is_active']);
            if ($newPassword !== '') {
                // Kasvattaa session_version-arvoa: käyttäjän avoimet istunnot päättyvät heti.
                set_user_password($pdo, $user['id'], $newPassword);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        auth_log(
            'user_updated target=' . $user['username'] . ' role=' . $form['role'] . ' active=' . (int) $form['is_active']
            . ($newPassword !== '' ? ' password_reset' : ''),
            $self['username']
        );
        flash_set('success', 'Käyttäjän ' . $user['username'] . ' tiedot tallennettu.');
        redirect('users.php', 303);
    }
}

render_header('Muokkaa käyttäjää', 'users');
?>
    <section class="panel" aria-labelledby="edit-heading">
        <h2 id="edit-heading">Käyttäjä <?= e($user['username']) ?></h2>

        <?php if ($errors): ?>
        <div class="alert alert--error" role="alert">
            <ul class="alert-list">
                <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="post" action="user_edit.php?id=<?= (int) $user['id'] ?>" class="form-grid">
            <?= csrf_field() ?>
            <div class="field">
                <label for="display_name">Nimi</label>
                <input type="text" id="display_name" name="display_name" value="<?= e($form['display_name']) ?>" maxlength="100" required>
            </div>

            <div class="field">
                <label for="role">Rooli</label>
                <select id="role" name="role" required>
                    <?php foreach (ROLE_LABELS as $value => $label): ?>
                    <option value="<?= e($value) ?>"<?= $form['role'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field field--wide">
                <label class="check">
                    <input type="checkbox" name="is_active" value="1"<?= $form['is_active'] ? ' checked' : '' ?>>
                    Tili on käytössä
                </label>
                <span class="form-hint">Käytöstä poistettu tili ei voi kirjautua sisään, ja sen avoimet istunnot päättyvät heti.</span>
            </div>

            <div class="field">
                <label for="new_password">Uusi salasana (valinnainen)</label>
                <input type="password" id="new_password" name="new_password" autocomplete="new-password">
                <span class="form-hint">Jätä tyhjäksi, jos salasana ei muutu. Muuttaminen päättää käyttäjän avoimet istunnot.</span>
            </div>

            <div class="field">
                <label for="new_password_confirm">Uusi salasana uudelleen</label>
                <input type="password" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password">
            </div>

            <div class="form-actions field--wide">
                <button type="submit" class="btn">Tallenna</button>
                <a class="btn btn--secondary" href="users.php">Peruuta</a>
            </div>
        </form>
    </section>
<?php
render_footer();
