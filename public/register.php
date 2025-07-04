<?php
// public/register.php

// 1) Ativa exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Define que a saída sempre será JSON
header('Content-Type: application/json; charset=utf-8');

try {                   
    require __DIR__ . '/../src/db.php';
    require __DIR__ . '/../config/config.php';

    // Lê o JSON do body
    $data = json_decode(file_get_contents('php://input'), true);
    $name     = trim($data['name'] ?? '');
    $phone    = trim($data['phone'] ?? '');
    $email    = trim($data['email'] ?? null);
    $password = $data['password'] ?? '';

    if (!$name || !$phone || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'name, phone e password são obrigatórios']);
        exit;
    }

    // Verifica duplicados
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE phone = :phone");
    $stmt->execute([':phone' => $phone]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Phone já cadastrado']);
        exit;
    }
    if ($email) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Email já cadastrado']);
            exit;
        }
    }

    // Insere usuário
    $hash   = password_hash($password, PASSWORD_DEFAULT);
    $roleId = $defaultRoleId; // do config.php
    $stmt = $pdo->prepare("
        INSERT INTO users (role_id, name, phone, email, password_hash)
        VALUES (:role_id, :name, :phone, :email, :hash)
    ");
    $stmt->execute([
        ':role_id'=> $roleId,
        ':name'   => $name,
        ':phone'  => $phone,
        ':email'  => $email,
        ':hash'   => $hash,
    ]);

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    // 6) Em caso de qualquer erro, mostra a mensagem
    http_response_code(500);
    echo json_encode([
        'error'   => 'Exceção capturada',
        'message' => $e->getMessage(),
        'line'    => $e->getLine(),
        'file'    => basename($e->getFile())
    ]);
    exit;
}
