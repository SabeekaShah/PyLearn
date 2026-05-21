<?php
require_once 'includes/config.php';
$pageTitle = 'Learn Python Interactively';
// Redirect if logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php'); exit;
}
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_lessons = $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
$total_quizzes = $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
?>
<?php include 'includes/header.php'; ?>

<section class="hero">
    <h1>Learn Python.<br><span>Code. Quiz. Level Up.</span></h1>
    <p>An interactive Python learning platform with lessons, quizzes, a code playground, and a leaderboard — all in one place.</p>
    <div class="hero-cta">
        <a href="register.php" class="btn-primary" style="font-size:1.1rem;padding:.7rem 2rem;">🚀 Start Learning Free</a>
        <a href="lessons.php" class="btn-secondary" style="font-size:1.1rem;padding:.7rem 2rem;">Browse Lessons</a>
    </div>
    <div class="hero-stats">
        <div class="hero-stat"><div class="num"><?= $total_lessons ?>+</div><div class="lbl">Lessons</div></div>
        <div class="hero-stat"><div class="num"><?= $total_quizzes ?>+</div><div class="lbl">Quiz Questions</div></div>
        <div class="hero-stat"><div class="num"><?= $total_users ?>+</div><div class="lbl">Learners</div></div>
        <div class="hero-stat"><div class="num">3</div><div class="lbl">Difficulty Levels</div></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="text-center mb-3">Why PyLearn?</h2>
        <div class="card-grid">
            <div class="card fade-in">
                <div style="font-size:2rem;margin-bottom:.75rem;">📚</div>
                <h3>Structured Lessons</h3>
                <p style="color:var(--text-muted);margin-top:.5rem;">From beginner to advanced — clear, concise lessons with syntax-highlighted code examples.</p>
            </div>
            <div class="card fade-in">
                <div style="font-size:2rem;margin-bottom:.75rem;">🧠</div>
                <h3>Interactive Quizzes</h3>
                <p style="color:var(--text-muted);margin-top:.5rem;">Test your knowledge after every lesson. Get instant feedback and explanations.</p>
            </div>
            <div class="card fade-in">
                <div style="font-size:2rem;margin-bottom:.75rem;">💻</div>
                <h3>Code Playground</h3>
                <p style="color:var(--text-muted);margin-top:.5rem;">Write and run Python code directly in your browser with preloaded examples.</p>
            </div>
            <div class="card fade-in">
                <div style="font-size:2rem;margin-bottom:.75rem;">🏆</div>
                <h3>XP & Leaderboard</h3>
                <p style="color:var(--text-muted);margin-top:.5rem;">Earn XP, level up, collect badges, and compete with other learners.</p>
            </div>
            <div class="card fade-in">
                <div style="font-size:2rem;margin-bottom:.75rem;">🔥</div>
                <h3>Daily Streaks</h3>
                <p style="color:var(--text-muted);margin-top:.5rem;">Build a daily learning habit and maintain your streak for bonus rewards.</p>
            </div>
            <div class="card fade-in">
                <div style="font-size:2rem;margin-bottom:.75rem;">📊</div>
                <h3>Progress Tracking</h3>
                <p style="color:var(--text-muted);margin-top:.5rem;">See exactly which lessons you've completed and how well you scored.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:var(--bg2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
    <div class="container text-center">
        <h2 class="mb-3">Ready to start?</h2>
        <p style="color:var(--text-muted);margin-bottom:2rem;">Join hundreds of learners mastering Python with PyLearn.</p>
        <a href="register.php" class="btn-primary" style="font-size:1.1rem;padding:.8rem 2.5rem;">Create Free Account</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
