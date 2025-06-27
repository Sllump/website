<?php
require __DIR__.'/config.php';
check_suspension($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u    = trim($_POST['username'] ?? '');
    $p    = $_POST['password'] ?? '';
    $code = trim($_POST['code'] ?? '');

    if ($code !== ADMIN_SIGNUP_CODE) {
        $err = "Invalid registration code.";
    }
    elseif (strlen($u) < 3 || strlen($p) < 6) {
        $err = "Username must be ≥3 chars, password ≥6.";
    }
    else {
        $hash = password_hash($p, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username,password) VALUES (?,?)");
        try {
            $stmt->execute([$u, $hash]);
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                $err = "That username is already taken.";
            } else {
                $err = "Database error, please try again.";
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign Up – Slump</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icons/7.0.3/css/flag-icons.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">
  <div class="container">
    <div class="bg-grid"></div>
    <div class="noise-overlay"></div>

    <div class="login-container page-transition">
      <h1>Sign Up</h1>

      <?php if (!empty($err)): ?>
        <div class="form-error">
          <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <label for="u">Username</label>
        <input id="u" name="username" type="text"
               required value="<?= htmlspecialchars($u ?? '', ENT_QUOTES) ?>">

        <label for="p">Password</label>
        <input id="p" name="password" type="password" required>

        <label for="c">Admin Code</label>
        <input id="c" name="code" type="password"
               required value="<?= htmlspecialchars($code ?? '', ENT_QUOTES) ?>">

        <button type="submit">Sign Up</button>
      </form>

      <p class="small">
        Already have an account? <a href="login.php">Log In</a>
      </p>
    </div>

    <footer class="page-transition">
      <p>© <?= date('Y') ?> Slump</p>
    </footer>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelector('.login-container').classList.add('page-loaded');
      document.querySelector('footer').classList.add('page-loaded');
    });
  </script>
</body>
</html>
