<?php
require_once '../includes/config.php';
requireAdmin($pdo);
$pageTitle = 'Admin Panel';
$user = currentUser($pdo);

$tab = $_GET['tab'] ?? 'overview';

// Handle actions
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_lesson'])) {
        $pdo->prepare("INSERT INTO lessons (title,slug,category,difficulty,content,xp_reward,order_num) VALUES (?,?,?,?,?,?,?)")
            ->execute([
                $_POST['title'], $_POST['slug'], $_POST['category'],
                $_POST['difficulty'], $_POST['content'], intval($_POST['xp_reward']),
                intval($_POST['order_num'])
            ]);
        $msg = 'Lesson added!';
    } elseif (isset($_POST['delete_lesson'])) {
        $pdo->prepare("DELETE FROM lessons WHERE id=?")->execute([$_POST['lid']]);
        $msg = 'Lesson deleted.';
    } elseif (isset($_POST['add_quiz'])) {
        $pdo->prepare("INSERT INTO quizzes (lesson_id,question,option_a,option_b,option_c,option_d,correct_answer,explanation) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$_POST['lesson_id'],$_POST['question'],$_POST['option_a'],$_POST['option_b'],$_POST['option_c'],$_POST['option_d'],$_POST['correct_answer'],$_POST['explanation']]);
        $msg = 'Quiz question added!';
    } elseif (isset($_POST['delete_user'])) {
        $pdo->prepare("DELETE FROM users WHERE id=? AND role != 'admin'")->execute([$_POST['uid']]);
        $msg = 'User deleted.';
    }
}

$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'lessons' => $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn(),
    'quizzes' => $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn(),
    'completions' => $pdo->query("SELECT COUNT(*) FROM user_progress WHERE completed=1")->fetchColumn(),
    'submissions' => $pdo->query("SELECT COUNT(*) FROM code_submissions")->fetchColumn(),
];
$lessons = $pdo->query("SELECT * FROM lessons ORDER BY order_num")->fetchAll();
$users = $pdo->query("SELECT u.*, lb.total_xp FROM users u LEFT JOIN leaderboard lb ON u.id=lb.user_id ORDER BY u.created_at DESC")->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="admin-page">
    <h1 style="margin-bottom:1.5rem;">🛡️ Admin Panel</h1>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div class="admin-tabs">
        <button class="tab-btn <?= $tab==='overview'?'active':'' ?>" onclick="window.location='?tab=overview'">📊 Overview</button>
        <button class="tab-btn <?= $tab==='lessons'?'active':'' ?>" onclick="window.location='?tab=lessons'">📚 Lessons</button>
        <button class="tab-btn <?= $tab==='add_lesson'?'active':'' ?>" onclick="window.location='?tab=add_lesson'">➕ Add Lesson</button>
        <button class="tab-btn <?= $tab==='quizzes'?'active':'' ?>" onclick="window.location='?tab=quizzes'">🧠 Add Quiz</button>
        <button class="tab-btn <?= $tab==='users'?'active':'' ?>" onclick="window.location='?tab=users'">👥 Users</button>
    </div>

    <?php if ($tab === 'overview'): ?>
    <div class="card-grid">
        <div class="stat-card"><div class="stat-icon">👥</div><div><div class="stat-val"><?= $stats['users'] ?></div><div class="stat-label">Total Users</div></div></div>
        <div class="stat-card"><div class="stat-icon">📚</div><div><div class="stat-val"><?= $stats['lessons'] ?></div><div class="stat-label">Lessons</div></div></div>
        <div class="stat-card"><div class="stat-icon">🧠</div><div><div class="stat-val"><?= $stats['quizzes'] ?></div><div class="stat-label">Quiz Questions</div></div></div>
        <div class="stat-card"><div class="stat-icon">✅</div><div><div class="stat-val"><?= $stats['completions'] ?></div><div class="stat-label">Lesson Completions</div></div></div>
        <div class="stat-card"><div class="stat-icon">💻</div><div><div class="stat-val"><?= $stats['submissions'] ?></div><div class="stat-label">Code Submissions</div></div></div>
    </div>
    <div class="card mt-3">
        <h3 style="margin-bottom:1rem;">Recent Users</h3>
        <table class="admin-table">
            <thead><tr><th>Username</th><th>Email</th><th>XP</th><th>Level</th><th>Role</th><th>Joined</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($users,0,10) as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['xp'] ?></td>
                <td><?= $u['level'] ?></td>
                <td><span style="color:<?= $u['role']==='admin'?'var(--warning)':'var(--accent)' ?>"><?= $u['role'] ?></span></td>
                <td style="color:var(--text-muted);"><?= date('M j, Y',strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($tab === 'lessons'): ?>
    <div class="card">
        <h3 style="margin-bottom:1rem;">All Lessons</h3>
        <table class="admin-table">
            <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Difficulty</th><th>XP</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($lessons as $l): ?>
            <tr>
                <td><?= $l['order_num'] ?></td>
                <td><a href="../lesson.php?slug=<?= $l['slug'] ?>"><?= htmlspecialchars($l['title']) ?></a></td>
                <td><?= htmlspecialchars($l['category']) ?></td>
                <td><span class="diff-badge <?= $l['difficulty'] ?>"><?= $l['difficulty'] ?></span></td>
                <td>⚡ <?= $l['xp_reward'] ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="lid" value="<?= $l['id'] ?>">
                        <button class="btn-danger" name="delete_lesson" onclick="return confirm('Delete this lesson?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($tab === 'add_lesson'): ?>
    <div class="card">
        <h3 style="margin-bottom:1.5rem;">➕ Add New Lesson</h3>
        <form method="POST">
            <div class="grid-2">
                <div class="form-group"><label>Title</label><input type="text" name="title" required placeholder="e.g. Python Decorators"></div>
                <div class="form-group"><label>Slug (URL)</label><input type="text" name="slug" required placeholder="e.g. python-decorators"></div>
                <div class="form-group"><label>Category</label><input type="text" name="category" required placeholder="e.g. Advanced"></div>
                <div class="form-group"><label>Difficulty</label>
                    <select name="difficulty"><option>beginner</option><option>intermediate</option><option>advanced</option></select>
                </div>
                <div class="form-group"><label>XP Reward</label><input type="number" name="xp_reward" value="20" min="5" max="100"></div>
                <div class="form-group"><label>Order Number</label><input type="number" name="order_num" value="<?= count($lessons)+1 ?>"></div>
            </div>
            <div class="form-group">
                <label>Content (HTML supported)</label>
                <textarea name="content" rows="12" required placeholder="<h2>Title</h2><p>Content...</p><pre><code class='language-python'>print('hello')</code></pre>"></textarea>
            </div>
            <button type="submit" name="add_lesson" class="btn-primary">Add Lesson</button>
        </form>
    </div>

    <?php elseif ($tab === 'quizzes'): ?>
    <div class="card">
        <h3 style="margin-bottom:1.5rem;">🧠 Add Quiz Question</h3>
        <form method="POST">
            <div class="form-group">
                <label>Lesson</label>
                <select name="lesson_id" required>
                    <?php foreach ($lessons as $l): ?>
                    <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Question</label><input type="text" name="question" required></div>
            <div class="grid-2">
                <div class="form-group"><label>Option A</label><input type="text" name="option_a" required></div>
                <div class="form-group"><label>Option B</label><input type="text" name="option_b" required></div>
                <div class="form-group"><label>Option C</label><input type="text" name="option_c"></div>
                <div class="form-group"><label>Option D</label><input type="text" name="option_d"></div>
            </div>
            <div class="form-group">
                <label>Correct Answer</label>
                <select name="correct_answer"><option>A</option><option>B</option><option>C</option><option>D</option></select>
            </div>
            <div class="form-group"><label>Explanation</label><textarea name="explanation" rows="2"></textarea></div>
            <button type="submit" name="add_quiz" class="btn-primary">Add Question</button>
        </form>
    </div>

    <?php elseif ($tab === 'users'): ?>
    <div class="card">
        <h3 style="margin-bottom:1rem;">All Users</h3>
        <table class="admin-table">
            <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>XP</th><th>Level</th><th>Streak</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span style="color:<?= $u['role']==='admin'?'var(--warning)':'var(--accent)' ?>"><?= $u['role'] ?></span></td>
                <td><?= $u['xp'] ?></td>
                <td><?= $u['level'] ?></td>
                <td>🔥 <?= $u['streak'] ?></td>
                <td>
                    <?php if ($u['role']!=='admin'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                        <button class="btn-danger btn-sm" name="delete_user" onclick="return confirm('Delete user?')">Delete</button>
                    </form>
                    <?php else: ?><span style="color:var(--text-muted);font-size:.8rem;">Protected</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
