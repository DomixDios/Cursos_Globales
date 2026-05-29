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
            <h5 class="fw-semibold mb-3" id="courseModalTitle">Nuevo Curso</h5>
            <form id="courseForm">
                <input type="hidden" name="id" value="0">
                <div class="mb-3"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small">Descripción corta</label><textarea name="short_description" class="form-control" rows="2"></textarea></div>
                <div class="mb-3"><label class="form-label small">Descripción completa</label><textarea name="description" class="form-control" rows="4"></textarea></div>
                <div class="row mb-3">
                    <div class="col"><label class="form-label small">Precio</label><input type="number" name="price" class="form-control" step="0.01" value="0"></div>
                    <div class="col"><label class="form-label small">Nivel</label><select name="level" class="form-select"><option value="principiante">Principiante</option><option value="intermedio">Intermedio</option><option value="avanzado">Avanzado</option></select></div>
                    <div class="col"><label class="form-label small">Categoría</label><select name="category_id" class="form-select" id="catSelect"></select></div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
    </div>
</div>
<?php $extraJs .= '<script>
function loadTeacherCourses() {
    $("#teacherCoursesContainer").html("<p class=\"text-muted\">Cargando cursos...</p>");
    $.getJSON(BASE_URL + "/api/course.php?action=list")
        .done(function(courses) {
            var h = "";
            if (!courses.length) {
                h = "<div class=\"col-12\"><div class=\"card p-5 text-center\"><i class=\"bi bi-journal-plus display-4 text-muted mb-3\"></i><h5>Aún no tienes cursos</h5><p class=\"text-muted\">Crea tu primer curso para comenzar.</p><button class=\"btn btn-primary\" data-bs-toggle=\"modal\" data-bs-target=\"#courseModal\"><i class=\"bi bi-plus-lg\"></i> Crear Curso</button></div></div>";
                $("#teacherCoursesContainer").html(h);
                return;
            }
            $.each(courses, function(i, c) {
                var badge = "";
                if (c.estado === "borrador") badge = "<span class=\"badge bg-secondary\">Borrador</span>";
                else if (c.estado === "pendiente") badge = "<span class=\"badge bg-warning text-dark\">Pendiente</span>";
                else if (c.estado === "aprobado") badge = "<span class=\"badge bg-success\">Aprobado</span>";
                else if (c.estado === "rechazado") badge = "<span class=\"badge bg-danger\">Rechazado</span>";
                else if (c.estado === "publicado") badge = "<span class=\"badge bg-info text-dark\">Publicado</span>";
                var cat = c.categoria_nombre ? "<span class=\"badge bg-light text-dark me-1\">" + c.categoria_nombre + "</span>" : "";
                h += "<div class=\"col-md-4 fade-in\"><div class=\"card course-card h-100\"><div class=\"card-body d-flex flex-column\">";
                h += "<h5 class=\"card-title\">" + c.titulo + "</h5>";
                h += "<p class=\"small text-muted flex-grow-1\">" + (c.descripcion_corta || "Sin descripción") + "</p>";
                h += "<p class=\"mb-1\">" + cat + " <strong>$" + parseFloat(c.precio).toFixed(2) + "</strong></p>";
                h += "<p class=\"mb-2\">" + badge + " <span class=\"text-muted small\">" + (c.total_estudiantes || 0) + " estudiantes</span></p>";
                h += "<div class=\"d-flex gap-2 mt-auto pt-2 border-top\">";
                h += "<a href=\"" + BASE_URL + "/index.php?page=syllabus&course_id=" + c.id + "\" class=\"btn btn-sm btn-outline-primary flex-fill\"><i class=\"bi bi-list-task\"></i> Temario</a>";
                h += "<button class=\"btn btn-sm btn-outline-secondary edit-course\" data-id=\"" + c.id + "\" title=\"Editar\"><i class=\"bi bi-pencil\"></i></button>";
                h += "<button class=\"btn btn-sm btn-outline-danger delete-course\" data-id=\"" + c.id + "\" title=\"Eliminar\"><i class=\"bi bi-trash\"></i></button>";
                h += "</div></div></div></div>";
            });
            $("#teacherCoursesContainer").html(h);
        })
        .fail(function() {
            $("#teacherCoursesContainer").html("<div class=\"col-12\"><div class=\"alert alert-danger\">Error al cargar los cursos. Verifica tu conexión.</div></div>");
        });
}

$(function() {
    loadTeacherCourses();

    $.getJSON(BASE_URL + "/api/admin.php?action=category-list")
        .done(function(cats) {
            var opts = "<option value=\"\">Sin categoría</option>";
            $.each(cats, function(i, c) { opts += "<option value=\"" + c.id + "\">" + c.nombre + "</option>"; });
            $("#catSelect").html(opts);
        });

    $("#courseForm").on("submit", function(e) {
        e.preventDefault();
        var btn = $(this).find("button[type=submit]");
        var origText = btn.text();
        btn.prop("disabled", true).text("Guardando...");
        $.post(BASE_URL + "/api/course.php?action=save", $(this).serialize())
            .done(function(r) {
                if (r.success) {
                    $("#courseModal").modal("hide");
                    loadTeacherCourses();
                }
            })
            .fail(function() { alert("Error al guardar el curso."); })
            .always(function() { btn.prop("disabled", false).text(origText); });
    });

    $("#courseModal").on("hidden.bs.modal", function() {
        $("#courseForm")[0].reset();
        $("#courseForm input[name=id]").val(0);
        $("#courseModalTitle").text("Nuevo Curso");
    });

    $(document).on("click", ".edit-course", function() {
        var id = $(this).data("id");
        $.getJSON(BASE_URL + "/api/course.php?action=detail&id=" + id)
            .done(function(c) {
                if (!c) return;
                $("#courseForm input[name=id]").val(c.id);
                $("#courseForm input[name=title]").val(c.titulo);
                $("#courseForm textarea[name=short_description]").val(c.descripcion_corta || "");
                $("#courseForm textarea[name=description]").val(c.descripcion || "");
                $("#courseForm input[name=price]").val(c.precio);
                $("#courseForm select[name=level]").val(c.nivel);
                $("#courseForm select[name=category_id]").val(c.categoria_id || "");
                $("#courseModalTitle").text("Editar Curso");
                $("#courseModal").modal("show");
            })
            .fail(function() { alert("Error al cargar los datos del curso."); });
    });

    $(document).on("click", ".delete-course", function() {
        if (!confirm("¿Estás seguro de eliminar este curso? Esta acción no se puede deshacer.")) return;
        var btn = $(this);
        btn.prop("disabled", true);
        $.post(BASE_URL + "/api/course.php?action=delete", { id: $(this).data("id") })
            .done(function(r) { if (r.success) loadTeacherCourses(); })
            .fail(function() { alert("Error al eliminar el curso."); })
            .always(function() { btn.prop("disabled", false); });
    });
});
</script>'; ?>
