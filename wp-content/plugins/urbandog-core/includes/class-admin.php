<?php
/**
 * UrbanDog Admin
 *
 * Admin menu, dashboard pages, walker approval, payment confirmation, and metrics.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Admin
{

    /**
     * Initialize admin hooks.
     */
    public static function init(): void
    {
        add_action('admin_menu', [__CLASS__, 'register_menus']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    /**
     * Register the UrbanDog admin menu.
     */
    public static function register_menus(): void
    {
        // Main menu
        add_menu_page(
            __('UrbanDog', 'urbandog'),
            __('UrbanDog', 'urbandog'),
            'manage_options',
            'urbandog',
            [__CLASS__, 'render_dashboard'],
            'dashicons-pets',
            25
        );

        // Sub-menus
        add_submenu_page('urbandog', __('Dashboard', 'urbandog'), __('Dashboard', 'urbandog'), 'manage_options', 'urbandog', [__CLASS__, 'render_dashboard']);

        add_submenu_page('urbandog', __('Aprobar Paseadores', 'urbandog'), __('Aprobar Paseadores', 'urbandog'), 'ud_approve_walkers', 'urbandog-walkers', [__CLASS__, 'render_walker_approval']);

        add_submenu_page('urbandog', __('Confirmar Pagos', 'urbandog'), __('Confirmar Pagos', 'urbandog'), 'ud_confirm_payments', 'urbandog-payments', [__CLASS__, 'render_payment_confirmation']);
    }

    /**
     * Enqueue admin CSS/JS.
     */
    public static function enqueue_admin_assets(string $hook): void
    {
        if (strpos($hook, 'urbandog') === false) {
            return;
        }

        wp_enqueue_style('ud-admin', UD_PLUGIN_URL . 'assets/css/admin.css', [], UD_VERSION);
    }

    /**
     * Render main dashboard.
     */
    public static function render_dashboard(): void
    {
        $total_owners = count(get_users(['role' => 'ud_owner']));
        $total_walkers = count(get_users(['role' => 'ud_walker']));
        $pending_walkers = count(get_users([
            'role' => 'ud_walker',
            'meta_key' => 'ud_walker_verification_status',
            'meta_value' => 'pending',
        ]));

        $total_bookings = wp_count_posts('ud_booking')->publish ?? 0;
        $total_pets = wp_count_posts('ud_pet')->publish ?? 0;
        ?>
        <div class="wrap">
            <h1>🐕
                <?php esc_html_e('UrbanDog — Dashboard', 'urbandog'); ?>
            </h1>

            <div class="ud-admin-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <div class="ud-card"
                    style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #4CAF50;">
                    <h3 style="margin: 0; color: #666;">
                        <?php esc_html_e('Dueños', 'urbandog'); ?>
                    </h3>
                    <p style="font-size: 2em; margin: 10px 0 0; font-weight: bold;">
                        <?php echo (int) $total_owners; ?>
                    </p>
                </div>
                <div class="ud-card"
                    style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #2196F3;">
                    <h3 style="margin: 0; color: #666;">
                        <?php esc_html_e('Paseadores', 'urbandog'); ?>
                    </h3>
                    <p style="font-size: 2em; margin: 10px 0 0; font-weight: bold;">
                        <?php echo (int) $total_walkers; ?>
                    </p>
                </div>
                <div class="ud-card"
                    style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #FF9800;">
                    <h3 style="margin: 0; color: #666;">
                        <?php esc_html_e('Pendientes Aprobación', 'urbandog'); ?>
                    </h3>
                    <p style="font-size: 2em; margin: 10px 0 0; font-weight: bold;">
                        <?php echo (int) $pending_walkers; ?>
                    </p>
                </div>
                <div class="ud-card"
                    style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #9C27B0;">
                    <h3 style="margin: 0; color: #666;">
                        <?php esc_html_e('Reservas', 'urbandog'); ?>
                    </h3>
                    <p style="font-size: 2em; margin: 10px 0 0; font-weight: bold;">
                        <?php echo (int) $total_bookings; ?>
                    </p>
                </div>
                <div class="ud-card"
                    style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #E91E63;">
                    <h3 style="margin: 0; color: #666;">
                        <?php esc_html_e('Mascotas', 'urbandog'); ?>
                    </h3>
                    <p style="font-size: 2em; margin: 10px 0 0; font-weight: bold;">
                        <?php echo (int) $total_pets; ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render walker approval page.
     */
    public static function render_walker_approval(): void
    {
        // Handle approval/rejection actions
        if (isset($_POST['ud_walker_action'], $_POST['ud_walker_user_id'], $_POST['_wpnonce'])) {
            if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'ud_walker_approval')) {
                $user_id = (int) $_POST['ud_walker_user_id'];
                $action = sanitize_text_field(wp_unslash($_POST['ud_walker_action']));

                if (in_array($action, ['approved', 'rejected'], true)) {
                    update_user_meta($user_id, 'ud_walker_verification_status', $action);
                    echo '<div class="notice notice-success"><p>' . esc_html__('Estado actualizado.', 'urbandog') . '</p></div>';
                }
            }
        }

        $pending_walkers = get_users([
            'role' => 'ud_walker',
            'meta_key' => 'ud_walker_verification_status',
            'meta_value' => 'pending',
        ]);
        ?>
        <div class="wrap">
            <h1>✅
                <?php esc_html_e('Aprobar Paseadores', 'urbandog'); ?>
            </h1>

            <?php if (empty($pending_walkers)): ?>
                <p>
                    <?php esc_html_e('No hay paseadores pendientes de aprobación.', 'urbandog'); ?>
                </p>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>
                                <?php esc_html_e('Nombre', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Email', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Teléfono', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('DNI', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('LinkedIn', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Acciones', 'urbandog'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_walkers as $walker): ?>
                            <tr>
                                <td>
                                    <?php echo esc_html($walker->display_name); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($walker->user_email); ?>
                                </td>
                                <td>
                                    <?php echo esc_html(get_user_meta($walker->ID, 'ud_phone', true) ?: '—'); ?>
                                </td>
                                <td>
                                    <?php echo esc_html(get_user_meta($walker->ID, 'ud_walker_dni', true) ?: '—'); ?>
                                </td>
                                <td>
                                    <?php
                                    $linkedin = get_user_meta($walker->ID, 'ud_walker_linkedin', true);
                                    echo $linkedin ? '<a href="' . esc_url($linkedin) . '" target="_blank">Ver</a>' : '—';
                                    ?>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('ud_walker_approval'); ?>
                                        <input type="hidden" name="ud_walker_user_id" value="<?php echo (int) $walker->ID; ?>">
                                        <button type="submit" name="ud_walker_action" value="approved" class="button button-primary">
                                            <?php esc_html_e('Aprobar', 'urbandog'); ?>
                                        </button>
                                        <button type="submit" name="ud_walker_action" value="rejected" class="button">
                                            <?php esc_html_e('Rechazar', 'urbandog'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render payment confirmation page.
     */
    public static function render_payment_confirmation(): void
    {
        // Handle payment confirmation
        if (isset($_POST['ud_confirm_booking_id'], $_POST['_wpnonce'])) {
            if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'ud_payment_confirm')) {
                $booking_id = (int) $_POST['ud_confirm_booking_id'];
                update_post_meta($booking_id, 'ud_booking_status', 'payment_confirmed');

                // Calculate commission
                $price = (float) get_post_meta($booking_id, 'ud_booking_price', true);
                $commission = round($price * 0.25, 2);
                update_post_meta($booking_id, 'ud_booking_commission', $commission);

                echo '<div class="notice notice-success"><p>' . esc_html__('Pago confirmado.', 'urbandog') . '</p></div>';
            }
        }

        // Get bookings pending payment
        $pending_payments = get_posts([
            'post_type' => 'ud_booking',
            'post_status' => 'publish',
            'meta_key' => 'ud_booking_status',
            'meta_value' => 'pending_payment',
            'numberposts' => -1,
        ]);
        ?>
        <div class="wrap">
            <h1>💳
                <?php esc_html_e('Confirmar Pagos (Yape/Plin)', 'urbandog'); ?>
            </h1>

            <?php if (empty($pending_payments)): ?>
                <p>
                    <?php esc_html_e('No hay pagos pendientes de confirmación.', 'urbandog'); ?>
                </p>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>
                                <?php esc_html_e('ID Reserva', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Dueño', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Paseador', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Fecha Paseo', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Monto (S/.)', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Comisión 25%', 'urbandog'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Acción', 'urbandog'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_payments as $booking):
                            $owner_id = get_post_meta($booking->ID, 'ud_booking_owner_id', true);
                            $walker_id = get_post_meta($booking->ID, 'ud_booking_walker_id', true);
                            $price = (float) get_post_meta($booking->ID, 'ud_booking_price', true);
                            $date = get_post_meta($booking->ID, 'ud_booking_date', true);
                            $owner = get_userdata((int) $owner_id);
                            $walker = get_userdata((int) $walker_id);
                            ?>
                            <tr>
                                <td>#
                                    <?php echo (int) $booking->ID; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($owner ? $owner->display_name : '—'); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($walker ? $walker->display_name : '—'); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($date ?: '—'); ?>
                                </td>
                                <td>S/.
                                    <?php echo esc_html(number_format($price, 2)); ?>
                                </td>
                                <td>S/.
                                    <?php echo esc_html(number_format($price * 0.25, 2)); ?>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('ud_payment_confirm'); ?>
                                        <input type="hidden" name="ud_confirm_booking_id" value="<?php echo (int) $booking->ID; ?>">
                                        <button type="submit" class="button button-primary">
                                            <?php esc_html_e('Confirmar Pago', 'urbandog'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
}
