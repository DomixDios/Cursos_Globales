<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error' => 'No autenticado']); exit; }

$pdo = getDB();
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($action === 'enroll') {
    $courseId = (int)($_POST['course_id'] ?? 0);
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO inscripciones (usuario_id, curso_id) VALUES (?, ?)');
    $stmt->execute([$userId, $courseId]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'my-courses') {
    $stmt = $pdo->prepare('SELECT e.*, c.titulo, c.slug, c.miniatura, c.nivel, u.nombre_completo AS profesor_nombre
        FROM inscripciones e JOIN cursos c ON c.id = e.curso_id JOIN usuarios u ON u.id = c.profesor_id
        WHERE e.usuario_id = ? ORDER BY e.inscrito_en DESC');
    $stmt->execute([$userId]);
    $courses = $stmt->fetchAll();
    $total = count($courses);
    $completed = 0;
    foreach ($courses as &$c) {
        if ($c['completado_en']) $completed++;
    }
    unset($c);
    echo json_encode(['courses' => $courses, 'total' => $total, 'completed' => $completed]);
    exit;
}

if ($action === 'certificates') {
    $stmt = $pdo->prepare('SELECT e.*, c.titulo, c.slug, c.nivel, u.nombre_completo AS profesor_nombre
        FROM inscripciones e JOIN cursos c ON c.id = e.curso_id JOIN usuarios u ON u.id = c.profesor_id
        WHERE e.usuario_id = ? AND e.completado_en IS NOT NULL ORDER BY e.completado_en DESC');
    $stmt->execute([$userId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'dashboard') {
    $enrolled = $pdo->prepare('SELECT COUNT(*) FROM inscripciones WHERE usuario_id = ?');
    $enrolled->execute([$userId]);
    $completed = $pdo->prepare('SELECT COUNT(*) FROM inscripciones WHERE usuario_id = ? AND completado_en IS NOT NULL');
    $completed->execute([$userId]);
    $avg = $pdo->prepare('SELECT COALESCE(AVG(progreso), 0) FROM inscripciones WHERE usuario_id = ?');
    $avg->execute([$userId]);
    echo json_encode([
        'enrolled'  => (int)$enrolled->fetchColumn(),
        'completed' => (int)$completed->fetchColumn(),
        'progress'  => round((float)$avg->fetchColumn()),
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acci\ufffdn no v\ufffdlida']);
