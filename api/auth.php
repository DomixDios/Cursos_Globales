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

if ($action === 'update-profile') {
    if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error' => 'No autenticado']); exit; }
    $pdo = getDB();
    $userId = $_SESSION['user_id'];
    $name  = trim($_POST['nombre_completo'] ?? '');
    $bio   = trim($_POST['bio'] ?? '');
    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
        exit;
    }
    $stmt = $pdo->prepare('UPDATE usuarios SET nombre_completo = ?, bio = ? WHERE id = ?');
    $stmt->execute([$name, $bio, $userId]);

    $avatar = '';
    if (!empty($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $avatarName = 'user_' . $userId . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarName)) {
                $avatar = 'assets/uploads/avatars/' . $avatarName;
                $stmt = $pdo->prepare('UPDATE usuarios SET avatar = ? WHERE id = ?');
                $stmt->execute([$avatar, $userId]);
            }
        }
    }

    $_SESSION['user_id'] = $userId;
    echo json_encode(['success' => true, 'message' => 'Perfil actualizado']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => "Acci\u{00f3}n no v\u{00e1}lida"]);
