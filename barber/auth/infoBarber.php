<?php
// Gerado pelo Copilot

require_once '../../db.php';
require_once '../../auth/validateToken.php';

/**
 * Retorna informações do barbeiro pelo user_id passado na URL.
 */
function obterInfoBarbeiroPorUserId(PDO $pdo, string $userId): ?array {
    $query = '
            SELECT b.name, b.email, b.bio, b.photo_url, b.custom_url, u.user_id AS user_id
            FROM barbers b
            JOIN users u ON b.user_id = u.user_id
            WHERE u.user_id = :user_id
    ';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Valida se o parâmetro user_id foi enviado na URL.
 */
function validarParametroUserId(): string {
    if (empty($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Parâmetro user_id é obrigatório']);
        exit;
    }
    return $_GET['user_id'];
}

// Controller principal
$userId = validarParametroUserId();
$barbeiro = obterInfoBarbeiroPorUserId($pdo, $userId);

if (!$barbeiro) {
    http_response_code(404);
    echo json_encode(['erro' => 'Barbeiro não encontrado']);
    exit;
}

echo json_encode([
    'success' => 'Barbeiro encontrado',
    'barber' => [
        'name' => $barbeiro['name'],
        'email' => $barbeiro['email'],
        'bio' => $barbeiro['bio'],
        'photo_url' => $barbeiro['photo_url'],
        'custom_url' => $barbeiro['custom_url']
    ]
]);