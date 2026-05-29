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
    $stmt = $pdo->prepare('SELECT e.id FROM inscripciones e JOIN clases cl ON cl.curso_id = e.curso_id WHERE e.usuario_id = ? AND cl.id = ?');
    $stmt->execute([$userId, $classId]);
    $enrollment = $stmt->fetch();
    if (!$enrollment) { http_response_code(403); echo json_encode(['error' => 'No inscrito']); exit; }

    $pdo->prepare('INSERT OR IGNORE INTO progreso_clases (inscripcion_id, clase_id, completado, completado_en) VALUES (?, ?, 1, datetime())')->execute([$enrollment['id'], $classId]);

    $total = $pdo->prepare('SELECT COUNT(*) FROM clases WHERE modulo_id IN (SELECT id FROM modulos WHERE curso_id = (SELECT curso_id FROM inscripciones WHERE id = ?))');
    $total->execute([$enrollment['id']]);
    $completed = $pdo->prepare('SELECT COUNT(*) FROM progreso_clases WHERE inscripcion_id = ? AND completado = 1');
    $completed->execute([$enrollment['id']]);
    $pct = $total->fetchColumn() > 0 ? round(($completed->fetchColumn() / $total->fetchColumn()) * 100) : 0;
    $pdo->prepare('UPDATE inscripciones SET progreso = ?, completado_en = CASE WHEN ? >= 100 THEN datetime() ELSE NULL END WHERE id = ?')->execute([$pct, $pct, $enrollment['id']]);

    echo json_encode(['success' => true, 'progress' => $pct]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acci\ufffdn no v\ufffdlida']);
