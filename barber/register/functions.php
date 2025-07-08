<?php
require_once '../../db.php';

/**
 * Verifica se o e-mail já está cadastrado na tabela barbers.
 *
 * @param string $email
 * @return bool
 * @throws Exception em caso de erro de banco.
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
 * Garante que barberShops/{user_id} exista,
 * usando como base back-end/barber/barberShops.
 */
function ensureBarberDir(int $user_id): string {
    if ($user_id <= 0) {
        throw new Exception("ID de barbeiro inválido: $user_id");
    }

    // Aqui __DIR__ aponta para back-end/barber/register
    $baseDir = __DIR__ . '/../barberShops';

    // cria barberShops se não existir
    if (!is_dir($baseDir) && !mkdir($baseDir, 0755, true)) {
        error_log("Falha ao criar baseDir: $baseDir");
        throw new Exception("Não foi possível criar barberShops");
    }

    // cria barberShops/{user_id}
    $userDir = $baseDir . '/' . $user_id;
    if (!is_dir($userDir) && !mkdir($userDir, 0755, true)) {
        error_log("Falha ao criar userDir: $userDir");
        throw new Exception("Não foi possível criar pasta do barbeiro");
    }

    return $userDir;
}

/**
 * Move $_FILES['photo'] para barberShops/{user_id}/nome_unico.ext.
 */
function saveBarberPhoto(int $user_id, array $file): string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro no upload (código {$file['error']})");
    }

    $dir = ensureBarberDir($user_id);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique = uniqid('barber_', true) . '.' . $ext;
    $dest  = $dir . '/' . $unique;

    error_log("Tentando mover arquivo para: $dest"); // DEBUG

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        error_log("Erro ao mover para $dest");
        throw new Exception("Falha ao salvar foto do barbeiro");
    }

    // Retorna sempre relativo a back-end/
    return "barberShops/{$user_id}/{$unique}";
}



/**
 * Insere um novo barbeiro na tabela barbers.
 *
 * @param string $name
 * @param string $email
 * @param string $bio
 * @param string $photoUrl Caminho relativo da foto.
 * @param string $user_id
 * @return bool
 * @throws Exception em caso de erro de inserção.
 */
function insertBarber(string $name, string $email, string $bio, string $photoUrl, string $user_id): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO barbers (name, email, bio, photo_url, custom_url, user_id) 
             VALUES (:name, :email, :bio, :photo_url, :custom_url, :user_id)'
        );
        return $stmt->execute([
            'name'       => $name,
            'email'      => $email,
            'bio'        => $bio,
            'photo_url'  => $photoUrl,
            'custom_url' => strtolower(str_replace(' ', '-', $name)),
            'user_id'    => $user_id,
        ]);
    } catch (PDOException $e) {
        error_log('Erro ao inserir barbeiro: ' . $e->getMessage());
        throw new Exception('Erro ao cadastrar barbeiro');
    }
}

/**
 * Registra um barbeiro: valida dados, faz upload da foto e insere no banco.
 *
 * @param array $data $_POST esperados: name, email, bio, user_id
 * @param array $file $_FILES['photo']
 * @throws Exception em caso de qualquer falha.
 */
function registerBarber(array $data, array $file): void {
    // Campos obrigatórios
    foreach (['name', 'email', 'bio', 'user_id'] as $field) {
        if (empty($data[$field])) {
            throw new Exception("Campo $field é obrigatório");
        }
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload da foto do barbeiro');
    }

    // Verifica e-mail duplicado
    if (emailJaCadastrado($data['email'])) {
        http_response_code(409);
        throw new Exception('E-mail já cadastrado');
    }

    // Salva foto e obtém URL relativa
    $photoUrl = saveBarberPhoto($data['user_id'], $file);

    // Insere barbeiro no banco
    if (!insertBarber($data['name'], $data['email'], $data['bio'], $photoUrl, $data['user_id'])) {
        throw new Exception('Falha ao inserir barbeiro no banco de dados');
    }
}
