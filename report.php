<?php

session_start();

require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');

$from = $_GET['from'] ?? $startOfMonth;
$to = $_GET['to'] ?? $endOfMonth;

$totalstmt = $pdo->prepare("
    SELECT SUM(amount) as total
    FROM expenses
    WHERE user_id = ? AND expense_date BETWEEN ? AND ?
    ");

$totalstmt->execute([$userId, $from, $to]);
$total = $totalstmt->fetchColumn() ?: 0;

$catstmt = $pdo->prepare("
    SELECT categories.name AS category, SUM(expenses.amount) AS total
    FROM expenses
    JOIN categories ON expenses.category_id = categories.id
    WHERE expenses.user_id = ? AND expense_date BETWEEN ? AND ?
    GROUP BY categories.id
    ORDER BY total DESC
    ");

$catstmt->execute([$userId, $from, $to]);
$categoryTotals = $catstmt->fetchAll();
?>

<h2>Expense Summary Report</h2>

<form method="GET">
    <label>From:</label>
    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
    <label>To:</label>
    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
    <button type="submit">Filter</button>
</form>

<h3>Total Spent: £<?= number_format($total, 2) ?></h3>

<h4>Spending by Category</h4>
<?php if (count($categoryTotals) > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Category</th>
            <th>Total Spent</th>
        </tr>
        <?php foreach ($categoryTotals as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td>£<?= number_format($row['total'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

    </table>

    <!-- Chart container -->

    <div style="max-width: 500px; margin-top: 20px;">
        <canvas id="categoryChart"></canvas>
    </div>


    <!-- Chart.js -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.createElement('canvas');
    document.body.appendChild(ctx);

    const data = {
        labels: <?= json_encode(array_column($categoryTotals, 'category')) ?>,
        datasets: [{
            label: 'Spending by Category',
            data: <?= json_encode(array_column($categoryTotals, 'total')) ?>,
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                '#9966FF', '#FF9F40', '#E7E9ED', '#7CFC00'
            ],
            borderWidth: 1
        }]
    };

    new Chart(ctx, {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Spending Breakdown by Category'
                }
            }
        }
    });
</script>

<?php else: ?>
    <p>No expenses found for this range.</p>
<?php endif; ?>


<p>
    <a href="export.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">Download CSV</a>
</p>

<p><a href="index.php">← Back to Dashboard</a></p>