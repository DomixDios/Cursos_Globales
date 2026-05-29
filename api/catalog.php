<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$pdo = getDB();
$where  = ['c.status = ?'];
$params = ['approved'];
$sql    = 'SELECT c.*, u.full_name AS teacher_name, cat.name AS category_name,
           (SELECT ROUND(AVG(rating),1) FROM reviews WHERE course_id = c.id) AS avg_rating,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS student_count
           FROM courses c
           JOIN users u ON u.id = c.teacher_id
           LEFT JOIN categories cat ON cat.id = c.category_id';

if (!empty($_GET['category'])) { $where[] = 'c.category_id = ?'; $params[] = (int)$_GET['category']; }
if (!empty($_GET['level']))    { $where[] = 'c.level = ?';       $params[] = $_GET['level']; }
if (!empty($_GET['search']))   { $where[] = 'c.title LIKE ?';    $params[] = '%' . $_GET['search'] . '%'; }

$sql .= ' WHERE ' . implode(' AND ', $where) . ' ORDER BY c.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());
