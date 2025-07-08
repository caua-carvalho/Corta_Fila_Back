<?php
require_once '../db.php';

// Recebe phone e password, retorna true se autenticado, false se não
function autenticarUser(string $phone, string $password): bool {
    // Valida os dados recebidos
    if (!validarDadosLogin($phone, $password)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Dados inválidos']);
        return false;
    }

    // Busca o User pelo phone
    $user = buscaruserPorPhone($phone);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['erro' => 'Usuário ou senha inválidos']);
        return false;
    }

    // Verifica a senha usando password_verify
    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['erro' => 'Usuário ou senha inválidos']);
        return false;
    }

    // Aqui tu pode gerar um token JWT ou sessão, mas vou só retornar sucesso por enquanto
    http_response_code(200);
    echo json_encode(['mensagem' => 'Login realizado com sucesso', 'user' => $user]);
    return true;
}

// Valida phone e password recebidos no login
function validarDadosLogin(string $phone, string $password): bool {
    if (empty($phone) || empty($password)) return false;
    if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) return false;
    if (strlen($password) < 6) return false;
    return true;
}

// Busca usuário pelo phone no banco
function buscaruserPorPhone(string $phone): ?array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT user_id, name, phone, password_hash, role FROM users WHERE phone = :phone');
    $stmt->execute(['phone' => $phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

// Recebe dados do POST e chama autenticação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
    $phone = $dados['phone'] ?? '';
    $password = $dados['password'] ?? '';
    autenticarUser($phone, $password);
} else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
}