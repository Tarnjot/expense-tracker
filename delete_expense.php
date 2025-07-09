<?php

session_start();

require_once 'includes/db.php';

if (!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Expense ID missing.";
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$expense = $stmt->fetch();

if (!$expense) {
    $_SESSION['error'] = "Expense not found or access denied.";
    header("Location: index.php");
    exit;
}

$delete = $pdo->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
$deleted = $delete->execute([$id, $_SESSION['user']['id']]);

if ($deleted) {
    $_SESSION['success'] = "Expense deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete expense.";
}

header("Location: index.php");
exit;