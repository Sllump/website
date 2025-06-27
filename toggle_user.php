<?php
session_start();
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);
if ($id < 1) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid user ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT suspended FROM users WHERE id=?");
    $stmt->execute([$id]);
    $current = (int)$stmt->fetchColumn();

    $new = $current ? 0 : 1;
    $upd = $pdo->prepare("UPDATE users SET suspended=? WHERE id=?");
    $upd->execute([$new, $id]);

    echo json_encode(['suspended'=>$new]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
