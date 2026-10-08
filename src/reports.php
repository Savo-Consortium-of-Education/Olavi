<?php
require 'config.php';

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
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Raportit</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Raportit</h1>
    <a href="index.php">Takaisin kotiin</a>

    <h2>Yrityksen kannattavuus</h2>
    <p>Kokonais tulot: <?php echo number_format($total_income, 2); ?> €</p>
    <p>Kokonais menot: <?php echo number_format($total_expense, 2); ?> €</p>
    <p>Voittomarginaali: <?php echo number_format($profit, 2); ?> €</p>

    <h2>Kvartaaliraportit</h2>
    <table>
        <tr>
            <th>Kvartaali</th>
            <th>Tulot</th>
            <th>Menot</th>
            <th>Voittomarginaali</th>
        </tr>
        <?php foreach ($quarters as $q => $data): ?>
        <tr>
            <td>Q<?php echo $q; ?></td>
            <td><?php echo number_format($data['income'] ?? 0, 2); ?> €</td>
            <td><?php echo number_format($data['expense'] ?? 0, 2); ?> €</td>
            <td><?php echo number_format(($data['income'] ?? 0) - ($data['expense'] ?? 0), 2); ?> €</td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>