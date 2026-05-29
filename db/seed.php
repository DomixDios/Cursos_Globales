<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

echo "Poblando base de datos...\n";

$password = password_hash('password123', PASSWORD_DEFAULT);

$usuarios = [
    ['Admin Principal',   'admin@cursosglobales.com',    'admin',      1],
    ['Moderador Uno',    'mod@cursosglobales.com',      'moderador',  1],
    ['Carlos Profesor',  'carlos@cursosglobales.com',   'profesor',   1],
    ['Maria Estudiante', 'maria@cursosglobales.com',    'estudiante', 1],
];

$stmt = $pdo->prepare('INSERT OR IGNORE INTO usuarios (nombre_completo, email, password, rol, activo) VALUES (?, ?, ?, ?, ?)');
foreach ($usuarios as $u) {
    $stmt->execute([$u[0], $u[1], $password, $u[2], $u[3]]);
}
echo "  Usuarios creados (password: password123)\n";

$categorias = [
    ['Desarrollo Web',       'desarrollo-web',       'Aprende HTML, CSS, JavaScript, PHP y mas'],
    ['Data Science',         'data-science',         'Python, SQL, Machine Learning y analisis'],
    ['Diseno UX/UI',         'diseno-ux-ui',         'Figma, prototipado, investigacion de usuarios'],
    ['Negocios Digitales',   'negocios-digitales',   'Marketing, ventas, emprendimiento online'],
];

$stmt = $pdo->prepare('INSERT OR IGNORE INTO categorias (nombre, slug, descripcion) VALUES (?, ?, ?)');
foreach ($categorias as $c) {
    $stmt->execute($c);
}
echo "  Categorias creadas\n";

$courseSlug = 'php-moderno-desde-cero';
$existing = $pdo->prepare('SELECT id FROM cursos WHERE slug = ?');
$existing->execute([$courseSlug]);
if (!$existing->fetch()) {
    $pdo->prepare('INSERT INTO cursos (profesor_id, categoria_id, titulo, slug, descripcion, descripcion_corta, precio, nivel, estado, destacado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([
            3, 1,
            'PHP Moderno desde Cero',
            $courseSlug,
            'Aprende PHP 8 con un enfoque moderno: PDO, MVC, seguridad, y buenas practicas para construir aplicaciones web profesionales.',
            'El curso definitivo para dominar PHP 8 desde los fundamentos hasta la arquitectura avanzada.',
            29.99, 'principiante', 'aprobado', 1,
        ]);

    $courseId = $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO modulos (curso_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)')
        ->execute([$courseId, 'Fundamentos de PHP', 'Sintaxis basica, variables, tipos de datos y estructuras de control.', 1]);
    $mod1 = $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO clases (modulo_id, titulo, descripcion, url_video, duracion, tipo_contenido, orden) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$mod1, 'Introduccion y configuracion del entorno', 'Instalacion de PHP, XAMPP y tu primer script.', 'https://www.youtube.com/embed/dummy1', 15, 'video', 1]);
    $pdo->prepare('INSERT INTO clases (modulo_id, titulo, descripcion, url_video, duracion, tipo_contenido, orden) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$mod1, 'Variables y tipos de datos', 'Strings, integers, floats, booleanos y null.', 'https://www.youtube.com/embed/dummy2', 20, 'video', 2]);
    $pdo->prepare('INSERT INTO clases (modulo_id, titulo, descripcion, duracion, tipo_contenido, texto_contenido, orden) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$mod1, 'Ejercicio practico: Calculadora basica', 'Pon a prueba lo aprendido construyendo una calculadora en PHP.', 0, 'articulo', 'Aqui iria el contenido del ejercicio...', 3]);

    $pdo->prepare('INSERT INTO modulos (curso_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)')
        ->execute([$courseId, 'Programacion Orientada a Objetos', 'Clases, objetos, herencia, interfaces y namespaces.', 2]);
    $mod2 = $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO clases (modulo_id, titulo, descripcion, url_video, duracion, tipo_contenido, orden) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$mod2, 'Clases y objetos en PHP', 'Definicion de clases, propiedades, metodos y el operador ->.', 'https://www.youtube.com/embed/dummy3', 25, 'video', 1]);

    echo "  Curso demo creado: 'PHP Moderno desde Cero'\n";

    $studentId = $pdo->query("SELECT id FROM usuarios WHERE email = 'maria@cursosglobales.com'")->fetchColumn();
    if ($studentId) {
        $pdo->prepare('INSERT OR IGNORE INTO inscripciones (usuario_id, curso_id) VALUES (?, ?)')
            ->execute([$studentId, $courseId]);
        echo "  Estudiante Maria inscrita al curso demo.\n";
    }
}

echo "\nBase de datos poblada exitosamente.\n";
