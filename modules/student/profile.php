<?php
$user = currentUser();
$pageTitle = 'Mi Perfil';
$pdo = getDB();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM inscripciones WHERE usuario_id = ?');
$stmt->execute([$user['id']]);
$totalEnrolled = $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM inscripciones WHERE usuario_id = ? AND completado_en IS NOT NULL');
$stmt->execute([$user['id']]);
$totalCompleted = $stmt->fetchColumn();
?>
<div class="profile-header mb-4">
    <div class="profile-cover"></div>
    <div class="profile-info">
        <div class="avatar-wrapper">
            <div id="avatarPreview" class="avatar-circle">
                <?php if ($user['avatar']): ?>
                    <img src="<?= BASE_URL . '/' . e($user['avatar']) ?>" alt="Avatar">
                <?php else: ?>
                    <span class="avatar-initial"><?= strtoupper(substr($user['nombre_completo'], 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <label for="avatar" class="avatar-upload-btn" title="Cambiar foto">
                <i class="bi bi-camera-fill"></i>
            </label>
        </div>
        <h3 class="fw-bold mb-1"><?= e($user['nombre_completo']) ?></h3>
        <p class="text-muted mb-3"><i class="bi bi-envelope me-1"></i><?= e($user['email']) ?></p>
        <?php if ($user['bio']): ?>
            <p class="profile-bio"><?= e($user['bio']) ?></p>
        <?php endif; ?>
        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-value"><?= $totalEnrolled ?></span>
                <span class="stat-label">Cursos inscritos</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= $totalCompleted ?></span>
                <span class="stat-label">Completados</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= $totalEnrolled > 0 ? round(($totalCompleted / $totalEnrolled) * 100) : 0 ?>%</span>
                <span class="stat-label">Tasa de finalización</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2 text-primary"></i>Editar perfil</h5>
                <form id="profileForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre_completo" class="form-label fw-medium">Nombre completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person text-primary"></i></span>
                                <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?= e($user['nombre_completo']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-medium">Correo electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope text-primary"></i></span>
                                <input type="email" class="form-control" id="email" value="<?= e($user['email']) ?>" disabled>
                            </div>
                            <div class="form-text">El email no se puede cambiar.</div>
                        </div>
                        <div class="col-12">
                            <label for="bio" class="form-label fw-medium">Biografía</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Cuéntanos sobre ti..."><?= e($user['bio']) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="avatar" class="form-label fw-medium">Foto de perfil</label>
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" style="display:none">
                            <div class="file-upload-area" onclick="document.getElementById('avatar').click()">
                                <i class="bi bi-cloud-arrow-up-fill fs-2 text-primary"></i>
                                <p class="mb-0 fw-medium">Haz clic para subir una foto</p>
                                <small class="text-muted">JPG, PNG, GIF o WebP</small>
                            </div>
                        </div>
                    </div>
                    <div id="profileMessage" class="alert d-none mt-3"></div>
                    <button type="submit" class="btn btn-primary btn-lg px-5 mt-3">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <i class="bi bi-check2-circle me-1"></i> Guardar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
