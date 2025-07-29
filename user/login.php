<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php';

use Firebase\JWT\JWT;

// Leitura dos dados
$dados = json_decode(file_get_contents('php://input'), true);
$phone = trim($dados['phone'] ?? '');
$pass  = $dados['password'] ?? '';

// Validação mínima
if (
    empty($phone) ||
    empty($pass) ||
    !preg_match('/^\+?[0-9]{10,15}$/', $phone) ||
    strlen($pass) < 6
) {
    http_response_code(400);
    echo json_encode(['error' => 'Telefone ou senha inválidos']);
    exit;
}

try {
    // Busca usuário
    $stmt = $pdo->prepare('SELECT user_id, password_hash, role FROM users WHERE phone = :phone');
    $stmt->execute(['phone' => $phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        throw new RuntimeException('Credenciais incorretas');
    }

    // Geração de token
    $secret = getenv('JWT_SECRET') ?: 'segredo_dev';
    $token  = JWT::encode(
        ['user_id' => $user['user_id'], 'exp' => time() + 3600],
        $secret,
        'HS256'
    );

    http_response_code(200);
    echo json_encode([
        'token' => $token,
        'role'  => $user['role'],
    ]);

} catch (RuntimeException $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);

} catch (Exception $e) {
    error_log('Erro de autenticação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno']);
}
