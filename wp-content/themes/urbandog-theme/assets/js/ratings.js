/**
 * UrbanDog Ratings Handler
 */
jQuery(document).ready(function ($) {
    const $modal = $('#ud-rating-modal');
    const $form = $('#ud-rating-form');
    const $stars = $('.ud-rating-stars-input i');
    const $scoreInput = $('#ud-rating-score');

    // Star interaction
    $stars.on('click', function () {
        const score = $(this).data('val');
        $scoreInput.val(score);

        $stars.each(function () {
            if ($(this).data('val') <= score) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    });

    // Open Modal
    $(document).on('click', '.ud-open-rating', function () {
        const bookingId = $(this).data('booking-id');
        const toUserId = $(this).data('to-user-id');
        const toName = $(this).data('to-name');
        const type = $(this).data('type');

        $('#ud-rating-booking-id').val(bookingId);
        $('#ud-rating-to-user-id').val(toUserId);
        $('#ud-rating-type').val(type);
        $('.ud-rating-to-name').text(toName);

        // Reset form
        $scoreInput.val(0);
        $stars.removeClass('active');
        $('#ud-rating-comment').val('');

        $modal.fadeIn().css('display', 'flex');
    });

    // Close Modal
    $('.ud-modal-close').on('click', function () {
        $modal.fadeOut();
    });

    // Handle Submit
    $form.on('submit', function (e) {
        e.preventDefault();

        if (!$scoreInput.val() || $scoreInput.val() == '0') {
            alert('Por favor selecciona una puntuación.');
            return;
        }

        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('Guardando...');

        const formData = {
            action: 'ud_submit_rating',
            nonce: udRatings.nonce,
            booking_id: $('#ud-rating-booking-id').val(),
            to_user_id: $('#ud-rating-to-user-id').val(),
            type: $('#ud-rating-type').val(),
            score: $scoreInput.val(),
            comment: $('#ud-rating-comment').val()
        };

        $.post(udRatings.ajaxUrl, formData, function (response) {
            if (response.success) {
                $modal.fadeOut();
                alert(response.data.message || '¡Gracias por tu calificación!');
                location.reload();
            } else {
                alert(response.data.message || 'Error al enviar calificación');
                $btn.prop('disabled', false).text(originalText);
            }
        }).fail(function () {
            alert('Error de conexión. Inténtalo de nuevo.');
            $btn.prop('disabled', false).text(originalText);
        });
    });
});
