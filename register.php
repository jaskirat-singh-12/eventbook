<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) redirect('index.php');
$title = 'Sign up';
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';
    if (!$errors) {
        $chk = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $chk->execute([$email]);
        if ($chk->fetch()) $errors[] = 'That email is already registered.';
    }
    if ($errors) {
        foreach ($errors as $er) flash($er, 'error');
    } else {
        $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)')
            ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
        flash('Account created. Please log in.');
        redirect('login.php');
    }
}
require 'includes/header.php';
?>
<div class="auth">
    <h2>Create account</h2>
    <form method="post">
        <?= csrf_field() ?>
        <label>Full name <input type="text" name="name" value="<?= e($name) ?>" required></label>
        <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label>
        <label>Password <input type="password" name="password" minlength="6" required></label>
        <button class="btn btn-block">Sign up</button>
    </form>
    <p class="muted center">Already registered? <a href="login.php">Login</a></p>
</div>
<?php require 'includes/footer.php'; ?>
