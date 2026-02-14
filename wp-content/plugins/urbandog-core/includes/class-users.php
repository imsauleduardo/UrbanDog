<?php
/**
 * UrbanDog Users
 *
 * Handles registration, profile updates, and user meta management for owners and walkers.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Users
{

    /**
     * Initialize user-related hooks.
     */
    public static function init(): void
    {
        add_action('wp_ajax_ud_register_user', [__CLASS__, 'handle_registration']);
        add_action('wp_ajax_nopriv_ud_register_user', [__CLASS__, 'handle_registration']);

        add_action('wp_ajax_ud_update_profile', [__CLASS__, 'handle_profile_update']);
    }

    /**
     * Handle AJAX user registration.
     */
    public static function handle_registration(): void
    {
        check_ajax_referer('ud_registration_nonce', 'nonce');

        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $role = sanitize_text_field($_POST['role'] ?? ''); // ud_owner or ud_walker
        $phone = sanitize_text_field($_POST['phone'] ?? '');

        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Email inválido.', 'urbandog')]);
        }

        if (email_exists($email)) {
            wp_send_json_error(['message' => __('Este email ya está registrado.', 'urbandog')]);
        }

        if (strlen($password) < 8) {
            wp_send_json_error(['message' => __('La contraseña debe tener al menos 8 caracteres.', 'urbandog')]);
        }

        if (!in_array($role, ['ud_owner', 'ud_walker'], true)) {
            wp_send_json_error(['message' => __('Rol inválido.', 'urbandog')]);
        }

        $user_id = wp_create_user($email, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }

        // Update user data
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => $role,
        ]);

        // Custom meta
        update_user_meta($user_id, 'ud_phone', $phone);

        if ($role === 'ud_walker') {
            update_user_meta($user_id, 'ud_walker_verification_status', 'pending');

            // Create a shadow Walker Profile CPT for this user
            $profile_id = wp_insert_post([
                'post_title' => $first_name . ' ' . $last_name,
                'post_type' => 'ud_walker_profile',
                'post_status' => 'publish',
                'post_author' => $user_id,
            ]);

            update_user_meta($user_id, 'ud_walker_profile_id', $profile_id);
        }

        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        $redirect = $role === 'ud_owner' ? home_url('/dashboard/owner/') : home_url('/dashboard/walker/');

        wp_send_json_success([
            'message' => __('Registro exitoso.', 'urbandog'),
            'redirect' => $redirect,
        ]);
    }

    /**
     * Handle Profile Update (Dashboard).
     */
    public static function handle_profile_update(): void
    {
        check_ajax_referer('ud_profile_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');

        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ]);

        update_user_meta($user_id, 'ud_phone', $phone);

        // If walker, update their shadow CPT title
        if (UD_Roles::is_walker($user_id)) {
            $profile_id = get_user_meta($user_id, 'ud_walker_profile_id', true);
            if ($profile_id) {
                wp_update_post([
                    'ID' => $profile_id,
                    'post_title' => $first_name . ' ' . $last_name,
                ]);
            }
        }

        wp_send_json_success(['message' => __('Perfil actualizado.', 'urbandog')]);
    }
}
