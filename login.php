<?php
session_start();
require __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password, suspended FROM users WHERE username = ?");
    $stmt->execute([$u]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['suspended']) {
            $error = "Your account is suspended! 
            Please contact an administrator.";
        }
        elseif (password_verify($p, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        }
        else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Slump - Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icons/7.0.3/css/flag-icons.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">
  <div class="login-wrapper">
    <div class="login-container page-transition">
      <h1>Log In</h1>

      <?php if (!empty($error)): ?>
        <div class="form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <label for="u">Username</label>
        <input id="u" name="username" type="text" required autofocus
               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : '' ?>">

        <label for="p">Password</label>
        <input id="p" name="password" type="password" required>

        <button type="submit">Log In</button>
      </form>

      <p class="small">
        No account? <a href="register.php">Register</a>
      </p>
    </div>
  </div>

  <footer class="page-transition">
    <p>© <?= date('Y') ?> Slump</p>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelector('.login-container').classList.add('page-loaded');
      document.querySelector('footer').classList.add('page-loaded');
    });
  </script>
</body>
</html>
