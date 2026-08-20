$(function () {
    function showAlert(message, type) {
        $('#alertBox')
            .removeClass('alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }

    $('#registerBtn').on('click', function () {
        var username = $('#username').val().trim();
        var email = $('#email').val().trim();
        var password = $('#password').val();

        if (!username || !email || !password) {
            showAlert('Please fill in all fields.', 'danger');
            return;
        }

        var $btn = $(this).prop('disabled', true).text('Registering...');

        $.ajax({
            url: 'php/register.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ username: username, email: email, password: password }),
            dataType: 'json'
        })
            .done(function (res) {
                if (res.success) {
                    showAlert(res.message + ' Redirecting to login...', 'success');
                    setTimeout(function () {
                        window.location.href = 'login.html';
                    }, 1200);
                } else {
                    showAlert(res.message || 'Registration failed.', 'danger');
                }
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Server error. Please try again.';
                showAlert(msg, 'danger');
            })
            .always(function () {
                $btn.prop('disabled', false).text('Register');
            });
    });
});
