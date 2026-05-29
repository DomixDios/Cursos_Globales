<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !in_array(currentUserRole(), ['admin','moderador'])) {
    http_response_code(403);     echo json_encode(['error' => 'No autorizado'], JSON_INVALID_UTF8_SUBSTITUTE); exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? '';
$isAdmin = currentUserRole() === 'admin';

if ($action === 'dashboard') {
    echo json_encode([
        'users'    => (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
        'courses'  => (int)$pdo->query('SELECT COUNT(*) FROM cursos')->fetchColumn(),
        'pending'  => (int)$pdo->query("SELECT COUNT(*) FROM cursos WHERE estado = 'pendiente'")->fetchColumn(),
        'revenue'  => (float)$pdo->query('SELECT COALESCE(SUM(monto),0) FROM pagos')->fetchColumn(),
    ]);
    exit;
}

if ($action === 'users') {
    $stmt = $pdo->query('SELECT id, nombre_completo, email, rol, activo, creado_en FROM usuarios ORDER BY creado_en DESC');
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($action === 'user-save' && $isAdmin) {
    $id   = (int)($_POST['id'] ?? 0);
    $name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'estudiante';
    $pass = $_POST['password'] ?? '';

    if ($id) {
        $sql = 'UPDATE usuarios SET nombre_completo = ?, email = ?, rol = ?, actualizado_en = datetime() WHERE id = ?';
        $params = [$name, $email, $role, $id];
        if (!empty($pass)) {
            $sql = 'UPDATE usuarios SET nombre_completo = ?, email = ?, rol = ?, password = ?, actualizado_en = datetime() WHERE id = ?';
            $params = [$name, $email, $role, password_hash($pass, PASSWORD_DEFAULT), $id];
        }
        $pdo->prepare($sql)->execute($params);
    } else {
        $stmt = $pdo->prepare('INSERT INTO usuarios (nombre_completo, email, password, rol) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($pass ?: 'password123', PASSWORD_DEFAULT), $role]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'user-toggle' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id === (int)$_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'No puedes desactivarte a ti mismo']);
        exit;
    }
    $pdo->prepare('UPDATE usuarios SET activo = CASE WHEN activo = 1 THEN 0 ELSE 1 END, actualizado_en = datetime() WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'approve' || $action === 'reject') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $action === 'approve' ? 'aprobado' : 'rechazado';
    $reason = $_POST['reason'] ?? '';
    $stmt = $pdo->prepare("UPDATE cursos SET estado = ?, motivo_rechazo = ?, aprobado_por = ?, aprobado_en = datetime() WHERE id = ?");
    $stmt->execute([$status, $reason, $_SESSION['user_id'], $id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'pending-courses') {
    $stmt = $pdo->query("SELECT c.*, u.nombre_completo AS profesor_nombre FROM cursos c JOIN usuarios u ON u.id = c.profesor_id WHERE c.estado = 'pendiente' ORDER BY c.creado_en ASC");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($action === 'category-list') {
    $stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM cursos WHERE categoria_id = c.id) AS total_cursos FROM categorias c WHERE c.activo = 1 ORDER BY c.nombre ASC");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($action === 'category-save') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $desc = $_POST['description'] ?? '';

    if ($id) {
        $pdo->prepare('UPDATE categorias SET nombre = ?, slug = ?, descripcion = ? WHERE id = ?')->execute([$name, $slug, $desc, $id]);
    } else {
        $pdo->prepare('INSERT OR IGNORE INTO categorias (nombre, slug, descripcion) VALUES (?, ?, ?)')->execute([$name, $slug, $desc]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'category-toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE categorias SET activo = CASE WHEN activo = 1 THEN 0 ELSE 1 END WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'category-delete') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE categorias SET activo = 0 WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'stats-users') {
    $stmt = $pdo->query("SELECT strftime('%Y-%m', creado_en) AS mes, COUNT(*) AS total FROM usuarios GROUP BY mes ORDER BY mes ASC LIMIT 12");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($action === 'stats-revenue') {
    $stmt = $pdo->query("SELECT strftime('%Y-%m', p.creado_en) AS mes, COALESCE(SUM(p.monto),0) AS total FROM pagos p GROUP BY mes ORDER BY mes ASC LIMIT 12");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acci\ufffdn no v\ufffdlida'], JSON_INVALID_UTF8_SUBSTITUTE);
