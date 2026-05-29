<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$pdo = getDB();
$where  = ['c.estado = ?'];
$params = ['aprobado'];
$sql    = 'SELECT c.*, u.nombre_completo AS profesor_nombre, cat.nombre AS categoria_nombre,
           (SELECT ROUND(AVG(puntuacion),1) FROM resenas WHERE curso_id = c.id) AS promedio_valoracion,
           (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS total_estudiantes
           FROM cursos c
           JOIN usuarios u ON u.id = c.profesor_id
           LEFT JOIN categorias cat ON cat.id = c.categoria_id';

if (!empty($_GET['category'])) { $where[] = 'c.categoria_id = ?'; $params[] = (int)$_GET['category']; }
if (!empty($_GET['level']))    { $where[] = 'c.nivel = ?';        $params[] = $_GET['level']; }
if (!empty($_GET['search']))   { $where[] = 'c.titulo LIKE ?';    $params[] = '%' . $_GET['search'] . '%'; }

$sql .= ' WHERE ' . implode(' AND ', $where) . ' ORDER BY c.creado_en DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());
