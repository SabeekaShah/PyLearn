<?php
require_once 'includes/config.php';
$pageTitle = 'Login';
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        // Update streak
        $today = date('Y-m-d');
        $last = $user['last_login'];
        $streak = $user['streak'];
        if ($last === date('Y-m-d', strtotime('-1 day'))) {
            $streak++;
        } elseif ($last !== $today) {
            $streak = 1;
        }
        $pdo->prepare("UPDATE users SET last_login=?, streak=? WHERE id=?")->execute([$today, $streak, $user['id']]);
        header('Location: dashboard.php'); exit;
    } else {
        $error = 'Invalid username/email or password.';
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-card fade-in">
        <div class="auth-logo">🐍</div>
        <h2>Welcome Back!</h2>
        <p>Continue your Python journey</p>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="login" value="<?= htmlspecialchars($_POST['login']??'') ?>" placeholder="Username or email" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;padding:.75rem;">Login</button>
        </form>
        <p class="text-center mt-2" style="font-size:.9rem;">No account? <a href="register.php">Register free</a></p>
        <div style="margin-top:1rem;padding:1rem;background:rgba(79,70,229,.1);border-radius:8px;font-size:.82rem;color:var(--text-muted);">
            <strong>Demo accounts:</strong><br>
            admin / password &nbsp;|&nbsp; alice / password
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
