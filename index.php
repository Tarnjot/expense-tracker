<?php

session_start();

require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$successMessage = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$stmt = $pdo->prepare("
    SELECT expenses.*, categories.name AS category_name
    FROM expenses
    JOIN categories ON expenses.category_id = categories.id
    WHERE expenses.user_id = ?
    ORDER BY expense_date DESC
");

$stmt->execute([$_SESSION['user']['id']]);
$expenses = $stmt->fetchAll();
?>

<h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h2>

<?php if ($successMessage): ?>
    <p style="color:green;"><?= htmlspecialchars($successMessage) ?></p>
<?php endif; ?>

<p>
    <a href="create_expense.php">+ Add New Expense</a> |
    <a href="manage_categories.php">Manage Categories</a> |
    <a href="report.php">📊 View Report</a> |
    <a href="logout.php">Logout</a>
</p>

<h3>Your Expenses</h3>

<?php if (count($expenses) > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Description</th>
        </tr>
        <?php foreach ($expenses as $expense): ?>
            <tr>
                <td><?= htmlspecialchars($expense['expense_date']) ?></td>
                <td><?= number_format($expense['amount'], 2) ?></td>
                <td><?= htmlspecialchars($expense['category_name']) ?></td>
                <td><?= htmlspecialchars($expense['description']) ?></td>
                <td>
                    <a href="editExpense.php?id=<?= $expense['id'] ?>">Edit</a> |
                    <a href="delete_expense.php?id=<?= $expense['id'] ?>" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p> No expenses yet. <a href="create_expense.php">add one now</a>.</p>
<?php endif; ?>

