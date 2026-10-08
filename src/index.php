<?php
require 'config.php';
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Pienyrityksen Taloushallinto</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { margin-bottom: 20px; }
        nav a { margin-right: 15px; text-decoration: none; padding: 5px 10px; background: #f0f0f0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Pienyrityksen Taloushallinto</h1>
    <nav>
        <a href="index.php">Koti</a>
        <a href="add_transaction.php">Lisää tapahtuma</a>
        <a href="reports.php">Raportit</a>
        <a href="tax_reports.php">Veroilmoitukset</a>
    </nav>

    <h2>Viimeisimmät tapahtumat</h2>
    <table>
        <tr>
            <th>Päivämäärä</th>
            <th>Tyyppi</th>
            <th>Kategoria</th>
            <th>Kuvaus</th>
            <th>Summa</th>
            <th>ALV</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM transactions ORDER BY date DESC LIMIT 10");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['date'] . "</td>";
            echo "<td>" . ($row['type'] == 'income' ? 'Tulo' : 'Meno') . "</td>";
            echo "<td>" . ucfirst(str_replace('_', ' ', $row['category'])) . "</td>";
            echo "<td>" . $row['description'] . "</td>";
            echo "<td>" . number_format($row['amount'], 2) . " €</td>";
            echo "<td>" . number_format($row['vat_amount'], 2) . " €</td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>
