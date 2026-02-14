<?php
/**
 * UrbanDog Gamification
 *
 * Handles levels, badges, and progress for walkers.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Gamification
{

    /**
     * Initialize gamification hooks.
     */
    public static function init(): void
    {
        add_action('ud_booking_completed', [__CLASS__, 'update_walker_progress']);
    }

    /**
     * Update walker level and badges after a booking is completed.
     */
    public static function update_walker_progress(int $booking_id): void
    {
        $walker_id = (int) get_post_meta($booking_id, 'ud_booking_walker_id', true);
        if (!$walker_id) {
            return;
        }

        // Count completed bookings for this walker
        $completed_bookings = count(get_posts([
            'post_type' => 'ud_booking',
            'meta_query' => [
                ['get_key' => 'ud_booking_walker_id', 'value' => $walker_id],
                ['get_key' => 'ud_booking_status', 'value' => 'completed'],
            ],
            'numberposts' => -1,
        ]));

        $level = 'Novato';
        if ($completed_bookings >= 50) {
            $level = 'Maestro';
        } elseif ($completed_bookings >= 10) {
            $level = 'Experto';
        }

        update_user_meta($walker_id, 'ud_walker_level', $level);
        update_user_meta($walker_id, 'ud_walker_total_walks', $completed_bookings);

        // Sync with Profile CPT for public view
        $profile_id = get_user_meta($walker_id, 'ud_walker_profile_id', true);
        if ($profile_id) {
            update_post_meta($profile_id, 'ud_walker_level', $level);
        }
    }
}
