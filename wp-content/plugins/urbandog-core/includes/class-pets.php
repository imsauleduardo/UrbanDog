<?php
/**
 * UrbanDog Pets
 *
 * Handles pet management (CRUD) for owners.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Pets
{

    /**
     * Initialize pet-related hooks.
     */
    public static function init(): void
    {
        add_action('wp_ajax_ud_add_pet', [__CLASS__, 'handle_add_pet']);
        add_action('wp_ajax_ud_update_pet', [__CLASS__, 'handle_update_pet']);
        add_action('wp_ajax_ud_delete_pet', [__CLASS__, 'handle_delete_pet']);
        add_action('wp_ajax_ud_get_pets', [__CLASS__, 'handle_get_pets']);
    }

    /**
     * Handle AJAX getting pets for current owner.
     */
    public static function handle_get_pets(): void
    {
        $user_id = get_current_user_id();
        if (!$user_id || !UD_Roles::is_owner($user_id)) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $pets = get_posts([
            'post_type' => 'ud_pet',
            'post_status' => 'publish',
            'author' => $user_id,
            'numberposts' => -1,
        ]);

        $data = [];
        foreach ($pets as $pet) {
            $data[] = [
                'id' => $pet->ID,
                'name' => get_post_meta($pet->ID, 'ud_pet_name', true) ?: $pet->post_title,
                'breed' => get_post_meta($pet->ID, 'ud_pet_breed', true),
                'age' => (int) get_post_meta($pet->ID, 'ud_pet_age', true),
                'weight' => (float) get_post_meta($pet->ID, 'ud_pet_weight', true),
                'temperament' => get_post_meta($pet->ID, 'ud_pet_temperament', true),
                'needs' => get_post_meta($pet->ID, 'ud_pet_special_needs', true),
                'vaccines' => get_post_meta($pet->ID, 'ud_pet_vaccines', true),
                'image' => get_the_post_thumbnail_url($pet->ID, 'medium') ?: '',
            ];
        }

        wp_send_json_success($data);
    }

    /**
     * Handle AJAX adding a pet.
     */
    public static function handle_add_pet(): void
    {
        check_ajax_referer('ud_pet_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id || !UD_Roles::is_owner($user_id)) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $name = sanitize_text_field($_POST['name'] ?? '');
        $breed = sanitize_text_field($_POST['breed'] ?? '');
        $temperament = sanitize_text_field($_POST['temperament'] ?? '');
        $age = (int) ($_POST['age'] ?? 0);
        $weight = (float) ($_POST['weight'] ?? 0);
        $needs = sanitize_textarea_field($_POST['needs'] ?? '');
        $vaccines = sanitize_textarea_field($_POST['vaccines'] ?? '');

        if (empty($name)) {
            wp_send_json_error(['message' => __('El nombre es obligatorio.', 'urbandog')]);
        }

        $pet_id = wp_insert_post([
            'post_title' => $name,
            'post_type' => 'ud_pet',
            'post_status' => 'publish',
            'post_author' => $user_id,
        ]);

        if (is_wp_error($pet_id)) {
            wp_send_json_error(['message' => $pet_id->get_error_message()]);
        }

        self::save_pet_meta($pet_id, $name, $breed, $temperament, $age, $weight, $needs, $vaccines);
        self::handle_pet_image_upload($pet_id);

        wp_send_json_success(['message' => __('Mascota agregada.', 'urbandog'), 'pet_id' => $pet_id]);
    }

    /**
     * Handle AJAX updating a pet.
     */
    public static function handle_update_pet(): void
    {
        check_ajax_referer('ud_pet_nonce', 'nonce');

        $user_id = get_current_user_id();
        $pet_id = (int) ($_POST['pet_id'] ?? 0);

        if (!$user_id || !$pet_id) {
            wp_send_json_error(['message' => __('Datos inválidos.', 'urbandog')]);
        }

        $pet = get_post($pet_id);
        if (!$pet || (int) $pet->post_author !== $user_id || $pet->post_type !== 'ud_pet') {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $name = sanitize_text_field($_POST['name'] ?? '');
        $breed = sanitize_text_field($_POST['breed'] ?? '');
        $temperament = sanitize_text_field($_POST['temperament'] ?? '');
        $age = (int) ($_POST['age'] ?? 0);
        $weight = (float) ($_POST['weight'] ?? 0);
        $needs = sanitize_textarea_field($_POST['needs'] ?? '');
        $vaccines = sanitize_textarea_field($_POST['vaccines'] ?? '');

        if (empty($name)) {
            wp_send_json_error(['message' => __('El nombre es obligatorio.', 'urbandog')]);
        }

        wp_update_post([
            'ID' => $pet_id,
            'post_title' => $name,
        ]);

        self::save_pet_meta($pet_id, $name, $breed, $temperament, $age, $weight, $needs, $vaccines);
        self::handle_pet_image_upload($pet_id);

        wp_send_json_success(['message' => __('Información actualizada.', 'urbandog')]);
    }

    /**
     * Helper to save pet metadata.
     */
    private static function save_pet_meta($pet_id, $name, $breed, $temperament, $age, $weight, $needs, $vaccines): void
    {
        update_post_meta($pet_id, 'ud_pet_name', $name);
        update_post_meta($pet_id, 'ud_pet_breed', $breed);
        update_post_meta($pet_id, 'ud_pet_temperament', $temperament);
        update_post_meta($pet_id, 'ud_pet_age', $age);
        update_post_meta($pet_id, 'ud_pet_weight', $weight);
        update_post_meta($pet_id, 'ud_pet_special_needs', $needs);
        update_post_meta($pet_id, 'ud_pet_vaccines', $vaccines);
    }

    /**
     * Helper to handle image upload for a pet.
     */
    private static function handle_pet_image_upload($pet_id): void
    {
        if (!empty($_FILES['image']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('image', $pet_id);

            if (!is_wp_error($attachment_id)) {
                set_post_thumbnail($pet_id, $attachment_id);
            }
        }
    }

    /**
     * Handle AJAX deleting a pet.
     */
    public static function handle_delete_pet(): void
    {
        check_ajax_referer('ud_pet_nonce', 'nonce');

        $user_id = get_current_user_id();
        $pet_id = (int) ($_POST['pet_id'] ?? 0);

        if (!$user_id || !$pet_id) {
            wp_send_json_error(['message' => __('Datos inválidos.', 'urbandog')]);
        }

        $pet = get_post($pet_id);
        if (!$pet || (int) $pet->post_author !== $user_id || $pet->post_type !== 'ud_pet') {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        wp_delete_post($pet_id, true);

        wp_send_json_success(['message' => __('Mascota eliminada.', 'urbandog')]);
    }
}
