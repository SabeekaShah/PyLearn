<?php
$user = isset($pdo) ? currentUser($pdo) : null;
$page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | PyLearn' : 'PyLearn – Learn Python' ?></title>
    <link rel="stylesheet" href="/pylearn/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">
        <span class="logo">🐍</span>
        <a href="/pylearn/index.php" class="brand-name">PyLearn</a>
    </div>
    <div class="nav-links">
        <?php if ($user): ?>
            <a href="/pylearn/dashboard.php" class="<?= $page==='dashboard'?'active':'' ?>"><i class="fa fa-home"></i> Dashboard</a>
            <a href="/pylearn/lessons.php" class="<?= $page==='lessons'?'active':'' ?>"><i class="fa fa-book"></i> Lessons</a>
            <a href="/pylearn/playground.php" class="<?= $page==='playground'?'active':'' ?>"><i class="fa fa-code"></i> Playground</a>
            <a href="/pylearn/leaderboard.php" class="<?= $page==='leaderboard'?'active':'' ?>"><i class="fa fa-trophy"></i> Leaderboard</a>
            <?php if ($user['role']==='admin'): ?>
            <a href="/pylearn/admin/index.php" class="admin-link"><i class="fa fa-shield"></i> Admin</a>
            <?php endif; ?>
            <div class="nav-user">
                <div class="user-xp"><i class="fa fa-bolt"></i> <?= $user['xp'] ?> XP</div>
                <a href="/pylearn/profile.php" class="nav-avatar">
                    <div class="avatar-sm"><?= strtoupper(substr($user['username'],0,1)) ?></div>
                    <?= htmlspecialchars($user['username']) ?>
                </a>
                <a href="/pylearn/logout.php" class="btn-logout"><i class="fa fa-sign-out-alt"></i></a>
            </div>
        <?php else: ?>
            <a href="/pylearn/login.php">Login</a>
            <a href="/pylearn/register.php" class="btn-primary">Get Started</a>
        <?php endif; ?>
    </div>
    <div class="hamburger" onclick="toggleMenu()"><i class="fa fa-bars"></i></div>
</nav>
<div class="nav-mobile" id="mobileMenu">
    <?php if ($user): ?>
        <a href="/pylearn/dashboard.php">Dashboard</a>
        <a href="/pylearn/lessons.php">Lessons</a>
        <a href="/pylearn/playground.php">Playground</a>
        <a href="/pylearn/leaderboard.php">Leaderboard</a>
        <a href="/pylearn/logout.php">Logout</a>
    <?php else: ?>
        <a href="/pylearn/login.php">Login</a>
        <a href="/pylearn/register.php">Register</a>
    <?php endif; ?>
</div>
