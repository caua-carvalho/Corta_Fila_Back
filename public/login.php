<?php
header('Content-Type: application/json');
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['phone']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

$stmt = $pdo->prepare('SELECT user_id, password_hash FROM users WHERE phone = ?');
$stmt->execute([$data['phone']]);
$user = $stmt->fetch();

if ($user && password_verify($data['password'], $user['password_hash'])) {
    echo json_encode(['success' => true, 'user_id' => $user['user_id']]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
}
?>
