<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401); echo json_encode(['error' => 'No autenticado']); exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($action === 'get') {
    $stmt = $pdo->prepare('SELECT id, nombre_completo, email, rol, bio, avatar FROM usuarios WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) { http_response_code(404); echo json_encode(['error' => 'Usuario no encontrado']); exit; }
    echo json_encode($user);
    exit;
}

if ($action === 'update') {
    $nombreCompleto = $_POST['full_name'] ?? '';
    $bio            = $_POST['bio'] ?? '';

    if (!$nombreCompleto) { http_response_code(400); echo json_encode(['error' => 'El nombre es requerido']); exit; }

    $stmt = $pdo->prepare('UPDATE usuarios SET nombre_completo = ?, bio = ?, actualizado_en = datetime() WHERE id = ?');
    $stmt->execute([$nombreCompleto, $bio, $userId]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'avatar') {
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400); echo json_encode(['error' => 'No se recibió ninguna imagen']); exit;
    }

    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        http_response_code(400); echo json_encode(['error' => 'Formato no permitido. Usa: jpg, png, gif, webp']); exit;
    }

    $dir = __DIR__ . '/../assets/uploads/avatars';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
    $destino  = $dir . '/' . $filename;

    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destino)) {
        http_response_code(500); echo json_encode(['error' => 'Error al guardar la imagen']); exit;
    }

    $avatarUrl = 'assets/uploads/avatars/' . $filename;
    $stmt = $pdo->prepare('UPDATE usuarios SET avatar = ?, actualizado_en = datetime() WHERE id = ?');
    $stmt->execute([$avatarUrl, $userId]);

    echo json_encode(['success' => true, 'avatar' => $avatarUrl]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acción no válida']);
