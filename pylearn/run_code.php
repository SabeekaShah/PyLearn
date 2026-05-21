<?php
require_once 'includes/config.php';
requireLogin();
header('Content-Type: application/json');

$user = currentUser($pdo);
$input = json_decode(file_get_contents('php://input'), true);
$code = $input['code'] ?? '';
$save_only = $input['save_only'] ?? false;

if (!$code) { echo json_encode(['output'=>'', 'error'=>'No code provided']); exit; }

// Sanitize: block dangerous functions
$blocked = ['exec','shell_exec','system','passthru','popen','proc_open','file_get_contents','file_put_contents','unlink','rmdir','mkdir'];
foreach ($blocked as $fn) {
    if (stripos($code, $fn . '(') !== false) {
        echo json_encode(['output'=>'', 'error'=>"Function '$fn' is not allowed for security reasons."]);
        exit;
    }
}

$output = '';
$error = '';

if (!$save_only) {
    // Try to execute Python if available
    $python = exec('which python3 2>/dev/null') ?: exec('which python 2>/dev/null');
    
    if ($python) {
        // Write to temp file
        $tmp = tempnam(sys_get_temp_dir(), 'pylearn_');
        file_put_contents($tmp, $code);
        
        $cmd = escapeshellcmd($python) . ' ' . escapeshellarg($tmp) . ' 2>&1';
        $descriptors = [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']];
        $proc = proc_open($cmd, $descriptors, $pipes);
        
        if (is_resource($proc)) {
            fclose($pipes[0]);
            stream_set_timeout($pipes[1], 5);
            $output = stream_get_contents($pipes[1], 10000);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);
        }
        unlink($tmp);
        if (!$output) $output = '(No output)';
    } else {
        // Simulate basic output
        $output = simulateBasicPython($code);
    }
}

// Save submission
$pdo->prepare("INSERT INTO code_submissions (user_id, code, output) VALUES (?,?,?)")
    ->execute([$user['id'], $code, $output]);

// Award XP for first 10 submissions
$count = $pdo->prepare("SELECT COUNT(*) FROM code_submissions WHERE user_id=?");
$count->execute([$user['id']]);
$sub_count = $count->fetchColumn();
$xp_awarded = 0;
if ($sub_count <= 10) {
    awardXP($pdo, $user['id'], 2);
    $xp_awarded = 2;
}
checkBadges($pdo, $user['id']);

echo json_encode(['output'=>$output,'saved'=>true,'xp'=>$xp_awarded,'submissions'=>$sub_count]);

function simulateBasicPython($code) {
    $lines = explode("\n", $code);
    $out_lines = [];
    foreach ($lines as $line) {
        $t = trim($line);
        if (preg_match('/^print\s*\(\s*["\'](.+?)["\']\s*\)\s*$/', $t, $m)) {
            $out_lines[] = $m[1];
        } elseif (preg_match('/^print\s*\((.+)\)\s*$/', $t, $m)) {
            $out_lines[] = $m[1];
        }
    }
    return $out_lines ? implode("\n", $out_lines) : "(Python not available on server — install Python 3 for execution)";
}
