<?php
require_once "config.php";
require_once "includes/functions.php";
$pageTitle = "Home";
$activePage = "home";
require "includes/header.php";

$latestMusic = $pdo->query("SELECT * FROM music ORDER BY created_at DESC LIMIT 5")->fetchAll();
$latestVideo = $pdo->query("SELECT * FROM video ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<main class="wrap">

  <section class="hero">
    <div>
      <h1>Every song.<br>Every video.<br>One stage.</h1>
      <p class="lede">SOUND brings together the latest Music and Videos across English and regional languages — browse by album, artist, year or genre, then rate and review what moves you.</p>
      <div class="hero-actions">
        <a href="music.php" class="pill-btn">Browse Music</a>
        <a href="video.php" class="pill-btn ghost">Browse Video</a>
      </div>
    </div>
    <div class="hero-art">
      <div class="hero-orb"></div>
      <span class="floating-icon note1">♪</span>
      <span class="floating-icon note2">♫</span>
      <span class="floating-icon note3">♬</span>
      <div class="play-badge">▶</div>
      <div class="eq-bars">
        <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
      </div>
    </div>
  </section>

  <section>
    <div class="section-head">
      <h2>Latest Music</h2>
      <a href="music.php" class="see-all">See all music →</a>
    </div>
    <div class="grid">
      <?php foreach ($latestMusic as $m) echo renderCard($pdo, $m, 'music'); ?>
    </div>
  </section>

  <section>
    <div class="section-head">
      <h2>Latest Video</h2>
      <a href="video.php" class="see-all">See all video →</a>
    </div>
    <div class="grid">
      <?php foreach ($latestVideo as $v) echo renderCard($pdo, $v, 'video'); ?>
    </div>
  </section>

  <section style="border-top:1px solid var(--line);padding-top:40px;">
    <div class="section-head"><h2>About SOUND</h2></div>
    <p style="color:var(--muted);max-width:70ch;">SOUND hosts new and classic Music and Video in both regional and English languages. Every title is organised by Album, Artist, Year, Genre and Language, and every listener can review and rate what they hear and watch. New additions are marked with a <span style="color:var(--amber);font-weight:600;">NEW</span> flag so nothing gets missed.</p>
  </section>

</main>

<?php require "includes/footer.php"; ?>
