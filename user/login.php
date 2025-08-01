<?php
require_once '../db.php';

function autenticarUser(string $phone, string $password): array {
    $user = buscaruserPorPhone($phone);
    if (!$user) {
        return [
            'status' => 401,
            'body' => ['erro' => 'Usuário ou senha inválidos']
        ];
    }

    if (!password_verify(password: $password, hash: $user['password_hash'])) {
        return [
            'status' => 401,
            'body' => ['erro' => 'Usuário ou senha inválidos']
        ];
    }

    // Sucesso na autenticação
    return [
        
        'status' => 200,
        'body' => ['mensagem' => 'Login realizado com sucesso', 'user' => $user, 'status' => 'success']
    ];
}


// Busca usuário pelo phone no banco
function buscaruserPorPhone(string $phone): ?array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT user_id, name, phone, password_hash, role FROM users WHERE phone = :phone');
    $stmt->execute(['phone' => $phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

// Controller que recebe dados do POST, valida e retorna resposta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
    $phone = $dados['phone'] ?? null;
    $password = $dados['password'] ?? null;

    if (empty($phone) || empty($password)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Campos obrigatórios não preenchidos']);
        return;
    }

    $resultado = autenticarUser($phone, $password);
    http_response_code($resultado['status']);
    echo json_encode($resultado['body']);
} else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
}   echo json_encode(['erro' => 'Método não permitido']);
