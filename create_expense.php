<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$amount = '';
$category_id = '';
$description = '';
$expense_date = date('Y-m-d');

$catStmt = $pdo->prepare("SELECT * FROM categories");
$catStmt->execute();
$categories = $catStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = trim($_POST['amount'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');

    if ($amount === '' || $category_id === '' || $expense_date === '') {
        $errors[] = 'Amount, category, and date are required.';
    }

    if (!is_numeric($amount)) {
        $errors[] = 'Amount must be a number.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO expenses (user_id, category_id, amount, description, expense_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user']['id'],
            $category_id,
            $amount,
            $description,
            $expense_date
        ]);

        $_SESSION['success'] = 'Expense Added!';
        header("Location: index.php");
        exit;
    }
}
?>

<h2>Add New Expense</h2>

<?php foreach ($errors as $error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Amount:</label><br>
    <input type="number" name="amount" value="<?= htmlspecialchars($amount) ?>" required><br><br>

    <label>Category:</label><br>
    <select name="category_id">
        <option value="">-- Select --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $category_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Description(optional):</label><br>
    <textarea name="description"><?= htmlspecialchars($description) ?></textarea><br><br>

    <label>Date:</label><br>
    <input type="date" name="expense_date" value="<?= htmlspecialchars($expense_date) ?>" required><br><br>

    <button type="submit">Add Expense</button>
</form>
    <a href="index.php">Go Back</a>