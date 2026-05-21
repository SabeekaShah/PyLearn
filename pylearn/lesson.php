<?php
require_once 'includes/config.php';
requireLogin();
$user = currentUser($pdo);
$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE slug=?");
$stmt->execute([$slug]);
$lesson = $stmt->fetch();
if (!$lesson) { header('Location: lessons.php'); exit; }
$pageTitle = $lesson['title'];

// Mark as started
$prog = $pdo->prepare("INSERT IGNORE INTO user_progress (user_id, lesson_id) VALUES (?,?)");
$prog->execute([$user['id'], $lesson['id']]);

// Get progress
$up = $pdo->prepare("SELECT * FROM user_progress WHERE user_id=? AND lesson_id=?");
$up->execute([$user['id'], $lesson['id']]);
$progress = $up->fetch();

// Quizzes
$quizzes = $pdo->prepare("SELECT * FROM quizzes WHERE lesson_id=? ORDER BY id");
$quizzes->execute([$lesson['id']]);
$qlist = $quizzes->fetchAll();

// Sidebar lessons
$all_lessons = $pdo->prepare("SELECT l.id,l.title,l.slug,up.completed FROM lessons l LEFT JOIN user_progress up ON l.id=up.lesson_id AND up.user_id=? ORDER BY l.order_num");
$all_lessons->execute([$user['id']]);
$sidebar_lessons = $all_lessons->fetchAll();

// Prev/Next
$nav = $pdo->prepare("SELECT slug,title FROM lessons WHERE order_num=?");
$nav->execute([$lesson['order_num']-1]);
$prev = $nav->fetch();
$nav->execute([$lesson['order_num']+1]);
$next = $nav->fetch();

// Mark completed if quiz already done
$is_complete = $progress && $progress['completed'];
?>
<?php include 'includes/header.php'; ?>

<div class="lesson-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <h3>All Lessons</h3>
        <?php foreach ($sidebar_lessons as $sl): ?>
        <a href="lesson.php?slug=<?= $sl['slug'] ?>" class="sidebar-item <?= $sl['slug']===$slug?'active':'' ?>">
            <span class="done"><?= $sl['completed'] ? '✅' : '⬜' ?></span>
            <?= htmlspecialchars($sl['title']) ?>
        </a>
        <?php endforeach; ?>
    </aside>

    <!-- Main Content -->
    <main>
        <div class="lesson-content">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;">
                <div>
                    <span class="diff-badge <?= $lesson['difficulty'] ?>"><?= ucfirst($lesson['difficulty']) ?></span>
                    <span style="margin-left:.5rem;color:var(--text-muted);font-size:.85rem;">📂 <?= htmlspecialchars($lesson['category']) ?></span>
                </div>
                <span class="xp-badge" style="font-size:1rem;">⚡ +<?= $lesson['xp_reward'] ?> XP</span>
            </div>

            <h1 style="font-size:1.7rem;margin-bottom:1.5rem;color:var(--python-yellow);"><?= htmlspecialchars($lesson['title']) ?></h1>

            <?= $lesson['content'] ?>

            <?php if ($is_complete): ?>
            <div class="alert alert-success mt-3">✅ You've completed this lesson! Quiz score: <?= $progress['quiz_score'] ?>%</div>
            <?php endif; ?>

            <!-- Navigation -->
            <div class="lesson-nav">
                <?php if ($prev): ?>
                <a href="lesson.php?slug=<?= $prev['slug'] ?>" class="btn-secondary">← <?= htmlspecialchars($prev['title']) ?></a>
                <?php else: ?><div></div><?php endif; ?>
                <?php if ($next): ?>
                <a href="lesson.php?slug=<?= $next['slug'] ?>" class="btn-primary"><?= htmlspecialchars($next['title']) ?> →</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quiz -->
        <?php if ($qlist): ?>
        <div class="quiz-box mt-3">
            <h3>🧠 Knowledge Check</h3>
            <?php if ($is_complete): ?>
            <div class="alert alert-success">You already completed this quiz with <?= $progress['quiz_score'] ?>% score! Retake below.</div>
            <?php endif; ?>

            <div id="quiz-score"></div>

            <form id="quiz-form">
            <?php foreach ($qlist as $i => $q): ?>
            <div class="quiz-q" id="q-<?= $q['id'] ?>">
                <p><?= $i+1 ?>. <?= htmlspecialchars($q['question']) ?></p>
                <div class="quiz-options">
                    <?php foreach (['A'=>$q['option_a'],'B'=>$q['option_b'],'C'=>$q['option_c'],'D'=>$q['option_d']] as $k=>$opt): ?>
                    <?php if ($opt): ?>
                    <label class="quiz-opt" data-value="<?= $k ?>">
                        <input type="radio" name="q<?= $q['id'] ?>" value="<?= $k ?>">
                        <strong><?= $k ?>.</strong> <?= htmlspecialchars($opt) ?>
                    </label>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="quiz-explanation" style="display:none;margin-top:.5rem;padding:.5rem;background:rgba(6,182,212,.1);border-radius:6px;font-size:.85rem;color:var(--text-muted);">
                    💡 <?= htmlspecialchars($q['explanation'] ?? '') ?>
                </div>
            </div>
            <?php endforeach; ?>
            <button type="button" id="submit-quiz-btn" class="btn-primary mt-3" onclick="submitQuiz(<?= $lesson['id'] ?>)">Submit Quiz</button>
            </form>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
// Handle quiz option click highlight
document.querySelectorAll('.quiz-opt').forEach(opt => {
    opt.addEventListener('click', () => {
        const name = opt.querySelector('input').name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            r.closest('.quiz-opt').style.borderColor = '';
            r.closest('.quiz-opt').style.background = '';
        });
        opt.style.borderColor = 'var(--primary)';
        opt.style.background = 'rgba(79,70,229,.1)';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
