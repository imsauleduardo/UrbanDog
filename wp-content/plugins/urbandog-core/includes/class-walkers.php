<?php
/**
 * UrbanDog Walkers
 *
 * Handles walker profile updates, availability, and pricing.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Walkers
{

    /**
     * Initialize walker-related hooks.
     */
    public static function init(): void
    {
        add_action('wp_ajax_ud_update_walker_profile', [__CLASS__, 'handle_profile_update']);
    }

    /**
     * Handle AJAX walker profile update.
     */
    public static function handle_profile_update(): void
    {
        check_ajax_referer('ud_walker_profile_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id || !UD_Roles::is_walker($user_id)) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $profile_id = get_user_meta($user_id, 'ud_walker_profile_id', true);
        if (!$profile_id) {
            wp_send_json_error(['message' => __('Perfil no encontrado.', 'urbandog')]);
        }

        // Pricing
        update_post_meta($profile_id, 'ud_walker_price_30', (float) ($_POST['price_30'] ?? 0));
        update_post_meta($profile_id, 'ud_walker_price_60', (float) ($_POST['price_60'] ?? 0));
        update_post_meta($profile_id, 'ud_walker_price_group_30', (float) ($_POST['price_group_30'] ?? 0));
        update_post_meta($profile_id, 'ud_walker_price_group_60', (float) ($_POST['price_group_60'] ?? 0));

        // Info
        update_post_meta($profile_id, 'ud_walker_zone', sanitize_text_field($_POST['zone'] ?? ''));
        update_post_meta($profile_id, 'ud_walker_lat', sanitize_text_field($_POST['lat'] ?? ''));
        update_post_meta($profile_id, 'ud_walker_lng', sanitize_text_field($_POST['lng'] ?? ''));
        update_post_meta($profile_id, 'ud_walker_radius_km', (float) ($_POST['radius'] ?? 0));
        update_post_meta($profile_id, 'ud_walker_max_dogs', (int) ($_POST['max_dogs'] ?? 1));
        update_post_meta($profile_id, 'ud_walker_schedule', sanitize_textarea_field($_POST['schedule'] ?? ''));

        wp_send_json_success(['message' => __('Perfil de paseador actualizado.', 'urbandog')]);
    }
}
