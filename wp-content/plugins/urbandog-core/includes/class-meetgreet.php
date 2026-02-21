<?php
/**
 * UrbanDog Meet & Greet
 *
 * Handles the logic for Meet & Greet requirements and pet approvals.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_MeetGreet
{
    /**
     * Initialize hooks.
     */
    public static function init(): void
    {
        // AJAX handlers for approval/rejection will be added in class-walkers or here
        add_action('wp_ajax_ud_pet_approval', [__CLASS__, 'handle_pet_approval']);
    }

    /**
     * Check if walker requires Meet & Greet.
     */
    public static function walker_requires_meetgreet(int $walker_id): bool
    {
        return get_post_meta($walker_id, 'ud_walker_requires_meetgreet', true) === 'yes';
    }

    /**
     * Check if pet is approved for walker.
     */
    public static function is_pet_approved(int $pet_id, int $walker_id): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ud_pet_walker_approvals';

        // Check if table exists first (to avoid errors during development)
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }

        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT status FROM $table WHERE pet_id = %d AND walker_id = %d",
            $pet_id,
            $walker_id
        ));

        return $status === 'approved';
    }

    /**
     * Check if pet needs Meet & Greet with walker.
     */
    public static function needs_meetgreet(int $pet_id, int $walker_id): bool
    {
        if (!self::walker_requires_meetgreet($walker_id)) {
            return false; // Walker doesn't require it
        }

        return !self::is_pet_approved($pet_id, $walker_id);
    }

    /**
     * Update or create approval record.
     */
    public static function update_approval(int $pet_id, int $walker_id, string $status, int $booking_id = 0): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ud_pet_walker_approvals';

        // Ensure table exists before proceeding
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            self::create_table();
        }

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE pet_id = %d AND walker_id = %d",
            $pet_id,
            $walker_id
        ));

        $data = [
            'status' => $status,
            'updated_at' => current_time('mysql'),
        ];

        if ($booking_id) {
            $data['meetgreet_booking_id'] = $booking_id;
        }

        if ($status === 'approved') {
            $data['approved_at'] = current_time('mysql');
        } elseif ($status === 'rejected') {
            $data['rejected_at'] = current_time('mysql');
        }

        if ($existing) {
            $wpdb->update($table, $data, ['id' => $existing]);
        } else {
            $data['pet_id'] = $pet_id;
            $data['walker_id'] = $walker_id;
            $wpdb->insert($table, $data);
        }
    }

    /**
     * Handle AJAX pet approval/rejection by walker.
     */
    public static function handle_pet_approval(): void
    {
        check_ajax_referer('ud_walker_nonce', 'nonce');

        if (!UD_Roles::is_walker(get_current_user_id())) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $pet_id = (int) ($_POST['pet_id'] ?? 0);
        $walker_id = (int) ($_POST['walker_id'] ?? 0); // This is the walker profile ID
        $approval_action = sanitize_text_field($_POST['approval_action'] ?? ''); // approve, reject
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (!$pet_id || !$walker_id || !in_array($approval_action, ['approve', 'reject'], true)) {
            wp_send_json_error(['message' => __('Datos inválidos.', 'urbandog')]);
        }

        $status = ($approval_action === 'approve') ? 'approved' : 'rejected';
        self::update_approval($pet_id, $walker_id, $status);

        // Add notes if provided
        if ($notes) {
            global $wpdb;
            $table = $wpdb->prefix . 'ud_pet_walker_approvals';
            $wpdb->update($table, ['notes' => $notes], ['pet_id' => $pet_id, 'walker_id' => $walker_id]);
        }

        $message = ($approval_action === 'approve') ? __('Mascota aprobada con éxito.', 'urbandog') : __('Mascota rechazada.', 'urbandog');
        wp_send_json_success(['message' => $message]);
    }

    /**
     * Create database table.
     */
    public static function create_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ud_pet_walker_approvals';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            pet_id BIGINT(20) UNSIGNED NOT NULL,
            walker_id BIGINT(20) UNSIGNED NOT NULL,
            status ENUM('pending_meetgreet', 'meetgreet_scheduled', 'pending_approval', 'approved', 'rejected') DEFAULT 'pending_meetgreet',
            meetgreet_booking_id BIGINT(20) UNSIGNED NULL,
            approved_at DATETIME NULL,
            rejected_at DATETIME NULL,
            notes TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_pet_walker (pet_id, walker_id),
            KEY idx_walker (walker_id),
            KEY idx_pet (pet_id),
            KEY idx_status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
