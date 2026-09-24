<?php
/**
 * Shared page header: <head>, opening <body>, and the site nav.
 * Expects $pageTitle and $activePage to be set by the including page.
 * Requires config.php + functions.php to already be loaded (for currentUser(), e()).
 */
$user = currentUser();
$title = isset($pageTitle) ? $pageTitle . " — SOUND" : "SOUND";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="site">
  <a href="index.php" class="brand">SOUND<span>.</span></a>

  <nav class="main">
    <a href="index.php" class="<?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">Home</a>
    <a href="music.php" class="<?= ($activePage ?? '') === 'music' ? 'active' : '' ?>">Music</a>
    <a href="video.php" class="<?= ($activePage ?? '') === 'video' ? 'active' : '' ?>">Video</a>
    <?php if ($user && $user['role'] === 'admin'): ?>
      <a href="admin.php" class="<?= ($activePage ?? '') === 'admin' ? 'active' : '' ?>">Admin Panel</a>
    <?php endif; ?>
  </nav>

  <div class="nav-right">
    <?php if ($user): ?>
      <div class="user-chip">Hi, <b><?= e($user['name']) ?></b></div>
      <a href="logout.php" class="pill-btn ghost">Log out</a>
    <?php else: ?>
      <a href="login.php" class="pill-btn ghost">Log in</a>
      <a href="register.php" class="pill-btn">Sign up</a>
    <?php endif; ?>
  </div>
</header>
