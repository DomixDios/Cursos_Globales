<?php $pageTitle = 'Categorías'; $extraJs = '<script src="' . BASE_URL . '/assets/js/admin.js"></script>'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0 fw-bold">Categorías</h2>
    <button class="btn btn-primary btn-sm" id="newCatBtn"><i class="bi bi-plus-lg"></i> Nueva</button>
</div>
<div class="card"><div class="card-body p-0"><table class="table mb-0"><thead><tr><th>ID</th><th>Nombre</th><th>Slug</th><th>Activa</th><th>Cursos</th><th>Acciones</th></tr></thead><tbody id="categoriesTable"></tbody></table></div></div>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <h5 class="fw-semibold mb-3" id="categoryModalLabel">Nueva Categoría</h5>
            <form id="catForm">
                <input type="hidden" name="id" id="catId" value="0">
                <div class="mb-2">
                    <label class="form-label small">Nombre</label>
                    <input type="text" name="name" id="catName" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Slug</label>
                    <input type="text" name="slug" id="catSlug" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Descripci&oacute;n</label>
                    <textarea name="description" id="catDesc" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
    </div>
</div>
