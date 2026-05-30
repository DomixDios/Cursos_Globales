<?php $user = currentUser(); $pageTitle = 'Dashboard'; ?>
<div class="welcome-banner mb-4">
    <div class="welcome-content">
        <div class="welcome-avatar">
            <?php if ($user['avatar']): ?>
                <img src="<?= BASE_URL . '/' . e($user['avatar']) ?>" alt="Avatar">
            <?php else: ?>
                <span><?= strtoupper(substr($user['nombre_completo'], 0, 1)) ?></span>
            <?php endif; ?>
        </div>
        <div>
            <h2 class="fw-bold mb-1 text-white">Bienvenido de vuelta, <?= e($user['nombre_completo']) ?>!</h2>
            <p class="mb-0 text-white-50">Panel de control de estudiante</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <div class="stat-body">
                <span class="stat-number" id="statEnrolled">0</span>
                <span class="stat-desc">Cursos inscritos</span>
            </div>
            <div class="stat-trend"><i class="bi bi-arrow-up"></i> en curso</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-patch-check-fill"></i></div>
            <div class="stat-body">
                <span class="stat-number" id="statCompleted">0</span>
                <span class="stat-desc">Completados</span>
            </div>
            <div class="stat-trend"><i class="bi bi-trophy"></i> logros</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-body">
                <span class="stat-number" id="statProgress">0%</span>
                <span class="stat-desc">Progreso general</span>
            </div>
            <div class="progress dashboard-progress mt-2">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="statProgressBar" style="width:0%"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Mis Cursos</h5>
                <a href="index.php?page=my-courses" class="btn btn-sm btn-outline-primary">Ver todos <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            <div class="card-body p-0" id="dashboardCourses">
                <div class="text-center text-muted py-5">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>Cargando cursos...
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Acceso rápido</h5>
            </div>
            <div class="card-body p-3">
                <a href="index.php?page=my-courses" class="quick-link">
                    <div class="ql-icon bg-primary-subtle text-primary"><i class="bi bi-book"></i></div>
                    <div><div class="fw-semibold">Mis Cursos</div><small class="text-muted">Ver todos tus cursos</small></div>
                    <i class="bi bi-chevron-right text-muted ms-auto"></i>
                </a>
                <a href="index.php?page=progress" class="quick-link">
                    <div class="ql-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></div>
                    <div><div class="fw-semibold">Mi Progreso</div><small class="text-muted">Revisa tu avance</small></div>
                    <i class="bi bi-chevron-right text-muted ms-auto"></i>
                </a>
                <a href="index.php?page=certificates" class="quick-link">
                    <div class="ql-icon bg-warning-subtle text-warning"><i class="bi bi-patch-check-fill"></i></div>
                    <div><div class="fw-semibold">Certificados</div><small class="text-muted">Descarga tus certificados</small></div>
                    <i class="bi bi-chevron-right text-muted ms-auto"></i>
                </a>
                <a href="index.php?page=profile" class="quick-link">
                    <div class="ql-icon bg-info-subtle text-info"><i class="bi bi-person-circle"></i></div>
                    <div><div class="fw-semibold">Mi Perfil</div><small class="text-muted">Edita tu información</small></div>
                    <i class="bi bi-chevron-right text-muted ms-auto"></i>
                </a>
            </div>
        </div>
    </div>
</div>
