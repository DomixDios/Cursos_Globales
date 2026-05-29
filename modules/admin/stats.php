<?php $pageTitle = 'Estad�sticas'; $extraJs = '<script src="' . BASE_URL . '/assets/js/admin.js"></script>'; ?>
<h2 class="fw-bold mb-4">Estad�sticas Globales</h2>
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card text-center p-3"><h3 class="fw-bold text-primary" id="sUsers">0</h3><p class="text-muted small mb-0">Usuarios</p></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h3 class="fw-bold text-success" id="sCourses">0</h3><p class="text-muted small mb-0">Cursos</p></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h3 class="fw-bold text-warning" id="sPending">0</h3><p class="text-muted small mb-0">Pendientes</p></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h3 class="fw-bold text-info" id="sRevenue">$0</h3><p class="text-muted small mb-0">Ingresos</p></div></div>
</div>
<div class="card mb-4"><div class="card-header">Usuarios registrados por mes</div><div class="card-body"><canvas id="usersChart" height="200"></canvas></div></div>
<div class="card"><div class="card-header">Ingresos por mes</div><div class="card-body"><canvas id="revenueChart" height="200"></canvas></div></div>
