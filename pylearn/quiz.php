<?php
include 'auth_check.php';
include 'db.php';
include 'check_badges.php';

$lesson_id = $_GET['lesson_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// ---------- RETRY LOGIC (only if not mastered) ----------
if (isset($_POST['retry'])) {
    // Delete all quiz attempts for this lesson
    $conn->query("DELETE FROM quiz_attempts 
                   WHERE user_id = $user_id 
                     AND quiz_id IN (SELECT id FROM quizzes WHERE lesson_id = $lesson_id)");
    // Allow re‑attempt: remove score
    $conn->query("UPDATE progress 
                   SET score_percent = NULL, completed = 1 
                   WHERE user_id = $user_id AND lesson_id = $lesson_id");
    header("Location: quiz.php?lesson_id=" . $lesson_id);
    exit();
}

// ---------- CHECK IF ALREADY TAKEN ----------
$check = $conn->prepare("SELECT score_percent FROM progress 
                          WHERE user_id = ? AND lesson_id = ? AND score_percent IS NOT NULL");
$check->bind_param("ii", $user_id, $lesson_id);
$check->execute();
$checkResult = $check->get_result();
$already_taken = ($checkResult->num_rows > 0);
$previous_score = $already_taken ? $checkResult->fetch_assoc()['score_percent'] : null;

// See if topic is mastered (any correct answer for this lesson)
$mastered = false;
$master_res = $conn->query("SELECT COUNT(*) as cnt FROM quiz_attempts qa 
                            JOIN quizzes q ON qa.quiz_id = q.id 
                            WHERE qa.user_id = $user_id AND q.lesson_id = $lesson_id AND qa.is_correct = 1");
if ($master_res && $master_res->fetch_assoc()['cnt'] > 0) {
    $mastered = true;
}

// Fetch previous attempt for display (if already taken)
$previous_attempts = [];
if ($already_taken) {
    $attempt_res = $conn->query("
        SELECT qa.*, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct
        FROM quiz_attempts qa 
        JOIN quizzes q ON qa.quiz_id = q.id
        WHERE qa.user_id = $user_id AND q.lesson_id = $lesson_id
    ");
    while ($row = $attempt_res->fetch_assoc()) {
        $previous_attempts[] = $row;
    }
}

// ---------- FETCH A NEW QUESTION (avoid last one if retried) ----------
$avoid_id = $_SESSION['avoid_quiz_id'] ?? 0;

$query = "SELECT * FROM quizzes WHERE lesson_id = ?";
$params = [$lesson_id];
$types = "i";

if ($avoid_id > 0) {
    $count_other = $conn->query("SELECT COUNT(*) as c FROM quizzes WHERE lesson_id = $lesson_id AND id != $avoid_id")->fetch_assoc()['c'];
    if ($count_other > 0) {
        $query .= " AND id != ?";
        $params[] = $avoid_id;
        $types .= "i";
    }
}

$query .= " ORDER BY RAND() LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$question = $stmt->get_result()->fetch_assoc();

unset($_SESSION['avoid_quiz_id']);

if (!$question && !$already_taken) {
    echo "No quiz for this lesson yet.";
    exit();
}

// ---------- HANDLE QUIZ SUBMISSION ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_quiz']) && !$already_taken) {
    $quiz_id = $question['id'];
    $selected = $_POST['answers'][$quiz_id] ?? '';
    $correct = $question['correct'];
    $is_correct = ($selected === $correct) ? 1 : 0;

    $ins = $conn->prepare("INSERT INTO quiz_attempts (user_id, quiz_id, selected, is_correct) VALUES (?, ?, ?, ?)");
    $ins->bind_param("iisi", $user_id, $quiz_id, $selected, $is_correct);
    $ins->execute();
    $ins->close();

    $percent = $is_correct ? 100 : 0;

    $upd = $conn->prepare("INSERT INTO progress (user_id, lesson_id, completed, score_percent) 
                           VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE score_percent = ?");
    $upd->bind_param("iidd", $user_id, $lesson_id, $percent, $percent);
    $upd->execute();
    $upd->close();

    awardBadges($conn, $user_id);

    header("Location: quiz.php?lesson_id=" . $lesson_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz - PyLearn</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-brain me-2 text-primary"></i>Quiz</h2>

    <?php if ($already_taken && !empty($previous_attempts)): ?>
        <?php $last = $previous_attempts[0]; ?>
        <div class="card p-4 mb-4">
            <?php if ($mastered): ?>
                <!-- Mastery celebration -->
                <div class="alert alert-success">
                    <i class="fas fa-crown me-2"></i>
                    <strong>Congratulations! You have mastered this topic!</strong>
                </div>
            <?php else: ?>
                <div class="alert <?= $last['is_correct'] ? 'alert-success' : 'alert-danger' ?>">
                    <?= $last['is_correct'] ? '✅ Correct!' : '❌ Wrong' ?>
                </div>
            <?php endif; ?>

            <div class="quiz-question">
                <p class="fw-semibold"><?= htmlspecialchars($last['question']) ?></p>
                <?php foreach(['a','b','c','d'] as $opt): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" disabled <?= ($last['selected'] == $opt) ? 'checked' : '' ?>>
                        <label class="form-check-label <?= ($opt == $last['correct']) ? 'correct-answer' : (($last['selected'] == $opt && $opt != $last['correct']) ? 'wrong-answer' : '') ?>">
                            <?= htmlspecialchars($last['option_'.$opt]) ?>
                            <?= ($opt == $last['correct']) ? ' ✅' : '' ?>
                            <?= ($last['selected'] == $opt && $opt != $last['correct']) ? ' ❌' : '' ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$mastered): ?>
                <form method="post" class="mt-3">
                    <button type="submit" name="retry" class="btn btn-warning">
                        <i class="fas fa-redo me-1"></i> Retry Quiz (New Question)
                    </button>
                </form>
            <?php else: ?>
                <div class="mt-3">
                    <?php
                    // Find next lesson id to suggest
                    $next = $conn->query("SELECT id, slug FROM lessons WHERE order_no > (SELECT order_no FROM lessons WHERE id = $lesson_id) ORDER BY order_no LIMIT 1")->fetch_assoc();
                    ?>
                    <?php if ($next): ?>
                        <a href="lesson.php?slug=<?= $next['slug'] ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-1"></i> Next Lesson
                        </a>
                    <?php else: ?>
                        <a href="lessons.php" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i> All Lessons
                        </a>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-outline-secondary ms-2">Dashboard</a>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($question): ?>
        <div class="card p-4">
            <form method="post">
                <div class="quiz-question">
                    <p class="fw-semibold"><?= htmlspecialchars($question['question']) ?></p>
                    <?php foreach(['a','b','c','d'] as $opt): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="answers[<?= $question['id'] ?>]" value="<?= $opt ?>" required id="opt_<?= $opt ?>">
                            <label class="form-check-label" for="opt_<?= $opt ?>"><?= htmlspecialchars($question['option_'.$opt]) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="submit_quiz" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i> Submit Answer
                </button>
            </form>
        </div>
    <?php else: ?>
        <p>No questions available. Try again later.</p>
    <?php endif; ?>

    <div class="mt-4">
        <a href="lesson.php?slug=<?= $conn->query("SELECT slug FROM lessons WHERE id=$lesson_id")->fetch_assoc()['slug'] ?? '' ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Lesson
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>