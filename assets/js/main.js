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
                errorEl.text('Error de conexión. Intenta de nuevo.').removeClass('d-none');
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

});
