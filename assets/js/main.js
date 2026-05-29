$(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type=submit]');
        var spinner = btn.find('.spinner-border');
        var errorEl = $('#loginError');

        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        errorEl.addClass('d-none');

        $.post(BASE_URL + '/api/auth.php?action=login', $(this).serialize())
            .done(function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    errorEl.text(res.message).removeClass('d-none');
                }
            })
            .fail(function () {
                errorEl.text('Error de conexi\u00f3n. Intenta de nuevo.').removeClass('d-none');
            })
            .always(function () {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            });
    });

    $('#loginModal').on('hidden.bs.modal', function () {
        $('#loginForm')[0].reset();
        $('#loginError').addClass('d-none');
    });

    function loadStudentDashboard() {
        var enrolledEl = $('#statEnrolled');
        if (!enrolledEl.length) return;
        $.get(BASE_URL + '/api/enrollment.php?action=dashboard')
            .done(function (r) {
                enrolledEl.text(r.enrolled);
                $('#statCompleted').text(r.completed);
                $('#statProgress').text(r.progress + '%');
                $('#statProgressBar').css('width', r.progress + '%').attr('aria-valuenow', r.progress);
                animateCounters();
            });
    }
    loadStudentDashboard();

    function animateCounters() {
        $('.stat-number').each(function () {
            var el = $(this);
            var text = el.text();
            var num = parseInt(text);
            if (isNaN(num)) return;
            el.text('0');
            $({ val: 0 }).animate({ val: num }, {
                duration: 800, easing: 'swing',
                step: function () { el.text(Math.ceil(this.val)); },
                complete: function () { el.text(num); }
            });
        });
    }

    function loadDashboardCourses() {
        var container = $('#dashboardCourses');
        if (!container.length) return;
        $.get(BASE_URL + '/api/enrollment.php?action=my-courses')
            .done(function (r) {
                if (!r.courses || r.courses.length === 0) {
                    container.html('<div class="text-center text-muted py-5"><i class="bi bi-book fs-1 d-block mb-3"></i><p>No est\u00e1s inscrito en ning\u00fan curso.</p><a href="index.php?page=catalog" class="btn btn-primary btn-sm mt-2">Explorar cursos</a></div>');
                    return;
                }
                var html = '';
                var maxItems = Math.min(r.courses.length, 5);
                for (var i = 0; i < maxItems; i++) {
                    var c = r.courses[i];
                    var img = c.miniatura ? BASE_URL + '/' + c.miniatura : '';
                    html += '<a href="index.php?page=player&course_id=' + c.curso_id + '&class_id=0" class="dashboard-course-item">';
                    if (img) {
                        html += '<img src="' + img + '" class="dc-thumb">';
                    } else {
                        html += '<div class="dc-thumb d-flex align-items-center justify-content-center text-muted"><i class="bi bi-book"></i></div>';
                    }
                    html += '<div class="dc-info"><div class="dc-title">' + c.titulo + '</div>';
                    html += '<div class="dc-meta">' + (c.nivel || '') + ' &middot; ' + c.progreso + '% completo</div></div>';
                    html += '<div class="dc-progress"><div class="progress" style="height:6px"><div class="progress-bar" style="width:' + c.progreso + '%"></div></div></div>';
                    html += '</a>';
                }
                if (r.courses.length > 5) {
                    html += '<a href="index.php?page=my-courses" class="dashboard-course-item justify-content-center text-primary fw-medium">Ver los ' + r.courses.length + ' cursos <i class="bi bi-arrow-right ms-2"></i></a>';
                }
                container.html(html);
            })
            .fail(function () {
                container.html('<div class="text-center text-muted py-4"><p>Error al cargar cursos.</p></div>');
            });
    }
    loadDashboardCourses();

    function loadMyCourses() {
        var container = $('#myCoursesContainer');
        if (!container.length) return;
        $.get(BASE_URL + '/api/enrollment.php?action=my-courses')
            .done(function (r) {
                if (!r.courses || r.courses.length === 0) {
                    container.html('<div class="col-12 text-center text-muted py-5"><i class="bi bi-book fs-1 d-block mb-3"></i><p>No est\u00e1s inscrito en ning\u00fan curso a\u00fan.</p><a href="index.php?page=catalog" class="btn btn-primary mt-2">Explorar cursos</a></div>');
                    return;
                }
                var html = '';
                $.each(r.courses, function (i, c) {
                    var img = c.miniatura ? BASE_URL + '/' + c.miniatura : 'https://placehold.co/600x400/eee/999?text=Curso';
                    var badge = '<span class="badge bg-' + (c.nivel === 'principiante' ? 'success' : c.nivel === 'intermedio' ? 'warning' : 'danger') + '">' + c.nivel + '</span>';
                    html += '<div class="col-md-6 col-lg-4">';
                    html += '<div class="card h-100">';
                    html += '<img src="' + img + '" class="card-img-top" style="height:160px;object-fit:cover" alt="' + c.titulo + '">';
                    html += '<div class="card-body d-flex flex-column">';
                    html += '<h5 class="card-title fw-bold">' + c.titulo + '</h5>';
                    html += '<p class="text-muted small mb-2">' + badge + ' &middot; ' + c.profesor_nombre + '</p>';
                    html += '<div class="progress mb-2" style="height:8px"><div class="progress-bar" style="width:' + c.progreso + '%"></div></div>';
                    html += '<div class="d-flex justify-content-between small text-muted mb-3"><span>Progreso</span><span>' + c.progreso + '%</span></div>';
                    html += '<div class="mt-auto d-flex gap-2">';
                    if (c.progreso > 0) {
                        html += '<a href="index.php?page=player&course_id=' + c.curso_id + '&class_id=0" class="btn btn-sm btn-outline-primary flex-fill"><i class="bi bi-play-fill"></i> Continuar</a>';
                    } else {
                        html += '<a href="index.php?page=player&course_id=' + c.curso_id + '&class_id=0" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-play-fill"></i> Comenzar</a>';
                    }
                    html += '<a href="index.php?page=progress&course_id=' + c.curso_id + '" class="btn btn-sm btn-outline-secondary"><i class="bi bi-graph-up"></i></a>';
                    html += '</div></div></div></div>';
                });
                container.html(html);
            })
            .fail(function () {
                container.html('<div class="col-12 text-center text-muted py-5"><p>Error al cargar los cursos.</p></div>');
            });
    }
    loadMyCourses();

    function loadCertificates() {
        var container = $('#certificatesContainer');
        if (!container.length) return;
        $.get(BASE_URL + '/api/enrollment.php?action=certificates')
            .done(function (r) {
                if (!r || r.length === 0) {
                    container.html('<div class="col-12 text-center text-muted py-5"><i class="bi bi-patch-check fs-1 d-block mb-3"></i><p>A\u00fan no has completado ning\u00fan curso.</p><a href="index.php?page=my-courses" class="btn btn-primary mt-2">Ver mis cursos</a></div>');
                    return;
                }
                var html = '';
                $.each(r, function (i, c) {
                    var fecha = c.completado_en ? new Date(c.completado_en).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
                    html += '<div class="col-md-6">';
                    html += '<div class="card border-warning border-2 h-100">';
                    html += '<div class="card-body text-center p-4">';
                    html += '<div class="fs-1 text-warning mb-3"><i class="bi bi-award-fill"></i></div>';
                    html += '<h4 class="fw-bold">' + c.titulo + '</h4>';
                    html += '<p class="text-muted small mb-1"><i class="bi bi-person"></i> ' + c.profesor_nombre + '</p>';
                    html += '<p class="text-muted small mb-3"><i class="bi bi-calendar-check"></i> Completado: ' + fecha + '</p>';
                    html += '<span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-patch-check-fill me-1"></i> Certificado</span>';
                    html += '</div></div></div>';
                });
                container.html(html);
            })
            .fail(function () {
                container.html('<div class="col-12 text-center text-muted py-5"><p>Error al cargar certificados.</p></div>');
            });
    }
    loadCertificates();

    function loadProgress() {
        var container = $('#progressContainer');
        if (!container.length) return;
        var courseId = new URLSearchParams(window.location.search).get('course_id');
        if (courseId) {
            loadCourseProgressDetail(courseId);
            return;
        }
        $.get(BASE_URL + '/api/enrollment.php?action=my-courses')
            .done(function (r) {
                if (!r.courses || r.courses.length === 0) {
                    container.html('<div class="col-12 text-center text-muted py-5"><i class="bi bi-graph-up fs-1 d-block mb-3"></i><p>No tienes cursos inscritos.</p></div>');
                    return;
                }
                var html = '';
                $.each(r.courses, function (i, c) {
                    var img = c.miniatura ? BASE_URL + '/' + c.miniatura : 'https://placehold.co/600x400/eee/999?text=Curso';
                    var badge = '<span class="badge bg-' + (c.nivel === 'principiante' ? 'success' : c.nivel === 'intermedio' ? 'warning' : 'danger') + '">' + c.nivel + '</span>';
                    html += '<div class="col-md-6">';
                    html += '<div class="card h-100">';
                    html += '<div class="row g-0 h-100">';
                    html += '<div class="col-md-4"><img src="' + img + '" class="img-fluid rounded-start h-100" style="object-fit:cover" alt="' + c.titulo + '"></div>';
                    html += '<div class="col-md-8"><div class="card-body">';
                    html += '<h5 class="card-title fw-bold">' + c.titulo + '</h5>';
                    html += '<p class="text-muted small mb-2">' + badge + '</p>';
                    html += '<div class="progress mb-2" style="height:10px"><div class="progress-bar" style="width:' + c.progreso + '%"></div></div>';
                    html += '<div class="d-flex justify-content-between small text-muted mb-3"><span>Progreso</span><span>' + c.progreso + '%</span></div>';
                    html += '<a href="index.php?page=progress&course_id=' + c.curso_id + '" class="btn btn-sm btn-outline-success">Ver detalle</a>';
                    html += '</div></div></div></div></div>';
                });
                container.html(html);
            })
            .fail(function () {
                container.html('<div class="col-12 text-center text-muted py-5"><p>Error al cargar progreso.</p></div>');
            });
    }

    function loadCourseProgressDetail(courseId) {
        var container = $('#progressContainer');
        container.html('<div class="col-12 text-center text-muted py-5"><div class="spinner-border" role="status"></div><p class="mt-2">Cargando detalle...</p></div>');
        $.get(BASE_URL + '/api/progress.php?action=course-progress&course_id=' + courseId)
            .done(function (r) {
                var html = '<div class="col-12 mb-3">';
                html += '<a href="index.php?page=progress" class="btn btn-sm btn-outline-secondary mb-3">&larr; Volver a progreso general</a>';
                html += '<div class="card"><div class="card-body d-flex align-items-center gap-3">';
                html += '<div class="flex-shrink-0"><div class="progress-circle" data-progress="' + r.progreso + '">';
                html += '<div class="display-6 fw-bold text-success">' + r.progreso + '%</div></div></div>';
                html += '<div><h4 class="fw-bold mb-1">Progreso del curso</h4>';
                html += '<p class="text-muted mb-0"><strong>' + r.completadas + '</strong> de <strong>' + r.total_clases + '</strong> clases completadas</p></div>';
                html += '</div></div></div>';
                $.each(r.modulos, function (i, mod) {
                    html += '<div class="col-12"><div class="card mb-3"><div class="card-header fw-semibold"><i class="bi bi-folder me-2"></i>' + mod.titulo + '</div>';
                    html += '<ul class="list-group list-group-flush">';
                    $.each(mod.clases, function (j, clase) {
                        var done = clase.completado ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>';
                        html += '<li class="list-group-item d-flex align-items-center gap-2"><span class="fs-5">' + done + '</span><span>' + clase.titulo + '</span>';
                        if (clase.duracion) html += '<span class="ms-auto text-muted small">' + clase.duracion + '</span>';
                        html += '</li>';
                    });
                    html += '</ul></div></div>';
                });
                container.html(html);
            })
            .fail(function () {
                container.html('<div class="col-12 text-center text-muted py-5"><p>Error al cargar el detalle del curso.</p></div>');
            });
    }
    loadProgress();

    $('#profileForm').on('submit', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type=submit]');
        var spinner = btn.find('.spinner-border');
        var msg = $('#profileMessage');
        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        msg.addClass('d-none');
        var formData = new FormData(this);
        $.ajax({
            url: BASE_URL + '/api/auth.php?action=update-profile',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).done(function (res) {
            msg.removeClass('d-none alert-danger').addClass('alert-success');
            if (res.success) {
                msg.text('Perfil actualizado correctamente.');
                setTimeout(function () { location.reload(); }, 1500);
            } else {
                msg.text(res.message).removeClass('alert-success').addClass('alert-danger');
            }
        }).fail(function () {
            msg.removeClass('d-none alert-success').addClass('alert-danger');
            msg.text('Error de conexi\u00f3n.');
        }).always(function () {
            btn.prop('disabled', false);
            spinner.addClass('d-none');
        });
    });

});
