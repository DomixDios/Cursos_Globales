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
        ['label' => 'Panel',          'icon' => 'bi-speedometer2', 'page' => 'moderator-dashboard'],
        ['label' => 'Pendientes',    'icon' => 'bi-clock-history','page' => 'moderator-pending'],
        ['label' => 'Categorias',    'icon' => 'bi-tags',         'page' => 'moderator-categories'],
    ],
    'teacher' => [
        ['label' => 'Panel',          'icon' => 'bi-speedometer2', 'page' => 'teacher-dashboard'],
        ['label' => 'Mis Cursos',    'icon' => 'bi-collection',   'page' => 'teacher-courses'],
        ['label' => 'Ganancias',     'icon' => 'bi-cash-stack',   'page' => 'earnings'],
    ],
    'student' => [
        ['label' => 'Panel',          'icon' => 'bi-speedometer2',   'page' => 'student-dashboard'],
        ['label' => 'Mis Cursos',    'icon' => 'bi-book',           'page' => 'my-courses'],
        ['label' => 'Progreso',       'icon' => 'bi-graph-up-arrow', 'page' => 'progress'],
        ['label' => 'Certificados',   'icon' => 'bi-patch-check-fill','page' => 'certificates'],
        ['label' => 'Perfil',         'icon' => 'bi-person-circle',  'page' => 'profile'],
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
<!-- Sidebar fijo izquierdo (visible en todos los tama�os) -->
<aside class="sidebar-fixed">
    <nav class="list-group list-group-flush">
        <?php renderSidebarItems($items, $currentPage); ?>
    </nav>
</aside>
