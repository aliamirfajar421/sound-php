<?php
require_once "config.php";
require_once "includes/functions.php";
$pageTitle = "Sign up";
$activePage = "";

$error = "";
$old = ['name'=>'','email'=>'','phone'=>'','address'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['address'] = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$old['name'] || !$old['email'] || !$old['phone'] || !$old['address'] || !$password) {
        $error = "Sab fields bharna zaroori hai.";
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Sahi email address likhein.";
    } elseif (strlen($password) < 6) {
        $error = "Password kam az kam 6 characters ka ho.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$old['email']]);
        if ($check->fetch()) {
            $error = "Ye email pehle se register hai.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name,email,phone,address,password,role) VALUES (?,?,?,?,?,'user')");
            $stmt->execute([$old['name'], $old['email'], $old['phone'], $old['address'], $hash]);

            $newId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT id,name,email,phone,address,role FROM users WHERE id=?");
            $stmt->execute([$newId]);
            $_SESSION['user'] = $stmt->fetch();

            header("Location: index.php");
            exit;
        }
    }
}

require "includes/header.php";
?>

<main class="wrap" style="padding-top:70px;">
  <div class="form-panel">
    <h2>Create your account</h2>
    <p class="sub">Name, address, phone and email are required.</p>

    <form method="post">
      <label for="name">Full name</label>
      <input type="text" id="name" name="name" required value="<?= e($old['name']) ?>">

      <label for="email">Email (this will be your unique ID)</label>
      <input type="email" id="email" name="email" required value="<?= e($old['email']) ?>">

      <label for="phone">Phone number</label>
      <input type="tel" id="phone" name="phone" required value="<?= e($old['phone']) ?>" placeholder="03XX-XXXXXXX">

      <label for="address">Address</label>
      <input type="text" id="address" name="address" required value="<?= e($old['address']) ?>">

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="6">

      <?php if ($error): ?>
        <p class="error-text" style="display:block;"><?= e($error) ?></p>
      <?php endif; ?>

      <button type="submit" class="pill-btn full-btn">Create account</button>
    </form>

    <p class="switch-link">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</main>

<?php require "includes/footer.php"; ?>
