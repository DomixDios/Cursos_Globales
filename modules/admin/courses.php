<?php
$pageTitle = 'Gestion de Cursos';
$isAdmin = currentUserRole() === 'admin';
$extraJs = '<script>var IS_ADMIN = ' . ($isAdmin ? 'true' : 'false') . ';</script><script src="' . BASE_URL . '/assets/js/admin.js"></script>';
?>
<h2 class="fw-bold mb-4">Gestion de Cursos</h2>

<div class="row mb-3 g-2">
    <div class="col-md-3">
        <select class="form-select" id="filterStatus">
            <option value="">Todos los estados</option>
            <option value="borrador">Borrador</option>
            <option value="pendiente">Pendiente</option>
            <option value="aprobado">Aprobado</option>
            <option value="rechazado">Rechazado</option>
            <option value="publicado">Publicado</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="filterTeacher">
            <option value="">Todos los docentes</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="filterCategory">
            <option value="">Todas las categorias</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" class="form-control" id="filterSearch" placeholder="Buscar curso...">
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titulo</th>
                        <th>Docente</th>
                        <th>Categoria</th>
                        <th>Precio</th>
                        <th>Nivel</th>
                        <th>Estado</th>
                        <th>Estud.</th>
                        <?php if ($isAdmin): ?><th>Dest.</th><?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="adminCoursesTable"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-semibold mb-3" id="courseModalLabel">Editar Curso</h5>
            <form id="adminCourseForm">
                <input type="hidden" name="id" id="courseId" value="0">
                <div class="mb-3">
                    <label class="form-label small">Titulo</label>
                    <input type="text" name="title" id="courseTitle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Descripcion corta</label>
                    <textarea name="short_description" id="courseShortDesc" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Descripcion completa</label>
                    <textarea name="description" id="courseDesc" class="form-control" rows="4"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label small">Precio</label>
                        <input type="number" name="price" id="coursePrice" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="col">
                        <label class="form-label small">Nivel</label>
                        <select name="level" id="courseLevel" class="form-select">
                            <option value="principiante">Principiante</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label small">Categoria</label>
                        <select name="category_id" id="courseCategory" class="form-select"></select>
                    </div>
                    <div class="col">
                        <label class="form-label small">Docente</label>
                        <select name="teacher_id" id="courseTeacher" class="form-select"></select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label small">Estado</label>
                        <select name="status" id="courseStatus" class="form-select">
                            <option value="borrador">Borrador</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                            <option value="publicado">Publicado</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label small">Destacado</label>
                        <select name="featured" id="courseFeatured" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Si</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 d-none" id="rejectionReasonGroup">
                    <label class="form-label small">Motivo de rechazo</label>
                    <textarea name="rejection_reason" id="courseRejectionReason" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="syllabusModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Temario: <span id="syllabusCourseTitle" class="text-muted fs-6"></span></h5>
                <button class="btn btn-sm btn-outline-primary" id="addModuleBtn"><i class="bi bi-folder-plus"></i> Modulo</button>
            </div>
            <div id="syllabusContainer"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="moduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-semibold mb-3" id="moduleModalLabel">Nuevo Modulo</h5>
            <form id="moduleForm">
                <input type="hidden" name="id" id="moduleId" value="0">
                <input type="hidden" name="course_id" id="moduleCourseId" value="0">
                <div class="mb-3">
                    <label class="form-label small">Titulo</label>
                    <input type="text" name="title" id="moduleTitle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Descripcion</label>
                    <textarea name="description" id="moduleDesc" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar Modulo</button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="classModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-semibold mb-3" id="classModalLabel">Nueva Clase</h5>
            <form id="classForm">
                <input type="hidden" name="id" id="classId" value="0">
                <input type="hidden" name="module_id" id="classModuleId" value="0">
                <div class="mb-3">
                    <label class="form-label small">Titulo</label>
                    <input type="text" name="title" id="classTitle" class="form-control" required>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label small">Tipo de contenido</label>
                        <select name="content_type" id="classContentType" class="form-select">
                            <option value="video">Video</option>
                            <option value="articulo">Articulo</option>
                            <option value="cuestionario">Cuestionario</option>
                            <option value="recurso">Recurso</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label small">Duracion (min)</label>
                        <input type="number" name="duration" id="classDuration" class="form-control" value="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">URL del Video</label>
                    <input type="text" name="url_video" id="classUrl" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Descripcion / Contenido</label>
                    <textarea name="description" id="classDesc" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="free" id="classFree" class="form-check-input" value="1">
                    <label class="form-check-label small" for="classFree">Clase gratuita</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar Clase</button>
            </form>
        </div>
    </div>
</div>
