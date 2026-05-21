<?php
require_once 'includes/config.php';
$pageTitle = 'Register';
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$username || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $error = 'Username or email already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)");
            $stmt->execute([$username, $email, $hash]);
            $uid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO leaderboard (user_id, total_xp) VALUES (?, 0)")->execute([$uid]);
            $_SESSION['user_id'] = $uid;
            header('Location: dashboard.php'); exit;
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-card fade-in">
        <div class="auth-logo">🐍</div>
        <h2>Create Account</h2>
        <p>Start your Python journey today</p>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username']??'') ?>" placeholder="e.g. pythondev" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="At least 6 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;padding:.75rem;">Create Account</button>
        </form>
        <p class="text-center mt-2" style="font-size:.9rem;">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
