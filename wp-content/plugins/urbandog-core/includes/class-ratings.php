<?php
/**
 * UrbanDog Ratings System
 *
 * Handles bidirectional ratings between owners and walkers.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Ratings
{
    /**
     * Initialize ratings hooks.
     */
    public static function init(): void
    {
        add_action('init', [__CLASS__, 'register_rating_post_type']);
        add_action('wp_ajax_ud_submit_rating', [__CLASS__, 'handle_submit_rating']);
        add_action('wp_ajax_ud_get_user_ratings', [__CLASS__, 'handle_get_user_ratings']);
    }

    /**
     * Register ud_rating custom post type.
     */
    public static function register_rating_post_type(): void
    {
        register_post_type('ud_rating', [
            'labels' => [
                'name' => __('Calificaciones', 'urbandog'),
                'singular_name' => __('Calificación', 'urbandog'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'urbandog',
            'capability_type' => 'post',
            'supports' => ['title'],
            'has_archive' => false,
        ]);
    }

    /**
     * Handle rating submission via AJAX.
     */
    public static function handle_submit_rating(): void
    {
        check_ajax_referer('ud_rating_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        $to_user_id = (int) ($_POST['to_user_id'] ?? 0);
        $score = (int) ($_POST['score'] ?? 0);
        $comment = sanitize_textarea_field($_POST['comment'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? '');

        // Validate inputs
        if (!$booking_id || !$to_user_id || $score < 1 || $score > 5) {
            wp_send_json_error(['message' => __('Datos inválidos.', 'urbandog')]);
        }

        if (!in_array($type, ['owner_to_walker', 'walker_to_owner'], true)) {
            wp_send_json_error(['message' => __('Tipo de calificación inválido.', 'urbandog')]);
        }

        // Verify booking exists and user is authorized
        $booking = get_post($booking_id);
        if (!$booking || $booking->post_type !== 'ud_booking') {
            wp_send_json_error(['message' => __('Reserva no encontrada.', 'urbandog')]);
        }

        $booking_owner = (int) get_post_meta($booking_id, 'ud_booking_owner_id', true);
        $booking_walker = (int) get_post_meta($booking_id, 'ud_booking_walker_id', true);

        // Verify user is part of this booking
        if ($type === 'owner_to_walker' && $user_id !== $booking_owner) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        if ($type === 'walker_to_owner' && $user_id !== $booking_walker) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        // Check if rating already exists
        $existing = get_posts([
            'post_type' => 'ud_rating',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'ud_rating_booking_id', 'value' => $booking_id],
                ['key' => 'ud_rating_from_user', 'value' => $user_id],
                ['key' => 'ud_rating_type', 'value' => $type],
            ],
            'posts_per_page' => 1,
        ]);

        if (!empty($existing)) {
            wp_send_json_error(['message' => __('Ya has calificado este paseo.', 'urbandog')]);
        }

        // Create rating
        $rating_id = wp_insert_post([
            'post_type' => 'ud_rating',
            'post_title' => sprintf('Rating #%d - %s', $booking_id, $type),
            'post_status' => 'publish',
        ]);

        if (is_wp_error($rating_id)) {
            wp_send_json_error(['message' => __('Error al guardar calificación.', 'urbandog')]);
        }

        // Save meta
        update_post_meta($rating_id, 'ud_rating_booking_id', $booking_id);
        update_post_meta($rating_id, 'ud_rating_from_user', $user_id);
        update_post_meta($rating_id, 'ud_rating_to_user', $to_user_id);
        update_post_meta($rating_id, 'ud_rating_score', $score);
        update_post_meta($rating_id, 'ud_rating_comment', $comment);
        update_post_meta($rating_id, 'ud_rating_type', $type);
        update_post_meta($rating_id, 'ud_rating_date', current_time('mysql'));

        wp_send_json_success(['message' => __('¡Gracias por tu calificación!', 'urbandog')]);
    }

    /**
     * Get ratings for a specific user.
     */
    public static function handle_get_user_ratings(): void
    {
        $user_id = (int) ($_GET['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error(['message' => __('Usuario inválido.', 'urbandog')]);
        }

        $ratings = self::get_user_ratings($user_id);
        wp_send_json_success($ratings);
    }

    /**
     * Get all ratings for a user.
     *
     * @param int $user_id User ID to get ratings for.
     * @return array Array of ratings with average and count.
     */
    public static function get_user_ratings(int $user_id): array
    {
        $ratings_posts = get_posts([
            'post_type' => 'ud_rating',
            'meta_key' => 'ud_rating_to_user',
            'meta_value' => $user_id,
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $ratings = [];
        $total_score = 0;

        foreach ($ratings_posts as $rating) {
            $score = (int) get_post_meta($rating->ID, 'ud_rating_score', true);
            $from_user_id = (int) get_post_meta($rating->ID, 'ud_rating_from_user', true);
            $from_user = get_userdata($from_user_id);

            $ratings[] = [
                'id' => $rating->ID,
                'score' => $score,
                'comment' => get_post_meta($rating->ID, 'ud_rating_comment', true),
                'date' => get_post_meta($rating->ID, 'ud_rating_date', true),
                'from_user_name' => $from_user ? $from_user->display_name : __('Usuario', 'urbandog'),
                'pet_name' => get_post_meta($rating->ID, 'ud_rating_pet_name', true),
                'type' => get_post_meta($rating->ID, 'ud_rating_type', true),
            ];

            $total_score += $score;
        }

        $count = count($ratings);
        $average = $count > 0 ? round($total_score / $count, 1) : 0;

        return [
            'average' => $average,
            'count' => $count,
            'ratings' => $ratings,
        ];
    }

    /**
     * Check if user can rate a booking.
     *
     * @param int $booking_id Booking ID.
     * @param int $user_id User ID.
     * @param string $type Rating type.
     * @return bool True if can rate, false otherwise.
     */
    public static function can_rate_booking(int $booking_id, int $user_id, string $type): bool
    {
        // Check if booking is completed
        $status = get_post_meta($booking_id, 'ud_booking_status', true);
        if ($status !== 'completed') {
            return false;
        }

        // Check if already rated
        $existing = get_posts([
            'post_type' => 'ud_rating',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'ud_rating_booking_id', 'value' => $booking_id],
                ['key' => 'ud_rating_from_user', 'value' => $user_id],
                ['key' => 'ud_rating_type', 'value' => $type],
            ],
            'posts_per_page' => 1,
        ]);

        return empty($existing);
    }
}
