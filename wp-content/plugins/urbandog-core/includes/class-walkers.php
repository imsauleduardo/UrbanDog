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
        add_action('wp_ajax_ud_save_walker_settings', [__CLASS__, 'handle_save_settings']);
    }

    /**
     * Handle AJAX walker profile update.
     */
    public static function handle_save_settings(): void
    {
        check_ajax_referer('ud_walker_settings_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id || !UD_Roles::is_walker($user_id)) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $profile_id = UD_Roles::get_walker_profile_id($user_id);
        if (!$profile_id) {
            wp_send_json_error(['message' => __('Perfil no encontrado.', 'urbandog')]);
        }

        // 1. Save Rates (Validation: must be positive)
        $rate_ind_30 = max(0, (float) ($_POST['rate_ind_30'] ?? 0));
        $rate_ind_60 = max(0, (float) ($_POST['rate_ind_60'] ?? 0));
        $rate_grp_30 = max(0, (float) ($_POST['rate_grp_30'] ?? 0));
        $rate_grp_60 = max(0, (float) ($_POST['rate_grp_60'] ?? 0));

        update_post_meta($profile_id, 'ud_walker_price_30', $rate_ind_30);
        update_post_meta($profile_id, 'ud_walker_price_60', $rate_ind_60);
        update_post_meta($profile_id, 'ud_walker_price_group_30', $rate_grp_30);
        update_post_meta($profile_id, 'ud_walker_price_group_60', $rate_grp_60);

        // 2. Save Schedules
        if (isset($_POST['schedule']) && is_array($_POST['schedule'])) {
            $schedules = [];
            foreach ($_POST['schedule'] as $day => $range) {
                $schedules[sanitize_text_field($day)] = sanitize_text_field($range);
            }
            update_post_meta($profile_id, 'ud_walker_custom_schedules', json_encode($schedules));
        }

        // 3. Save Bio & Max Dogs
        if (isset($_POST['bio'])) {
            wp_update_post([
                'ID' => $profile_id,
                'post_content' => wp_kses_post($_POST['bio']),
            ]);
        }
        $max_dogs = max(1, (int) ($_POST['max_dogs'] ?? 1));
        update_post_meta($profile_id, 'ud_walker_max_dogs', $max_dogs);

        // 3.1 Save Accepted Pet Sizes
        $accepted_sizes = isset($_POST['accepted_sizes']) ? array_map('sanitize_text_field', (array) $_POST['accepted_sizes']) : [];
        update_post_meta($profile_id, 'ud_walker_pet_sizes', json_encode($accepted_sizes));

        // 4. Save Zone & Location
        if (isset($_POST['zone'])) {
            update_post_meta($profile_id, 'ud_walker_zone', sanitize_text_field($_POST['zone']));
        }
        if (isset($_POST['lat'])) {
            update_post_meta($profile_id, 'ud_walker_lat', (float) $_POST['lat']);
        }
        if (isset($_POST['lng'])) {
            update_post_meta($profile_id, 'ud_walker_lng', (float) $_POST['lng']);
        }
        if (isset($_POST['radius_km'])) {
            $radius = max(0.1, (float) $_POST['radius_km']);
            update_post_meta($profile_id, 'ud_walker_radius_km', $radius);
        }

        // 5. Save Meet & Greet Policy
        $requires_meetgreet = isset($_POST['requires_meetgreet']) && $_POST['requires_meetgreet'] === 'yes' ? 'yes' : 'no';
        update_post_meta($profile_id, 'ud_walker_requires_meetgreet', $requires_meetgreet);

        wp_send_json_success(['message' => __('¡Ajustes actualizados con éxito!', 'urbandog')]);
    }
}
