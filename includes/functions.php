<?php
/**
 * Shared helper functions used across the SOUND site.
 * Loaded on every page right after config.php.
 */

/** Escape a value for safe HTML output. */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Return the logged-in user's session array, or null if nobody is logged in. */
function currentUser() {
    return $_SESSION['user'] ?? null;
}

/** Send a guest to the login page; only used on pages that require any login. */
function requireLogin() {
    if (!currentUser()) {
        header("Location: login.php");
        exit;
    }
}

/** Only admins may reach the page calling this; everyone else is bounced to login. */
function requireAdmin() {
    $user = currentUser();
    if (!$user || $user['role'] !== 'admin') {
        header("Location: login.php");
        exit;
    }
}

/**
 * Handle an optional uploaded image (cover/thumbnail) from the admin "Add" forms.
 * Returns the relative path to store in the database, or null if none/failed.
 */
function handleImageUpload($file, &$notice) {
    if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // optional — nothing uploaded is fine
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $notice = "Image upload mein masla hua (error code: " . (int)$file['error'] . ").";
        return null;
    }

    $allowed = ['jpg' => true, 'jpeg' => true, 'png' => true, 'webp' => true, 'gif' => true];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!array_key_exists($ext, $allowed)) {
        $notice = "Sirf jpg, jpeg, png, webp, gif images allowed hain.";
        return null;
    }

    $destDir = __DIR__ . "/../uploads/images";
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $safeName = uniqid('img_', true) . '.' . $ext;
    $destPath = "$destDir/$safeName";

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $notice = "Image save nahi ho saki — uploads folder ka permission check karein.";
        return null;
    }

    return "uploads/images/$safeName";
}

/**
 * Handle an optional uploaded audio/video file from the admin "Add" forms.
 * $file is one entry from $_FILES (or null); $kind is 'music' or 'video'.
 * Returns the relative path to store in the database (e.g. "uploads/music/xyz.mp3"),
 * or null if no file was uploaded / upload failed.
 */
function handleMediaUpload($file, string $kind, string &$notice) {
    if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // nothing uploaded, that's fine — it's optional
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $notice = "File upload mein masla hua (error code: " . (int)$file['error'] . ").";
        return null;
    }

    $allowed = $kind === 'video'
        ? ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg']
        : ['mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'ogg' => 'audio/ogg'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!array_key_exists($ext, $allowed)) {
        $notice = "Sirf " . implode(', ', array_keys($allowed)) . " files allowed hain.";
        return null;
    }

    $destDir = __DIR__ . "/../uploads/$kind";
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $safeName = uniqid($kind . '_', true) . '.' . $ext;
    $destPath = "$destDir/$safeName";

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $notice = "File save nahi ho saki — uploads folder ka permission check karein.";
        return null;
    }

    return "uploads/$kind/$safeName";
}

/** Distinct category values (genre/language/year) for filter dropdowns, sorted. */
function categoryValues(PDO $pdo, string $type): array {
    $stmt = $pdo->prepare("SELECT value FROM categories WHERE type = ? ORDER BY value ASC");
    $stmt->execute([$type]);
    return array_column($stmt->fetchAll(), 'value');
}

/** Average rating (rounded to 1 decimal) for a music/video item, or null if no reviews yet. */
function avgRating(PDO $pdo, string $type, int $id) {
    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg FROM reviews WHERE item_type = ? AND item_id = ?");
    $stmt->execute([$type, $id]);
    $avg = $stmt->fetch()['avg'];
    return $avg !== null ? round((float)$avg, 1) : null;
}

/** Number of reviews submitted for a music/video item. */
function reviewCount(PDO $pdo, string $type, int $id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM reviews WHERE item_type = ? AND item_id = ?");
    $stmt->execute([$type, $id]);
    return (int)$stmt->fetch()['c'];
}

/** Render a numeric rating (1-5, or an average like 4.3) as a ★★★★☆-style string. */
function starString($rating): string {
    $full = (int)round((float)$rating);
    $full = max(0, min(5, $full));
    return str_repeat('★', $full) . str_repeat('☆', 5 - $full);
}

/**
 * Render one grid card for a music or video row.
 * $type is 'music' or 'video'; used to build the detail.php link and the "kind" badge.
 */
function renderCard(PDO $pdo, array $item, string $type): string {
    $avg = avgRating($pdo, $type, (int)$item['id']);
    $count = reviewCount($pdo, $type, (int)$item['id']);
    $kindLabel = $type === 'video' ? 'Video' : 'Music';
    $initial = strtoupper(substr($item['title'], 0, 1));

    ob_start();
    ?>
    <a class="card" href="detail.php?type=<?= e($type) ?>&id=<?= (int)$item['id'] ?>">
      <div class="thumb">
        <?php if (!empty($item['is_new'])): ?><span class="new-flag">NEW</span><?php endif; ?>
        <?php if (!empty($item['image']) && file_exists(__DIR__ . '/../' . $item['image'])): ?>
          <img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
          <?= e($initial) ?>
        <?php endif; ?>
        <span class="kind"><?= e($kindLabel) ?></span>
      </div>
      <div class="card-body">
        <h3><?= e($item['title']) ?></h3>
        <div class="meta"><?= e($item['artist']) ?> — <?= e($item['album']) ?></div>
        <div class="meta"><?= e($item['year']) ?> · <?= e($item['genre']) ?> · <?= e($item['language']) ?></div>
        <div class="rating">
          <?= $avg ? starString($avg) . ' ' . $avg . '/5 (' . $count . ')' : 'No ratings yet' ?>
        </div>
      </div>
    </a>
    <?php
    return ob_get_clean();
}
