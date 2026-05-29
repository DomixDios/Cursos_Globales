<?php
$pageTitle = 'Temario';
$courseId = $_GET['course_id'] ?? 0;
?>
<h2 class="fw-bold mb-4">Temario del Curso <span id="courseName" class="text-muted fs-5"></span></h2>
<button class="btn btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#moduleModal">
    <i class="bi bi-folder-plus"></i> A&ntilde;adir M&oacute;dulo
</button>
<div id="syllabusList"></div>
<div class="modal fade" id="moduleModal" tabindex="-1">...</div>
<div class="modal fade" id="classModal" tabindex="-1">...</div>