<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once './functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
    $name = $dados['name'] ?? '';
    $email = $dados['email'] ?? '';
    $bio = $dados['bio'] ?? '';
    $photo = $dados['photo'] ?? '';
    $user_id = $dados['user_id'] ?? '';

    registerBarber($name, $email, $bio, $photo, $user_id);
} else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
}
