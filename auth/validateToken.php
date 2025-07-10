<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token ausente']);
    exit;
}

$token = $matches[1];

try {
    $tokenData = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token inválido']);
    exit;
}
