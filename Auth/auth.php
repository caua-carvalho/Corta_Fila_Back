<?php
// src/auth.php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../src/db.php';

/**
 * Gera um token (32 hex) e salva em user_tokens.
 */
function generateToken(int $userId): string
{
    global $pdo, $tokenExpiry;

    $token     = bin2hex(random_bytes(16));
    $expiresAt = date('Y-m-d H:i:s', time() + $tokenExpiry);

    $stmt = $pdo->prepare("
        INSERT INTO user_tokens (token, user_id, expires_at)
        VALUES (:token, :user_id, :expires_at)
    ");
    $stmt->execute([
        ':token'      => $token,
        ':user_id'    => $userId,
        ':expires_at' => $expiresAt
    ]);

    return $token;
}

/**
 * Lê o header Authorization e retorna só o token, ou null.
 */
function getBearerToken(): ?string
{
    $headers = function_exists('getallheaders') ? getallheaders() : $_SERVER;
    $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if ($auth && preg_match('/Bearer\s+(.+)/', $auth, $m)) {
        return $m[1];
    }
    return null;
}

/**
 * Verifica se o token existe e não expirou. Retorna user_id ou false.
 */
function validateToken(string $token)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT user_id
          FROM user_tokens
         WHERE token = :token
           AND expires_at > NOW()
    ");
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch();

    return $row ? (int)$row['user_id'] : false;
}

/**
 * Para rotas protegidas: valida token, envia 401 em falha e retorna user_id.
 */
function requireAuth(): int
{
    header('Content-Type: application/json; charset=utf-8');

    $token = getBearerToken();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['authorized' => false, 'error' => 'Token não fornecido']);
        exit;
    }

    $userId = validateToken($token);
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['authorized' => false, 'error' => 'Token inválido ou expirado']);
        exit;
    }

    return $userId;
}

/**
 * Revoga (logout): remove o token da base.
 */
function revokeToken(string $token): void
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE token = :token");
    $stmt->execute([':token' => $token]);
}
