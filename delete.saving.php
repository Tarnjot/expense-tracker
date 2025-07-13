<?php
require_once 'includes/db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: view_savings.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid delete request.";
    header('Location: view_savings.php');
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['error'] = "Invalid saving ID.";
    header('Location: view_savings.php');
    exit;
}

$saving_id = (int) $_POST['id'];
$user_id = $_SESSION['user_id'];

try {

    $stmt = $pdo->prepare("SELECT id FROM savings WHERE id = ? AND user_id = ?");
    $stmt->execute([$saving_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Saving entry not found or not yours.";
        header('Location: view_savings.php');
        exit;
    }

    $del = $pdo->prepare("DELETE FROM savings WHERE id = ? AND user_id = ?");
    $del->execute([$saving_id, $user_id]);

    $_SESSION['success'] = "Saving entry deleted successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleteing entry.";
}

header("Location: view_savings.php");
exit;


