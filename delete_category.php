<?php

session_start();

require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? '';

if (!is_numeric($id)) {
    $_SESSION['success'] = 'Invalid category ID.';
    header("Location: manage_categories.php");
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE category_id = ?");
$stmt->execute([$id]);

if ($stmt->fetchColumn() > 0) {
    $_SESSION['error'] = 'Cannot delete: Category is in use by one or more expenses';
    header("Location: manage_categories.php");
    exit;
}

$delStmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$delStmt->execute([$id]);

$_SESSION['success'] = 'Category deleted successfully.';
header("Location: manage_categories.php");
exit;