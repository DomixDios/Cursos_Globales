<?php
$pageTitle = 'Reproductor';
$courseId = $_GET['course_id'] ?? 0;
$classId  = $_GET['class_id'] ?? 0;
?>
<div class="row">
    <div class="col-lg-8">
        <div class="ratio ratio-16x9 mb-3 bg-dark rounded">
            <div id="videoContainer" class="d-flex align-items-center justify-content-center text-white">
                <p class="mb-0">Cargando clase...</p>
            </div>
        </div>
        <h3 id="classTitle" class="fw-bold">Cargando...</h3>
        <div class="d-flex gap-2 mb-3">
            <button class="btn btn-success" id="btnComplete">
                <i class="bi bi-check-lg"></i> Marcar como completada
            </button>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-list me-2"></i>Clases</div>
            <div class="card-body p-0" id="classList"></div>
        </div>
        <div class="mt-3">
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Progreso</span>
                <span id="progressPercent">0%</span>
            </div>
            <div class="progress"><div class="progress-bar" id="progressBar" style="width:0%;"></div></div>
        </div>
    </div>
</div>
