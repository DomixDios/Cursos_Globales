<?php $pageTitle = 'Ganancias'; ?>
<h2 class="fw-bold mb-4">Ganancias</h2>
<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card text-center p-3"><h3 class="fw-bold text-success" id="eTotal">$0</h3><p class="text-muted small mb-0">Total ganado</p></div></div>
    <div class="col-md-4"><div class="card text-center p-3"><h3 class="fw-bold text-primary" id="ePending">$0</h3><p class="text-muted small mb-0">Pendiente</p></div></div>
    <div class="col-md-4"><div class="card text-center p-3"><h3 class="fw-bold text-info" id="eStudents">0</h3><p class="text-muted small mb-0">Estudiantes</p></div></div>
</div>
<div class="card"><div class="card-header">Historial de pagos</div><div class="card-body p-0"><table class="table mb-0"><thead><tr><th>Curso</th><th>Estudiante</th><th>Monto</th><th>Comisi&oacute;n</th><th>Neto</th><th>Fecha</th></tr></thead><tbody id="earningsTable"></tbody></table></div></div>