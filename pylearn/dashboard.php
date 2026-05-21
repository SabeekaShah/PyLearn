<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'Dashboard';
$user = currentUser($pdo);

// Check/update streak
$today = date('Y-m-d');
if ($user['last_login'] !== $today) {
    $streak = ($user['last_login'] === date('Y-m-d', strtotime('-1 day'))) ? $user['streak'] + 1 : 1;
    $pdo->prepare("UPDATE users SET last_login=?, streak=? WHERE id=?")->execute([$today, $streak, $user['id']]);
    $user['streak'] = $streak;
}

// Progress stats
$done = $pdo->prepare("SELECT COUNT(*) FROM user_progress WHERE user_id=? AND completed=1");
$done->execute([$user['id']]);
$lessons_done = $done->fetchColumn();

$total_lessons = $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();

$rank_stmt = $pdo->prepare("SELECT COUNT(*)+1 as rank FROM leaderboard WHERE total_xp > (SELECT total_xp FROM leaderboard WHERE user_id=?)");
$rank_stmt->execute([$user['id']]);
$rank = $rank_stmt->fetchColumn();

// Recent lessons with progress
$recent = $pdo->prepare("
    SELECT l.*, up.completed, up.quiz_score
    FROM lessons l
    LEFT JOIN user_progress up ON l.id=up.lesson_id AND up.user_id=?
    ORDER BY l.order_num LIMIT 6
");
$recent->execute([$user['id']]);
$lessons = $recent->fetchAll();

// Badges
$user_badges = $pdo->prepare("SELECT b.* FROM badges b JOIN user_badges ub ON b.id=ub.badge_id WHERE ub.user_id=?");
$user_badges->execute([$user['id']]);
$badges = $user_badges->fetchAll();

// XP for next level
$xp_for_next = $user['level'] * 100;
$xp_in_level = $user['xp'] - (($user['level']-1)*100);
$pct = min(100, round(($xp_in_level / 100) * 100));
?>
<?php include 'includes/header.php'; ?>
<div class="dashboard">
    <!-- Level Bar -->
    <div class="level-bar">
        <div class="level-header">
            <span class="level-title">⚡ Level <?= $user['level'] ?> Python Learner</span>
            <span style="color:var(--text-muted);font-size:.85rem;"><?= $user['xp'] ?> / <?= $user['level']*100 ?> XP to next level</span>
        </div>
        <div class="level-bar-wrap">
            <div class="level-bar-fill" data-width="<?= $pct ?>%" style="width:<?= $pct ?>%"></div>
        </div>
    </div>

    <!-- Stats -->
    <div class="dash-grid">
        <div class="stat-card">
            <div class="stat-icon">⚡</div>
            <div>
                <div class="stat-val" data-target="<?= $user['xp'] ?>">0</div>
                <div class="stat-label">Total XP Earned</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div>
                <div class="stat-val" data-target="<?= $lessons_done ?>"><?= $lessons_done ?></div>
                <div class="stat-label">Lessons Completed (<?= $total_lessons ?> total)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div>
                <div class="stat-val" data-target="<?= $rank ?>"><?= $rank ?></div>
                <div class="stat-label">Leaderboard Rank</div>
            </div>
        </div>
        <div class="stat-card streak-card">
            <div class="streak-fire">🔥</div>
            <div>
                <div class="streak-val" data-target="<?= $user['streak'] ?>"><?= $user['streak'] ?></div>
                <div class="stat-label">Day Streak</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎖️</div>
            <div>
                <div class="stat-val" data-target="<?= count($badges) ?>"><?= count($badges) ?></div>
                <div class="stat-label">Badges Earned</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div>
                <div class="stat-val"><?= $total_lessons > 0 ? round(($lessons_done/$total_lessons)*100) : 0 ?>%</div>
                <div class="stat-label">Course Progress</div>
            </div>
        </div>
    </div>

    <!-- Lessons -->
    <div class="section-header mt-4">
        <h2>📖 Continue Learning</h2>
        <a href="lessons.php" class="btn-secondary btn-sm">All Lessons →</a>
    </div>
    <div class="card-grid">
    <?php foreach ($lessons as $l): ?>
        <a href="lesson.php?slug=<?= $l['slug'] ?>" class="card lesson-card" style="color:var(--text);">
            <?php if ($l['completed']): ?>
                <div class="completed-mark">✅</div>
            <?php endif; ?>
            <div class="diff-badge <?= $l['difficulty'] ?>"><?= ucfirst($l['difficulty']) ?></div>
            <h3><?= htmlspecialchars($l['title']) ?></h3>
            <div class="cat">📂 <?= htmlspecialchars($l['category']) ?></div>
            <div class="xp-badge">⚡ +<?= $l['xp_reward'] ?> XP</div>
            <?php if ($l['quiz_score'] !== null): ?>
                <div style="font-size:.8rem;color:var(--success);margin-top:.25rem;">Quiz: <?= $l['quiz_score'] ?>%</div>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
    </div>

    <!-- Badges -->
    <?php if ($badges): ?>
    <div class="mt-4">
        <h2 class="mb-2">🎖️ Your Badges</h2>
        <div class="badges-grid">
        <?php foreach ($badges as $b): ?>
            <div class="badge-item" title="<?= htmlspecialchars($b['description']) ?>">
                <span class="badge-icon"><?= $b['icon'] ?></span>
                <span><?= htmlspecialchars($b['name']) ?></span>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick links -->
    <div class="grid-2 mt-4">
        <a href="playground.php" class="card" style="display:block;color:var(--text);">
            <h3>💻 Code Playground</h3>
            <p style="color:var(--text-muted);margin-top:.4rem;font-size:.9rem;">Practice Python in your browser with examples and live execution.</p>
        </a>
        <a href="leaderboard.php" class="card" style="display:block;color:var(--text);">
            <h3>🏆 Leaderboard</h3>
            <p style="color:var(--text-muted);margin-top:.4rem;font-size:.9rem;">See how you rank against other learners. Climb to the top!</p>
        </a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
