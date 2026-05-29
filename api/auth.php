<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    echo json_encode(login($email, $password));
    exit;
}

if ($action === 'me') {
    echo json_encode(currentUser());
    exit;
}

http_response_code(404);
echo json_encode(['error' => "Acci\u{00f3}n no v\u{00e1}lida"]);
