<?php $user = currentUser(); $pageTitle = 'Dashboard'; ?>
<h2 class="fw-bold mb-4">Bienvenido, <?= e($user['nombre_completo']) ?></h2>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card text-center p-3">
            <h3 class="fw-bold text-primary" id="statEnrolled">0</h3>
            <p class="text-muted mb-0 small">Cursos inscritos</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3">
            <h3 class="fw-bold text-success" id="statCompleted">0</h3>
            <p class="text-muted mb-0 small">Completados</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3">
            <h3 class="fw-bold text-warning" id="statProgress">0%</h3>
            <p class="text-muted mb-0 small">Progreso general</p>
        </div>
    </div>
</div>
