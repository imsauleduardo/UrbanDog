<?php
/**
 * UrbanDog Roles
 * 
 * Custom user roles: ud_owner (dueño de mascota) and ud_walker (paseador).
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Roles
{

    /**
     * Initialize role-related hooks.
     */
    public static function init(): void
    {
        add_action('template_redirect', [__CLASS__, 'redirect_after_login']);
        add_filter('login_redirect', [__CLASS__, 'custom_login_redirect'], 10, 3);
        add_action('show_user_profile', [__CLASS__, 'add_profile_fields']);
        add_action('edit_user_profile', [__CLASS__, 'add_profile_fields']);
        add_action('personal_options_update', [__CLASS__, 'save_profile_fields']);
        add_action('edit_user_profile_update', [__CLASS__, 'save_profile_fields']);
    }

    /**
     * Create custom roles on plugin activation.
     */
    public static function create_roles(): void
    {
        // Remove existing custom roles to ensure clean state
        remove_role('ud_owner');
        remove_role('ud_walker');

        // Dueño de mascota
        add_role('ud_owner', __('Dueño de Mascota', 'urbandog'), [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'upload_files' => true,
            // UrbanDog capabilities
            'ud_manage_pets' => true,
            'ud_book_walks' => true,
            'ud_write_reviews' => true,
            'ud_view_dashboard' => true,
        ]);

        // Paseador de perros
        add_role('ud_walker', __('Paseador de Perros', 'urbandog'), [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'upload_files' => true,
            // UrbanDog capabilities
            'ud_manage_walker_profile' => true,
            'ud_manage_bookings' => true,
            'ud_write_reviews' => true,
            'ud_view_dashboard' => true,
        ]);

        // Add UrbanDog caps to administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('ud_manage_pets');
            $admin->add_cap('ud_book_walks');
            $admin->add_cap('ud_manage_walker_profile');
            $admin->add_cap('ud_manage_bookings');
            $admin->add_cap('ud_write_reviews');
            $admin->add_cap('ud_view_dashboard');
            $admin->add_cap('ud_approve_walkers');
            $admin->add_cap('ud_confirm_payments');
            $admin->add_cap('ud_view_metrics');
            $admin->add_cap('ud_manage_disputes');
        }
    }

    /**
     * Redirect users to their dashboard after login.
     *
     * @param string           $redirect_to Default redirect URL.
     * @param string           $requested   Requested redirect URL.
     * @param \WP_User|\WP_Error $user      User object.
     * @return string Redirect URL.
     */
    public static function custom_login_redirect(string $redirect_to, string $requested, $user): string
    {
        if (!is_wp_error($user) && $user instanceof WP_User) {
            if (in_array('ud_owner', $user->roles, true)) {
                return home_url('/dashboard/owner/');
            }
            if (in_array('ud_walker', $user->roles, true)) {
                return home_url('/dashboard/walker/');
            }
            if (in_array('administrator', $user->roles, true)) {
                return admin_url();
            }
        }
        return $redirect_to;
    }

    /**
     * Redirect non-admin users away from wp-admin.
     */
    public static function redirect_after_login(): void
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            $user = wp_get_current_user();
            if ($user && !empty($user->roles)) {
                if (in_array('ud_owner', $user->roles, true) || in_array('ud_walker', $user->roles, true)) {
                    wp_safe_redirect(home_url('/dashboard/'));
                    exit;
                }
            }
        }
    }

    /**
     * Add custom profile fields in wp-admin user edit.
     *
     * @param \WP_User $user User object.
     */
    public static function add_profile_fields(WP_User $user): void
    {
        if (!in_array('ud_walker', $user->roles, true)) {
            return;
        }
        ?>
        <h3>
            <?php esc_html_e('Información del Paseador — UrbanDog', 'urbandog'); ?>
        </h3>
        <table class="form-table">
            <tr>
                <th><label>
                        <?php esc_html_e('Estado de verificación', 'urbandog'); ?>
                    </label></th>
                <td>
                    <?php
                    $status = get_user_meta($user->ID, 'ud_walker_verification_status', true);
                    $statuses = [
                        'pending' => __('Pendiente', 'urbandog'),
                        'approved' => __('Aprobado', 'urbandog'),
                        'rejected' => __('Rechazado', 'urbandog'),
                    ];
                    if (current_user_can('ud_approve_walkers')) {
                        echo '<select name="ud_walker_verification_status">';
                        foreach ($statuses as $key => $label) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($key),
                                selected($status, $key, false),
                                esc_html($label)
                            );
                        }
                        echo '</select>';
                    } else {
                        echo esc_html($statuses[$status] ?? __('Pendiente', 'urbandog'));
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><label>
                        <?php esc_html_e('DNI', 'urbandog'); ?>
                    </label></th>
                <td>
                    <?php
                    $dni = get_user_meta($user->ID, 'ud_walker_dni', true);
                    echo esc_html($dni ?: '—');
                    ?>
                </td>
            </tr>
            <tr>
                <th><label>
                        <?php esc_html_e('LinkedIn', 'urbandog'); ?>
                    </label></th>
                <td>
                    <?php
                    $linkedin = get_user_meta($user->ID, 'ud_walker_linkedin', true);
                    if ($linkedin) {
                        printf('<a href="%s" target="_blank">%s</a>', esc_url($linkedin), esc_html($linkedin));
                    } else {
                        echo '—';
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save custom profile fields.
     *
     * @param int $user_id User ID.
     */
    public static function save_profile_fields(int $user_id): void
    {
        if (!current_user_can('ud_approve_walkers')) {
            return;
        }

        if (isset($_POST['ud_walker_verification_status'])) {
            $status = sanitize_text_field(wp_unslash($_POST['ud_walker_verification_status']));
            if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
                update_user_meta($user_id, 'ud_walker_verification_status', $status);
            }
        }
    }

    /**
     * Check if a user is an owner.
     */
    public static function is_owner(int $user_id = 0): bool
    {
        $user = $user_id ? get_userdata($user_id) : wp_get_current_user();
        return $user && in_array('ud_owner', $user->roles, true);
    }

    /**
     * Check if a user is a walker.
     */
    public static function is_walker(int $user_id = 0): bool
    {
        $user = $user_id ? get_userdata($user_id) : wp_get_current_user();
        return $user && in_array('ud_walker', $user->roles, true);
    }

    /**
     * Check if a walker is verified.
     */
    public static function is_walker_verified(int $user_id = 0): bool
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        return get_user_meta($user_id, 'ud_walker_verification_status', true) === 'approved';
    }
}
