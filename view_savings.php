<?php

require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT * FROM savings
    WHERE user_id = :user_id 
    ORDER BY created_at DESC
    ");
$stmt->execute([':user_id' => $userId]);
$savings = $stmt->fetchAll();

?>

<h1>Savings</h1>

<?php if (count($savings) > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Title</th>
            <th>Target Amount</th>
            <th>Saved Amount</th>
            <th>Progress</th>
            <th>Created At</th>
        </tr>
        <?php foreach ($savings as $saving): 
            $progress = $saving['saved_amount'] / $saving['target_amount'];
            $progressPercent = round($progress * 100, 2);
        ?>
            <tr>
                <td><?= htmlspecialchars($saving['title']) ?></td>
                <td><?= number_format($saving['target_amount']) ?></td>
                <td><?= number_format($saving['saved_amount'], 2) ?></td>
                <td><?= $progressPercent ?>%</td>
                <td><?= htmlspecialchars($saving['created_at']) ?></td>
                <td>
                    <a href="edit_saving.php?id=<?= $saving['id'] ?>">Edit</a> |
                    <a href="delete_Saving.php?id=<?= $saving['id'] ?>" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p> No savings yet. <a href="add_saving.php">add one now</a>.</p>
<?php endif; ?>

<a href="index.php">Back to index</a>