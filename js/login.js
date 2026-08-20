$(function () {
    // Already logged in? skip straight to profile.
    if (localStorage.getItem('authToken')) {
        window.location.href = 'profile.html';
        return;
    }

    function showAlert(message, type) {
        $('#alertBox')
            .removeClass('alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }

    $('#loginBtn').on('click', function () {
        var identifier = $('#identifier').val().trim();
        var password = $('#password').val();

        if (!identifier || !password) {
            showAlert('Please enter your username/email and password.', 'danger');
            return;
        }

        var $btn = $(this).prop('disabled', true).text('Logging in...');

        $.ajax({
            url: 'php/login.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ username: identifier, password: password }),
            dataType: 'json'
        })
            .done(function (res) {
                if (res.success) {
                    // Session is kept client-side in localStorage, not a PHP session.
                    localStorage.setItem('authToken', res.token);
                    localStorage.setItem('authUser', JSON.stringify(res.user));
                    window.location.href = 'profile.html';
                } else {
                    showAlert(res.message || 'Login failed.', 'danger');
                }
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Server error. Please try again.';
                showAlert(msg, 'danger');
            })
            .always(function () {
                $btn.prop('disabled', false).text('Log in');
            });
    });
});
