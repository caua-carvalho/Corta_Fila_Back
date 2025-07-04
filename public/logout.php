<?php
// public/logout.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../src/auth.php';

$token = getBearerToken();
if ($token) {
    revokeToken($token);
}

echo json_encode(['success' => true]);
