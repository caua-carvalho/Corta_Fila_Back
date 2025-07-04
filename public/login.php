<?php
// Ativa exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../Auth/auth.php';

$data    = json_decode(file_get_contents('php://input'), true);
$phone   = trim($data['phone'] ?? '');
$password= $data['password'] ?? '';

if (!$phone || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'phone e password são obrigatórios']);
    exit;
}

// Busca pelo phone
$stmt = $pdo->prepare("
    SELECT user_id, password_hash, is_active
    FROM users
    WHERE phone = :phone
");
$stmt->execute([':phone' => $phone]);
$user = $stmt->fetch();

if (
    !$user ||
    !password_verify($password, $user['password_hash'])
) {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciais inválidas']);
    exit;
}

if ((int)$user['is_active'] !== 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Conta inativa']);
    exit;
}

// Gera token e retorna
$token = generateToken((int)$user['user_id']);
echo json_encode(['token' => $token]);
