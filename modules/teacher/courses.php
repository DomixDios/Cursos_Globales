<?php $pageTitle = 'Mis Cursos'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0 fw-bold">Mis Cursos</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
        <i class="bi bi-plus-lg"></i> Nuevo Curso
    </button>
</div>
<div id="teacherCoursesContainer" class="row g-4"></div>
<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4" id="courseModalContent">
            <h5 class="fw-semibold mb-3">Nuevo Curso</h5>
            <form id="courseForm">
                <input type="hidden" name="id" value="0">
                <div class="mb-3"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small">Descripción corta</label><textarea name="short_description" class="form-control" rows="2"></textarea></div>
                <div class="mb-3"><label class="form-label small">Descripción completa</label><textarea name="description" class="form-control" rows="4"></textarea></div>
                <div class="row mb-3">
                    <div class="col"><label class="form-label small">Precio</label><input type="number" name="price" class="form-control" step="0.01" value="0"></div>
                    <div class="col"><label class="form-label small">Nivel</label><select name="level" class="form-select"><option value="beginner">Principiante</option><option value="intermediate">Intermedio</option><option value="advanced">Avanzado</option></select></div>
                    <div class="col"><label class="form-label small">Categoría</label><select name="category_id" class="form-select" id="catSelect"></select></div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
    </div>
</div>
