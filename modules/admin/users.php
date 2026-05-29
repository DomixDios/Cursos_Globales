<?php $pageTitle = 'Usuarios'; $extraJs = '<script src="' . BASE_URL . '/assets/js/admin.js"></script>'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0 fw-bold">Usuarios</h2>
    <button class="btn btn-primary btn-sm" id="newUserBtn"><i class="bi bi-plus-lg"></i> Nuevo</button>
</div>
<div class="card"><div class="card-body p-0"><table class="table mb-0"><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Acciones</th></tr></thead><tbody id="usersTable"></tbody></table></div></div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <h5 class="fw-semibold mb-3" id="userModalLabel">Nuevo Usuario</h5>
            <form id="userForm">
                <input type="hidden" name="id" id="userId" value="0">
                <div class="mb-2">
                    <label class="form-label small">Nombre</label>
                    <input type="text" name="full_name" id="userName" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Email</label>
                    <input type="email" name="email" id="userEmail" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Contrase&ntilde;a</label>
                    <input type="password" name="password" id="userPassword" class="form-control" minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Rol</label>
                    <select name="role" id="userRole" class="form-select">
                        <option value="student">Estudiante</option>
                        <option value="teacher">Docente</option>
                        <option value="moderator">Moderador</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
    </div>
</div>
