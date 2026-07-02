$(document).ready(function () {
    const token = localStorage.getItem('session_token');
    
    if (token && token !== 'undefined' && token !== 'null') {
        window.location.replace('profile.html');
        return;
    } else {
        localStorage.removeItem('session_token');
    }

    // Switched from form submit to strict button click
    $('#loginBtn').on('click', function () {
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const $alert = $('#statusAlert');

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text('incorrect email');
            return;
        }

        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            dataType: 'json',
            data: { email: email, password: password },
            success: function (response) {
                if (response.status === 'success') {
                    localStorage.setItem('session_token', response.token);
                    window.location.replace('profile.html');
                } else {
                    $alert.removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
                }
            },
            error: function () {
                $alert.removeClass('d-none alert-success').addClass('alert-danger').text('Backend error: Check if MySQL and Redis are running.');
            }
        });
    });
});