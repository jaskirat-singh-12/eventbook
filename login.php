<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) redirect('index.php');
$title = 'Login';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([$email]);
    $user = $st->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'role' => $user['role']];
        flash('Welcome back, ' . $user['name'] . '!');
        redirect($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
    }
    flash('Invalid email or password.', 'error');
}
require 'includes/header.php';
?>
<div class="auth">
    <h2>Login</h2>
    <form method="post">
        <?= csrf_field() ?>
        <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label>
        <label>Password <input type="password" name="password" required></label>
        <button class="btn btn-block">Login</button>
    </form>
    <p class="muted center">New here? <a href="register.php">Create an account</a></p>
    <p class="hint">Demo: admin@college.edu / admin123 &nbsp;|&nbsp; student@college.edu / student123</p>
</div>
<?php require 'includes/footer.php'; ?>
