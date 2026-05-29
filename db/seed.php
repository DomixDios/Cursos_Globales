<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

echo "Poblando base de datos...\n";

$password = password_hash('password123', PASSWORD_DEFAULT);

$users = [
    ['Admin Principal',   'admin@cursosglobales.com',    'admin',    1],
    ['Moderador Uno',    'mod@cursosglobales.com',      'moderator',1],
    ['Carlos Profesor',  'carlos@cursosglobales.com',   'teacher',  1],
    ['María Estudiante', 'maria@cursosglobales.com',    'student',  1],
];

$stmt = $pdo->prepare('INSERT OR IGNORE INTO users (full_name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)');
foreach ($users as $u) {
    $stmt->execute([$u[0], $u[1], $password, $u[2], $u[3]]);
}
echo "  Usuarios creados (password: password123)\n";

$categories = [
    ['Desarrollo Web',       'desarrollo-web',       'Aprende HTML, CSS, JavaScript, PHP y más'],
    ['Data Science',         'data-science',         'Python, SQL, Machine Learning y análisis'],
    ['Diseño UI/UX',         'diseno-ui-ux',         'Figma, prototipado, investigación de usuarios'],
    ['Negocios Digitales',   'negocios-digitales',   'Marketing, ventas, emprendimiento online'],
];

$stmt = $pdo->prepare('INSERT OR IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)');
foreach ($categories as $c) {
    $stmt->execute($c);
}
echo "  Categorías creadas\n";

$courseSlug = 'php-moderno-desde-cero';
$existing = $pdo->prepare('SELECT id FROM courses WHERE slug = ?');
$existing->execute([$courseSlug]);
if (!$existing->fetch()) {
    $pdo->prepare('INSERT INTO courses (teacher_id, category_id, title, slug, description, short_description, price, level, status, is_featured)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([
            3, 1,
            'PHP Moderno desde Cero',
            $courseSlug,
            'Aprende PHP 8 con un enfoque moderno: PDO, MVC, seguridad, y buenas prácticas para construir aplicaciones web profesionales.',
            'El curso definitivo para dominar PHP 8 desde los fundamentos hasta la arquitectura avanzada.',
            29.99, 'beginner', 'approved', 1,
        ]);

    $courseId = $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO modules (course_id, title, description, sort_order) VALUES (?, ?, ?, ?)')
        ->execute([$courseId, 'Fundamentos de PHP', 'Sintaxis básica, variables, tipos de datos y estructuras de control.', 1]);
    $mod1 = $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO classes (module_id, title, description, video_url, duration, content_type, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$mod1, 'Introducción y configuración del entorno', 'Instalación de PHP, XAMPP y tu primer script.', 'https://www.youtube.com/embed/dummy1', 15, 'video', 1]);
    $pdo->prepare('INSERT INTO classes (module_id, title, description, video_url, duration, content_type, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$mod1, 'Variables y tipos de datos', 'Strings, integers, floats, booleanos y null.', 'https://www.youtube.com/embed/dummy2', 20, 'video', 2]);
    $pdo->prepare('INSERT INTO classes (module_id, title, description, duration, content_type, content_text, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$mod1, 'Ejercicio práctico: Calculadora básica', 'Pon a prueba lo aprendido construyendo una calculadora en PHP.', 0, 'article', 'Aquí iría el contenido del ejercicio...', 3]);

    $pdo->prepare('INSERT INTO modules (course_id, title, description, sort_order) VALUES (?, ?, ?, ?)')
        ->execute([$courseId, 'Programación Orientada a Objetos', 'Clases, objetos, herencia, interfaces y namespaces.', 2]);
    $mod2 = $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO classes (module_id, title, description, video_url, duration, content_type, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$mod2, 'Clases y objetos en PHP', 'Definición de clases, propiedades, métodos y el operador ->.', 'https://www.youtube.com/embed/dummy3', 25, 'video', 1]);

    echo "  Curso demo creado: 'PHP Moderno desde Cero'\n";
}

echo "\n? Base de datos poblada exitosamente.\n";
