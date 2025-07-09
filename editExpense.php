<?php

session_start();

require_once 'include/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ? AND user_ id = ?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$expense = $stmt->fetch();

if (!expense) {
    $_SESSION['error'] = "Expense not found or access denied.";
    header("Location: index.php");
    exit;
}

$catstmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ?");
$catstmt->execute([$_SESSION['user']['id']]);
$categories = $catstmt->fetchAll();

?>

<h2>Edit Expense</h2>

<?php if ($isset($_SESSION['error'])): ?>
    <p style="color:red"><?= $_SESSION['error'] ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST">
    <label>Amount:</label>
    <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($expense['amount']) ?>" required><br>

    <label>Category:</label>
    <select name="category_id" required>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $expense['category_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    
    <label>Description:</label>
    <input type="text" name="description" value="<?= htmlspecialchars($expense['description']) ?>"><br>

    <label>Created At:</label>
    <input type="date" name="created_at" value="<?= htmlspecialchars($expense['date']) ?>" required><br>

    <button type="submit" name="update">Update Expense</button>
</form>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $amount = $_POST['amount'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $created_at = $_POST['created_at'];

    if ($amount <= 0 || empty($date)) {
        $_SESSION['error'] = "Amount must be greater than zero and date is required.";
    } else {
        $update = $pdo->prepare("UPDATE expense SET amount = ?, category_id = ?, description = ?, created_at = ? WHERE id = ? AND user_id = ?");
        $updated = $update->execute([$amount, $category_id, $description, $created_at, $id, $_SESSION['user']['id']]);

        if ($updated) {
            $_SESSION['success'] = "Expense updated successfully!";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update expense.";
        }
    }
}