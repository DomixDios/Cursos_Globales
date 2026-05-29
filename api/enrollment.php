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
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO enrollments (user_id, course_id) VALUES (?, ?)');
    $stmt->execute([$userId, $courseId]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'my-courses') {
    $stmt = $pdo->prepare('SELECT e.*, c.title, c.slug, c.thumbnail, c.level, u.full_name AS teacher_name
        FROM enrollments e JOIN courses c ON c.id = e.course_id JOIN users u ON u.id = c.teacher_id
        WHERE e.user_id = ? ORDER BY e.enrolled_at DESC');
    $stmt->execute([$userId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acción no válida']);
