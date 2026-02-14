<?php
/**
 * UrbanDog Reviews
 *
 * Handles bidirectional ratings and reviews.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Reviews
{

    /**
     * Initialize review-related hooks.
     */
    public static function init(): void
    {
        add_action('wp_ajax_ud_submit_review', [__CLASS__, 'handle_submit_review']);
    }

    /**
     * Handle AJAX review submission.
     */
    public static function handle_submit_review(): void
    {
        check_ajax_referer('ud_review_nonce', 'nonce');

        $author_id = get_current_user_id();
        $recipient_id = (int) ($_POST['recipient_id'] ?? 0);
        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = sanitize_textarea_field($_POST['comment'] ?? '');

        if (!$author_id || !$recipient_id || !$booking_id) {
            wp_send_json_error(['message' => __('Datos inválidos.', 'urbandog')]);
        }

        // Check if booking is completed
        $status = get_post_meta($booking_id, 'ud_booking_status', true);
        if ($status !== 'completed') {
            wp_send_json_error(['message' => __('Solo puedes calificar paseos completados.', 'urbandog')]);
        }

        $review_id = wp_insert_post([
            'post_title' => sprintf(__('Reseña para reserva #%d', 'urbandog'), $booking_id),
            'post_type' => 'ud_review',
            'post_status' => 'publish',
            'post_author' => $author_id,
            'post_content' => $comment,
        ]);

        update_post_meta($review_id, 'ud_review_recipient_id', $recipient_id);
        update_post_meta($review_id, 'ud_review_booking_id', $booking_id);
        update_post_meta($review_id, 'ud_review_rating', $rating);

        // Update walker average if recipient is walker
        if (UD_Roles::is_walker($recipient_id)) {
            self::update_walker_average_rating($recipient_id);
        }

        wp_send_json_success(['message' => __('Reseña enviada.', 'urbandog')]);
    }

    /**
     * Update average rating for a walker.
     */
    public static function update_walker_average_rating(int $walker_id): void
    {
        $reviews = get_posts([
            'post_type' => 'ud_review',
            'meta_key' => 'ud_review_recipient_id',
            'meta_value' => $walker_id,
            'numberposts' => -1,
        ]);

        if (empty($reviews)) {
            return;
        }

        $total_rating = 0;
        foreach ($reviews as $review) {
            $total_rating += (int) get_post_meta($review->ID, 'ud_review_rating', true);
        }

        $average = round($total_rating / count($reviews), 1);

        $profile_id = get_user_meta($walker_id, 'ud_walker_profile_id', true);
        if ($profile_id) {
            update_post_meta($profile_id, 'ud_walker_average_rating', $average);
            update_post_meta($profile_id, 'ud_walker_review_count', count($reviews));
        }
    }
}
