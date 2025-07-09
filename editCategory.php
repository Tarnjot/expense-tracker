<?php

session_start();

require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header ("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$category = $stmt->fetch();

if (!$category) {
    $_SESSION['error'] = "Category not found or access denied.";
    header("Location: categories.php");
    exit;
}
?>

<h2>Edit Category</h2>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color:red"><?= $_SESSION['error'] ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST">
    <label>Category Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required><br>
    <button type="submit" name="update">Update</button>
</form>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['error'] = "Category name cannot be empty.";
    } else {
        $check = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ? AND id != ?");
        $check->execute([$name, $_SESSION['user']['id'], $id]);

        if ($check->fetch()) {
            $_SESSION['error'] = "Category name already exists.";
        } else {
            $update = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ? AND user_id = ?");
            $updated = $update->execute([$name, $id, $_SESSION['user']['id']]);

            if ($updated) {
                $_SESSION['success'] = "Category updated successfully!";
                header("Location: manage_categories.php");
                exit;
            } else {
                $_SESSION['error'] = "Failed to update category.";
            }
        }
    }
}