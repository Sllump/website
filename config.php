<?php
define('DB_HOST','localhost');
define('DB_NAME','website'); // DB Name
define('DB_USER','root'); // DB User
define('DB_PASS','DBPassword'); // DB Password
define('ADMIN_SIGNUP_CODE', 'RegisterationCode'); // For new Admins to register

try {
  $pdo = new PDO(
    "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
  );
} catch (PDOException $e) {
  die("DB connection failed: " . $e->getMessage());
}

function check_suspension(PDO $pdo) {
    if (!empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT suspended FROM users WHERE id = ?");
        $stmt->execute([ $_SESSION['user_id'] ]);
        $s = $stmt->fetchColumn();
        if ($s) {
            session_unset();
            session_destroy();
            header('Location: login.php?error=suspended');
            exit;
        }
    }
}