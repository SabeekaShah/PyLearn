<?php
require_once 'includes/config.php';
$pageTitle = 'Leaderboard';
$user = isset($pdo) ? currentUser($pdo) : null;

$lb = $pdo->query("
    SELECT u.username, u.level, u.xp, u.streak, l.lessons_done, l.badges_count
    FROM leaderboard l
    JOIN users u ON l.user_id=u.id
    ORDER BY l.total_xp DESC, l.lessons_done DESC
    LIMIT 20
")->fetchAll();

// Current user rank
$my_rank = null;
if ($user) {
    $r = $pdo->prepare("SELECT COUNT(*)+1 FROM leaderboard WHERE total_xp > (SELECT total_xp FROM leaderboard WHERE user_id=?)");
    $r->execute([$user['id']]);
    $my_rank = $r->fetchColumn();
}
?>
<?php include 'includes/header.php'; ?>

<div class="leaderboard">
    <div style="text-align:center;padding:2rem 0 1rem;">
        <h1>🏆 Leaderboard</h1>
        <p style="color:var(--text-muted);">Top Python learners ranked by XP earned.</p>
        <?php if ($my_rank): ?>
        <div style="margin-top:1rem;display:inline-block;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:.5rem 1.5rem;">
            <span style="color:var(--text-muted);font-size:.85rem;">Your Rank: </span>
            <span style="font-size:1.3rem;font-weight:700;color:var(--accent);">#<?= $my_rank ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top 3 Podium -->
    <?php if (count($lb) >= 3): ?>
    <div style="display:flex;justify-content:center;align-items:flex-end;gap:1rem;margin:2rem 0;flex-wrap:wrap;">
        <!-- 2nd -->
        <div style="text-align:center;width:120px;">
            <div style="background:var(--card);border:2px solid #cbd5e1;border-radius:var(--radius);padding:1rem .5rem;">
                <div style="font-size:2rem;margin-bottom:.25rem;">🥈</div>
                <div class="lb-avatar" style="margin:0 auto .5rem;"><?= strtoupper(substr($lb[1]['username'],0,1)) ?></div>
                <div style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars($lb[1]['username']) ?></div>
                <div style="color:var(--accent);font-size:.85rem;"><?= $lb[1]['xp'] ?> XP</div>
            </div>
        </div>
        <!-- 1st -->
        <div style="text-align:center;width:130px;">
            <div style="background:var(--card);border:2px solid var(--python-yellow);border-radius:var(--radius);padding:1.25rem .5rem;transform:translateY(-15px);box-shadow:0 0 25px rgba(255,212,59,.2);">
                <div style="font-size:2.5rem;margin-bottom:.25rem;">🥇</div>
                <div class="lb-avatar" style="margin:0 auto .5rem;width:40px;height:40px;font-size:1.1rem;"><?= strtoupper(substr($lb[0]['username'],0,1)) ?></div>
                <div style="font-weight:700;"><?= htmlspecialchars($lb[0]['username']) ?></div>
                <div style="color:var(--python-yellow);font-weight:600;"><?= $lb[0]['xp'] ?> XP</div>
            </div>
        </div>
        <!-- 3rd -->
        <div style="text-align:center;width:120px;">
            <div style="background:var(--card);border:2px solid #b45309;border-radius:var(--radius);padding:1rem .5rem;">
                <div style="font-size:2rem;margin-bottom:.25rem;">🥉</div>
                <div class="lb-avatar" style="margin:0 auto .5rem;"><?= strtoupper(substr($lb[2]['username'],0,1)) ?></div>
                <div style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars($lb[2]['username']) ?></div>
                <div style="color:var(--accent);font-size:.85rem;"><?= $lb[2]['xp'] ?> XP</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Full Table -->
    <table class="lb-table">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Level</th>
                <th>XP</th>
                <th>Lessons</th>
                <th>Badges</th>
                <th>Streak</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lb as $i => $row): ?>
        <tr class="<?= ($user && $row['username']===$user['username']) ? 'style="background:rgba(79,70,229,.1);"' : '' ?>">
            <td>
                <span class="lb-rank <?= $i==0?'gold':($i==1?'silver':($i==2?'bronze':'')) ?>">
                    <?= $i==0?'🥇':($i==1?'🥈':($i==2?'🥉':'#'.($i+1))) ?>
                </span>
            </td>
            <td>
                <div class="lb-user">
                    <div class="lb-avatar"><?= strtoupper(substr($row['username'],0,1)) ?></div>
                    <?= htmlspecialchars($row['username']) ?>
                    <?php if ($user && $row['username']===$user['username']): ?><span style="font-size:.75rem;color:var(--primary);margin-left:.3rem;">(you)</span><?php endif; ?>
                </div>
            </td>
            <td><span style="color:var(--python-yellow);">Lv <?= $row['level'] ?></span></td>
            <td><span class="lb-xp">⚡ <?= number_format($row['xp']) ?></span></td>
            <td><?= $row['lessons_done'] ?> 📚</td>
            <td><?= $row['badges_count'] ?> 🎖️</td>
            <td><?= $row['streak'] ?> 🔥</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
