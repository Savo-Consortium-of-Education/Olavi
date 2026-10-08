<?php
require 'config.php';
require __DIR__ . '/lib/layout.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $vat_rate = $_POST['vat_rate'];
    $vat_amount = ($amount * $vat_rate / 100) / (1 + $vat_rate / 100); // Calculate VAT amount

    $stmt = $pdo->prepare("INSERT INTO transactions (date, type, category, description, amount, vat_rate, vat_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $type, $category, $description, $amount, $vat_rate, $vat_amount]);

    $message = 'Tapahtuma lisätty onnistuneesti!';
}

render_header('Lisää tapahtuma', 'add');
?>
    <?php if ($message): ?>
    <div class="alert alert--success" role="status"><?= e($message) ?></div>
    <?php endif; ?>

    <section class="panel" aria-labelledby="form-heading">
        <h2 id="form-heading">Uusi tapahtuma</h2>
        <form method="post" class="form-grid">
            <div class="field">
                <label for="date">Päivämäärä</label>
                <input type="date" id="date" name="date" required>
            </div>

            <div class="field">
                <label for="type">Tyyppi</label>
                <select id="type" name="type" required>
                    <?php foreach (TYPE_LABELS as $value => $label): ?>
                    <option value="<?= e($value) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="category">Kategoria</label>
                <select id="category" name="category" required>
                    <?php foreach (CATEGORY_LABELS as $value => $label): ?>
                    <option value="<?= e($value) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="amount">Summa (€)</label>
                <input type="number" step="0.01" id="amount" name="amount" required>
            </div>

            <div class="field field--wide">
                <label for="description">Kuvaus</label>
                <input type="text" id="description" name="description" required>
            </div>

            <div class="field">
                <label for="vat_rate">ALV-prosentti</label>
                <input type="number" step="0.01" id="vat_rate" name="vat_rate" value="24">
            </div>

            <div class="form-actions field--wide">
                <button type="submit" class="btn">Tallenna</button>
            </div>
        </form>
    </section>
<?php
render_footer();
