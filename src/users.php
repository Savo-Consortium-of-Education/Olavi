<?php
require __DIR__ . '/lib/bootstrap.php';
require_permission('manage_users');

$self = current_user();
$errors = [];
$form = ['username' => '', 'display_name' => '', 'role' => 'accountant'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $form['username'] = normalize_username(post_string('username'));
    $form['display_name'] = trim(post_string('display_name'));
    $form['role'] = post_string('role');
    $password = post_string('password');

    $errors = array_values(array_filter([
        username_error($form['username']),
        display_name_error($form['display_name']),
        role_error($form['role']),
        password_policy_error($password, $form['username']),
        $password !== post_string('password_confirm') ? 'Salasanat eivät täsmää.' : null,
    ]));

    if (!$errors) {
        try {
            create_user($pdo, $form['username'], $form['display_name'], $form['role'], $password);
            auth_log('user_created target=' . $form['username'] . ' role=' . $form['role'], $self['username']);
            flash_set('success', 'Käyttäjä ' . $form['username'] . ' luotu.');
            redirect('users.php', 303);
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') { // 23000 = UNIQUE-rajoite: tunnus on jo olemassa
                throw $e;
            }
            $errors[] = 'Käyttäjätunnus on jo käytössä.';
        }
    }
}

$users = list_users($pdo);

render_header('Käyttäjät', 'users');
?>
    <section class="panel" aria-labelledby="list-heading">
        <h2 id="list-heading">Käyttäjätilit</h2>
        <div class="table-wrap">
            <?php /* Pienellä näytöllä taulukko muuttuu korttilistaksi (ks. index.php ja style.css). */ ?>
            <table class="data data--stack" role="table">
                <thead>
                    <tr role="row">
                        <th scope="col" role="columnheader">Käyttäjä</th>
                        <th scope="col" role="columnheader">Rooli</th>
                        <th scope="col" role="columnheader">Tila</th>
                        <th scope="col" role="columnheader">Kirjautui viimeksi</th>
                        <th scope="col" role="columnheader">Toiminnot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $row): ?>
                    <tr role="row">
                        <td role="cell" class="cell-wrap" data-label="Käyttäjä">
                            <div class="user-cell">
                                <span class="user-cell__name"><?= e($row['display_name']) ?></span>
                                <span class="user-cell__login"><?= e($row['username']) ?></span>
                            </div>
                        </td>
                        <td role="cell" data-label="Rooli"><span class="badge badge--role"><?= e(ROLE_LABELS[$row['role']] ?? $row['role']) ?></span></td>
                        <td role="cell" data-label="Tila">
                            <?php if ($row['is_active']): ?>
                            <span class="badge badge--active">Käytössä</span>
                            <?php else: ?>
                            <span class="badge badge--inactive">Ei käytössä</span>
                            <?php endif; ?>
                        </td>
                        <td role="cell" data-label="Kirjautui viimeksi"><?= $row['last_login_ts'] !== null ? e(format_datetime($row['last_login_ts'])) : 'Ei vielä' ?></td>
                        <td role="cell" data-label="Toiminnot">
                            <?php if ($row['id'] === $self['id']): ?>
                            <a class="btn btn--secondary" href="account.php">Oma tili</a>
                            <?php else: ?>
                            <a class="btn btn--secondary" href="user_edit.php?id=<?= (int) $row['id'] ?>">Muokkaa</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel" aria-labelledby="create-heading">
        <h2 id="create-heading">Lisää käyttäjä</h2>

        <?php if ($errors): ?>
        <div class="alert alert--error" role="alert">
            <ul class="alert-list">
                <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="post" action="users.php" class="form-grid">
            <?= csrf_field() ?>
            <div class="field">
                <label for="username">Käyttäjätunnus</label>
                <input type="text" id="username" name="username" value="<?= e($form['username']) ?>" maxlength="50"
                       autocomplete="off" autocapitalize="none" spellcheck="false" required>
                <span class="form-hint">3–50 merkkiä: a–z, 0–9, . _ ja -</span>
            </div>

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
                <span class="form-hint">Omistaja hallitsee käyttäjiä; kirjanpitäjä käsittelee tapahtumia ja raportteja.</span>
            </div>

            <div class="field">
                <label for="password">Salasana</label>
                <input type="password" id="password" name="password" autocomplete="new-password" minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
                <span class="form-hint">Vähintään <?= PASSWORD_MIN_LENGTH ?> merkkiä. Käyttäjä voi vaihtaa sen omalla tilisivullaan.</span>
            </div>

            <div class="field">
                <label for="password_confirm">Salasana uudelleen</label>
                <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password" required>
            </div>

            <div class="form-actions field--wide">
                <button type="submit" class="btn">Lisää käyttäjä</button>
            </div>
        </form>
    </section>
<?php
render_footer();
