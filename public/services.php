<?php
header('Content-Type: application/json');
require 'config.php';

$barberId = isset($_GET['barber_id']) ? (int)$_GET['barber_id'] : 0;
if (!$barberId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid barber']);
    exit;
}

$stmt = $pdo->prepare('SELECT service_id, service_name, price, duration_mins FROM services WHERE barber_id = ? AND is_active = 1');
$stmt->execute([$barberId]);
$services = $stmt->fetchAll();

echo json_encode([
    [
    'service_id' => 1,
    'service_name' => 'Corte de Cabelo',
    'price' => 35.00,
    'duration_mins' => 30
    ]
]);

