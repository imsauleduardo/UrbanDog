/**
 * Walker Profile Booking Handler
 */
jQuery(document).ready(function ($) {
    const $form = $('#ud-booking-form');
    const $summaryBox = $('#ud-booking-summary');
    const $totalPriceEl = $('#ud-summary-total');
    const $alert = $('#ud-booking-alert');

    // Rates from localized data
    const rates = udBookings.rates; // { base_price, group_price }

    function updatePrice() {
        const bookingType = $('input[name="booking_type"]').val();

        if (bookingType === 'meetgreet') {
            $totalPriceEl.text('0.00');
            $('#final-price-input').val('0.00');
            return;
        }

        const modality = $('input[name="modality"]:checked').val();
        const duration = parseInt($('select[name="duration"]').val());
        const dogCount = $('#pet-selector option:selected').length || 1;

        let basePrice = 0;
        if (modality === 'group') {
            basePrice = duration === 60 ? parseFloat(rates.grp_60) : parseFloat(rates.grp_30);
        } else {
            basePrice = duration === 60 ? parseFloat(rates.ind_60) : parseFloat(rates.ind_30);
        }

        const total = basePrice + (basePrice * 0.5 * (dogCount - 1));

        $totalPriceEl.text(total.toFixed(2));
        $('#final-price-input').val(total.toFixed(2));
    }

    // Listeners for changes
    $('input[name="modality"], select[name="duration"], #pet-selector').on('change', updatePrice);

    // Initial calculation and restore from session
    if ($form.length) {
        // Restore from sessionStorage if exists
        const pendingBooking = sessionStorage.getItem('ud_pending_booking');
        if (pendingBooking) {
            try {
                const data = JSON.parse(pendingBooking);
                if (data.walker_id == udBookings.walkerId) {
                    $(`input[name="modality"][value="${data.modality}"]`).prop('checked', true);
                    $('select[name="duration"]').val(data.duration);
                    $('#booking-date').val(data.date);
                    // Time slots update
                    if (typeof updateTimeSlots === 'function') {
                        updateTimeSlots(data.date);
                        setTimeout(() => $('#booking-time').val(data.time), 100);
                    }
                    $('#booking-notes').val(data.notes);

                    // Clear after restoration to avoid repeated pre-fills
                    sessionStorage.removeItem('ud_pending_booking');

                    // If we just logged in, show a little alert
                    if ($('input[name="is_guest"]').length === 0) {
                        $alert.text('¡Bienvenido! Hemos recuperado tu reserva pendiente. Por favor, selecciona a tu mascota para finalizar.').addClass('success').fadeIn();
                    }
                }
            } catch (e) { console.error("Error restoring pending booking", e); }
        }

        updatePrice();
    }

    // Form Submission
    $form.on('submit', function (e) {
        e.preventDefault();

        const bookingType = $('input[name="booking_type"]').val();
        const isGuest = $('input[name="is_guest"]').val() === '1';

        // Guest handling - Regular Walk still redirects to login, 
        // but M&G handles registration inline
        if (isGuest && bookingType !== 'meetgreet') {
            const guestData = {
                walker_id: udBookings.walkerId,
                date: $('#booking-date').val(),
                time: $('#booking-time').val(),
                modality: $('input[name="modality"]:checked').val(),
                duration: $('select[name="duration"]').val(),
                booking_type: bookingType,
                notes: $('#booking-notes').val()
            };
            sessionStorage.setItem('ud_pending_booking', JSON.stringify(guestData));

            const currentUrl = window.location.href;
            window.location.href = `${window.location.origin}/login/?redirect_to=${encodeURIComponent(currentUrl)}`;
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();

        $alert.hide().removeClass('success error');
        $submitBtn.prop('disabled', true).html('<i class="spinner-icon"></i> Enviando...');

        const formData = {
            action: 'ud_request_walk',
            nonce: udBookings.nonce,
            walker_id: udBookings.walkerId,
            date: $('#booking-date').length ? $('#booking-date').val() : '',
            time: $('#booking-time').length ? $('#booking-time').val() : '',
            modality: $('input[name="modality"]:checked').val() || 'group',
            duration: $('select[name="duration"]').val() || '30',
            booking_type: bookingType,
            dog_count: $('#pet-selector').val() ? $('#pet-selector').val().length : 1,
            price: $('#final-price-input').val(),
            notes: $('#booking-notes').val(),
            pets: $('#pet-selector').val()
        };

        // Add registration data for guests in M&G
        if (isGuest && bookingType === 'meetgreet') {
            const password = $('#reg-password').val();
            const passwordConfirm = $('#reg-password-confirm').val();

            if (password !== passwordConfirm) {
                $alert.text('Las contraseñas no coinciden').addClass('error').fadeIn();
                $submitBtn.prop('disabled', false).html(originalText);
                return;
            }

            formData.is_guest_mg = '1';
            formData.first_name = $('#reg-first-name').val();
            formData.last_name = $('#reg-last-name').val();
            formData.email = $('#reg-email').val();
            formData.phone = $('#reg-phone').val();
            formData.password = password;
        }
        $.post(udBookings.ajaxUrl, formData, function (response) {
            if (response.success) {
                $alert.text(response.data.message).addClass('success').fadeIn();
                $submitBtn.html('<i data-lucide="check"></i> ¡Enviado!');

                let redirectUrl = udBookings.dashboardUrl;

                // Si es M&G de invitado y tenemos pet_id, añadir parámetros para abrir el modal
                // isGuest y bookingType están disponibles en el scope superior de esta función
                if (typeof isGuest !== 'undefined' && isGuest &&
                    typeof bookingType !== 'undefined' && bookingType === 'meetgreet' &&
                    response.data.pet_id) {
                    redirectUrl += (redirectUrl.indexOf('?') === -1 ? '?' : '&') + 'action=edit_pet&pet_id=' + response.data.pet_id;
                }

                // Redirect to dashboard after a bit
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 2000);
            } else {
                $alert.text(response.data.message || 'Error al enviar solicitud').addClass('error').fadeIn();
                $submitBtn.prop('disabled', false).html(originalText);
            }
        }).fail(function () {
            $alert.text('Error de conexión').addClass('error').fadeIn();
            $submitBtn.prop('disabled', false).html(originalText);
        });
    });
});
