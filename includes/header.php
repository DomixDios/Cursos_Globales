<?php $user = currentUser(); ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4" href="<?= BASE_URL ?>">
            <i class="bi bi-book me-2"></i><?= APP_NAME ?>
        </a>
        <div class="d-flex align-items-center gap-2">
            <?php if ($user): ?>
                <span class="text-muted small d-none d-md-inline"><?= e($user['nombre_completo']) ?></span>
                <span class="badge bg-light text-dark d-none d-md-inline">
                    <?= ucfirst(e($user['rol'])) ?>
                </span>
                <a href="<?= BASE_URL ?>/index.php?page=logout" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            <?php else: ?>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#loginModal">
                    <i class="bi bi-person"></i> Ingresar
                </button>
                <a href="<?= BASE_URL ?>/index.php?page=register" class="btn btn-sm btn-primary">
                    Registrarse
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
