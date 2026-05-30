$(function () {

    var API = BASE_URL + '/api/admin.php';

    // Global AJAX error handler
    function showError(msg) {
        console.error('Admin API:', msg);
        var el = $('#adminError');
        if (!el.length) {
            el = $('<div id="adminError" class="alert alert-danger alert-dismissible fade show small py-2" role="alert" style="position:fixed;top:80px;right:20px;z-index:9999;max-width:400px;"></div>').appendTo('body');
            el.append('<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
        }
        el.html('<i class="bi bi-exclamation-triangle me-1"></i>' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
        el.removeClass('d-none');
        setTimeout(function () { el.addClass('d-none'); }, 5000);
    }

    // =====================================================
    // DASHBOARD
    // =====================================================
    function loadDashboard() {
        $.getJSON(API, { action: 'dashboard' })
            .done(function (d) {
                $('#aUsers').text(d.users || 0);
                $('#aCourses').text(d.courses || 0);
                $('#aPending').text(d.pending || 0);
                $('#aRevenue').text('$' + parseFloat(d.revenue || 0).toFixed(2));
            })
            .fail(function () { showError('Error al cargar estadisticas'); });
    }
    if ($('#aUsers').length) loadDashboard();

    // =====================================================
    // USUARIOS
    // =====================================================
    function loadUsers() {
        $('#usersTable').html('<tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>');
        $.getJSON(API, { action: 'users' })
            .done(function (users) {
                var html = '';
                $.each(users, function (i, u) {
                    html += '<tr>' +
                        '<td>' + u.id + '</td>' +
                        '<td>' + u.nombre_completo + '</td>' +
                        '<td>' + u.email + '</td>' +
                        '<td><span class="badge bg-' + (u.rol === 'admin' ? 'danger' : u.rol === 'moderador' ? 'warning text-dark' : u.rol === 'profesor' ? 'info text-dark' : 'secondary') + '">' + u.rol + '</span></td>' +
                        '<td>' + (u.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>') + '</td>' +
                        '<td>' +
                        '  <button class="btn btn-sm btn-outline-primary edit-user" data-id="' + u.id + '" data-name="' + u.nombre_completo + '" data-email="' + u.email + '" data-role="' + u.rol + '"><i class="bi bi-pencil"></i></button>' +
                        '  <button class="btn btn-sm btn-outline-' + (u.activo == 1 ? 'secondary' : 'success') + ' toggle-user" data-id="' + u.id + '" data-active="' + u.activo + '"><i class="bi bi-' + (u.activo == 1 ? 'pause' : 'play') + '"></i></button>' +
                        '</td>' +
                        '</tr>';
                });
                $('#usersTable').html(html);
            })
            .fail(function () { showError('Error al cargar usuarios'); });
    }
    if ($('#usersTable').length) loadUsers();

    $(document).on('click', '.edit-user', function () {
        var btn = $(this);
        $('#userModalLabel').text('Editar Usuario');
        $('#userId').val(btn.data('id'));
        $('#userName').val(btn.data('name'));
        $('#userEmail').val(btn.data('email'));
        $('#userRole').val(btn.data('role'));
        $('#userPassword').prop('required', false).val('');
        $('#userModal').modal('show');
    });

    $(document).on('click', '#newUserBtn', function () {
        $('#userModalLabel').text('Nuevo Usuario');
        $('#userForm')[0].reset();
        $('#userId').val(0);
        $('#userPassword').prop('required', true);
        $('#userModal').modal('show');
    });

    $('#userForm').on('submit', function (e) {
        e.preventDefault();
        $.post(API + '?action=user-save', $(this).serialize())
            .done(function (r) {
                if (r.success) { $('#userModal').modal('hide'); loadUsers(); }
            })
            .fail(function () { showError('Error al guardar usuario'); });
    });

    $(document).on('click', '.toggle-user', function () {
        if (!confirm('Cambiar estado de este usuario?')) return;
        $.post(API + '?action=user-toggle', { id: $(this).data('id') })
            .done(function (r) {
                if (r.success) { loadUsers(); }
                else { showError(r.error || 'Error al cambiar estado'); }
            })
            .fail(function () { showError('Error al cambiar estado'); });
    });

    // =====================================================
    // CATEGORIAS
    // =====================================================
    function loadCategories() {
        $('#categoriesTable').html('<tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>');
        $.getJSON(API, { action: 'category-list' })
            .done(function (cats) {
                var html = '';
                $.each(cats, function (i, c) {
                    html += '<tr>' +
                        '<td>' + c.id + '</td>' +
                        '<td>' + c.nombre + '</td>' +
                        '<td>' + c.slug + '</td>' +
                        '<td><span class="badge bg-success">Si</span></td>' +
                        '<td>' + (c.total_cursos || 0) + '</td>' +
                        '<td>' +
                        '  <button class="btn btn-sm btn-outline-primary edit-cat" data-id="' + c.id + '" data-name="' + c.nombre + '" data-slug="' + c.slug + '" data-desc="' + (c.descripcion || '') + '"><i class="bi bi-pencil"></i></button>' +
                        '  <button class="btn btn-sm btn-outline-danger delete-cat" data-id="' + c.id + '" data-name="' + c.nombre + '"><i class="bi bi-trash"></i></button>' +
                        '</td>' +
                        '</tr>';
                });
                $('#categoriesTable').html(html);
                var opts = '<option value="">Seleccionar...</option>';
                $.each(cats, function (i, c) {
                    if (c.activo) opts += '<option value="' + c.id + '">' + c.nombre + '</option>';
                });
                $('#catSelect, .cat-select').each(function () {
                    var val = $(this).val();
                    $(this).html(opts).val(val);
                });
            })
            .fail(function () { showError('Error al cargar categorias'); });
    }
    if ($('#categoriesTable').length) loadCategories();

    $(document).on('click', '.edit-cat', function () {
        var btn = $(this);
        $('#categoryModalLabel').text('Editar Categoria');
        $('#catId').val(btn.data('id'));
        $('#catName').val(btn.data('name'));
        $('#catSlug').val(btn.data('slug'));
        $('#catDesc').val(btn.data('desc'));
        $('#categoryModal').modal('show');
    });

    $(document).on('click', '#newCatBtn', function () {
        $('#categoryModalLabel').text('Nueva Categoria');
        $('#catForm')[0].reset();
        $('#catId').val(0);
        $('#categoryModal').modal('show');
    });

    $('#catForm').on('submit', function (e) {
        e.preventDefault();
        $.post(API + '?action=category-save', $(this).serialize())
            .done(function (r) {
                if (r.success) { $('#categoryModal').modal('hide'); loadCategories(); }
            })
            .fail(function () { showError('Error al guardar categoria'); });
    });

    $(document).on('click', '.delete-cat', function () {
        if (!confirm('Eliminar la categoria "' + $(this).data('name') + '"?\nSe ocultara de la plataforma.')) return;
        $.post(API + '?action=category-delete', { id: $(this).data('id') })
            .done(function () { loadCategories(); })
            .fail(function () { showError('Error al eliminar categoria'); });
    });

    // =====================================================
    // CURSOS PENDIENTES
    // =====================================================
    function loadPending() {
        $('#pendingCoursesTable').html('<tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>');
        $.getJSON(API, { action: 'pending-courses' })
            .done(function (courses) {
                var html = '';
                $.each(courses, function (i, c) {
                    html += '<tr>' +
                        '<td><a href="?page=course-detail&id=' + c.id + '" class="fw-semibold text-decoration-none">' + c.titulo + '</a></td>' +
                        '<td>' + c.profesor_nombre + '</td>' +
                        '<td>' + c.creado_en + '</td>' +
                        '<td>' +
                        '  <button class="btn btn-sm btn-success approve-course" data-id="' + c.id + '"><i class="bi bi-check-lg"></i></button>' +
                        '  <button class="btn btn-sm btn-danger reject-course" data-id="' + c.id + '"><i class="bi bi-x-lg"></i></button>' +
                        '</td>' +
                        '</tr>';
                });
                $('#pendingCoursesTable').html(html);
                // Reload dashboard stats if present
                if ($('#aPending').length) loadDashboard();
            })
            .fail(function () { showError('Error al cargar cursos pendientes'); });
    }
    if ($('#pendingCoursesTable').length) loadPending();

    $(document).on('click', '.approve-course', function () {
        if (!confirm('Aprobar este curso?')) return;
        $.post(API + '?action=approve', { id: $(this).data('id') })
            .done(function () { loadPending(); })
            .fail(function () { showError('Error al aprobar curso'); });
    });

    $(document).on('click', '.reject-course', function () {
        var id = $(this).data('id');
        var reason = prompt('Motivo del rechazo (opcional):');
        $.post(API + '?action=reject', { id: id, reason: reason || '' })
            .done(function () { loadPending(); })
            .fail(function () { showError('Error al rechazar curso'); });
    });

    // =====================================================
    // ESTADISTICAS
    // =====================================================
    function loadStats() {
        if (!$('#usersChart').length) return;

        $.getJSON(API, { action: 'stats-users' })
            .done(function (data) {
                if (typeof Chart === 'undefined') {
                    $('#usersChart').parent().html('<p class="text-muted text-center py-4">Instala Chart.js para ver las graficas</p>');
                    return;
                }
                new Chart(document.getElementById('usersChart'), {
                    type: 'bar',
                    data: {
                        labels: data.map(function (d) { return d.mes; }),
                        datasets: [{ label: 'Usuarios', data: data.map(function (d) { return d.total; }), backgroundColor: '#0d6efd' }]
                    }
                });
            })
            .fail(function () { showError('Error al cargar estadisticas'); });

        $.getJSON(API, { action: 'stats-revenue' })
            .done(function (data) {
                if (typeof Chart === 'undefined') return;
                new Chart(document.getElementById('revenueChart'), {
                    type: 'line',
                    data: {
                        labels: data.map(function (d) { return d.mes; }),
                        datasets: [{ label: 'Ingresos', data: data.map(function (d) { return d.total; }), borderColor: '#198754', fill: false }]
                    }
                });
            })
            .fail(function () { showError('Error al cargar ingresos'); });
    }
    if ($('#usersChart').length) {
        if (typeof Chart === 'undefined') {
            $.getScript('https://cdn.jsdelivr.net/npm/chart.js', loadStats);
        } else {
            loadStats();
        }
    }

    // =====================================================
    // CURSOS (Admin)
    // =====================================================
    var allCourses = [];
    var allTeachers = [];
    var allCategories = [];

    function renderCourseTable(courses) {
        var html = '';
        $.each(courses, function (i, c) {
            var statusBadge = {
                'borrador': 'secondary',
                'pendiente': 'warning text-dark',
                'aprobado': 'info text-dark',
                'rechazado': 'danger',
                'publicado': 'success'
            }[c.estado] || 'secondary';

            html += '<tr>' +
                '<td>' + c.id + '</td>' +
                '<td><a href="?page=course-detail&id=' + c.id + '" class="fw-semibold text-decoration-none">' + c.titulo + '</a></td>' +
                '<td>' + c.profesor_nombre + '</td>' +
                '<td>' + (c.categoria_nombre || '-') + '</td>' +
                '<td>$' + parseFloat(c.precio || 0).toFixed(2) + '</td>' +
                '<td><span class="badge bg-light text-dark">' + c.nivel + '</span></td>' +
                '<td><span class="badge bg-' + statusBadge + '">' + c.estado + '</span></td>' +
                '<td>' + (c.total_estudiantes || 0) + '</td>';

            if (IS_ADMIN) {
                html += '<td>' + (c.destacado == 1 ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-muted"></i>') + '</td>';
            }

            html += '<td>' +
                '  <button class="btn btn-sm btn-outline-info syllabus-course-btn" data-id="' + c.id + '" title="Temario"><i class="bi bi-list-check"></i></button>' +
                (IS_ADMIN ? '  <button class="btn btn-sm btn-outline-primary edit-course-btn" data-id="' + c.id + '" title="Editar"><i class="bi bi-pencil"></i></button>' : '') +
                (IS_ADMIN ? '  <button class="btn btn-sm btn-outline-' + (c.destacado == 1 ? 'warning' : 'secondary') + ' feature-course-btn" data-id="' + c.id + '" data-featured="' + c.destacado + '" title="' + (c.destacado == 1 ? 'Quitar destacado' : 'Destacar') + '"><i class="bi bi-' + (c.destacado == 1 ? 'star-fill' : 'star') + '"></i></button>' : '') +
                (IS_ADMIN ? '  <button class="btn btn-sm btn-outline-danger delete-course-btn" data-id="' + c.id + '" data-title="' + c.titulo + '" title="Eliminar"><i class="bi bi-trash"></i></button>' : '') +
                '</td>' +
                '</tr>';
        });
        $('#adminCoursesTable').html(html || '<tr><td colspan="' + (IS_ADMIN ? 11 : 9) + '" class="text-center text-muted py-4">No se encontraron cursos</td></tr>');
    }

    function loadAdminCourses() {
        var params = { action: 'course-list-all' };
        var status = $('#filterStatus').val();
        var teacherId = $('#filterTeacher').val();
        var categoryId = $('#filterCategory').val();
        var search = $('#filterSearch').val();

        if (status) params.status = status;
        if (teacherId) params.teacher_id = teacherId;
        if (categoryId) params.category_id = categoryId;
        if (search) params.search = search;

        $('#adminCoursesTable').html('<tr><td colspan="' + (IS_ADMIN ? 11 : 9) + '" class="text-center text-muted">Cargando...</td></tr>');
        $.getJSON(API, params)
            .done(function (courses) {
                allCourses = courses;
                renderCourseTable(courses);
            })
            .fail(function () { showError('Error al cargar cursos'); });
    }

    function loadCourseFilters() {
        $.getJSON(API, { action: 'teachers' })
            .done(function (teachers) {
                allTeachers = teachers;
                var opts = '<option value="">Todos los docentes</option>';
                $.each(teachers, function (i, t) {
                    opts += '<option value="' + t.id + '">' + t.nombre_completo + '</option>';
                });
                $('#filterTeacher').html(opts);

                var sopts = '<option value="">Seleccionar docente...</option>';
                $.each(teachers, function (i, t) {
                    sopts += '<option value="' + t.id + '">' + t.nombre_completo + '</option>';
                });
                $('#courseTeacher').html(sopts);
            });

        $.getJSON(API, { action: 'category-list' })
            .done(function (cats) {
                allCategories = cats;
                var opts = '<option value="">Todas las categorias</option>';
                $.each(cats, function (i, c) {
                    opts += '<option value="' + c.id + '">' + c.nombre + '</option>';
                });
                $('#filterCategory').html(opts);

                var copts = '<option value="">Seleccionar categoria...</option>';
                $.each(cats, function (i, c) {
                    copts += '<option value="' + c.id + '">' + c.nombre + '</option>';
                });
                $('#courseCategory').html(copts);
            });
    }

    if ($('#adminCoursesTable').length) {
        loadCourseFilters();
        loadAdminCourses();

        $('#filterStatus, #filterTeacher, #filterCategory').on('change', loadAdminCourses);
        $('#filterSearch').on('keyup', function () {
            clearTimeout(window._searchTimer);
            window._searchTimer = setTimeout(loadAdminCourses, 300);
        });
    }

    $(document).on('click', '.edit-course-btn', function () {
        var id = $(this).data('id');
        var course = allCourses.find(function (c) { return c.id == id; });
        if (!course) return;

        $('#courseModalLabel').text('Editar Curso');
        $('#courseId').val(course.id);
        $('#courseTitle').val(course.titulo);
        $('#courseShortDesc').val(course.descripcion_corta || '');
        $('#courseDesc').val(course.descripcion || '');
        $('#coursePrice').val(course.precio);
        $('#courseLevel').val(course.nivel);
        $('#courseCategory').val(course.categoria_id || '');
        $('#courseTeacher').val(course.profesor_id);
        $('#courseStatus').val(course.estado);
        $('#courseFeatured').val(course.destacado || 0);
        $('#courseRejectionReason').val(course.motivo_rechazo || '');
        $('#rejectionReasonGroup').toggleClass('d-none', course.estado !== 'rechazado');
        $('#courseModal').modal('show');
    });

    $('#courseStatus').on('change', function () {
        $('#rejectionReasonGroup').toggleClass('d-none', $(this).val() !== 'rechazado');
    });

    $('#adminCourseForm').on('submit', function (e) {
        e.preventDefault();
        $.post(API + '?action=course-save', $(this).serialize())
            .done(function (r) {
                if (r.success) { $('#courseModal').modal('hide'); loadAdminCourses(); }
            })
            .fail(function () { showError('Error al guardar curso'); });
    });

    $(document).on('click', '.delete-course-btn', function () {
        var btn = $(this);
        if (!confirm('Eliminar el curso "' + btn.data('title') + '"?\nSe eliminaran modulos, clases, inscripciones y progreso.')) return;
        $.post(API + '?action=course-delete', { id: btn.data('id') })
            .done(function () { loadAdminCourses(); })
            .fail(function () { showError('Error al eliminar curso'); });
    });

    $(document).on('click', '.feature-course-btn', function () {
        $.post(API + '?action=course-feature', { id: $(this).data('id') })
            .done(function () { loadAdminCourses(); })
            .fail(function () { showError('Error al cambiar destacado'); });
    });

    // =====================================================
    // SYLLABUS (admin)
    // =====================================================
    var currentSyllabusCourseId = 0;

    $(document).on('click', '.syllabus-course-btn', function () {
        currentSyllabusCourseId = $(this).data('id');
        var course = allCourses.find(function (c) { return c.id == currentSyllabusCourseId; });
        $('#syllabusCourseTitle').text(course ? course.titulo : '');
        $('#syllabusContainer').html('<p class="text-muted text-center py-4">Cargando...</p>');
        $('#syllabusModal').modal('show');

        $.getJSON(API, { action: 'course-syllabus', id: currentSyllabusCourseId })
            .done(function (data) {
                renderSyllabus(data.modules);
            })
            .fail(function () { showError('Error al cargar temario'); });
    });

    function renderSyllabus(modules) {
        if (!modules || !modules.length) {
            $('#syllabusContainer').html('<p class="text-muted text-center py-4">Sin modulos todavia. Haga clic en "Modulo" para agregar uno.</p>');
            return;
        }
        var html = '<div class="accordion" id="syllabusAccordion">';
        $.each(modules, function (i, mod) {
            var id = 'mod-' + mod.id;
            html += '<div class="accordion-item">' +
                '<h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#' + id + '">' +
                '<span class="fw-semibold small">' + mod.titulo + '</span>' +
                '</button></h2>' +
                '<div id="' + id + '" class="accordion-collapse collapse" data-bs-parent="#syllabusAccordion">' +
                '<div class="accordion-body p-3">';

            if (mod.descripcion) {
                html += '<p class="small text-muted mb-2">' + mod.descripcion + '</p>';
            }

            if (mod.clases && mod.clases.length) {
                html += '<ul class="list-group list-group-flush mb-3">';
                $.each(mod.clases, function (j, cl) {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center py-2 small">' +
                        '<span><i class="bi bi-' + (cl.tipo_contenido === 'video' ? 'play-circle' : cl.tipo_contenido === 'articulo' ? 'file-text' : cl.tipo_contenido === 'cuestionario' ? 'question-circle' : 'link-45deg') + ' me-2"></i>' +
                        cl.titulo + '</span>' +
                        '<span>' +
                        (cl.gratuito == 1 ? '<span class="badge bg-success me-1">Gratis</span>' : '') +
                        '<button class="btn btn-sm btn-outline-primary edit-class-btn me-1" data-id="' + cl.id + '" data-module-id="' + mod.id + '" data-title="' + cl.titulo.replace(/'/g, "\\'") + '" data-type="' + cl.tipo_contenido + '" data-url="' + (cl.url_video || '') + '" data-desc="' + (cl.descripcion || '').replace(/'/g, "\\'") + '" data-duration="' + (cl.duracion || 0) + '" data-free="' + (cl.gratuito || 0) + '"><i class="bi bi-pencil"></i></button>' +
                        '<button class="btn btn-sm btn-outline-danger delete-class-btn" data-id="' + cl.id + '" data-title="' + cl.titulo.replace(/'/g, "\\'") + '"><i class="bi bi-trash"></i></button>' +
                        '</span></li>';
                });
                html += '</ul>';
            } else {
                html += '<p class="small text-muted mb-2">Sin clases</p>';
            }

            html += '<button class="btn btn-sm btn-outline-primary add-class-btn" data-module-id="' + mod.id + '"><i class="bi bi-plus-circle"></i> Clase</button>' +
                '<button class="btn btn-sm btn-outline-danger ms-2 delete-module-btn" data-id="' + mod.id + '" data-title="' + mod.titulo.replace(/'/g, "\\'") + '"><i class="bi bi-trash"></i> Modulo</button>' +
                '</div></div></div>';
        });
        html += '</div>';
        $('#syllabusContainer').html(html);
    }

    $('#addModuleBtn').on('click', function () {
        $('#moduleModalLabel').text('Nuevo Modulo');
        $('#moduleForm')[0].reset();
        $('#moduleId').val(0);
        $('#moduleCourseId').val(currentSyllabusCourseId);
        $('#moduleModal').modal('show');
    });

    $(document).on('click', '.delete-module-btn', function () {
        var btn = $(this);
        if (!confirm('Eliminar el modulo "' + btn.data('title') + '" y todas sus clases?')) return;
        $.post(API + '?action=module-delete', { id: btn.data('id') })
            .done(function () {
                $.getJSON(API, { action: 'course-syllabus', id: currentSyllabusCourseId })
                    .done(function (data) { renderSyllabus(data.modules); });
            })
            .fail(function () { showError('Error al eliminar modulo'); });
    });

    $('#moduleForm').on('submit', function (e) {
        e.preventDefault();
        $.post(API + '?action=module-save', $(this).serialize())
            .done(function (r) {
                if (r.success) {
                    $('#moduleModal').modal('hide');
                    $.getJSON(API, { action: 'course-syllabus', id: currentSyllabusCourseId })
                        .done(function (data) { renderSyllabus(data.modules); });
                }
            })
            .fail(function () { showError('Error al guardar modulo'); });
    });

    $(document).on('click', '.add-class-btn', function () {
        $('#classModalLabel').text('Nueva Clase');
        $('#classForm')[0].reset();
        $('#classId').val(0);
        $('#classModuleId').val($(this).data('module-id'));
        $('#classFree').prop('checked', false);
        $('#classModal').modal('show');
    });

    $(document).on('click', '.edit-class-btn', function () {
        var btn = $(this);
        $('#classModalLabel').text('Editar Clase');
        $('#classId').val(btn.data('id'));
        $('#classModuleId').val(btn.data('module-id'));
        $('#classTitle').val(btn.data('title'));
        $('#classContentType').val(btn.data('type'));
        $('#classUrl').val(btn.data('url'));
        $('#classDesc').val(btn.data('desc'));
        $('#classDuration').val(btn.data('duration'));
        $('#classFree').prop('checked', btn.data('free') == 1);
        $('#classModal').modal('show');
    });

    $(document).on('click', '.delete-class-btn', function () {
        if (!confirm('Eliminar la clase "' + $(this).data('title') + '"?')) return;
        $.post(API + '?action=class-delete', { id: $(this).data('id') })
            .done(function () {
                $.getJSON(API, { action: 'course-syllabus', id: currentSyllabusCourseId })
                    .done(function (data) { renderSyllabus(data.modules); });
            })
            .fail(function () { showError('Error al eliminar clase'); });
    });

    $('#classForm').on('submit', function (e) {
        e.preventDefault();
        $.post(API + '?action=class-save', $(this).serialize())
            .done(function (r) {
                if (r.success) {
                    $('#classModal').modal('hide');
                    $.getJSON(API, { action: 'course-syllabus', id: currentSyllabusCourseId })
                        .done(function (data) { renderSyllabus(data.modules); });
                }
            })
            .fail(function () { showError('Error al guardar clase'); });
    });

});
