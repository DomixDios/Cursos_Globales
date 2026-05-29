<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !in_array(currentUserRole(), ['profesor','admin'])) {
    http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($action === 'list') {
    $stmt = $pdo->prepare('SELECT c.*, cat.nombre AS categoria_nombre,
        (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS total_estudiantes
        FROM cursos c LEFT JOIN categorias cat ON cat.id = c.categoria_id
        WHERE c.profesor_id = ? ORDER BY c.actualizado_en DESC');
    $stmt->execute([$userId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'titulo'            => $_POST['title'],
        'slug'              => strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $_POST['title']), '-')),
        'descripcion_corta' => $_POST['short_description'] ?? '',
        'descripcion'       => $_POST['description'] ?? '',
        'precio'            => (float)($_POST['price'] ?? 0),
        'nivel'             => $_POST['level'] ?? 'principiante',
        'categoria_id'      => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'estado'            => 'pendiente',
    ];
    if ($id) {
        $stmt = $pdo->prepare('UPDATE cursos SET titulo=?,slug=?,descripcion_corta=?,descripcion=?,precio=?,nivel=?,categoria_id=?,estado=?,actualizado_en=datetime() WHERE id=? AND profesor_id=?');
        $stmt->execute([...array_values($data), $id, $userId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO cursos (profesor_id,titulo,slug,descripcion_corta,descripcion,precio,nivel,categoria_id,estado) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$userId, ...array_values($data)]);
        $id = $pdo->lastInsertId();
    }
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM cursos WHERE id = ? AND profesor_id = ?')->execute([$id, $userId]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'detail') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM cursos WHERE id = ? AND (profesor_id = ? OR ? = true)');
    $stmt->execute([$id, $userId, currentUserRole() === 'admin']);
    echo json_encode($stmt->fetch());
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acci\ufffdn no v\ufffdlida']);
