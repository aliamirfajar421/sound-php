<?php
require_once "config.php";
require_once "includes/functions.php";
requireAdmin();
$pageTitle = "Admin Panel";
$activePage = "admin";

$notice = "";

// ---- Handle POST actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_music') {
        $image = handleImageUpload($_FILES['image'] ?? null, $notice);
        $mediaFile = handleMediaUpload($_FILES['media_file'] ?? null, 'music', $notice);
        $stmt = $pdo->prepare("INSERT INTO music (title,artist,album,year,genre,language,description,media_file,image,is_new) VALUES (?,?,?,?,?,?,?,?,?,1)");
        $stmt->execute([
            trim($_POST['title']), trim($_POST['artist']), trim($_POST['album']), trim($_POST['year']),
            $_POST['genre'], $_POST['language'], trim($_POST['description']), $mediaFile, $image
        ]);
        $notice = $mediaFile ? "Music (with audio file) add ho gaya!" : "Music add ho gaya (koi audio file attach nahi hui).";
    }

    if ($action === 'add_video') {
        $image = handleImageUpload($_FILES['image'] ?? null, $notice);
        $mediaFile = handleMediaUpload($_FILES['media_file'] ?? null, 'video', $notice);
        $stmt = $pdo->prepare("INSERT INTO video (title,artist,album,year,genre,language,description,media_file,image,is_new) VALUES (?,?,?,?,?,?,?,?,?,1)");
        $stmt->execute([
            trim($_POST['title']), trim($_POST['artist']), trim($_POST['album']), trim($_POST['year']),
            $_POST['genre'], $_POST['language'], trim($_POST['description']), $mediaFile, $image
        ]);
        $notice = $mediaFile ? "Video (with video file) add ho gaya!" : "Video add ho gaya (koi video file attach nahi hui).";
    }

    if ($action === 'add_category') {
        $type = $_POST['cat_type'];
        $value = trim($_POST['cat_value']);
        if (in_array($type, ['genre','language','year'], true) && $value !== '') {
            $stmt = $pdo->prepare("INSERT IGNORE INTO categories (type,value) VALUES (?,?)");
            $stmt->execute([$type, $value]);
            $notice = "Category add ho gayi!";
        }
    }

    if ($action === 'add_user') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || !$phone || !$address || !$password) {
            $notice = "User add karne ke liye sab fields (Name, Email, Phone, Address, Password) zaroori hain.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $notice = "Sahi email address likhein.";
        } elseif (strlen($password) < 6) {
            $notice = "Password kam az kam 6 characters ka ho.";
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $notice = "Ye email pehle se registered hai.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name,email,phone,address,password,role) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$name, $email, $phone, $address, $hash, $role]);
                $notice = "User add ho gaya!";
            }
        }
    }
}

// ---- Handle GET delete actions ----
if (isset($_GET['delete_music'])) {
    $pdo->prepare("DELETE FROM music WHERE id = ?")->execute([(int)$_GET['delete_music']]);
    header("Location: admin.php?tab=manage-music"); exit;
}
if (isset($_GET['delete_video'])) {
    $pdo->prepare("DELETE FROM video WHERE id = ?")->execute([(int)$_GET['delete_video']]);
    header("Location: admin.php?tab=manage-video"); exit;
}
if (isset($_GET['delete_user'])) {
    $targetId = (int)$_GET['delete_user'];
    $me = currentUser();
    if ($me && $targetId === (int)$me['id']) {
        $notice = "Aap apna hi account delete nahi kar sakte.";
    } else {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$targetId]);
        header("Location: admin.php?tab=users"); exit;
    }
}

// ---- Data for the page ----
$musicCount = $pdo->query("SELECT COUNT(*) c FROM music")->fetch()['c'];
$videoCount = $pdo->query("SELECT COUNT(*) c FROM video")->fetch()['c'];
$userCount  = $pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'];
$reviewCount = $pdo->query("SELECT COUNT(*) c FROM reviews")->fetch()['c'];

$allMusic = $pdo->query("SELECT * FROM music ORDER BY created_at DESC")->fetchAll();
$allVideo = $pdo->query("SELECT * FROM video ORDER BY created_at DESC")->fetchAll();
$allUsers = $pdo->query("SELECT id,name,email,phone,address,role FROM users ORDER BY created_at DESC")->fetchAll();

$genres = categoryValues($pdo, 'genre');
$languages = categoryValues($pdo, 'language');
$years = categoryValues($pdo, 'year');

require "includes/header.php";
?>

<main class="wrap">
  <div class="section-head"><h2>Admin Panel</h2></div>

  <?php if ($notice): ?>
    <p style="background:var(--bg-panel);border:1px solid var(--amber);color:var(--amber);padding:12px 16px;border-radius:8px;margin-bottom:24px;"><?= e($notice) ?></p>
  <?php endif; ?>

  <div class="stat-row">
    <div class="stat-card"><div class="num"><?= $musicCount ?></div><div class="lbl">Music tracks</div></div>
    <div class="stat-card"><div class="num"><?= $videoCount ?></div><div class="lbl">Videos</div></div>
    <div class="stat-card"><div class="num"><?= $userCount ?></div><div class="lbl">Registered users</div></div>
    <div class="stat-card"><div class="num"><?= $reviewCount ?></div><div class="lbl">Reviews submitted</div></div>
  </div>

  <div class="tabs">
    <button class="tab-btn active" data-tab="add-music">Add Music</button>
    <button class="tab-btn" data-tab="add-video">Add Video</button>
    <button class="tab-btn" data-tab="manage-music">Manage Music</button>
    <button class="tab-btn" data-tab="manage-video">Manage Video</button>
    <button class="tab-btn" data-tab="categories">Categories</button>
    <button class="tab-btn" data-tab="users">Users</button>
  </div>

  <!-- Add Music -->
  <div class="tab-panel active" id="add-music">
    <div class="form-panel wide">
      <h2>Add a Music file</h2>
      <p class="sub">Fill in the details below. Fields marked required must be completed.</p>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_music">
        <div class="form-row">
          <div><label>Title</label><input type="text" name="title" required></div>
          <div><label>Artist</label><input type="text" name="artist" required></div>
        </div>
        <div class="form-row">
          <div><label>Album</label><input type="text" name="album" required></div>
          <div><label>Year</label><input type="text" name="year" required></div>
        </div>
        <div class="form-row">
          <div><label>Genre</label>
            <select name="genre"><?php foreach ($genres as $g): ?><option value="<?= e($g) ?>"><?= e($g) ?></option><?php endforeach; ?></select>
          </div>
          <div><label>Language</label>
            <select name="language"><?php foreach ($languages as $l): ?><option value="<?= e($l) ?>"><?= e($l) ?></option><?php endforeach; ?></select>
          </div>
        </div>
        <label>Description</label>
        <textarea name="description"></textarea>
        <label>Cover picture (jpg/png/webp — optional)</label>
        <input type="file" name="image" accept="image/*">
        <label>Audio file (mp3/wav/ogg — optional, max 20MB)</label>
        <input type="file" name="media_file" accept="audio/*">
        <button type="submit" class="pill-btn full-btn">Add Music</button>
      </form>
    </div>
  </div>

  <!-- Add Video -->
  <div class="tab-panel" id="add-video">
    <div class="form-panel wide">
      <h2>Add a Video file</h2>
      <p class="sub">Fill in the details below. Fields marked required must be completed.</p>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_video">
        <div class="form-row">
          <div><label>Title</label><input type="text" name="title" required></div>
          <div><label>Artist</label><input type="text" name="artist" required></div>
        </div>
        <div class="form-row">
          <div><label>Album</label><input type="text" name="album" required></div>
          <div><label>Year</label><input type="text" name="year" required></div>
        </div>
        <div class="form-row">
          <div><label>Genre</label>
            <select name="genre"><?php foreach ($genres as $g): ?><option value="<?= e($g) ?>"><?= e($g) ?></option><?php endforeach; ?></select>
          </div>
          <div><label>Language</label>
            <select name="language"><?php foreach ($languages as $l): ?><option value="<?= e($l) ?>"><?= e($l) ?></option><?php endforeach; ?></select>
          </div>
        </div>
        <label>Description</label>
        <textarea name="description"></textarea>
        <label>Cover picture (jpg/png/webp — optional)</label>
        <input type="file" name="image" accept="image/*">
        <label>Video file (mp4/webm — optional, max 20MB)</label>
        <input type="file" name="media_file" accept="video/*">
        <button type="submit" class="pill-btn full-btn">Add Video</button>
      </form>
    </div>
  </div>

  <!-- Manage Music -->
  <div class="tab-panel" id="manage-music">
    <table class="admin-table">
      <thead><tr><th>Title</th><th>Artist</th><th>Album</th><th>Year</th><th>Genre</th><th></th></tr></thead>
      <tbody>
        <?php if ($allMusic): foreach ($allMusic as $m): ?>
          <tr>
            <td><?= e($m['title']) ?></td><td><?= e($m['artist']) ?></td><td><?= e($m['album']) ?></td>
            <td><?= e($m['year']) ?></td><td><?= e($m['genre']) ?></td>
            <td><a class="del-btn" href="admin.php?delete_music=<?= (int)$m['id'] ?>" onclick="return confirm('Ye music delete karna hai?');">Delete</a></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6">Koi music nahi hai.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Manage Video -->
  <div class="tab-panel" id="manage-video">
    <table class="admin-table">
      <thead><tr><th>Title</th><th>Artist</th><th>Album</th><th>Year</th><th>Genre</th><th></th></tr></thead>
      <tbody>
        <?php if ($allVideo): foreach ($allVideo as $v): ?>
          <tr>
            <td><?= e($v['title']) ?></td><td><?= e($v['artist']) ?></td><td><?= e($v['album']) ?></td>
            <td><?= e($v['year']) ?></td><td><?= e($v['genre']) ?></td>
            <td><a class="del-btn" href="admin.php?delete_video=<?= (int)$v['id'] ?>" onclick="return confirm('Ye video delete karna hai?');">Delete</a></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6">Koi video nahi hai.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Categories -->
  <div class="tab-panel" id="categories">
    <div class="form-panel wide" style="max-width:100%;">
      <h2>Manage Categories</h2>
      <p class="sub">Create new Genre, Language or Year categories used when adding Music/Video.</p>
      <form method="post">
        <input type="hidden" name="action" value="add_category">
        <div class="form-row">
          <div>
            <label>Category type</label>
            <select name="cat_type">
              <option value="genre">Genre</option>
              <option value="language">Language</option>
              <option value="year">Year</option>
            </select>
          </div>
          <div>
            <label>New value</label>
            <input type="text" name="cat_value" placeholder="e.g. Jazz">
          </div>
        </div>
        <button type="submit" class="pill-btn full-btn" style="max-width:220px;">Add Category</button>
      </form>

      <div style="margin-top:34px;">
        <?php foreach (['genre'=>$genres,'language'=>$languages,'year'=>$years] as $label => $vals): ?>
          <div style="margin-bottom:18px;">
            <div class="meta" style="text-transform:capitalize;margin-bottom:8px;"><?= e($label) ?></div>
            <div class="tag-row"><?php foreach ($vals as $v): ?><span class="tag"><?= e($v) ?></span><?php endforeach; ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Users -->
  <div class="tab-panel" id="users">
    <div class="form-panel wide" style="max-width:100%;margin-bottom:32px;">
      <h2>Add a new User</h2>
      <p class="sub">Admin can create user (or admin) accounts directly from here.</p>
      <form method="post">
        <input type="hidden" name="action" value="add_user">
        <div class="form-row">
          <div><label>Full name</label><input type="text" name="name" required></div>
          <div><label>Email</label><input type="email" name="email" required></div>
        </div>
        <div class="form-row">
          <div><label>Phone</label><input type="tel" name="phone" required></div>
          <div><label>Address</label><input type="text" name="address" required></div>
        </div>
        <div class="form-row">
          <div><label>Password</label><input type="password" name="password" required minlength="6"></div>
          <div><label>Role</label>
            <select name="role">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <button type="submit" class="pill-btn full-btn" style="max-width:220px;">Add User</button>
      </form>
    </div>

    <table class="admin-table">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Role</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($allUsers as $u): ?>
          <tr>
            <td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td>
            <td><?= e($u['address']) ?></td><td style="text-transform:capitalize;"><?= e($u['role']) ?></td>
            <td>
              <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                <a class="del-btn" href="admin.php?delete_user=<?= (int)$u['id'] ?>&tab=users" onclick="return confirm('Ye user delete karna hai?');">Delete</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</main>

<?php require "includes/footer.php"; ?>

<script>
  document.querySelectorAll('.tab-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });
  const params = new URLSearchParams(window.location.search);
  const tab = params.get('tab');
  if (tab && document.getElementById(tab)) {
    document.querySelector('.tab-btn[data-tab="'+tab+'"]').click();
  }
</script>
