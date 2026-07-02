$(document).ready(function () {
    if (localStorage.getItem('session_token')) {
        window.location.replace('profile.html');
        return;
    }

    // Switched from form submit to strict button click
    $('#registerBtn').on('click', function () {
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const $alert = $('#statusAlert');

        // REQUIREMENT: Email Format Validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text('email incorrect , Valid email is required');
            return; 
        }

        // REQUIREMENT: Strict Password Validation
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passRegex.test(password)) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text('Password must be at least 8 characters, with min 1 uppercase, 1 lowercase, 1 numeric, and 1 special character.');
            return; 
        }

        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            dataType: 'json',
            data: { email: email, password: password },
            success: function (response) {
                if (response.status === 'success') {
                    $alert.removeClass('d-none alert-danger').addClass('alert-success').text('Registration successful. Redirecting to login...');
                    setTimeout(() => window.location.replace('login.html'), 1500);
                } else {
                    $alert.removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
                }
            },
            error: function () {
                $alert.removeClass('d-none alert-success').addClass('alert-danger').text('Backend error: Check MySQL connection.');
            }
        });
    });
});