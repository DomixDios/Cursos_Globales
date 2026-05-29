<?php
$pageTitle = 'Temario';
$courseId = (int)($_GET['course_id'] ?? 0);
?>

<h2 class="fw-bold mb-4">Temario del Curso <span id="courseName" class="text-muted fs-5"></span></h2>
<button class="btn btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#moduleModal">
    <i class="bi bi-folder-plus"></i> Añadir Módulo
</button>

<div id="syllabusList"></div>

<div class="modal fade" id="moduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3" id="moduleModalTitle">Nuevo Módulo</h5>
            <form id="moduleForm">
                <input type="hidden" name="id" value="0">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <div class="mb-3"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small">Descripción</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="mb-3"><label class="form-label small">Orden</label><input type="number" name="sort_order" class="form-control" value="0"></div>
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="classModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="fw-bold mb-3" id="classModalTitle">Nueva Clase</h5>
            <form id="classForm">
                <input type="hidden" name="id" value="0">
                <input type="hidden" name="module_id" value="0">
                <div class="mb-3"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small">Descripción</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="row mb-3">
                    <div class="col"><label class="form-label small">URL del video</label><input type="text" name="video_url" class="form-control"></div>
                    <div class="col"><label class="form-label small">Duración (min)</label><input type="number" name="duration" class="form-control" value="0"></div>
                </div>
                <div class="row mb-3">
                    <div class="col"><label class="form-label small">Tipo</label><select name="content_type" class="form-select"><option value="video">Video</option><option value="articulo">Artículo</option><option value="cuestionario">Cuestionario</option><option value="recurso">Recurso</option></select></div>
                    <div class="col"><label class="form-label small">Orden</label><input type="number" name="sort_order" class="form-control" value="0"></div>
                    <div class="col d-flex align-items-end"><div class="form-check"><input type="checkbox" name="is_free" class="form-check-input" value="1" id="is_free"><label class="form-check-label small" for="is_free">Gratuito</label></div></div>
                </div>
                <div class="mb-3"><label class="form-label small">Contenido (texto)</label><textarea name="content_text" class="form-control" rows="4"></textarea></div>
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
    </div>
</div>

<?php $extraJs = '
<script>
var courseId = ' . $courseId . ';

fn loadSyllabus() {
    $.getJSON(BASE_URL + "/api/syllabus.php?action=module-list&course_id=" + courseId, function(modules) {
        var h = "";
        $.each(modules, function(i, m) {
            var bg = "bg-" + (i % 2 === 0 ? "light" : "white");
            h += "<div class=\"card mb-3 " + bg + "\"><div class=\"card-body\">";
            h += "<div class=\"d-flex justify-content-between align-items-center\">";
            h += "<h5 class=\"mb-0\"><i class=\"bi bi-folder me-2\"></i>" + m.titulo + "</h5>";
            h += "<div class=\"d-flex gap-2\">";
            h += "<button class=\"btn btn-sm btn-outline-primary add-class\" data-module-id=\"" + m.id + "\"><i class=\"bi bi-plus-circle\"></i> Clase</button>";
            h += "<button class=\"btn btn-sm btn-outline-secondary edit-module\" data-id=\"" + m.id + "\" data-title=\"" + m.titulo + "\" data-desc=\"" + (m.descripcion || "") + "\" data-order=\"" + m.orden + "\"><i class=\"bi bi-pencil\"></i></button>";
            h += "<button class=\"btn btn-sm btn-outline-danger delete-module\" data-id=\"" + m.id + "\"><i class=\"bi bi-trash\"></i></button>";
            h += "</div></div>";
            if (m.descripcion) h += "<p class=\"small text-muted mt-2 mb-2\">" + m.descripcion + "</p>";
            h += "<div class=\"ms-4 mt-2\">";
            $.each(m.clases, function(j, cl) {
                var icon = cl.tipo_contenido === "video" ? "bi-play-circle" : cl.tipo_contenido === "articulo" ? "bi-file-text" : cl.tipo_contenido === "cuestionario" ? "bi-question-circle" : "bi-paperclip";
                var freeBadge = cl.gratuito == 1 ? " <span class=\"badge bg-success-subtle text-success\">Gratis</span>" : "";
                h += "<div class=\"d-flex justify-content-between align-items-center py-2 border-bottom\">";
                h += "<span><i class=\"bi " + icon + " me-2\"></i>" + cl.titulo + freeBadge + " <span class=\"text-dark small\">(" + cl.duracion + " min)</span></span>";
                h += "<div class=\"d-flex gap-2\">";
                h += "<button class=\"btn btn-sm btn-outline-secondary edit-class\" data-id=\"" + cl.id + "\"><i class=\"bi bi-pencil\"></i></button>";
                h += "<button class=\"btn btn-sm btn-outline-danger delete-class\" data-id=\"" + cl.id + "\"><i class=\"bi bi-trash\"></i></button>";
                h += "</div></div>";
            });
            h += "</div></div></div>";
        });
        $("#syllabusList").html(h || "<p class=\"text-muted\">Este curso no tiene módulos aún.</p>");
    });
}

$(function() {
    if (courseId) {
        $.getJSON(BASE_URL + "/api/course.php?action=detail&id=" + courseId, function(c) {
            if (c) $("#courseName").text("- " + c.titulo);
        });
        loadSyllabus();
    }

    $("#moduleForm").on("submit", function(e) {
        e.preventDefault();
        var btn = $(this).find("button[type=submit]");
        btn.prop("disabled", true).text("Guardando...");
        $.post(BASE_URL + "/api/syllabus.php?action=module-save", $(this).serialize()).done(function(r) {
            if (r.success) { $("#moduleModal").modal("hide"); loadSyllabus(); }
        }).always(function() { btn.prop("disabled", false).text("Guardar"); });
    });

    $("#moduleModal").on("hidden.bs.modal", function() { $("#moduleForm")[0].reset(); $("#moduleForm input[name=id]").val(0); });

    $(document).on("click", ".edit-module", function() {
        var d = $(this).data();
        $("#moduleForm input[name=id]").val(d.id);
        $("#moduleForm input[name=title]").val(d.title);
        $("#moduleForm textarea[name=description]").val(d.desc);
        $("#moduleForm input[name=sort_order]").val(d.order);
        $("#moduleModalTitle").text("Editar Módulo");
        $("#moduleModal").modal("show");
    });

    $(document).on("click", ".delete-module", function() {
        if (!confirm("¿Eliminar este módulo y todas sus clases?")) return;
        $.post(BASE_URL + "/api/syllabus.php?action=module-delete", { id: $(this).data("id") }).done(function(r) {
            if (r.success) loadSyllabus();
        });
    });

    $(document).on("click", ".add-class", function() {
        $("#classForm input[name=module_id]").val($(this).data("module-id"));
        $("#classForm")[0].reset();
        $("#classForm input[name=id]").val(0);
        $("#classModalTitle").text("Nueva Clase");
        $("#classModal").modal("show");
    });

    $(document).on("click", ".edit-class", function() {
        var cl = $(this).data("cl");
        $("#classForm input[name=id]").val(cl.id);
        $("#classForm input[name=module_id]").val(cl.modulo_id);
        $("#classForm input[name=title]").val(cl.titulo);
        $("#classForm textarea[name=description]").val(cl.descripcion);
        $("#classForm input[name=video_url]").val(cl.url_video);
        $("#classForm input[name=duration]").val(cl.duracion);
        $("#classForm select[name=content_type]").val(cl.tipo_contenido);
        $("#classForm input[name=sort_order]").val(cl.orden);
        $("#classForm input[name=is_free]").prop("checked", cl.gratuito == 1);
        $("#classForm textarea[name=content_text]").val(cl.texto_contenido);
        $("#classModalTitle").text("Editar Clase");
        $("#classModal").modal("show");
    });

    $("#classForm").on("submit", function(e) {
        e.preventDefault();
        var btn = $(this).find("button[type=submit]");
        btn.prop("disabled", true).text("Guardando...");
        var data = $(this).serialize();
        if ($("#classForm input[name=is_free]").is(":checked")) {
            data += "&is_free=1";
        }
        $.post(BASE_URL + "/api/syllabus.php?action=class-save", data).done(function(r) {
            if (r.success) { $("#classModal").modal("hide"); loadSyllabus(); }
        }).always(function() { btn.prop("disabled", false).text("Guardar"); });
    });

    $("#classModal").on("hidden.bs.modal", function() { $("#classForm")[0].reset(); });

    $(document).on("click", ".delete-class", function() {
        if (!confirm("¿Eliminar esta clase?")) return;
        $.post(BASE_URL + "/api/syllabus.php?action=class-delete", { id: $(this).data("id") }).done(function(r) {
            if (r.success) loadSyllabus();
        });
    });
});
</script>'; ?>
>>>>>>> 2bc397a (feat: modulo docente finalizado (Syllabus, Earnings, Profile y parches API))
