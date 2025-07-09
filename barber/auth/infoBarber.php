<?php

require_once '../../db.php';

if(empty($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Parâmetro user_id é obrigatório']);
    exit;
}

$user_id = $_GET['user_id'];

$stmt = $pdo->prepare('
    SELECT b.name, b.email, b.bio, b.photo_url, b.custom_url, u.id AS user_id
    FROM barbers b
    JOIN users u ON b.user_id = u.id
    WHERE u.id = :user_id
');
$stmt->execute(['user_id' => $user_id]);
$barber = $stmt->fetch(PDO::FETCH_ASSOC);

if(empty($barber)) {
    http_response_code(404);
    echo json_encode(['erro' => 'Barbeiro não encontrado']);
    exit;
}

echo json_encode([
    'name' => $barber['name'],
    'email' => $barber['email'],
    'bio' => $barber['bio'],
    'photo_url' => $barber['photo_url'],
    'custom_url' => $barber['custom_url']
]);