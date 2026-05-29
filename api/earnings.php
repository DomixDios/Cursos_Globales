<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || currentUserRole() !== 'teacher') {
    http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
}

$pdo = getDB();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT p.*, c.title AS course_name, u.full_name AS student_name, e.enrolled_at
    FROM payments p
    JOIN enrollments e ON e.id = p.enrollment_id
    JOIN courses c ON c.id = e.course_id
    JOIN users u ON u.id = e.user_id
    WHERE c.teacher_id = ?
    ORDER BY p.created_at DESC');
$stmt->execute([$userId]);
$payments = $stmt->fetchAll();

$total   = array_sum(array_column($payments, 'teacher_earnings'));
$pending = array_sum(array_column(array_filter($payments, fn($p) => !$p['paid_to_teacher']), 'teacher_earnings'));

echo json_encode([
    'payments' => $payments,
    'total'    => $total,
    'pending'  => $pending,
    'students' => count(array_unique(array_column($payments, 'student_name'))),
]);
