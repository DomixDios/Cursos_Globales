<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error' => 'No autenticado']); exit; }

$pdo = getDB();
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($action === 'complete') {
    $classId = (int)($_POST['class_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT e.id FROM enrollments e JOIN classes cl ON cl.course_id = e.course_id WHERE e.user_id = ? AND cl.id = ?');
    $stmt->execute([$userId, $classId]);
    $enrollment = $stmt->fetch();
    if (!$enrollment) { http_response_code(403); echo json_encode(['error' => 'No inscrito']); exit; }

    $pdo->prepare('INSERT OR IGNORE INTO class_progress (enrollment_id, class_id, is_completed, completed_at) VALUES (?, ?, 1, datetime())')->execute([$enrollment['id'], $classId]);

    $total = $pdo->prepare('SELECT COUNT(*) FROM classes WHERE module_id IN (SELECT id FROM modules WHERE course_id = (SELECT course_id FROM enrollments WHERE id = ?))');
    $total->execute([$enrollment['id']]);
    $completed = $pdo->prepare('SELECT COUNT(*) FROM class_progress WHERE enrollment_id = ? AND is_completed = 1');
    $completed->execute([$enrollment['id']]);
    $pct = $total->fetchColumn() > 0 ? round(($completed->fetchColumn() / $total->fetchColumn()) * 100) : 0;
    $pdo->prepare('UPDATE enrollments SET progress = ?, completed_at = CASE WHEN ? >= 100 THEN datetime() ELSE NULL END WHERE id = ?')->execute([$pct, $pct, $enrollment['id']]);

    echo json_encode(['success' => true, 'progress' => $pct]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acción no válida']);
