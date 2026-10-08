<?php
require __DIR__ . '/lib/bootstrap.php';
require_permission('view');

$message = '';

if (isset($_GET['export'])) {
    require_permission('export');

    $type = $_GET['export'];
    $filename = ($type == 'vat') ? 'alv_ilmoitus.csv' : 'veroilmoitus.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV headers
    fputcsv($output, ['Päivämäärä', 'Tyyppi', 'Kategoria', 'Kuvaus', 'Summa', 'ALV-prosentti', 'ALV-summa']);

    $stmt = $pdo->query("SELECT * FROM transactions ORDER BY date");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['date'],
            $row['type'] == 'income' ? 'Tulo' : 'Meno',
            ucfirst(str_replace('_', ' ', $row['category'])),
            $row['description'],
            $row['amount'],
            $row['vat_rate'],
            $row['vat_amount']
        ]);
    }

    fclose($output);
    exit;
}

// VAT summary
$stmt = $pdo->query("SELECT SUM(vat_amount) as total_vat FROM transactions WHERE type='income'");
$vat_payable = $stmt->fetch(PDO::FETCH_ASSOC)['total_vat'] ?? 0;

$stmt = $pdo->query("SELECT SUM(vat_amount) as total_vat FROM transactions WHERE type='expense'");
$vat_deductible = $stmt->fetch(PDO::FETCH_ASSOC)['total_vat'] ?? 0;

$vat_balance = $vat_payable - $vat_deductible;

// Tax summary
$stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type='income'");
$total_income = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type='expense'");
$total_expense = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$taxable_income = $total_income - $total_expense;

render_header('Veroilmoitukset', 'tax');
?>
    <section class="panel" aria-labelledby="vat-heading">
        <h2 id="vat-heading">ALV-ilmoitus</h2>
        <div class="cards">
            <?php
            render_stat_card('ALV maksettava', format_eur($vat_payable));
            render_stat_card('ALV vähennettävä', format_eur($vat_deductible));
            render_stat_card('ALV-saldo', format_eur($vat_balance), $vat_balance < 0 ? 'negative' : '');
            ?>
        </div>
    </section>

    <section class="panel" aria-labelledby="tax-heading">
        <h2 id="tax-heading">Veroilmoitus</h2>
        <div class="cards">
            <?php
            render_stat_card('Kokonais tulot', format_eur($total_income));
            render_stat_card('Kokonais menot', format_eur($total_expense));
            render_stat_card('Verotettava tulo', format_eur($taxable_income), $taxable_income < 0 ? 'negative' : '');
            ?>
        </div>
    </section>

    <?php if (user_can('export')): ?>
    <section class="panel" aria-labelledby="export-heading">
        <h2 id="export-heading">CSV-viennit</h2>
        <p class="muted">Lataa tapahtumat CSV-tiedostona verottajalle toimitettavaksi.</p>
        <div class="button-row">
            <a class="btn" href="?export=vat">Vie ALV-ilmoitus CSV:ään</a>
            <a class="btn" href="?export=tax">Vie veroilmoitus CSV:ään</a>
        </div>
    </section>
    <?php endif; ?>
<?php
render_footer();
