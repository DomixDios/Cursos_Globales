<?php
$role = currentUserRole();
if (!$role) return;

$menuMap = [
    'admin' => [
        ['label' => 'Panel',          'icon' => 'bi-speedometer2', 'page' => 'admin-dashboard'],
        ['label' => 'Usuarios',      'icon' => 'bi-people',       'page' => 'users'],
        ['label' => 'Categorias',    'icon' => 'bi-tags',         'page' => 'categories'],
        ['label' => 'Pendientes',    'icon' => 'bi-clock-history','page' => 'pending-courses'],
        ['label' => 'Estadisticas',  'icon' => 'bi-bar-chart',    'page' => 'stats'],
    ],
    'moderator' => [
        ['label' => 'Panel',          'icon' => 'bi-speedometer2', 'page' => 'admin-dashboard'],
        ['label' => 'Pendientes',    'icon' => 'bi-clock-history','page' => 'pending-courses'],
        ['label' => 'Categorias',    'icon' => 'bi-tags',         'page' => 'categories'],
    ],
    'teacher' => [
        ['label' => 'Panel',          'icon' => 'bi-speedometer2', 'page' => 'teacher-dashboard'],
        ['label' => 'Mis Cursos',    'icon' => 'bi-collection',   'page' => 'teacher-courses'],
        ['label' => 'Ganancias',     'icon' => 'bi-cash-stack',   'page' => 'earnings'],
    ],
    'student' => [
        ['label' => 'Panel',          'icon' => 'bi-speedometer2', 'page' => 'student-dashboard'],
        ['label' => 'Mis Cursos',    'icon' => 'bi-book',         'page' => 'my-courses'],
    ],
];

$items     = $menuMap[$role] ?? [];
$currentPage = $_GET['page'] ?? '';

function renderSidebarItems(array $items, string $currentPage): void
{
    foreach ($items as $item): ?>
        <a href="<?= BASE_URL ?>/index.php?page=<?= $item['page'] ?>"
           class="list-group-item list-group-item-action border-0 py-3 d-flex align-items-center
                  <?= ($currentPage === $item['page']) ? 'active' : '' ?>">
            <i class="bi <?= $item['icon'] ?> me-3 fs-5"></i>
            <?= $item['label'] ?>
        </a>
    <?php endforeach;
}
?>
<!-- Sidebar para escritorio (siempre visible, fijo) -->
<aside class="sidebar-desktop d-none d-lg-block">
    <nav class="list-group list-group-flush">
        <?php renderSidebarItems($items, $currentPage); ?>
    </nav>
</aside>

<!-- Offcanvas para m?vil (se abre con el bot?n hamburguesa) -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarCanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-semibold">Navegación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="list-group list-group-flush">
            <?php renderSidebarItems($items, $currentPage); ?>
        </nav>
    </div>
</div>
