$(document).ready(function () {
    const token = localStorage.getItem('session_token');
    const $alert = $('#statusAlert');

    if (!token || token === 'undefined' || token === 'null') {
        localStorage.removeItem('session_token');
        window.location.replace('login.html');
        return;
    }

    // Function to handle the session timeout requirement
    function handleSessionTimeout() {
        localStorage.removeItem('session_token');
        $('#profileForm').addClass('d-none'); 
        $alert.removeClass('d-none alert-success').addClass('alert-danger').text('Session timed out, return to login page.');
        setTimeout(() => window.location.replace('login.html'), 3000);
    }

    $.ajax({
        url: 'php/profile.php',
        type: 'GET',
        dataType: 'json',
        data: { token: token },
        success: function (response) {
            if (response.status === 'success' && response.data) {
                $('#name').val(response.data.name);
                $('#age').val(response.data.age);
                $('#dob').val(response.data.dob);
                $('#contact').val(response.data.contact);
            }
        },
        error: function (xhr) {
            if (xhr.status === 401) {
                handleSessionTimeout();
            }
        }
    });

    const calculateAge = (dob) => {
        const birthDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        return age;
    };

    $('#dob').on('change', function() {
        $('#age').val(calculateAge($(this).val()));
    });

    // Switched from form submit to strict button click
    $('#saveProfileBtn').on('click', function () {
        const nameVal = $('#name').val().trim();
        const contactVal = $('#contact').val().trim();
        
        // REQUIREMENT: Name should not contain numeric
        const nameRegex = /^[A-Za-z\s]+$/;
        if (!nameRegex.test(nameVal)) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text('Invalid Name, Name should not contain numeric values.');
            return;
        }

        // REQUIREMENT: Contact exactly 10 digits numeric
        const contactRegex = /^\d{10}$/;
        if (!contactRegex.test(contactVal)) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text('Contact number should be of 10 digits.');
            return;
        }

        const calculatedAge = calculateAge($('#dob').val());
        if (parseInt($('#age').val()) !== calculatedAge) {
            $('#age').val(calculatedAge);
        }

        const profileData = {
            token: token,
            name: nameVal,
            age: $('#age').val(),
            dob: $('#dob').val(),
            contact: contactVal
        };

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            dataType: 'json',
            data: profileData,
            success: function(response) {
                if(response.status === 'success') {
                    $alert.text('Profile saved successfully.').removeClass('d-none alert-danger').addClass('alert-success');
                    $('#postSaveActions').removeClass('d-none'); 
                } else {
                    $alert.text(response.message).removeClass('d-none alert-success').addClass('alert-danger');
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    handleSessionTimeout();
                } else {
                    $alert.text('Server connection failed.').removeClass('d-none alert-success').addClass('alert-danger');
                }
            }
        });
    });

    $('#logoutAction').on('click', function () {
        localStorage.removeItem('session_token');
        window.location.replace('login.html');
    });

    // REQUIREMENT: Return to Login ends session
    $('#returnLoginBtn').on('click', function () {
        localStorage.removeItem('session_token');
        window.location.replace('login.html');
    });
});