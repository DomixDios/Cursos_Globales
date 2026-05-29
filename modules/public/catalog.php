<?php
$pageTitle = 'Catálogo';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Catálogo de Cursos</h2>
    <div class="d-flex gap-2">
        <select id="filterCategory" class="form-select form-select-sm" style="width:auto;">
            <option value="">Todas las categorías</option>
        </select>
        <select id="filterLevel" class="form-select form-select-sm" style="width:auto;">
            <option value="">Todos los niveles</option>
            <option value="beginner">Principiante</option>
            <option value="intermediate">Intermedio</option>
            <option value="advanced">Avanzado</option>
        </select>
        <input type="search" id="searchInput" class="form-control form-control-sm" placeholder="Buscar..." style="width:200px;">
    </div>
</div>
<div id="courseGrid" class="row g-4"></div>
<div id="loadingSpinner" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
</div>
