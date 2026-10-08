<?php
require 'config.php';

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
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Lisää tapahtuma</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 400px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 5px; }
        button { margin-top: 15px; padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .message { color: green; }
    </style>
</head>
<body>
    <h1>Lisää tapahtuma</h1>
    <a href="index.php">Takaisin kotiin</a>
    <br><br>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>
    <form method="post">
        <label>Päivämäärä:</label>
        <input type="date" name="date" required>

        <label>Tyyppi:</label>
        <select name="type" required>
            <option value="income">Tulo</option>
            <option value="expense">Meno</option>
        </select>

        <label>Kategoria:</label>
        <select name="category" required>
            <option value="income">Tulo</option>
            <option value="general_expense">Yleinen meno</option>
            <option value="travel">Matkalasku</option>
            <option value="phone_data">Puhelin ja tietoliikenne</option>
        </select>

        <label>Kuvaus:</label>
        <input type="text" name="description" required>

        <label>Summa (€):</label>
        <input type="number" step="0.01" name="amount" required>

        <label>ALV-prosentti:</label>
        <input type="number" step="0.01" name="vat_rate" value="24">

        <button type="submit">Lisää tapahtuma</button>
    </form>
</body>
</html>