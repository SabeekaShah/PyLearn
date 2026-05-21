<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'My Profile';
$user = currentUser($pdo);

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $newpw = $_POST['new_password'] ?? '';
    $current = $_POST['current_password'] ?? '';
    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } else {
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $pdo->prepare("UPDATE users SET email=? WHERE id=?")->execute([$email, $user['id']]);
        }
        if ($newpw && strlen($newpw) >= 6) {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($newpw, PASSWORD_DEFAULT), $user['id']]);
        }
        $success = 'Profile updated!';
        $user = currentUser($pdo);
    }
}

$all_badges = $pdo->query("SELECT * FROM badges")->fetchAll();
$earned_ids = $pdo->prepare("SELECT badge_id FROM user_badges WHERE user_id=?");
$earned_ids->execute([$user['id']]);
$earned = array_column($earned_ids->fetchAll(), 'badge_id');

$completed_lessons = $pdo->prepare("SELECT l.*, up.quiz_score, up.completed_at FROM lessons l JOIN user_progress up ON l.id=up.lesson_id WHERE up.user_id=? AND up.completed=1 ORDER BY up.completed_at DESC");
$completed_lessons->execute([$user['id']]);
$done_lessons = $completed_lessons->fetchAll();

$rank = $pdo->prepare("SELECT COUNT(*)+1 FROM leaderboard WHERE total_xp > (SELECT total_xp FROM leaderboard WHERE user_id=?)");
$rank->execute([$user['id']]);
$user_rank = $rank->fetchColumn();

$xp_pct = min(100, (($user['xp'] % 100)));
?>
<?php include 'includes/header.php'; ?>

<div class="profile-page">
    <div class="profile-header">
        <div class="profile-avatar"><?= strtoupper(substr($user['username'],0,1)) ?></div>
        <div class="profile-info">
            <h2><?= htmlspecialchars($user['username']) ?></h2>
            <div><?= htmlspecialchars($user['email']) ?></div>
            <div style="margin-top:.4rem;">
                <span class="level-badge">Level <?= $user['level'] ?></span>
                <span style="margin-left:.5rem;color:var(--accent);">⚡ <?= $user['xp'] ?> XP</span>
                <span style="margin-left:.5rem;color:var(--warning);">🔥 <?= $user['streak'] ?> day streak</span>
                <span style="margin-left:.5rem;color:var(--text-muted);">🏆 Rank #<?= $user_rank ?></span>
            </div>
            <!-- Level progress bar -->
            <div style="margin-top:.75rem;">
                <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.3rem;">XP to next level: <?= $user['xp'] % 100 ?>/100</div>
                <div style="height:6px;background:var(--border);border-radius:6px;width:200px;">
                    <div style="height:6px;background:linear-gradient(90deg,var(--primary),var(--accent));border-radius:6px;width:<?= $xp_pct ?>%;transition:width 1s;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Edit Profile -->
        <div class="card">
            <h3 style="margin-bottom:1rem;">⚙️ Edit Profile</h3>
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep)</label>
                    <input type="password" name="new_password" placeholder="New password...">
                </div>
                <div class="form-group">
                    <label>Current Password (required to save)</label>
                    <input type="password" name="current_password" required>
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>

        <!-- Stats -->
        <div class="card">
            <h3 style="margin-bottom:1rem;">📊 Your Stats</h3>
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                <div style="display:flex;justify-content:space-between;padding:.6rem;background:var(--bg3);border-radius:8px;">
                    <span>Total XP</span><strong style="color:var(--accent);"><?= $user['xp'] ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.6rem;background:var(--bg3);border-radius:8px;">
                    <span>Level</span><strong style="color:var(--python-yellow);"><?= $user['level'] ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.6rem;background:var(--bg3);border-radius:8px;">
                    <span>Lessons Done</span><strong><?= count($done_lessons) ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.6rem;background:var(--bg3);border-radius:8px;">
                    <span>Day Streak</span><strong style="color:var(--warning);">🔥 <?= $user['streak'] ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.6rem;background:var(--bg3);border-radius:8px;">
                    <span>Rank</span><strong>#<?= $user_rank ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.6rem;background:var(--bg3);border-radius:8px;">
                    <span>Member Since</span><strong><?= date('M Y', strtotime($user['created_at'])) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Badges -->
    <div class="card mt-3">
        <h3 style="margin-bottom:1rem;">🎖️ Badges (<?= count($earned) ?>/<?= count($all_badges) ?>)</h3>
        <div class="badges-grid">
        <?php foreach ($all_badges as $b): ?>
            <div class="badge-item <?= !in_array($b['id'], $earned)?'locked':'' ?>" title="<?= htmlspecialchars($b['description']) ?>">
                <span class="badge-icon"><?= $b['icon'] ?></span>
                <div>
                    <div style="font-size:.85rem;"><?= htmlspecialchars($b['name']) ?></div>
                    <div style="font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars($b['description']) ?></div>
                </div>
                <?php if (!in_array($b['id'], $earned)): ?>
                <span style="font-size:.7rem;color:var(--text-muted);">🔒 Locked</span>
                <?php else: ?>
                <span style="font-size:.7rem;color:var(--success);">✅ Earned</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- Completed Lessons -->
    <?php if ($done_lessons): ?>
    <div class="card mt-3">
        <h3 style="margin-bottom:1rem;">✅ Completed Lessons</h3>
        <table class="admin-table">
            <thead><tr><th>Lesson</th><th>Quiz Score</th><th>Completed</th></tr></thead>
            <tbody>
            <?php foreach ($done_lessons as $dl): ?>
            <tr>
                <td><a href="lesson.php?slug=<?= $dl['slug'] ?>"><?= htmlspecialchars($dl['title']) ?></a></td>
                <td><?= $dl['quiz_score'] ?>%</td>
                <td style="color:var(--text-muted);font-size:.85rem;"><?= date('M j, Y', strtotime($dl['completed_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
