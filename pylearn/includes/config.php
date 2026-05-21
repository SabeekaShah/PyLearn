<?php
// PyLearn - Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pylearn');

$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

// Session config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: current user
function currentUser($pdo) {
    if (!isset($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Helper: is logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Helper: is admin
function requireAdmin($pdo) {
    requireLogin();
    $user = currentUser($pdo);
    if (!$user || $user['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit;
    }
}

// Award XP and check level up
function awardXP($pdo, $user_id, $xp) {
    $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id = ?")->execute([$xp, $user_id]);
    $pdo->prepare("UPDATE leaderboard SET total_xp = total_xp + ? WHERE user_id = ?")->execute([$xp, $user_id]);
    // Level up every 100 XP
    $pdo->prepare("UPDATE users SET level = FLOOR(xp/100) + 1 WHERE id = ?")->execute([$user_id]);
}

// Check and award badges
function checkBadges($pdo, $user_id) {
    $user = $pdo->prepare("SELECT xp, streak FROM users WHERE id = ?");
    $user->execute([$user_id]);
    $u = $user->fetch();

    $lessons = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_progress WHERE user_id = ? AND completed = 1");
    $lessons->execute([$user_id]);
    $lesson_count = $lessons->fetch()['cnt'];

    $subs = $pdo->prepare("SELECT COUNT(*) as cnt FROM code_submissions WHERE user_id = ?");
    $subs->execute([$user_id]);
    $sub_count = $subs->fetch()['cnt'];

    $badges = $pdo->query("SELECT * FROM badges")->fetchAll();
    foreach ($badges as $badge) {
        $earned = false;
        switch ($badge['condition_type']) {
            case 'lessons': $earned = $lesson_count >= $badge['condition_value']; break;
            case 'xp': $earned = $u['xp'] >= $badge['condition_value']; break;
            case 'streak': $earned = $u['streak'] >= $badge['condition_value']; break;
            case 'submissions': $earned = $sub_count >= $badge['condition_value']; break;
        }
        if ($earned) {
            $pdo->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)")
                ->execute([$user_id, $badge['id']]);
        }
    }
    // Update badge count in leaderboard
    $cnt = $pdo->prepare("SELECT COUNT(*) as c FROM user_badges WHERE user_id = ?");
    $cnt->execute([$user_id]);
    $bc = $cnt->fetch()['c'];
    $pdo->prepare("UPDATE leaderboard SET badges_count = ? WHERE user_id = ?")->execute([$bc, $user_id]);
}
?>
