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
        $min_price = (float) ($_POST['min_price'] ?? 0);
        $max_price = (float) ($_POST['max_price'] ?? 0);
        $modality = sanitize_text_field($_POST['modality'] ?? 'individual'); // individual, group
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        $requested_pet_sizes = sanitize_text_field($_POST['pet_size'] ?? '');
        $dog_count = (int) ($_POST['dog_count'] ?? 0);
        $puppy_count = (int) ($_POST['puppy_count'] ?? 0);
        $total_pets = $dog_count + $puppy_count;

        $requested_dates = sanitize_text_field($_POST['dates'] ?? '');
        $requested_slots = sanitize_text_field($_POST['time_slots'] ?? '');

        $args = [
            'post_type' => 'ud_walker_profile',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => [
                'relation' => 'AND',
            ],
        ];

        if (!empty($zone)) {
            $args['meta_query'][] = [
                'key' => 'ud_walker_zone',
                'value' => $zone,
                'compare' => 'LIKE',
            ];
        }

        // --- Price Filtering ---
        $price_key = $modality === 'group' ? 'ud_walker_price_group_30' : 'ud_walker_price_30';

        if ($min_price > 0 && $max_price > 0) {
            $args['meta_query'][] = [
                'key' => $price_key,
                'value' => [$min_price, $max_price],
                'type' => 'DECIMAL',
                'compare' => 'BETWEEN',
            ];
        } elseif ($max_price > 0) {
            $args['meta_query'][] = [
                'key' => $price_key,
                'value' => $max_price,
                'type' => 'DECIMAL',
                'compare' => '<=',
            ];
        } elseif ($min_price > 0) {
            $args['meta_query'][] = [
                'key' => $price_key,
                'value' => $min_price,
                'type' => 'DECIMAL',
                'compare' => '>=',
            ];
        }

        $profiles = get_posts($args);
        $results = [];

        // Pre-parse requested filters
        $filters_dates = !empty($requested_dates) ? explode(',', $requested_dates) : [];
        $filters_slots = !empty($requested_slots) ? explode(',', $requested_slots) : [];
        $filters_pet_sizes = !empty($requested_pet_sizes) ? explode(',', $requested_pet_sizes) : [];

        // Day mapping: search.js (dom, lun...) -> settings (Dom, Lun...)
        $day_conv = [
            'dom' => 'Dom',
            'lun' => 'Lun',
            'mar' => 'Mar',
            'mié' => 'Mie',
            'jue' => 'Jue',
            'vie' => 'Vie',
            'sáb' => 'Sab'
        ];

        // Slot mapping: name -> [start_min, end_min]
        $slot_times = [
            'morning' => [360, 660],  // 06:00 - 11:00
            'midday' => [660, 900],  // 11:00 - 15:00
            'afternoon' => [900, 1320], // 15:00 - 22:00
        ];

        foreach ($profiles as $profile) {
            $user_id = $profile->post_author;
            $user = get_userdata($user_id);

            if (!UD_Roles::is_walker_verified($user_id) && !in_array('administrator', $user->roles)) {
                continue;
            }

            // --- Capacity Filtering ---
            if ($total_pets > 0) {
                $max_dogs = (int) get_post_meta($profile->ID, 'ud_walker_max_dogs', true) ?: 1;
                if ($total_pets > $max_dogs) {
                    continue;
                }
            }

            // --- Pet Size Filtering ---
            if (!empty($filters_pet_sizes)) {
                $accepted_sizes = json_decode(get_post_meta($profile->ID, 'ud_walker_pet_sizes', true), true) ?: [];

                // Walker must accept ALL requested sizes
                foreach ($filters_pet_sizes as $req_size) {
                    if (!in_array($req_size, $accepted_sizes)) {
                        continue 2; // Next walker
                    }
                }
            }

            // --- Availability Filtering ---
            if (!empty($filters_dates)) {
                $schedules = json_decode(get_post_meta($profile->ID, 'ud_walker_custom_schedules', true), true) ?: [];
                $is_available = false;

                foreach ($filters_dates as $d) {
                    $key = $day_conv[$d] ?? null;
                    if (!$key || empty($schedules[$key]) || strtolower($schedules[$key]) === 'cerrado')
                        continue;

                    // Check time slots if specified
                    if (!empty($filters_slots)) {
                        $walker_range = $schedules[$key]; // e.g., "08:00-18:00"
                        if (preg_match('/(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/', $walker_range, $matches)) {
                            $w_start = self::to_minutes($matches[1]);
                            $w_end = self::to_minutes($matches[2]);

                            foreach ($filters_slots as $slot) {
                                if (!isset($slot_times[$slot]))
                                    continue;
                                $s_start = $slot_times[$slot][0];
                                $s_end = $slot_times[$slot][1];

                                // Overlap logic: max(start) < min(end)
                                if (max($w_start, $s_start) < min($w_end, $s_end)) {
                                    $is_available = true;
                                    break 2;
                                }
                            }
                        }
                    } else {
                        // Only day filtered, and walker is open
                        $is_available = true;
                        break;
                    }
                }

                if (!$is_available)
                    continue;
            }

            $results[] = [
                'id' => $profile->ID,
                'user_id' => $user_id,
                'name' => $profile->post_title,
                'image' => get_the_post_thumbnail_url($profile->ID, 'medium') ?: null,
                'zone' => get_post_meta($profile->ID, 'ud_walker_zone', true),
                'price_30' => get_post_meta($profile->ID, 'ud_walker_price_30', true),
                'price_60' => get_post_meta($profile->ID, 'ud_walker_price_60', true),
                'rating' => 4.5,
                'reviews' => 12,
                'badges' => ['Experto', 'Verificado'],
                'url' => get_permalink($profile->ID),
                'lat' => get_post_meta($profile->ID, 'ud_walker_lat', true),
                'lng' => get_post_meta($profile->ID, 'ud_walker_lng', true),
                'requires_meetgreet' => UD_MeetGreet::walker_requires_meetgreet($profile->ID),
            ];
        }

        wp_send_json_success($results);
    }

    /**
     * Helper to convert "HH:MM" to minutes from midnight.
     */
    private static function to_minutes($time_str): int
    {
        $parts = explode(':', $time_str);
        return (intval($parts[0]) * 60) + intval($parts[1]);
    }
}
