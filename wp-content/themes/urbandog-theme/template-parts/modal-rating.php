<?php
/**
 * Template part: Rating Modal
 * Used in dashboards to submit ratings.
 */
?>
<div id="ud-rating-modal" class="ud-modal-overlay" style="display:none;">
    <div class="ud-modal">
        <div class="ud-modal-header">
            <h3>
                <?php _e('Calificar Servicio', 'urbandog'); ?>
            </h3>
            <button class="ud-modal-close" aria-label="<?php _e('Cerrar', 'urbandog'); ?>">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="ud-rating-form" class="ud-modal-body">
            <input type="hidden" name="booking_id" id="ud-rating-booking-id">
            <input type="hidden" name="to_user_id" id="ud-rating-to-user-id">
            <input type="hidden" name="type" id="ud-rating-type">
            <input type="hidden" name="score" id="ud-rating-score" value="5">

            <div class="ud-form-group text-center mb-6">
                <p class="text-sm text-slate-600 mb-4">
                    <?php _e('¿Cómo fue tu experiencia con', 'urbandog'); ?> <strong id="rating-to-name"></strong>?
                </p>
                <div class="ud-rating-stars-input">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i data-lucide="star" class="star-icon active" data-val="<?php echo $i; ?>"></i>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="ud-form-group mb-6">
                <label for="ud-rating-comment" class="ud-rating-label">
                    <?php _e('Comentario (opcional)', 'urbandog'); ?>
                </label>
                <textarea id="ud-rating-comment" name="comment" class="ud-textarea w-full" rows="3"
                    placeholder="<?php _e('Comparte los detalles de tu experiencia...', 'urbandog'); ?>"></textarea>
            </div>

            <div id="ud-rating-alert" class="ud-alert-mini" style="display:none; margin-bottom: 1rem;"></div>

            <div class="ud-modal-footer">
                <button type="submit" class="ud-btn ud-btn-primary w-full">
                    <i data-lucide="save"></i>
                    <?php _e('Guardar Calificación', 'urbandog'); ?>
                </button>
            </div>
        </form>
    </div>
</div>