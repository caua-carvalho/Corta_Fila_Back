<?php
require_once '../../db.php';

// Verifica se o email já está cadastrado no banco
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
 * Cria o diretório para armazenar as infos de um barbeiro específico.
 *
 * Este diretório será criado em: /barberShops/{user_id}
 * Caso o diretório já exista, nada será feito.
 * Caso ocorra um erro ao criar o diretório, uma exceção será lançada e um erro será registrado no log.
 *
 * @param string $user_id O identificador único do barbeiro.
 * @throws Exception Se não for possível criar o diretório.
 */
function createBarberDirectory(string $user_id): void {
    $dir = __DIR__ . '/barberShops/' . $user_id;
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log('Erro ao criar diretório para barbeiro: ' . $dir);
            throw new Exception('Erro ao criar diretório para barbeiro');
        }
    }
}

function uploadBarberLogo(string $user_id, string $photo): string {
    $target_dir = __DIR__ . '/barberShops/' . $user_id . '/';
    $target_file = $target_dir . basename($photo);
    
    // Verifica se o diretório existe, caso contrário cria
    if (!is_dir($target_dir)) {
        createBarberDirectory($user_id);
    }

    // Move o arquivo para o diretório do barbeiro
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        return $target_file; // Retorna o caminho do arquivo
    } else {
        throw new Exception('Erro ao fazer upload da foto do barbeiro');
    }
}


// Insere um novo barbeiro no banco
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

// Faz o registro do barbeiro, validando dados e tratando erros
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