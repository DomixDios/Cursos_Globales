<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || currentUserRole() !== 'profesor') {
    http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($action === 'dashboard') {
    $totalCursos  = $pdo->prepare('SELECT COUNT(*) FROM cursos WHERE profesor_id = ?');
    $totalCursos->execute([$userId]);

    $totalEstudiantes = $pdo->prepare('SELECT COUNT(DISTINCT i.usuario_id) FROM inscripciones i JOIN cursos c ON c.id = i.curso_id WHERE c.profesor_id = ?');
    $totalEstudiantes->execute([$userId]);

    $cursosPendientes = $pdo->prepare("SELECT COUNT(*) FROM cursos WHERE profesor_id = ? AND estado = 'pendiente'");
    $cursosPendientes->execute([$userId]);

    $ganancias = $pdo->prepare('SELECT COALESCE(SUM(p.ganancias_profesor),0) AS total, COALESCE(SUM(CASE WHEN p.pagado_profesor = 0 THEN p.ganancias_profesor ELSE 0 END),0) AS pendiente FROM pagos p JOIN inscripciones i ON i.id = p.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE c.profesor_id = ?');
    $ganancias->execute([$userId]);
    $g = $ganancias->fetch();

    echo json_encode([
        'total_cursos'         => (int)$totalCursos->fetchColumn(),
        'total_estudiantes'    => (int)$totalEstudiantes->fetchColumn(),
        'cursos_pendientes'    => (int)$cursosPendientes->fetchColumn(),
        'ganancias_totales'    => (float)$g['total'],
        'ganancias_pendientes' => (float)$g['pendiente'],
    ]);
    exit;
}

if ($action === 'students') {
    $stmt = $pdo->prepare('SELECT u.id, u.nombre_completo, u.email, c.titulo AS curso_titulo, c.id AS curso_id, i.inscrito_en, i.progreso FROM inscripciones i JOIN usuarios u ON u.id = i.usuario_id JOIN cursos c ON c.id = i.curso_id WHERE c.profesor_id = ? ORDER BY i.inscrito_en DESC');
    $stmt->execute([$userId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acción no válida']);
