<?php
session_start();
require_once 'includes/db.php';


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM savings WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$saving = $stmt->fetch();

if (!$saving) {
    $_SESSION['error'] = "Saving not found or access denied.";
    header("Location: view_savings.php");
    exit;
}
?>

<h1>Editing Saving</h1>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color:red"><?= $_SESSION['error'] ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST">
    <label>Title</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($saving['title']) ?>" required><br>

    <label>Target Amount</label><br>
    <input type="number" name="target_amount" min="0.1" step="0.01" value="<?= htmlspecialchars($saving['target_amount']) ?>" required><br>

    <label>Saved Amount</label><br>
    <input type="number" name="saved_amount" value="<?= htmlspecialchars($saving['saved_amount']) ?>" readonly><br>

    <label>Created At</label><br>
    <input type="date" name="created_at" value="<?= date('Y-m-d', strtotime($saving['created_at'])) ?>"><br>

    <button type="submit" name="update">Update</button>
</form>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $target_amount = $_POST['target_amount'];
    $saved_amount = $_POST['saved_amount'];
    $created_at = $_POST['created_at'];

    if (empty($title) || empty($target_amount)) {
        $_SESSION['error'] = "Title or target amount cannot be empty.";
    } else {
        $update = $pdo->prepare("UPDATE savings SET title = ?, target_amount = ?, created_at = ? WHERE id = ? AND user_id = ?");
        $updated = $update->execute([$title, $target_amount, $created_at, $id, $_SESSION['user']['id']]);

        if ($updated) {
            $_SESSION['success'] = "Saving updated successfully";
            header("Location: view_savings.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update saving.";
        }
    }
}