<?php
session_start();
require __DIR__.'/config.php';
check_suspension($pdo);

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(['error'=>'Unauthorized']);
  exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error'=>'Invalid user ID']);
  exit;
}

try {
  $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
  $stmt->execute([$id]);
  echo json_encode(['success'=>true]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error'=>$e->getMessage()]);
}
