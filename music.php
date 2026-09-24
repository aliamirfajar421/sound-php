<?php
require_once "config.php";
require_once "includes/functions.php";
$pageTitle = "Music";
$activePage = "music";

$q = trim($_GET['q'] ?? '');
$genre = $_GET['genre'] ?? '';
$language = $_GET['language'] ?? '';
$year = $_GET['year'] ?? '';

$sql = "SELECT * FROM music WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= " AND (title LIKE ? OR artist LIKE ? OR album LIKE ?)";
    $like = "%$q%";
    array_push($params, $like, $like, $like);
}
if ($genre !== '') { $sql .= " AND genre = ?"; $params[] = $genre; }
if ($language !== '') { $sql .= " AND language = ?"; $params[] = $language; }
if ($year !== '') { $sql .= " AND year = ?"; $params[] = $year; }
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

$genres = categoryValues($pdo, 'genre');
$languages = categoryValues($pdo, 'language');
$years = categoryValues($pdo, 'year');

require "includes/header.php";
?>

<main class="wrap">
  <div class="section-head"><h2>Music</h2></div>

  <form class="filter-bar" method="get">
    <input type="text" name="q" placeholder="Search by title, artist or album..." value="<?= e($q) ?>">
    <select name="genre" onchange="this.form.submit()">
      <option value="">All genres</option>
      <?php foreach ($genres as $g): ?>
        <option value="<?= e($g) ?>" <?= $g === $genre ? 'selected' : '' ?>><?= e($g) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="language" onchange="this.form.submit()">
      <option value="">All languages</option>
      <?php foreach ($languages as $l): ?>
        <option value="<?= e($l) ?>" <?= $l === $language ? 'selected' : '' ?>><?= e($l) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="year" onchange="this.form.submit()">
      <option value="">All years</option>
      <?php foreach ($years as $y): ?>
        <option value="<?= e($y) ?>" <?= $y === $year ? 'selected' : '' ?>><?= e($y) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="pill-btn ghost">Search</button>
  </form>

  <?php if ($results): ?>
    <div class="grid">
      <?php foreach ($results as $m) echo renderCard($pdo, $m, 'music'); ?>
    </div>
  <?php else: ?>
    <div class="empty-state">Koi music is filter se nahi mila. Filters change karke dobara try karein.</div>
  <?php endif; ?>
</main>

<?php require "includes/footer.php"; ?>
