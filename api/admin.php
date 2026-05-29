<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

$pdo = getDB();
$action = $_GET['action'] ?? '';

$allowedRoles = ['admin','moderador'];
if (in_array($action, ['category-list'], true)) {
    $allowedRoles[] = 'profesor';
    $allowedRoles[] = 'estudiante';
}
if (!isLoggedIn() || !in_array(currentUserRole(), $allowedRoles)) {
    http_response_code(403);     echo json_encode(['error' => 'No autorizado'], JSON_INVALID_UTF8_SUBSTITUTE); exit;
}
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

if ($action === 'teachers') {
    $stmt = $pdo->query("SELECT id, nombre_completo FROM usuarios WHERE rol = 'profesor' AND activo = 1 ORDER BY nombre_completo ASC");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($action === 'course-list-all') {
    $where = "1=1";
    $params = [];
    if (!empty($_GET['status'])) { $where .= " AND c.estado = ?"; $params[] = $_GET['status']; }
    if (!empty($_GET['teacher_id'])) { $where .= " AND c.profesor_id = ?"; $params[] = (int)$_GET['teacher_id']; }
    if (!empty($_GET['category_id'])) { $where .= " AND c.categoria_id = ?"; $params[] = (int)$_GET['category_id']; }
    if (!empty($_GET['search'])) { $where .= " AND (c.titulo LIKE ? OR u.nombre_completo LIKE ?)"; $s = '%' . $_GET['search'] . '%'; $params[] = $s; $params[] = $s; }

    $stmt = $pdo->prepare("SELECT c.*, u.nombre_completo AS profesor_nombre, cat.nombre AS categoria_nombre,
        (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS total_estudiantes
        FROM cursos c JOIN usuarios u ON u.id = c.profesor_id
        LEFT JOIN categorias cat ON cat.id = c.categoria_id
        WHERE $where ORDER BY c.actualizado_en DESC");
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($action === 'course-save' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $title), '-'));
    $shortDesc = $_POST['short_description'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $level = $_POST['level'] ?? 'principiante';
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $teacherId = (int)($_POST['teacher_id'] ?? 0);
    $status = $_POST['status'] ?? 'borrador';
    $featured = (int)($_POST['featured'] ?? 0);
    $reason = $_POST['rejection_reason'] ?? '';

    if ($id) {
        $stmt = $pdo->prepare('SELECT estado FROM cursos WHERE id = ?');
        $stmt->execute([$id]);
        $oldStatus = $stmt->fetchColumn();

        $sql = 'UPDATE cursos SET titulo=?, slug=?, descripcion_corta=?, descripcion=?, precio=?, nivel=?, categoria_id=?, profesor_id=?, estado=?, destacado=?, motivo_rechazo=?, actualizado_en=datetime()';
        $params = [$title, $slug, $shortDesc, $desc, $price, $level, $categoryId, $teacherId, $status, $featured, $reason, $id];

        if (in_array($status, ['aprobado', 'publicado']) && $oldStatus !== $status) {
            $sql = 'UPDATE cursos SET titulo=?, slug=?, descripcion_corta=?, descripcion=?, precio=?, nivel=?, categoria_id=?, profesor_id=?, estado=?, destacado=?, motivo_rechazo=?, aprobado_por=?, aprobado_en=datetime(), actualizado_en=datetime() WHERE id=?';
            $params = [$title, $slug, $shortDesc, $desc, $price, $level, $categoryId, $teacherId, $status, $featured, $reason, $_SESSION['user_id'], $id];
        }

        $pdo->prepare($sql)->execute($params);
    } else {
        if (!$teacherId) $teacherId = (int)$_SESSION['user_id'];
        $pdo->prepare('INSERT INTO cursos (profesor_id,titulo,slug,descripcion_corta,descripcion,precio,nivel,categoria_id,estado,destacado) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([$teacherId, $title, $slug, $shortDesc, $desc, $price, $level, $categoryId, $status, $featured]);
        $id = $pdo->lastInsertId();
    }
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($action === 'course-delete' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM progreso_clases WHERE inscripcion_id IN (SELECT id FROM inscripciones WHERE curso_id = ?)')->execute([$id]);
    $pdo->prepare('DELETE FROM inscripciones WHERE curso_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM clases WHERE modulo_id IN (SELECT id FROM modulos WHERE curso_id = ?)')->execute([$id]);
    $pdo->prepare('DELETE FROM modulos WHERE curso_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM resenas WHERE curso_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM comentarios WHERE curso_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM pagos WHERE inscripcion_id IN (SELECT id FROM inscripciones WHERE curso_id = ?)')->execute([$id]);
    $pdo->prepare('DELETE FROM cursos WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'course-feature' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE cursos SET destacado = CASE WHEN destacado = 1 THEN 0 ELSE 1 END WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'course-syllabus') {
    $courseId = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, titulo FROM cursos WHERE id = ?');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    if (!$course) { echo json_encode(['error' => 'Curso no encontrado']); exit; }

    $stmt = $pdo->prepare('SELECT * FROM modulos WHERE curso_id = ? ORDER BY orden ASC');
    $stmt->execute([$courseId]);
    $modules = $stmt->fetchAll();

    foreach ($modules as &$mod) {
        $stmt = $pdo->prepare('SELECT * FROM clases WHERE modulo_id = ? ORDER BY orden ASC');
        $stmt->execute([$mod['id']]);
        $mod['clases'] = $stmt->fetchAll();
    }

    echo json_encode(['course' => $course, 'modules' => $modules], JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($action === 'module-save' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    $courseId = (int)($_POST['course_id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $desc = $_POST['description'] ?? '';

    if ($id) {
        $pdo->prepare('UPDATE modulos SET titulo = ?, descripcion = ? WHERE id = ? AND curso_id = ?')
            ->execute([$title, $desc, $id, $courseId]);
    } else {
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(orden),0) FROM modulos WHERE curso_id = ?');
        $stmt->execute([$courseId]);
        $maxOrder = (int)$stmt->fetchColumn();
        $pdo->prepare('INSERT INTO modulos (curso_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)')
            ->execute([$courseId, $title, $desc, $maxOrder + 1]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'module-delete' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM clases WHERE modulo_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM modulos WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'class-save' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    $moduleId = (int)($_POST['module_id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $contentType = $_POST['content_type'] ?? 'video';
    $url = $_POST['url_video'] ?? '';
    $desc = $_POST['description'] ?? '';
    $duration = (int)($_POST['duration'] ?? 0);
    $free = (int)($_POST['free'] ?? 0);

    if ($id) {
        $pdo->prepare('UPDATE clases SET titulo = ?, tipo_contenido = ?, url_video = ?, descripcion = ?, duracion = ?, gratuito = ? WHERE id = ? AND modulo_id = ?')
            ->execute([$title, $contentType, $url, $desc, $duration, $free, $id, $moduleId]);
    } else {
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(orden),0) FROM clases WHERE modulo_id = ?');
        $stmt->execute([$moduleId]);
        $maxOrder = (int)$stmt->fetchColumn();
        $pdo->prepare('INSERT INTO clases (modulo_id, titulo, tipo_contenido, url_video, descripcion, duracion, gratuito, orden) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$moduleId, $title, $contentType, $url, $desc, $duration, $free, $maxOrder + 1]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'class-delete' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM clases WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acci\u00f3n no v\u00e1lida'], JSON_INVALID_UTF8_SUBSTITUTE);
