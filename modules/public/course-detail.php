<?php
$pageTitle = 'Detalle del Curso';
$courseId = $_GET['id'] ?? 0;
?>
<div id="courseDetailContainer" class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h2 id="courseTitle" class="fw-bold">Cargando...</h2>
                <p id="courseDescription" class="text-muted"></p>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><i class="bi bi-list-check me-2"></i>Temario</div>
            <div class="card-body" id="syllabusContainer"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:1rem;">
            <div class="card-body text-center">
                <h3 id="coursePrice" class="fw-bold text-primary"></h3>
                <button class="btn btn-primary w-100 btn-lg mb-2">Inscribirme</button>
            </div>
        </div>
    </div>
</div>
