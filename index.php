<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/includes/functions.php';

$page  = $_GET['page'] ?? 'landing';
$error = $_GET['error'] ?? '';

$publicRoutes = ['landing', 'catalog', 'course-detail', 'register'];

$routeMap = [
    'landing'           => 'modules/public/landing.php',
    'catalog'           => 'modules/public/catalog.php',
    'course-detail'     => 'modules/public/course-detail.php',
    'register'          => 'modules/public/register.php',
    'logout'            => null,
    'student-dashboard' => 'modules/student/dashboard.php',
    'my-courses'        => 'modules/student/my-courses.php',
    'player'            => 'modules/student/player.php',
    'profile'           => 'modules/student/profile.php',
    'certificates'      => 'modules/student/certificates.php',
    'progress'          => 'modules/student/progress.php',
    'teacher-dashboard' => 'modules/teacher/dashboard.php',
    'teacher-courses'   => 'modules/teacher/courses.php',
    'syllabus'          => 'modules/teacher/syllabus.php',
    'earnings'          => 'modules/teacher/earnings.php',
    'admin-dashboard'   => 'modules/admin/dashboard.php',
    'users'             => 'modules/admin/users.php',
    'categories'        => 'modules/admin/categories.php',
    'pending-courses'   => 'modules/admin/pending-courses.php',
    'stats'             => 'modules/admin/stats.php',
];

$roleMap = [
    'student-dashboard' => [ROLE_STUDENT],
    'my-courses'        => [ROLE_STUDENT],
    'player'            => [ROLE_STUDENT],
    'profile'           => [ROLE_STUDENT],
    'certificates'      => [ROLE_STUDENT],
    'progress'          => [ROLE_STUDENT],
    'teacher-dashboard' => [ROLE_TEACHER],
    'teacher-courses'   => [ROLE_TEACHER],
    'syllabus'          => [ROLE_TEACHER],
    'earnings'          => [ROLE_TEACHER],
    'admin-dashboard'   => [ROLE_ADMIN, ROLE_MODERATOR],
    'users'             => [ROLE_ADMIN],
    'categories'        => [ROLE_ADMIN, ROLE_MODERATOR],
    'pending-courses'   => [ROLE_ADMIN, ROLE_MODERATOR],
    'stats'             => [ROLE_ADMIN],
];

if ($page === 'logout') {
    logout();
    redirect(BASE_URL . '/index.php');
}

if (isset($roleMap[$page]) && !isLoggedIn()) {
    redirect(BASE_URL . '/index.php?error=login_required');
}

if (isset($roleMap[$page])) {
    $userRole = currentUserRole();
    if (!in_array($userRole, $roleMap[$page], true)) {
        redirect(BASE_URL . '/index.php?error=forbidden');
    }
}

$pageTitle = ucfirst(str_replace('-', ' ', $page));
$extraCss  = '';
$extraJs   = '';

$contentFile = $routeMap[$page] ?? 'modules/public/landing.php';
$needsLayout = in_array($page, $publicRoutes) || isset($roleMap[$page]);

if ($needsLayout) {
    include __DIR__ . '/includes/head.php';
    include __DIR__ . '/includes/header.php';
    include __DIR__ . '/includes/sidebar.php';
    echo '<div class="main-content">';
    require $contentFile;
    echo '</div>';
    include __DIR__ . '/includes/footer.php';
} else {
    require $contentFile;
}
