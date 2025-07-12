<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background-color: #f8f9fa;
    }

    h2 {
        margin-bottom: 10px;
        color: #333;
    }

    form {
        margin-bottom: 20px;
    }

    label {
        margin-right: 10px;
    }

    input[type="date"] {
        padding: 6px;
        margin-right: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button {
        padding: 6px 12px;
        border: none;
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    th, td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th {
        background-color: #f1f1f1;
    }

    .chart-container {
        max-width: 500px;
        margin: 30px auto;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

</style>


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

$monthlystmt = $pdo->prepare("
    SELECT DATE_FORMAT(expense_date, '%Y-%m') AS month, SUM(amount) AS total
    FROM expenses
    WHERE user_id = ?
    GROUP BY month
    ORDER BY month DESC
");

$monthlystmt->execute([$userId]);
$monthlyTotals = $monthlystmt->fetchAll();
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
    <table>
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

        <tr style="font-weight: bold;">
            <td>Total</td>
            <td>£<?= number_format($total, 2) ?></td>
        </tr>

    </table>

    <h5>Monthly Overview</h4>
    <?php if (count($monthlyTotals) > 0): ?>
        <table>
            <tr>
                <th>Month</th>
                <th>Total Spent</th>
            </tr>
            <?php foreach ($monthlyTotals as $row): ?>
                <tr>
                    <td><?= htmlspecialchars(date('F Y', strtotime($row['month'] . '-01'))) ?></td>
                    <td>£<?= number_format($row['total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No monthly data available.</p>
    <?php endif; ?>

    

    <!-- Chart container -->

    <div class="chart-container">
        <canvas id="categoryChart"></canvas>
        <canvas id="monthlyChart"></canvas>
    </div>


    <!-- Chart.js -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('categoryChart').getContext('2d');

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

<script>
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

    const monthlyData = {
        labels: <?= json_encode(array_map(function($row) {
            return date('M Y', strtotime($row['month'] . '-01'));
        }, $monthlyTotals)) ?>,
        datasets: [{
            label: 'Monthly Spending (£)',
            data: <?= json_encode(array_column($monthlyTotals, 'total')) ?>,
            backgroundColor: '#36A2EB',
            borderColor: '#1d75bd',
            borderWidth: 1
        }]
    };

    new Chart(monthlyCtx, {
        type: 'bar',
        data: monthlyData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Spending Per Month'
                }
            }
        }
    });
</script>


<form method="GET" action="export_csv.php" target="_blank" style="margin-top: 20px;">
    <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
    <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">
    <button type="submit">Download CSV Report</button>
</form>

<p><a href="index.php">← Back to Dashboard</a></p>