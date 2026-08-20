$(function () {
    var token = localStorage.getItem('authToken');

    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    function showAlert(message, type) {
        $('#alertBox')
            .removeClass('alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }

    function authHeader() {
        return { Authorization: 'Bearer ' + token };
    }

    function loadProfile() {
        $.ajax({
            url: 'php/profile.php',
            method: 'GET',
            headers: authHeader(),
            dataType: 'json'
        })
            .done(function (res) {
                if (!res.success) {
                    showAlert(res.message || 'Could not load profile.', 'danger');
                    return;
                }

                $('#welcomeText').text('Signed in as ' + res.account.username);
                $('#accUsername').val(res.account.username);
                $('#accEmail').val(res.account.email);

                var p = res.profile || {};
                $('#age').val(p.age || '');
                $('#dob').val(p.dob || '');
                $('#contact').val(p.contact || '');
                $('#address').val(p.address || '');
                $('#bio').val(p.bio || '');
            })
            .fail(function (xhr) {
                if (xhr.status === 401) {
                    localStorage.removeItem('authToken');
                    localStorage.removeItem('authUser');
                    window.location.href = 'login.html';
                    return;
                }
                showAlert('Server error loading profile.', 'danger');
            });
    }

    $('#saveBtn').on('click', function () {
        var payload = {
            age: $('#age').val(),
            dob: $('#dob').val(),
            contact: $('#contact').val(),
            address: $('#address').val(),
            bio: $('#bio').val()
        };

        var $btn = $(this).prop('disabled', true).text('Saving...');

        $.ajax({
            url: 'php/profile.php',
            method: 'POST',
            contentType: 'application/json',
            headers: authHeader(),
            data: JSON.stringify(payload),
            dataType: 'json'
        })
            .done(function (res) {
                if (res.success) {
                    showAlert('Profile updated successfully.', 'success');
                } else {
                    showAlert(res.message || 'Update failed.', 'danger');
                }
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Server error saving profile.';
                showAlert(msg, 'danger');
            })
            .always(function () {
                $btn.prop('disabled', false).text('Save changes');
            });
    });

    $('#logoutBtn').on('click', function () {
        $.ajax({
            url: 'php/logout.php',
            method: 'POST',
            headers: authHeader()
        }).always(function () {
            localStorage.removeItem('authToken');
            localStorage.removeItem('authUser');
            window.location.href = 'login.html';
        });
    });

    loadProfile();
});
