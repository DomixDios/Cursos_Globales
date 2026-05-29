<?php $pageTitle = 'Panel del Docente'; ?>
<h2 class="fw-bold mb-4">Panel del Docente</h2>
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card text-center p-3"><h3 class="fw-bold text-primary" id="tStatCourses">0</h3><p class="text-muted small mb-0">Cursos</p></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h3 class="fw-bold text-success" id="tStatStudents">0</h3><p class="text-muted small mb-0">Estudiantes</p></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h3 class="fw-bold text-warning" id="tStatPending">0</h3><p class="text-muted small mb-0">Pendientes</p></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h3 class="fw-bold text-info" id="tStatEarnings">$0</h3><p class="text-muted small mb-0">Ganancias</p></div></div>
</div>
<?php $extraJs .= '<script>
$(function() {
    $.getJSON(BASE_URL + "/api/teacher.php?action=dashboard", function(r) {
        $("#tStatCourses").text(r.total_cursos);
        $("#tStatStudents").text(r.total_estudiantes);
        $("#tStatPending").text(r.cursos_pendientes);
        $("#tStatEarnings").text("$" + parseFloat(r.ganancias_totales).toFixed(2));
    });
});
</script>'; ?>
