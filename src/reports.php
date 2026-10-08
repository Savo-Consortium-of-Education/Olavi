<?php
require 'config.php';
require __DIR__ . '/lib/layout.php';

// Profitability
$stmt = $pdo->query("SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as total_expense FROM transactions");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_income = $row['total_income'] ?? 0;
$total_expense = $row['total_expense'] ?? 0;
$profit = $total_income - $total_expense;

// Quarterly reports
$quarters = [];
for ($q = 1; $q <= 4; $q++) {
    $start_month = ($q - 1) * 3 + 1;
    $end_month = $q * 3;
    $stmt = $pdo->prepare("SELECT
        SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income,
        SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense
        FROM transactions
        WHERE MONTH(date) BETWEEN ? AND ?");
    $stmt->execute([$start_month, $end_month]);
    $quarters[$q] = $stmt->fetch(PDO::FETCH_ASSOC);
}

render_header('Raportit', 'reports');
?>
    <h2 class="section-title">Kvartaaliraportit</h2>
    <section class="cards" aria-label="Kvartaaliraportit">
        <?php foreach ($quarters as $q => $data):
            $income = $data['income'] ?? 0;
            $expense = $data['expense'] ?? 0;
        ?>
        <article class="card">
            <h3 class="card-label">Q<?= e($q) ?></h3>
            <dl class="card-details">
                <div><dt>Tulot</dt><dd><?= e(format_eur($income)) ?></dd></div>
                <div><dt>Menot</dt><dd><?= e(format_eur($expense)) ?></dd></div>
                <div><dt>Voittomarginaali</dt><dd><?= e(format_eur($income - $expense)) ?></dd></div>
            </dl>
        </article>
        <?php endforeach; ?>
    </section>

    <section class="panel" aria-labelledby="profit-heading">
        <h2 id="profit-heading">Yrityksen kannattavuus</h2>
        <div class="cards">
            <?php
            render_stat_card('Kokonais tulot', format_eur($total_income));
            render_stat_card('Kokonais menot', format_eur($total_expense));
            render_stat_card('Voittomarginaali', format_eur($profit), $profit < 0 ? 'negative' : '');
            ?>
        </div>
    </section>
<?php
render_footer();
