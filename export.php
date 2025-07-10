<?php

session_start();

require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

$from = $_GET['from'] ?? date ('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-t');

header('Content-Type: text/csv');

$filename = "expense_report_{$from}_to_{$to}.csv";
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen('php://output', 'w');

fputcsv($output, ['Category', 'Total Spent (£)']);

$stmt = $pdo->prepare("
    SELECT categories.name AS category, SUM(expenses.amount) AS total
    FROM expenses
    JOIN categories ON expenses.category_id = categories.id
    WHERE expenses.user_id = ? AND expense_date BETWEEN ? AND ?
    GROUP BY categories.id
    ORDER BY total DESC
");

$stmt->execute([$userId, $from, $to]);
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    fputcsv($output, [$row['category'], number_format($row['total'], 2)]);
}

fclose($output);
exit;
?>