<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !in_array(currentUserRole(), ['admin','moderator'])) {
    http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? '';

if ($action === 'dashboard') {
    echo json_encode([
        'users'    => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'courses'  => $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn(),
        'pending'  => $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'pending'")->fetchColumn(),
        'revenue'  => $pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments')->fetchColumn(),
    ]);
    exit;
}

if ($action === 'users') {
    $stmt = $pdo->query('SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY created_at DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'approve' || $action === 'reject') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $reason = $_POST['reason'] ?? '';
    $stmt = $pdo->prepare('UPDATE courses SET status = ?, rejection_reason = ?, approved_by = ?, approved_at = datetime() WHERE id = ?');
    $stmt->execute([$status, $reason, $_SESSION['user_id'], $id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'pending-courses') {
    $stmt = $pdo->query("SELECT c.*, u.full_name AS teacher_name FROM courses c JOIN users u ON u.id = c.teacher_id WHERE c.status = 'pending' ORDER BY c.created_at ASC");
    echo json_encode($stmt->fetchAll());
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acción no válida']);
