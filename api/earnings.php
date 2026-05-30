<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || currentUserRole() !== 'profesor') {
    http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
}

$pdo = getDB();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT p.*, c.titulo AS curso_nombre, u.nombre_completo AS estudiante_nombre, e.inscrito_en
    FROM pagos p
    JOIN inscripciones e ON e.id = p.inscripcion_id
    JOIN cursos c ON c.id = e.curso_id
    JOIN usuarios u ON u.id = e.usuario_id
    WHERE c.profesor_id = ?
    ORDER BY p.creado_en DESC');
$stmt->execute([$userId]);
$payments = $stmt->fetchAll();

$total   = array_sum(array_column($payments, 'ganancias_profesor'));
$pending = array_sum(array_column(array_filter($payments, function($p) { return !$p['pagado_profesor']; }), 'ganancias_profesor'));

echo json_encode([
    'payments' => $payments,
    'total'    => $total,
    'pending'  => $pending,
    'students' => count(array_unique(array_column($payments, 'estudiante_nombre'))),
]);
