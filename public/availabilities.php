<?php
header('Content-Type: application/json');
require 'config.php';

$barberId = isset($_GET['barber_id']) ? (int)$_GET['barber_id'] : 0;
if (!$barberId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid barber']);
    exit;
}

$stmt = $pdo->prepare('SELECT availability_id, weekday, start_time, end_time FROM availabilities WHERE barber_id = ?');
$stmt->execute([$barberId]);
$slots = $stmt->fetchAll();

echo json_encode($slots);
?>
