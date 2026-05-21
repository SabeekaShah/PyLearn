<?php
function awardBadges($conn, $user_id) {
    // 1. Lesson completion badges
    $cnt = $conn->query("SELECT COUNT(DISTINCT lesson_id) as c FROM progress WHERE user_id=$user_id AND completed=1")->fetch_assoc()['c'];
    
    // First Step
    if ($cnt >= 1) {
        $exists = $conn->query("SELECT id FROM badges WHERE user_id=$user_id AND badge_name='First Step'");
        if ($exists->num_rows == 0) {
            $conn->query("INSERT INTO badges (user_id, badge_name) VALUES ($user_id, 'First Step')");
            echo "<script>Toastify({text: 'Badge earned: First Step!', duration: 3000}).showToast();</script>";
        }
    }
    // Halfway Hero
    if ($cnt >= 5) {
        $exists = $conn->query("SELECT id FROM badges WHERE user_id=$user_id AND badge_name='Halfway Hero'");
        if ($exists->num_rows == 0) {
            $conn->query("INSERT INTO badges (user_id, badge_name) VALUES ($user_id, 'Halfway Hero')");
            echo "<script>Toastify({text: 'Badge earned: Halfway Hero!', duration: 3000}).showToast();</script>";
        }
    }
    // Python Pro
    $total = $conn->query("SELECT COUNT(*) as c FROM lessons")->fetch_assoc()['c'];
    if ($cnt == $total) {
        $exists = $conn->query("SELECT id FROM badges WHERE user_id=$user_id AND badge_name='Python Pro'");
        if ($exists->num_rows == 0) {
            $conn->query("INSERT INTO badges (user_id, badge_name) VALUES ($user_id, 'Python Pro')");
            echo "<script>Toastify({text: 'Badge earned: Python Pro!', duration: 3000}).showToast();</script>";
        }
    }

    // 2. Quiz badges
    // Quiz Master: any quiz attempt with 100% score
    $perfect = $conn->query("SELECT COUNT(*) as c FROM progress WHERE user_id=$user_id AND completed=1 AND score_percent = 100");
    if ($perfect->fetch_assoc()['c'] > 0) {
        $exists = $conn->query("SELECT id FROM badges WHERE user_id=$user_id AND badge_name='Quiz Master'");
        if ($exists->num_rows == 0) {
            $conn->query("INSERT INTO badges (user_id, badge_name) VALUES ($user_id, 'Quiz Master')");
            echo "<script>Toastify({text: 'Badge earned: Quiz Master!', duration: 3000}).showToast();</script>";
        }
    }

    // Quiz Whiz: overall quiz accuracy >= 80% (based on quiz_attempts)
    $stats = $conn->query("SELECT SUM(is_correct) as correct, COUNT(*) as total FROM quiz_attempts WHERE user_id=$user_id")->fetch_assoc();
    if ($stats['total'] > 0) {
        $accuracy = ($stats['correct'] / $stats['total']) * 100;
        if ($accuracy >= 80) {
            $exists = $conn->query("SELECT id FROM badges WHERE user_id=$user_id AND badge_name='Quiz Whiz'");
            if ($exists->num_rows == 0) {
                $conn->query("INSERT INTO badges (user_id, badge_name) VALUES ($user_id, 'Quiz Whiz')");
                echo "<script>Toastify({text: 'Badge earned: Quiz Whiz!', duration: 3000}).showToast();</script>";
            }
        }
    }
}
?>