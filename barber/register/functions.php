<?php
require_once '../../db.php';

/**
 * Verifica se o email já está cadastrado no banco.
 * Gerado pelo Copilot
 */
function emailJaCadastrado(string $email): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT barber_id FROM barbers WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log('Erro ao verificar email: ' . $e->getMessage());
        throw new Exception('Erro ao acessar o banco de dados');
    }
}

/**
 * Insere um novo barbeiro no banco.
 * Gerado pelo Copilot
 */
function insertBarber(string $name, string $email, string $bio, string $photo, string $user_id): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO barbers (name, email, bio, photo_url, custom_url, user_id) VALUES (:name, :email, :bio, :photo, :url, :user_id)'
        );
        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'bio' => $bio,
            'photo' => $photo,
            'url' => strtolower(str_replace(' ', '-', $name)), // Custom URL baseado no nome
            'user_id' => $user_id,
        ]);
    } catch (PDOException $e) {
        error_log('Erro ao inserir barbeiro: ' . $e->getMessage());
        throw new Exception('Erro ao cadastrar barbeiro');
    }
}

/**
 * Faz o registro do barbeiro, validando dados e tratando erros.
 * Gerado pelo Copilot
 */
function registerBarber(string $name, string $email, string $bio, string $photo, string $user_id): bool {
    if (empty($name) || empty($email) || empty($bio) || empty($photo) || empty($user_id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Dados inválidos']);
        return false;
    }

    try {
        if (emailJaCadastrado($email)) {
            http_response_code(409);
            echo json_encode(['erro' => 'Email já cadastrado']);
            return false;
        }

        if (!insertBarber($name, $email, $bio, $photo, $user_id)) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar barbeiro']);
            return false;
        }

        http_response_code(201);
        echo json_encode(['mensagem' => 'Barbeiro cadastrado com sucesso']);
        return true;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => $e->getMessage()]);
        return false;
    }
}