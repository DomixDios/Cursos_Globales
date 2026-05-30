<?php
define('APP_NAME', 'Cursos Globales');

define('ROLE_ADMIN',     'admin');
define('ROLE_MODERATOR', 'moderador');
define('ROLE_TEACHER',   'profesor');
define('ROLE_STUDENT',   'estudiante');

define('COURSE_DRAFT',     'borrador');
define('COURSE_PENDING',   'pendiente');
define('COURSE_APPROVED',  'aprobado');
define('COURSE_REJECTED',  'rechazado');
define('COURSE_PUBLISHED', 'publicado');

if (!defined('BASE_URL')) {
    // Intentamos leer la configuración local si existe
    $localConfig = __DIR__ . '/config.local.php';
    if (file_exists($localConfig)) {
        require_once $localConfig;
    }
    
    // Si no se definió en el archivo local, usamos la detección dinámica
    if (!defined('BASE_URL')) {
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $base = str_replace(basename($scriptName), '', $scriptName);
        define('BASE_URL', rtrim($base, '/'));
    }
}
