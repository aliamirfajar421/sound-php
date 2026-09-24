<?php
require_once "config.php";
require_once "includes/functions.php";
$pageTitle = "Log in";
$activePage = "";

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        unset($user['password']);
        $_SESSION['user'] = $user;
        header("Location: " . ($user['role'] === 'admin' ? 'admin.php' : 'index.php'));
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}

require "includes/header.php";
?>

<main class="wrap" style="padding-top:70px;">
  <div class="form-panel">
    <h2>Welcome back</h2>
    <p class="sub">Log in to rate, review and manage your account.</p>

    <form method="post">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <?php if ($error): ?>
        <p class="error-text" style="display:block;"><?= e($error) ?></p>
      <?php endif; ?>

      <button type="submit" class="pill-btn full-btn">Log in</button>
    </form>

    <p class="form-note" style="margin-top:18px;text-align:center;">
      Demo admin: admin@sound.com / admin123<br>Demo user: ayesha@example.com / user123
    </p>

    <p class="switch-link">Don't have an account? <a href="register.php">Sign up</a></p>
  </div>
</main>

<?php require "includes/footer.php"; ?>
