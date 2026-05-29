<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !in_array(currentUserRole(), ['admin','moderator'])) {
    http_response_code(403);     echo json_encode(['error' => 'No autorizado'], JSON_INVALID_UTF8_SUBSTITUTE); exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? '';
$isAdmin = currentUserRole() === 'admin';

// ── Dashboard stats ──
if ($action === 'dashboard') {
    echo json_encode([
        'users'    => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'courses'  => (int)$pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn(),
        'pending'  => (int)$pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'pending'")->fetchColumn(),
        'revenue'  => (float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments')->fetchColumn(),
    ]);
    exit;
}

// ── List users ──
if ($action === 'users') {
    $stmt = $pdo->query('SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY created_at DESC');
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

// ── Save user (create/update) ──
if ($action === 'user-save' && $isAdmin) {
    $id   = (int)($_POST['id'] ?? 0);
    $name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $pass = $_POST['password'] ?? '';

    if ($id) {
        $sql = 'UPDATE users SET full_name = ?, email = ?, role = ?, updated_at = datetime() WHERE id = ?';
        $params = [$name, $email, $role, $id];
        if (!empty($pass)) {
            $sql = 'UPDATE users SET full_name = ?, email = ?, role = ?, password = ?, updated_at = datetime() WHERE id = ?';
            $params = [$name, $email, $role, password_hash($pass, PASSWORD_DEFAULT), $id];
        }
        $pdo->prepare($sql)->execute($params);
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($pass ?: 'password123', PASSWORD_DEFAULT), $role]);
    }
    echo json_encode(['success' => true]);
    exit;
}

// ── Toggle user active ──
if ($action === 'user-toggle' && $isAdmin) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE users SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, updated_at = datetime() WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── Approve/Reject course ──
if ($action === 'approve' || $action === 'reject') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $reason = $_POST['reason'] ?? '';
    $stmt = $pdo->prepare("UPDATE courses SET status = ?, rejection_reason = ?, approved_by = ?, approved_at = datetime() WHERE id = ?");
    $stmt->execute([$status, $reason, $_SESSION['user_id'], $id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── Pending courses ──
if ($action === 'pending-courses') {
    $stmt = $pdo->query("SELECT c.*, u.full_name AS teacher_name FROM courses c JOIN users u ON u.id = c.teacher_id WHERE c.status = 'pending' ORDER BY c.created_at ASC");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

// ── Category list ──
if ($action === 'category-list') {
    $stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM courses WHERE category_id = c.id) AS course_count FROM categories c ORDER BY c.name ASC");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

// ── Stats: users per month ──
if ($action === 'stats-users') {
    $stmt = $pdo->query("SELECT strftime('%Y-%m', created_at) AS month, COUNT(*) AS total FROM users GROUP BY month ORDER BY month ASC LIMIT 12");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

// ── Stats: revenue per month ──
if ($action === 'stats-revenue') {
    $stmt = $pdo->query("SELECT strftime('%Y-%m', p.created_at) AS month, COALESCE(SUM(p.amount),0) AS total FROM payments p GROUP BY month ORDER BY month ASC LIMIT 12");
    echo json_encode($stmt->fetchAll(), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acci�n no v�lida'], JSON_INVALID_UTF8_SUBSTITUTE);
