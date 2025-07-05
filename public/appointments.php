<?php
header('Content-Type: application/json');
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['client_id']) || empty($data['barber_id']) || empty($data['service_id']) || empty($data['datetime'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

// Check overlapping
$check = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE barber_id = ? AND appointment_datetime = ?');
$check->execute([$data['barber_id'], $data['datetime']]);
if ($check->fetchColumn() > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Time slot unavailable']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO appointments (client_id, barber_id, service_id, appointment_datetime) VALUES (?, ?, ?, ?)');
    $stmt->execute([$data['client_id'], $data['barber_id'], $data['service_id'], $data['datetime']]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Could not create appointment']);
}
?>
