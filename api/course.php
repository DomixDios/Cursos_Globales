<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !in_array(currentUserRole(), ['teacher','admin'])) {
    http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($action === 'list') {
    $stmt = $pdo->prepare('SELECT c.*, cat.name AS category_name,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS student_count
        FROM courses c LEFT JOIN categories cat ON cat.id = c.category_id
        WHERE c.teacher_id = ? ORDER BY c.updated_at DESC');
    $stmt->execute([$userId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'title'             => $_POST['title'],
        'slug'              => strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $_POST['title']), '-')),
        'short_description' => $_POST['short_description'] ?? '',
        'description'       => $_POST['description'] ?? '',
        'price'             => (float)($_POST['price'] ?? 0),
        'level'             => $_POST['level'] ?? 'beginner',
        'category_id'       => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'status'            => 'pending',
    ];
    if ($id) {
        $stmt = $pdo->prepare('UPDATE courses SET title=?,slug=?,short_description=?,description=?,price=?,level=?,category_id=?,status=?,updated_at=datetime() WHERE id=? AND teacher_id=?');
        $stmt->execute([...array_values($data), $id, $userId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO courses (teacher_id,title,slug,short_description,description,price,level,category_id,status) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$userId, ...array_values($data)]);
        $id = $pdo->lastInsertId();
    }
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM courses WHERE id = ? AND teacher_id = ?')->execute([$id, $userId]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'detail') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ? AND (teacher_id = ? OR ? = true)');
    $stmt->execute([$id, $userId, currentUserRole() === 'admin']);
    echo json_encode($stmt->fetch());
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acción no válida']);
