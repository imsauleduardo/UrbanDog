<?php
/**
 * UrbanDog Search
 *
 * Handles walker discovery and AJAX filtering.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Search
{

    /**
     * Initialize search-related hooks.
     */
    public static function init(): void
    {
        add_action('wp_ajax_ud_search_walkers', [__CLASS__, 'handle_search']);
        add_action('wp_ajax_nopriv_ud_search_walkers', [__CLASS__, 'handle_search']);
    }

    /**
     * Handle AJAX walker search.
     */
    public static function handle_search(): void
    {
        $zone = sanitize_text_field($_POST['zone'] ?? '');
        $price_max = (float) ($_POST['price_max'] ?? 0);
        $modality = sanitize_text_field($_POST['modality'] ?? 'individual'); // individual, group
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        $pet_size = sanitize_text_field($_POST['pet_size'] ?? '');

        $args = [
            'post_type' => 'ud_walker_profile',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => [
                'relation' => 'AND',
            ],
        ];

        // Filter by verification status (user meta via author id)
        // Note: For simplicity, we only show verified walkers. 
        // We'll need a way to filter CPT by user meta. We can do it post-query or pre-query.
        // Let's do a JOIN-friendly meta query if we can, or just get all and filter in PHP for MVP.

        if (!empty($zone)) {
            $args['meta_query'][] = [
                'key' => 'ud_walker_zone',
                'value' => $zone,
                'compare' => 'LIKE',
            ];
        }

        if ($price_max > 0) {
            $price_key = $modality === 'group' ? 'ud_walker_price_group_30' : 'ud_walker_price_30';
            $args['meta_query'][] = [
                'key' => $price_key,
                'value' => $price_max,
                'type' => 'DECIMAL',
                'compare' => '<=',
            ];
        }

        $profiles = get_posts($args);
        $results = [];

        foreach ($profiles as $profile) {
            $user_id = $profile->post_author;
            $user = get_userdata($user_id);

            // Verification check (Permissive for admins during testing)
            if (!UD_Roles::is_walker_verified($user_id) && !in_array('administrator', $user->roles)) {
                continue;
            }

            $results[] = [
                'id' => $profile->ID,
                'user_id' => $user_id,
                'name' => $profile->post_title,
                'image' => get_the_post_thumbnail_url($profile->ID, 'medium') ?: UD_PLUGIN_URL . 'assets/images/default-avatar.png',
                'zone' => get_post_meta($profile->ID, 'ud_walker_zone', true),
                'price_30' => get_post_meta($profile->ID, 'ud_walker_price_30', true),
                'price_60' => get_post_meta($profile->ID, 'ud_walker_price_60', true),
                'rating' => 4.5, // Mock for now
                'reviews' => 12,  // Mock for now
                'badges' => ['Experto', 'Verificado'], // Mock
                'url' => get_permalink($profile->ID),
                'lat' => get_post_meta($profile->ID, 'ud_walker_lat', true),
                'lng' => get_post_meta($profile->ID, 'ud_walker_lng', true),
            ];
        }

        wp_send_json_success($results);
    }
}
