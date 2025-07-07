<?php

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>

<h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h2>

<?php if (isset($_SESSION['success'])): ?>
    <p style="color:green;"><?=htmlspecialchars($_SESSION['success']) ?></p>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<a href="logout.php">Logout</a>