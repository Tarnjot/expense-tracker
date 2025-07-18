<?php

session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

?>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php
$errors = [];
$name = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if ($name == '') {
        $errors[] = 'Category name is required.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND user_id = ?");
        $stmt->execute([$name, $_SESSION['user']['id']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Category already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, user_id) VALUES (?, ?)");
        $stmt->execute([$name, $_SESSION['user']['id']]);
        $success = 'Category added successfully';
        $name = '';
    }
}

$catstmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY name");
$catstmt->execute([$_SESSION['user']['id']]);
$categories = $catstmt->fetchAll();
?>

<h2>Manage Categories</h2>

<?php if ($success): ?>
    <p style="color:green;"><?=htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>New Category:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
    <button type="submit">Add Category</button>
</form>

<h3>Existing Categories</h3>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Name</th>
        <th>Action</th>
    </tr>
    <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= htmlspecialchars($cat['name']) ?></td>
            <td>
                <a href="editCategory.php?id=<?= $cat['id'] ?>">Edit</a> |
                <a href="delete_category.php?id=<?= $cat['id'] ?>" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>



<p><a href="index.php">← Back to Dashboard</a></p>