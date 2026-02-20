<?php
/**
 * UrbanDog Bookings
 *
 * Handles the complete booking flow: Request -> Visit -> Confirmation -> Payment -> Completion.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Bookings
{

    /**
     * Initialize booking-related hooks.
     */
    public static function init(): void
    {
        add_action('wp_ajax_ud_request_walk', [__CLASS__, 'handle_request']);
        add_action('wp_ajax_ud_update_booking_status', [__CLASS__, 'handle_status_update']);
        add_action('wp_ajax_ud_schedule_visit', [__CLASS__, 'handle_schedule_visit']);
    }

    /**
     * Handle AJAX walk request by owner.
     */
    public static function handle_request(): void
    {
        check_ajax_referer('ud_booking_nonce', 'nonce');

        $owner_id = get_current_user_id();
        $walker_id = (int) ($_POST['walker_id'] ?? 0);
        $date = sanitize_text_field($_POST['date'] ?? '');
        $time = sanitize_text_field($_POST['time'] ?? '');
        $modality = sanitize_text_field($_POST['modality'] ?? 'group');
        $duration = (int) ($_POST['duration'] ?? 30);
        $dog_count = (int) ($_POST['dog_count'] ?? 1);
        $price = (float) ($_POST['price'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (!$owner_id || !$walker_id || !UD_Roles::is_owner($owner_id)) {
            wp_send_json_error(['message' => __('No autorizado o sesión expirada.', 'urbandog')]);
        }

        if (empty($date) || empty($time)) {
            wp_send_json_error(['message' => __('Por favor selecciona fecha y hora.', 'urbandog')]);
        }

        $booking_id = wp_insert_post([
            'post_title' => sprintf(__('Reserva de %s (%s)', 'urbandog'), get_userdata($owner_id)->display_name, $date),
            'post_type' => 'ud_booking',
            'post_status' => 'publish',
            'post_author' => $owner_id,
            'post_content' => $notes,
        ]);

        if (is_wp_error($booking_id)) {
            wp_send_json_error(['message' => $booking_id->get_error_message()]);
        }

        // Save Metadata
        update_post_meta($booking_id, 'ud_booking_status', 'pending_request');
        update_post_meta($booking_id, 'ud_booking_owner_id', $owner_id);
        update_post_meta($booking_id, 'ud_booking_walker_id', $walker_id);
        update_post_meta($booking_id, 'ud_booking_date', $date);
        update_post_meta($booking_id, 'ud_booking_time', $time);
        update_post_meta($booking_id, 'ud_booking_modality', $modality);
        update_post_meta($booking_id, 'ud_booking_duration', $duration);
        update_post_meta($booking_id, 'ud_booking_dogs', $dog_count);
        update_post_meta($booking_id, 'ud_booking_price', $price);

        wp_send_json_success([
            'message' => __('Solicitud enviada con éxito.', 'urbandog'),
            'booking_id' => $booking_id
        ]);
    }

    /**
     * Handle AJAX status update (accept/reject) by walker.
     */
    public static function handle_status_update(): void
    {
        check_ajax_referer('ud_booking_nonce', 'nonce');

        $walker_id = get_current_user_id();
        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        $new_status = sanitize_text_field($_POST['status'] ?? ''); // accepted, rejected

        if (!$walker_id || !UD_Roles::is_walker($walker_id)) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $current_walker = (int) get_post_meta($booking_id, 'ud_booking_walker_id', true);
        if ($current_walker !== $walker_id) {
            wp_send_json_error(['message' => __('Esta reserva no te pertenece.', 'urbandog')]);
        }

        if (!in_array($new_status, ['accepted', 'rejected'], true)) {
            wp_send_json_error(['message' => __('Estado inválido.', 'urbandog')]);
        }

        update_post_meta($booking_id, 'ud_booking_status', $new_status);

        wp_send_json_success(['message' => sprintf(__('Reserva %s.', 'urbandog'), $new_status === 'accepted' ? 'aceptada' : 'rechazada')]);
    }

    /**
     * Handle AJAX scheduling a visit.
     */
    public static function handle_schedule_visit(): void
    {
        check_ajax_referer('ud_visit_nonce', 'nonce');

        $owner_id = get_current_user_id();
        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        $date = sanitize_text_field($_POST['date'] ?? '');
        $time = sanitize_text_field($_POST['time'] ?? '');

        if (!$owner_id || !UD_Roles::is_owner($owner_id)) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $booking_owner = (int) get_post_meta($booking_id, 'ud_booking_owner_id', true);
        if ($booking_owner !== $owner_id) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $walker_id = (int) get_post_meta($booking_id, 'ud_booking_walker_id', true);

        $visit_id = wp_insert_post([
            'post_title' => sprintf(__('Visita para reserva #%d', 'urbandog'), $booking_id),
            'post_type' => 'ud_visit',
            'post_status' => 'publish',
            'post_author' => $owner_id,
        ]);

        update_post_meta($visit_id, 'ud_visit_booking_id', $booking_id);
        update_post_meta($visit_id, 'ud_visit_owner_id', $owner_id);
        update_post_meta($visit_id, 'ud_visit_walker_id', $walker_id);
        update_post_meta($visit_id, 'ud_visit_date', $date);
        update_post_meta($visit_id, 'ud_visit_time', $time);
        update_post_meta($visit_id, 'ud_visit_status', 'scheduled');

        update_post_meta($booking_id, 'ud_booking_status', 'visit_scheduled');

        wp_send_json_success(['message' => __('Visita agendada.', 'urbandog'), 'visit_id' => $visit_id]);
    }
}
