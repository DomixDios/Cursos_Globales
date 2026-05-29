<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !in_array(currentUserRole(), ['profesor', 'admin'])) {
    http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];
$esAdmin = currentUserRole() === 'admin';

function verificarPropiedadCurso(PDO $pdo, int $cursoId, int $userId, bool $esAdmin): bool
{
    if ($esAdmin) return true;
    $stmt = $pdo->prepare('SELECT id FROM cursos WHERE id = ? AND profesor_id = ?');
    $stmt->execute([$cursoId, $userId]);
    return (bool)$stmt->fetch();
}

function verificarPropiedadModulo(PDO $pdo, int $moduloId, int $userId, bool $esAdmin): bool
{
    if ($esAdmin) return true;
    $stmt = $pdo->prepare('SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE m.id = ? AND c.profesor_id = ?');
    $stmt->execute([$moduloId, $userId]);
    return (bool)$stmt->fetch();
}

if ($action === 'module-list') {
    $cursoId = (int)($_GET['course_id'] ?? 0);
    if (!$cursoId) { http_response_code(400); echo json_encode(['error' => 'course_id requerido']); exit; }
    if (!verificarPropiedadCurso($pdo, $cursoId, $userId, $esAdmin)) {
        http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
    }
    $modulos = $pdo->prepare('SELECT * FROM modulos WHERE curso_id = ? ORDER BY orden ASC');
    $modulos->execute([$cursoId]);
    $result = $modulos->fetchAll();
    foreach ($result as &$mod) {
        $cls = $pdo->prepare('SELECT * FROM clases WHERE modulo_id = ? ORDER BY orden ASC');
        $cls->execute([$mod['id']]);
        $mod['clases'] = $cls->fetchAll();
    }
    echo json_encode($result);
    exit;
}

if ($action === 'module-save') {
    $id         = (int)($_POST['id'] ?? 0);
    $cursoId    = (int)($_POST['course_id'] ?? 0);
    $titulo     = $_POST['title'] ?? '';
    $descripcion = $_POST['description'] ?? '';
    $orden      = (int)($_POST['sort_order'] ?? 0);

    if (!$titulo) { http_response_code(400); echo json_encode(['error' => 'El título es requerido']); exit; }
    if (!verificarPropiedadCurso($pdo, $cursoId, $userId, $esAdmin)) {
        http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
    }

    if ($id) {
        $stmt = $pdo->prepare('UPDATE modulos SET titulo = ?, descripcion = ?, orden = ? WHERE id = ? AND curso_id = ?');
        $stmt->execute([$titulo, $descripcion, $orden, $id, $cursoId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO modulos (curso_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)');
        $stmt->execute([$cursoId, $titulo, $descripcion, $orden]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($action === 'module-delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'id requerido']); exit; }
    if (!verificarPropiedadModulo($pdo, $id, $userId, $esAdmin)) {
        http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
    }
    $pdo->prepare('DELETE FROM modulos WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'class-list') {
    $moduloId = (int)($_GET['module_id'] ?? 0);
    if (!$moduloId) { http_response_code(400); echo json_encode(['error' => 'module_id requerido']); exit; }
    if (!verificarPropiedadModulo($pdo, $moduloId, $userId, $esAdmin)) {
        http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM clases WHERE modulo_id = ? ORDER BY orden ASC');
    $stmt->execute([$moduloId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'class-save') {
    $id          = (int)($_POST['id'] ?? 0);
    $moduloId    = (int)($_POST['module_id'] ?? 0);
    $titulo      = $_POST['title'] ?? '';
    $descripcion = $_POST['description'] ?? '';
    $urlVideo    = $_POST['video_url'] ?? '';
    $duracion    = (int)($_POST['duration'] ?? 0);
    $tipo        = $_POST['content_type'] ?? 'video';
    $texto       = $_POST['content_text'] ?? '';
    $orden       = (int)($_POST['sort_order'] ?? 0);
    $gratuito    = (int)($_POST['is_free'] ?? 0);

    if (!$titulo) { http_response_code(400); echo json_encode(['error' => 'El título es requerido']); exit; }
    if (!verificarPropiedadModulo($pdo, $moduloId, $userId, $esAdmin)) {
        http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
    }

    if ($id) {
        $stmt = $pdo->prepare('UPDATE clases SET titulo = ?, descripcion = ?, url_video = ?, duracion = ?, tipo_contenido = ?, texto_contenido = ?, orden = ?, gratuito = ? WHERE id = ? AND modulo_id = ?');
        $stmt->execute([$titulo, $descripcion, $urlVideo, $duracion, $tipo, $texto, $orden, $gratuito, $id, $moduloId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO clases (modulo_id, titulo, descripcion, url_video, duracion, tipo_contenido, texto_contenido, orden, gratuito) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$moduloId, $titulo, $descripcion, $urlVideo, $duracion, $tipo, $texto, $orden, $gratuito]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($action === 'class-delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'id requerido']); exit; }
    $stmt = $pdo->prepare('SELECT modulo_id FROM clases WHERE id = ?');
    $stmt->execute([$id]);
    $clase = $stmt->fetch();
    if (!$clase || !verificarPropiedadModulo($pdo, (int)$clase['modulo_id'], $userId, $esAdmin)) {
        http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
    }
    $pdo->prepare('DELETE FROM clases WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acción no válida']);
