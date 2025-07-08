<?php
// Ativa exibição de erros para facilitar debug durante o desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../db.php';
require_once './functions.php';

try {
    // 1) Validar campos obrigatórios
    foreach (['name','email','bio','user_id'] as $f) {
        if (empty($_POST[$f])) {
            throw new Exception("Campo $f é obrigatório");
        }
    }
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Foto não enviada ou com erro de upload');
    }

    // 2) Verificar e-mail duplicado
    if (emailJaCadastrado($_POST['email'])) {
        http_response_code(409);
        echo json_encode(['erro'=>'E-mail já cadastrado']);
        exit;
    }

    // 3) Faz upload e recebe URL relativa
    $photoUrl = saveBarberPhoto($_POST['user_id'], $_FILES['photo']);

    // 4) Insere no banco
    $ok = insertBarber(
        $_POST['name'],
        $_POST['email'],
        $_POST['bio'],
        $photoUrl,
        $_POST['user_id']
    );
    if (!$ok) {
        throw new Exception('Erro ao inserir barbeiro no banco');
    }

    // 5) Sucesso
    http_response_code(201);
    echo json_encode(['success'=>'Barbeiro cadastrado com sucesso','photo_url'=>$photoUrl]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['erro'=>$e->getMessage()]);
}
