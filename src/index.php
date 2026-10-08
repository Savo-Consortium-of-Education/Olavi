<?php
require 'config.php';
require __DIR__ . '/lib/layout.php';

// Yhteenvetokortit (wireframe: Koti)
$summary = $pdo->query(
    "SELECT
        COALESCE(SUM(CASE WHEN type='income'  THEN amount END), 0)     AS total_income,
        COALESCE(SUM(CASE WHEN type='expense' THEN amount END), 0)     AS total_expense,
        COALESCE(SUM(CASE WHEN type='income'  THEN vat_amount END), 0) AS vat_payable,
        COALESCE(SUM(CASE WHEN type='expense' THEN vat_amount END), 0) AS vat_deductible
     FROM transactions"
)->fetch(PDO::FETCH_ASSOC);
$vat_balance = $summary['vat_payable'] - $summary['vat_deductible'];

// Viimeisimmät tapahtumat
$transactions = $pdo->query("SELECT * FROM transactions ORDER BY date DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

render_header('Koti', 'home');
?>
    <section class="cards" aria-label="Yhteenveto">
        <?php
        render_stat_card('Kokonais tulot', format_eur($summary['total_income']));
        render_stat_card('Kokonais menot', format_eur($summary['total_expense']));
        render_stat_card('ALV-saldo', format_eur($vat_balance), $vat_balance < 0 ? 'negative' : '');
        ?>
    </section>

    <section class="panel" aria-labelledby="recent-heading">
        <h2 id="recent-heading">Viimeisimmät tapahtumat</h2>
        <?php if (!$transactions): ?>
            <p class="muted">Ei tapahtumia vielä. <a href="add_transaction.php">Lisää ensimmäinen tapahtuma</a>.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th scope="col">Päivämäärä</th>
                        <th scope="col">Tyyppi</th>
                        <th scope="col">Kategoria</th>
                        <th scope="col">Kuvaus</th>
                        <th scope="col" class="num">Summa</th>
                        <th scope="col" class="num">ALV</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $row): ?>
                    <tr>
                        <td><?= e(format_date($row['date'])) ?></td>
                        <td><span class="badge badge--<?= e($row['type']) ?>"><?= e(TYPE_LABELS[$row['type']] ?? $row['type']) ?></span></td>
                        <td><?= e(CATEGORY_LABELS[$row['category']] ?? $row['category']) ?></td>
                        <td><?= e($row['description']) ?></td>
                        <td class="num"><?= e(format_eur($row['amount'])) ?></td>
                        <td class="num"><?= e(format_eur($row['vat_amount'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
<?php
render_footer();
