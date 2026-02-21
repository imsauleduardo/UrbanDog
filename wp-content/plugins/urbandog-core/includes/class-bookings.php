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
        add_action('wp_ajax_nopriv_ud_request_walk', [__CLASS__, 'handle_request']);
        add_action('wp_ajax_ud_update_booking_status', [__CLASS__, 'handle_status_update']);
        add_action('wp_ajax_ud_schedule_visit', [__CLASS__, 'handle_schedule_visit']);
    }

    /**
     * Handle AJAX walk request by owner.
     */
    public static function handle_request(): void
    {
        check_ajax_referer('ud_booking_nonce', 'nonce');

        $is_guest_mg = isset($_POST['is_guest_mg']) && $_POST['is_guest_mg'] === '1';
        $owner_id = get_current_user_id();
        $walker_id = (int) ($_POST['walker_id'] ?? 0);
        $booking_type = sanitize_text_field($_POST['booking_type'] ?? 'walk');

        // Handle Guest Registration for Meet & Greet
        if ($is_guest_mg && !$owner_id) {
            $email = sanitize_email($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $first_name = sanitize_text_field($_POST['first_name'] ?? '');
            $last_name = sanitize_text_field($_POST['last_name'] ?? '');
            $phone = sanitize_text_field($_POST['phone'] ?? '');
            $pet_name = sanitize_text_field($_POST['pet_name'] ?? '');
            $pet_breed = sanitize_text_field($_POST['pet_breed'] ?? '');

            if (!is_email($email) || email_exists($email)) {
                wp_send_json_error(['message' => __('Email inválido o ya registrado.', 'urbandog')]);
            }

            if (strlen($password) < 8) {
                wp_send_json_error(['message' => __('La contraseña debe tener al menos 8 caracteres.', 'urbandog')]);
            }

            // Create User
            $user_id = wp_create_user($email, $password, $email);
            if (is_wp_error($user_id)) {
                wp_send_json_error(['message' => $user_id->get_error_message()]);
            }

            wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => trim($first_name . ' ' . $last_name),
                'role' => 'ud_owner'
            ]);
            update_user_meta($user_id, 'ud_phone', $phone);

            // Log user in
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            $owner_id = $user_id;

            // Create Pet
            $pet_id = wp_insert_post([
                'post_title' => !empty($pet_name) ? $pet_name : __('Mi Mascota', 'urbandog'),
                'post_type' => 'ud_pet',
                'post_status' => 'publish',
                'post_author' => $user_id
            ]);
            if (!is_wp_error($pet_id)) {
                update_post_meta($pet_id, 'ud_pet_breed', !empty($pet_breed) ? $pet_breed : __('Por definir', 'urbandog'));
                $_POST['pets'] = [$pet_id]; // Assign the new pet to the booking
            }
        }

        $is_authorized = ($owner_id && $walker_id && (UD_Roles::is_owner($owner_id) || $is_guest_mg));

        if (!$is_authorized) {
            wp_send_json_error(['message' => __('No autorizado o sesión expirada.', 'urbandog')]);
        }

        $date = sanitize_text_field($_POST['date'] ?? '');
        $time = sanitize_text_field($_POST['time'] ?? '');
        $modality = sanitize_text_field($_POST['modality'] ?? 'group');
        $duration = (int) ($_POST['duration'] ?? 30);
        $pets = isset($_POST['pets']) ? (array) $_POST['pets'] : [];
        $dog_count = count($pets) ?: (int) ($_POST['dog_count'] ?? 1);
        $price = (float) ($_POST['price'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (!$is_guest_mg && (empty($date) || empty($time))) {
            wp_send_json_error(['message' => __('Por favor selecciona fecha y hora.', 'urbandog')]);
        }

        $owner_name = get_userdata($owner_id)->display_name;
        $title_suffix = !empty($date) ? $date : __('Por coordinar', 'urbandog');
        $booking_id = wp_insert_post([
            'post_title' => sprintf(__('Reserva de %s (%s)', 'urbandog'), $owner_name, $title_suffix),
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
        update_post_meta($booking_id, 'ud_booking_date', !empty($date) ? $date : __('Por coordinar', 'urbandog'));
        update_post_meta($booking_id, 'ud_booking_time', !empty($time) ? $time : __('Por coordinar', 'urbandog'));
        update_post_meta($booking_id, 'ud_booking_modality', $modality);
        update_post_meta($booking_id, 'ud_booking_duration', $duration);
        update_post_meta($booking_id, 'ud_booking_dogs', $dog_count);
        update_post_meta($booking_id, 'ud_booking_pets', $pets);
        update_post_meta($booking_id, 'ud_booking_price', $price);

        $booking_type = sanitize_text_field($_POST['booking_type'] ?? 'walk');
        update_post_meta($booking_id, 'ud_booking_type', $booking_type);

        // If it's a meet & greet, initialize the approval record
        if ($booking_type === 'meetgreet') {
            foreach ($pets as $pet_id) {
                UD_MeetGreet::update_approval((int) $pet_id, $walker_id, 'meetgreet_scheduled', $booking_id);
            }
        }

        wp_send_json_success([
            'message' => __('Solicitud enviada con éxito.', 'urbandog'),
            'booking_id' => $booking_id,
            'pet_id' => $pets[0] ?? null
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
        $new_status = sanitize_text_field($_POST['status'] ?? ''); // accepted, rejected, completed

        if (!$walker_id || !UD_Roles::is_walker($walker_id)) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $walker_profile_id = UD_Roles::get_walker_profile_id($walker_id);
        $current_walker = (int) get_post_meta($booking_id, 'ud_booking_walker_id', true);
        if ($current_walker !== $walker_profile_id) {
            wp_send_json_error(['message' => __('Esta reserva no te pertenece.', 'urbandog')]);
        }

        if (!in_array($new_status, ['accepted', 'rejected', 'completed'], true)) {
            wp_send_json_error(['message' => __('Estado inválido.', 'urbandog')]);
        }

        update_post_meta($booking_id, 'ud_booking_status', $new_status);

        // If completed, trigger transaction creation or Meet & Greet logic
        if ($new_status === 'completed') {
            $booking_type = get_post_meta($booking_id, 'ud_booking_type', true) ?: 'walk';

            if ($booking_type === 'meetgreet') {
                $pets = get_post_meta($booking_id, 'ud_booking_pets', true);
                $walker_id_meta = (int) get_post_meta($booking_id, 'ud_booking_walker_id', true);
                $profile_id = UD_Roles::get_walker_profile_id($walker_id_meta);

                foreach ((array) $pets as $pet_id) {
                    UD_MeetGreet::update_approval((int) $pet_id, (int) $profile_id, 'pending_approval', $booking_id);
                }
            } else {
                UD_Payments::create_transaction($booking_id);
            }
        }

        $status_labels = [
            'accepted' => __('aceptada', 'urbandog'),
            'rejected' => __('rechazada', 'urbandog'),
            'completed' => __('completada', 'urbandog'),
        ];

        wp_send_json_success(['message' => sprintf(__('Reserva %s.', 'urbandog'), $status_labels[$new_status])]);
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
