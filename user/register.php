<?php

// Gerado pelo Copilot

require_once '../db.php'; // Certifique-se que esse arquivo existe e conecta ao banco

// Função de alto nível para registrar barbeiro
function registrarBarbeiro(string $name, string $phone, string $password_hash): bool {
    // Valida os dados recebidos
    if (!validarDados($name, $phone, $password_hash)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Dados inválidos']);
        return false;
    }

    // Verifica se o phone já está cadastrado
    if (phoneJaCadastrado($phone)) {
        http_response_code(409);
        echo json_encode(['erro' => 'phone já cadastrado']);
        return false;
    }

    // Tenta inserir o barbeiro no banco
    if (!inserirBarbeiro($name, $phone, $password_hash)) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao cadastrar barbeiro']);
        return false;
    }

    http_response_code(201);
    echo json_encode(['mensagem' => 'Barbeiro cadastrado com sucesso']);
    return true;
}

// Valida name, phone e password_hash
function validarDados(string $name, string $phone, string $password_hash): bool {
    // Verifica se os campos estão preenchidos
    if (empty($name) || empty($phone) || empty($password_hash)) return false;

    // Posteriormente adicionar uma validação mais robusta para o phone
    if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) return false;

    // Verifica se a password_hash tem pelo menos 6 caracteres
    if (strlen($password_hash) < 6) return false;

    return true;
}

// Verifica se o phone já existe no banco
function phoneJaCadastrado(string $phone): bool {
    global $pdo;
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE phone = :phone');
    $stmt->execute(['phone' => $phone]);
    return $stmt->fetch() !== false;
}

// Insere o usuario no banco
function inserirBarbeiro(string $name, string $phone, string $password_hash): bool {
    global $pdo;
    $password_hash = password_hash($password_hash, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, phone, password_hash) VALUES (:name, :phone, :password_hash)');
    return $stmt->execute([
        'name' => $name,
        'phone' => $phone,
        'password_hash' => $password_hash
    ]);
}

// Recebe dados do POST e chama o registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
    $name = $dados['name'] ?? '';
    $phone = $dados['phone'] ?? '';
    $password_hash = $dados['password'] ?? '';
    registrarBarbeiro($name, $phone, $password_hash);
} else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
}