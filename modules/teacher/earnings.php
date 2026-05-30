<?php $pageTitle = 'Ganancias'; ?>
<h2 class="fw-bold mb-4">Ganancias</h2>
<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card text-center p-3"><h3 class="fw-bold text-success" id="eTotal">$0</h3><p class="text-muted small mb-0">Total ganado</p></div></div>
    <div class="col-md-4"><div class="card text-center p-3"><h3 class="fw-bold text-primary" id="ePending">$0</h3><p class="text-muted small mb-0">Pendiente</p></div></div>
    <div class="col-md-4"><div class="card text-center p-3"><h3 class="fw-bold text-info" id="eStudents">0</h3><p class="text-muted small mb-0">Estudiantes</p></div></div>
</div>
<div class="card"><div class="card-header">Historial de pagos</div><div class="card-body p-0"><table class="table mb-0"><thead><tr><th>Curso</th><th>Estudiante</th><th>Monto</th><th>Comisión</th><th>Neto</th><th>Fecha</th></tr></thead><tbody id="earningsTable"></tbody></table></div></div>
<?php $extraJs .= '<script>
$(function() {
    $.getJSON(BASE_URL + "/api/earnings.php", function(r) {
        $("#eTotal").text("$" + parseFloat(r.total).toFixed(2));
        $("#ePending").text("$" + parseFloat(r.pending).toFixed(2));
        $("#eStudents").text(r.students);
        var h = "";
        $.each(r.payments, function(i, p) {
            h += "<tr>";
            h += "<td>" + p.curso_nombre + "</td>";
            h += "<td>" + p.estudiante_nombre + "</td>";
            h += "<td>$" + parseFloat(p.monto).toFixed(2) + "</td>";
            h += "<td>$" + parseFloat(p.comision).toFixed(2) + "</td>";
            h += "<td><strong>$" + parseFloat(p.ganancias_profesor).toFixed(2) + "</strong></td>";
            h += "<td class=\"small text-muted\">" + p.creado_en + "</td>";
            h += "</tr>";
        });
        $("#earningsTable").html(h || "<tr><td colspan=\"6\" class=\"text-center text-muted\">Sin pagos registrados</td></tr>");
    });
});
</script>'; ?>
>>>>>>> 2bc397a (feat: modulo docente finalizado (Syllabus, Earnings, Profile y parches API))
