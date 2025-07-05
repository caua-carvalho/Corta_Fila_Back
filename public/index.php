<?php
// Simple router to handle API requests
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($path) {
    case '/login':
        require 'login.php';
        break;
    case '/register':
        require 'register.php';
        break;
    case '/services':
        require 'services.php';
        break;
    case '/availabilities':
        require 'availabilities.php';
        break;
    case '/appointments':
        require 'appointments.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
}
?>
