<?php
session_start();
require_once 'includes/db.php';


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];


if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $title = $_POST['title'];
    $target_amount = $_POST['target_amount'];
    $created_at = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');

    if (empty($target_amount)) {
        echo"Target amount cannot be empty.";
    } else {
        try {
        $stmt = $pdo->prepare("
            INSERT INTO savings 
            (user_id, title, target_amount, created_at) 
            VALUES (:user_id, :title, :target_amount, :created_at)
            ");

            $stmt->execute([
                ':user_id' => $userId,
                ':title' => $title,
                ':target_amount' => $target_amount,
                ':created_at' => $created_at
            ]);

            header("Location: view_savings.php");
            exit;
        } catch (PDOException $e) {
            echo "Failed to create saving: " . $e->getMessage();
        }
    }
}
?>

<h1>Create Saving</h1>

<?php if (!empty($error)) echo "<p style='color:red;'$error</p>"; ?>
<form method="POST">

    <label>Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required><br>

    <label>Target Amount:</label><br>
    <input type="number" name="target_amount" min="0.1" step="0.01" value="<?= htmlspecialchars($target_amount ?? '') ?>" required><br>

    <input type="hidden" id="date" name="date"><br>

    <button type="submit">Add Savings!</button>

</form>