/**
 * User Registration Handler
 */
jQuery(document).ready(function ($) {
    const $form = $('#ud-registration-form');
    const $submitBtn = $form.find('button[type="submit"]');
    const $alert = $('#ud-registration-alert');

    $form.on('submit', function (e) {
        e.preventDefault();

        // Clear alerts
        $alert.hide().removeClass('ud-alert-error ud-alert-success').text('');

        // --- Password Strength Logic ---
        const $pwInput = $('#password');
        const $strengthMeter = $('#password-strength');
        const $strengthBar = $strengthMeter.find('.ud-strength-bar');
        const $strengthText = $strengthMeter.find('.ud-strength-text');

        $pwInput.on('input', function () {
            const val = $(this).val();
            if (!val) {
                $strengthMeter.hide();
                return;
            }

            $strengthMeter.show();
            let score = 0;
            if (val.length > 7) score++;
            if (val.length > 10) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            let label = '';
            let colorClass = '';

            switch (score) {
                case 0:
                case 1:
                    label = 'Muy débil';
                    colorClass = 'strength-very-weak';
                    break;
                case 2:
                    label = 'Débil';
                    colorClass = 'strength-weak';
                    break;
                case 3:
                    label = 'Media';
                    colorClass = 'strength-medium';
                    break;
                case 4:
                    label = 'Fuerte';
                    colorClass = 'strength-strong';
                    break;
                case 5:
                    label = 'Excelente';
                    colorClass = 'strength-excellent';
                    break;
            }

            $strengthBar.attr('class', 'ud-strength-bar ' + colorClass);
            $strengthText.text(label);
        });

        // Basic validation
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password !== confirmPassword) {
            showAlert('Las contraseñas no coinciden.', 'error');
            return;
        }

        if (password.length < 8) {
            showAlert('La contraseña debe tener al menos 8 caracteres.', 'error');
            return;
        }

        // Get form data
        const formData = new FormData(this);
        formData.append('action', 'ud_register_user');
        formData.append('nonce', udRegistration.nonce);

        // Submit form
        $submitBtn.prop('disabled', true).text('Registrando...');

        $.ajax({
            url: udRegistration.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    showAlert(response.data.message, 'success');
                    setTimeout(function () {
                        window.location.href = response.data.redirect;
                    }, 1500);
                } else {
                    showAlert(response.data.message || 'Ocurrió un error inesperado.', 'error');
                    $submitBtn.prop('disabled', false).text('Registrarse');
                }
            },
            error: function () {
                showAlert('Error en la conexión con el servidor.', 'error');
                $submitBtn.prop('disabled', false).text('Registrarse');
            }
        });
    });

    function showAlert(message, type) {
        $alert.addClass('ud-alert-' + type).text(message).fadeIn();
        $('html, body').animate({
            scrollTop: $alert.offset().top - 100
        }, 500);
    }

    // Custom File Input Filename Display
    $('.ud-file-custom input[type="file"]').on('change', function () {
        const fileName = $(this).val().split('\\').pop();
        const $nameSpan = $(this).siblings('.ud-file-name');
        if (fileName) {
            $nameSpan.text(fileName);
        } else {
            $nameSpan.text('Sin archivo seleccionado');
        }
    });
});
