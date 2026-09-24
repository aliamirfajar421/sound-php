<?php
require_once "config.php";
require_once "includes/functions.php";
$pageTitle = "Details";
$activePage = "";

$type = ($_GET['type'] ?? '') === 'video' ? 'video' : 'music';
$id = (int)($_GET['id'] ?? 0);
$table = $type === 'video' ? 'video' : 'music';

$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

$user = currentUser();
$reviewError = "";

// Handle review submit (insert or update this user's review for this item)
if ($item && $user && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $text = trim($_POST['review_text'] ?? '');
    if ($rating < 1 || $rating > 5) {
        $reviewError = "Pehle ek star rating select karein.";
    } elseif ($text === '') {
        $reviewError = "Review text likhein.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (item_type, item_id, user_id, rating, review_text)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text)
        ");
        $stmt->execute([$type, $id, $user['id'], $rating, $text]);
        header("Location: detail.php?type=$type&id=$id");
        exit;
    }
}

require "includes/header.php";

if (!$item) {
    echo '<main class="wrap" style="padding-top:40px;"><div class="empty-state">Ye item nahi mila.</div></main>';
    require "includes/footer.php";
    exit;
}

$avg = avgRating($pdo, $type, $id);
$count = reviewCount($pdo, $type, $id);
$reviewsStmt = $pdo->prepare("
    SELECT r.*, u.name AS user_name FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.item_type = ? AND r.item_id = ?
    ORDER BY r.created_at DESC
");
$reviewsStmt->execute([$type, $id]);
$reviews = $reviewsStmt->fetchAll();
?>

<main class="wrap" style="padding-top:40px;">

  <div class="detail-head">
    <?php
      $hasImage = !empty($item['image']) && file_exists(__DIR__ . '/' . $item['image']);
      $artStyle = $hasImage
        ? "background-image:url('" . e($item['image']) . "');background-size:cover;background-position:center;"
        : "";
    ?>
    <div class="detail-art" style="<?= $artStyle ?>">
      <?php if (!empty($item['media_file']) && file_exists(__DIR__ . '/' . $item['media_file'])): ?>
        <?php if ($type === 'video'): ?>
          <video controls style="width:100%;height:100%;border-radius:14px;object-fit:cover;background:#000;">
            <source src="<?= e($item['media_file']) ?>">
            Your browser does not support video playback.
          </video>
        <?php else: ?>
          <div style="display:flex;align-items:center;justify-content:center;height:100%;<?= $hasImage ? 'background:rgba(0,0,0,.45);border-radius:14px;' : '' ?>">
            <audio controls style="width:90%;">
              <source src="<?= e($item['media_file']) ?>">
              Your browser does not support audio playback.
            </audio>
          </div>
        <?php endif; ?>
      <?php elseif (!$hasImage): ?>
        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted);font-size:.85rem;text-align:center;padding:20px;">
          No <?= $type === 'video' ? 'video' : 'audio' ?> file uploaded yet.<br>Admin can add one from the Admin Panel.
        </div>
      <?php endif; ?>
    </div>
    <div>
      <div class="meta" style="color:var(--muted);text-transform:uppercase;font-size:.78rem;letter-spacing:.04em;">
        <?= $type === 'video' ? 'Video' : 'Music' ?><?= $item['is_new'] ? ' · New' : '' ?>
      </div>
      <h1 style="font-size:2.2rem;margin-top:6px;"><?= e($item['title']) ?></h1>
      <div class="meta" style="font-size:1rem;"><?= e($item['artist']) ?> — <?= e($item['album']) ?></div>
      <div class="tag-row">
        <span class="tag">Year: <?= e($item['year']) ?></span>
        <span class="tag">Genre: <?= e($item['genre']) ?></span>
        <span class="tag">Language: <?= e($item['language']) ?></span>
      </div>
      <p style="color:var(--muted);max-width:60ch;"><?= e($item['description']) ?></p>
      <div style="margin-top:16px;">
        <span class="stars"><?= $avg ? starString($avg) : '☆☆☆☆☆' ?></span>
        <span class="meta"> <?= $avg ? $avg . ' / 5 (' . $count . ' review' . ($count===1?'':'s') . ')' : 'No ratings yet' ?></span>
      </div>
    </div>
  </div>

  <section>
    <div class="section-head"><h2>Reviews & Ratings</h2></div>

    <?php if ($reviews): ?>
      <?php foreach ($reviews as $r): ?>
        <div class="review-item">
          <div class="who"><?= e($r['user_name']) ?></div>
          <div class="stars"><?= starString($r['rating']) ?></div>
          <p><?= e($r['review_text']) ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">Abhi koi review nahi hai — pehla review aap dein!</div>
    <?php endif; ?>

    <?php if ($user): ?>
      <?php
        $existing = null;
        foreach ($reviews as $r) if ($r['user_id'] == $user['id']) $existing = $r;
      ?>
      <div class="review-form">
        <h3 style="font-size:1.05rem;"><?= $existing ? 'Update your review' : 'Add your review' ?></h3>
        <form method="post" id="review-form">
          <input type="hidden" name="rating" id="rating-input" value="<?= $existing ? (int)$existing['rating'] : 0 ?>">
          <div class="star-select" id="star-select">
            <?php for ($n=1;$n<=5;$n++): ?>
              <span data-val="<?= $n ?>" class="<?= ($existing && $n <= $existing['rating']) ? 'on' : '' ?>">★</span>
            <?php endfor; ?>
          </div>
          <textarea name="review_text" placeholder="Ye music/video kaisa laga?"><?= $existing ? e($existing['review_text']) : '' ?></textarea>
          <?php if ($reviewError): ?><p class="error-text" style="display:block;"><?= e($reviewError) ?></p><?php endif; ?>
          <button type="submit" class="pill-btn full-btn">Submit review</button>
        </form>
      </div>
      <script>
        const stars = document.querySelectorAll('#star-select span');
        const input = document.getElementById('rating-input');
        stars.forEach(s=>{
          s.addEventListener('click', ()=>{
            const val = parseInt(s.dataset.val);
            input.value = val;
            stars.forEach(st => st.classList.toggle('on', parseInt(st.dataset.val) <= val));
          });
        });
      </script>
    <?php else: ?>
      <p class="form-note" style="margin-top:20px;"><a href="login.php" style="color:var(--amber);font-weight:600;">Log in</a> to add a review and rating.</p>
    <?php endif; ?>
  </section>
</main>

<?php require "includes/footer.php"; ?>
