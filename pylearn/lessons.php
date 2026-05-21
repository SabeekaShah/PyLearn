<?php
require_once 'includes/config.php';
$pageTitle = 'All Lessons';
$user = isset($pdo) ? currentUser($pdo) : null;

$filter = $_GET['diff'] ?? '';
$search = $_GET['q'] ?? '';
$cat = $_GET['cat'] ?? '';

$sql = "SELECT l.*, up.completed, up.quiz_score FROM lessons l LEFT JOIN user_progress up ON l.id=up.lesson_id AND up.user_id=? WHERE 1";
$params = [$user['id'] ?? 0];
if ($filter) { $sql .= " AND l.difficulty=?"; $params[] = $filter; }
if ($cat) { $sql .= " AND l.category=?"; $params[] = $cat; }
if ($search) { $sql .= " AND l.title LIKE ?"; $params[] = "%$search%"; }
$sql .= " ORDER BY l.order_num";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lessons = $stmt->fetchAll();

$cats = $pdo->query("SELECT DISTINCT category FROM lessons ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include 'includes/header.php'; ?>

<div class="page-title">
    <h1>📚 Python Lessons</h1>
    <p>Learn Python step by step — from basics to advanced concepts.</p>
</div>

<div class="section">
    <div class="container">
        <!-- Filters -->
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center;">
            <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;width:100%;">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="🔍 Search lessons..." style="background:var(--card);border:1px solid var(--border);color:var(--text);padding:.5rem 1rem;border-radius:8px;min-width:200px;">
                <select name="diff" style="background:var(--card);border:1px solid var(--border);color:var(--text);padding:.5rem .75rem;border-radius:8px;">
                    <option value="">All Levels</option>
                    <option value="beginner" <?= $filter==='beginner'?'selected':'' ?>>🟢 Beginner</option>
                    <option value="intermediate" <?= $filter==='intermediate'?'selected':'' ?>>🟡 Intermediate</option>
                    <option value="advanced" <?= $filter==='advanced'?'selected':'' ?>>🔴 Advanced</option>
                </select>
                <select name="cat" style="background:var(--card);border:1px solid var(--border);color:var(--text);padding:.5rem .75rem;border-radius:8px;">
                    <option value="">All Categories</option>
                    <?php foreach ($cats as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= $cat===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary">Filter</button>
                <?php if ($filter||$search||$cat): ?><a href="lessons.php" class="btn-secondary">Clear</a><?php endif; ?>
            </form>
        </div>

        <?php if (empty($lessons)): ?>
        <div class="card text-center" style="padding:3rem;">
            <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
            <h3>No lessons found</h3>
            <p style="color:var(--text-muted);margin-top:.5rem;">Try adjusting your filters.</p>
        </div>
        <?php else: ?>
        <div class="card-grid">
        <?php foreach ($lessons as $l): ?>
            <a href="lesson.php?slug=<?= $l['slug'] ?>" class="card lesson-card fade-in" style="color:var(--text);">
                <?php if ($l['completed']): ?>
                    <div class="completed-mark">✅</div>
                <?php endif; ?>
                <span class="diff-badge <?= $l['difficulty'] ?>"><?= ucfirst($l['difficulty']) ?></span>
                <h3 style="margin-top:.5rem;"><?= htmlspecialchars($l['title']) ?></h3>
                <div class="cat">📂 <?= htmlspecialchars($l['category']) ?></div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.75rem;">
                    <span class="xp-badge">⚡ +<?= $l['xp_reward'] ?> XP</span>
                    <?php if ($l['quiz_score'] !== null): ?>
                        <span style="font-size:.8rem;color:var(--success);">Quiz: <?= $l['quiz_score'] ?>%</span>
                    <?php else: ?>
                        <span style="font-size:.8rem;color:var(--text-muted);">Not started</span>
                    <?php endif; ?>
                </div>
                <?php if ($l['completed']): ?>
                <div class="progress-bar-wrap mt-1">
                    <div class="progress-bar-fill" style="width:100%"></div>
                </div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
