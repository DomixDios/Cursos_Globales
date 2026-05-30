$(function () {
    var API = BASE_URL + '/api/admin.php';

    function loadDashboard() {
        $.getJSON(API, { action: 'dashboard' })
            .done(function (d) {
                $('#modPendientes').text(d.pending || 0);
                $('#modCursos').text(d.courses || 0);
                $('#modUsuarios').text(d.users || 0);
            });

        $.getJSON(API, { action: 'category-list' })
            .done(function (cats) {
                $('#modCategorias').text(cats.length || 0);
            });
    }

    if ($('#modPendientes').length) loadDashboard();
});
