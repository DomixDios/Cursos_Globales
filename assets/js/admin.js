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
                        '<td>' + u.full_name + '</td>' +
                        '<td>' + u.email + '</td>' +
                        '<td><span class="badge bg-' + (u.role === 'admin' ? 'danger' : u.role === 'moderator' ? 'warning text-dark' : u.role === 'teacher' ? 'info text-dark' : 'secondary') + '">' + u.role + '</span></td>' +
                        '<td>' + (u.is_active == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>') + '</td>' +
                        '<td>' +
                        '  <button class="btn btn-sm btn-outline-primary edit-user" data-id="' + u.id + '" data-name="' + u.full_name + '" data-email="' + u.email + '" data-role="' + u.role + '"><i class="bi bi-pencil"></i></button>' +
                        '  <button class="btn btn-sm btn-outline-' + (u.is_active == 1 ? 'secondary' : 'success') + ' toggle-user" data-id="' + u.id + '" data-active="' + u.is_active + '"><i class="bi bi-' + (u.is_active == 1 ? 'pause' : 'play') + '"></i></button>' +
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
            .done(function () { loadUsers(); })
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
                        '<td>' + c.name + '</td>' +
                        '<td>' + c.slug + '</td>' +
                        '<td>' + (c.is_active == 1 ? '<span class="badge bg-success">Si</span>' : '<span class="badge bg-secondary">No</span>') + '</td>' +
                        '<td>' + (c.course_count || 0) + '</td>' +
                        '<td>' +
                        '  <button class="btn btn-sm btn-outline-primary edit-cat" data-id="' + c.id + '" data-name="' + c.name + '" data-slug="' + c.slug + '" data-desc="' + (c.description || '') + '"><i class="bi bi-pencil"></i></button>' +
                        '  <button class="btn btn-sm btn-outline-' + (c.is_active == 1 ? 'secondary' : 'success') + ' toggle-cat" data-id="' + c.id + '"><i class="bi bi-' + (c.is_active == 1 ? 'pause' : 'play') + '"></i></button>' +
                        '</td>' +
                        '</tr>';
                });
                $('#categoriesTable').html(html);
                var opts = '<option value="">Seleccionar...</option>';
                $.each(cats, function (i, c) {
                    if (c.is_active) opts += '<option value="' + c.id + '">' + c.name + '</option>';
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

    $(document).on('click', '.toggle-cat', function () {
        $.post(API + '?action=category-toggle', { id: $(this).data('id') })
            .done(function () { loadCategories(); })
            .fail(function () { showError('Error al cambiar estado'); });
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
                        '<td><a href="?page=course-detail&id=' + c.id + '" class="fw-semibold text-decoration-none">' + c.title + '</a></td>' +
                        '<td>' + c.teacher_name + '</td>' +
                        '<td>' + c.created_at + '</td>' +
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
                        labels: data.map(function (d) { return d.month; }),
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
                        labels: data.map(function (d) { return d.month; }),
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

});
