<?php
$role = currentUserRole();
if (!$role) return;

$menuMap = [
    'admin' => [
        ['label' => 'Dashboard',     'icon' => 'bi-speedometer2', 'page' => 'admin-dashboard'],
        ['label' => 'Usuarios',      'icon' => 'bi-people',       'page' => 'users'],
        ['label' => 'Categorías',    'icon' => 'bi-tags',         'page' => 'categories'],
        ['label' => 'Pendientes',    'icon' => 'bi-clock-history','page' => 'pending-courses'],
        ['label' => 'Estadísticas',  'icon' => 'bi-bar-chart',    'page' => 'stats'],
    ],
    'moderator' => [
        ['label' => 'Dashboard',     'icon' => 'bi-speedometer2', 'page' => 'admin-dashboard'],
        ['label' => 'Pendientes',    'icon' => 'bi-clock-history','page' => 'pending-courses'],
        ['label' => 'Categorías',    'icon' => 'bi-tags',         'page' => 'categories'],
    ],
    'teacher' => [
        ['label' => 'Dashboard',     'icon' => 'bi-speedometer2', 'page' => 'teacher-dashboard'],
        ['label' => 'Mis Cursos',    'icon' => 'bi-collection',   'page' => 'teacher-courses'],
        ['label' => 'Ganancias',     'icon' => 'bi-cash-stack',   'page' => 'earnings'],
    ],
    'student' => [
        ['label' => 'Dashboard',     'icon' => 'bi-speedometer2', 'page' => 'student-dashboard'],
        ['label' => 'Mis Cursos',    'icon' => 'bi-book',         'page' => 'my-courses'],
    ],
];

$items     = $menuMap[$role] ?? [];
$currentPage = $_GET['page'] ?? '';
?>
<div class="offcanvas offcanvas-start d-lg-block border-end" tabindex="-1" id="sidebarCanvas"
     style="width: 250px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-semibold">Navegación</h5>
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="list-group list-group-flush">
            <?php foreach ($items as $item): ?>
                <a href="<?= BASE_URL ?>/index.php?page=<?= $item['page'] ?>"
                   class="list-group-item list-group-item-action border-0 py-3 d-flex align-items-center
                          <?= ($currentPage === $item['page']) ? 'active' : '' ?>">
                    <i class="bi <?= $item['icon'] ?> me-3 fs-5"></i>
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>
