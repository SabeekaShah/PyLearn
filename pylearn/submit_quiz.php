<?php
require_once 'includes/config.php';
requireLogin();
header('Content-Type: application/json');

$user = currentUser($pdo);
$lesson_id = intval($_POST['lesson_id'] ?? 0);

if (!$lesson_id) { echo json_encode(['error'=>'Invalid lesson']); exit; }

// Get quizzes for this lesson
$quizzes = $pdo->prepare("SELECT * FROM quizzes WHERE lesson_id=?");
$quizzes->execute([$lesson_id]);
$qlist = $quizzes->fetchAll();

$correct = 0;
$results = [];
$perfect = true;

foreach ($qlist as $q) {
    $selected = strtoupper($_POST['q'.$q['id']] ?? '');
    $is_correct = $selected === $q['correct_answer'];
    if ($is_correct) $correct++;
    else $perfect = false;
    $results[] = [
        'quiz_id' => $q['id'],
        'selected' => $selected,
        'correct' => $q['correct_answer'],
        'is_correct' => $is_correct
    ];
}

$total = count($qlist);
$score_pct = $total > 0 ? round(($correct/$total)*100) : 0;

// Award XP
$lesson = $pdo->prepare("SELECT * FROM lessons WHERE id=?")->execute([$lesson_id]);
$lesson_data = $pdo->prepare("SELECT xp_reward FROM lessons WHERE id=?")->execute([$lesson_id]);
$lesson_stmt = $pdo->prepare("SELECT xp_reward FROM lessons WHERE id=?");
$lesson_stmt->execute([$lesson_id]);
$lesson_row = $lesson_stmt->fetch();
$base_xp = $lesson_row['xp_reward'] ?? 10;

$xp_earned = 0;
$level_up = false;
$new_level = $user['level'];
$badge_name = null;

// Only award XP on first pass or improvement
$existing = $pdo->prepare("SELECT * FROM user_progress WHERE user_id=? AND lesson_id=?");
$existing->execute([$user['id'], $lesson_id]);
$exist = $existing->fetch();

$already_completed = $exist && $exist['completed'];
if (!$already_completed) {
    $xp_earned = $base_xp;
    if ($perfect) $xp_earned += 5; // Bonus for perfect
    awardXP($pdo, $user['id'], $xp_earned);
    // Check level up
    $updated = $pdo->prepare("SELECT level FROM users WHERE id=?")->execute([$user['id']]);
    $upd = $pdo->prepare("SELECT level FROM users WHERE id=?");
    $upd->execute([$user['id']]);
    $new_level = $upd->fetchColumn();
    if ($new_level > $user['level']) $level_up = true;
}

// Mark as completed
$pdo->prepare("UPDATE user_progress SET completed=1, quiz_score=?, completed_at=NOW() WHERE user_id=? AND lesson_id=?")
    ->execute([$score_pct, $user['id'], $lesson_id]);

// Update leaderboard lessons count
$pdo->prepare("UPDATE leaderboard SET lessons_done=(SELECT COUNT(*) FROM user_progress WHERE user_id=? AND completed=1) WHERE user_id=?")
    ->execute([$user['id'], $user['id']]);

// Check for perfect quiz badge
if ($perfect && !$already_completed) {
    $pdo->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, 2)")->execute([$user['id']]);
    $badge_name = '🏆 Quiz Master';
}

// Check all badges
checkBadges($pdo, $user['id']);

echo json_encode([
    'score' => $correct,
    'total' => $total,
    'score_pct' => $score_pct,
    'xp_earned' => $xp_earned,
    'results' => $results,
    'level_up' => $level_up,
    'new_level' => $new_level,
    'badge' => $badge_name
]);
