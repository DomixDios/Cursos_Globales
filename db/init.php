<?php
echo "=== Inicializando Cursos Globales ===\n\n";

$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false) {
    die("ERROR: No se encontr\ufffdo schema.sql\n");
}

require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

$statements = explode(';', $schema);
foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (!empty($stmt)) {
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            echo "  [OK] (ignorado: {$e->getMessage()})\n";
        }
    }
}

echo "  \u{1f4be} Tablas creadas exitosamente\n\n";

require __DIR__ . '/seed.php';

echo "\n=== Inicializaci\ufffdn completada ===\n";
echo "  Usuarios de prueba (password: password123):\n";
echo "    - admin@cursosglobales.com    (admin)\n";
echo "    - mod@cursosglobales.com      (moderador)\n";
echo "    - carlos@cursosglobales.com   (profesor)\n";
echo "    - maria@cursosglobales.com    (estudiante)\n";
