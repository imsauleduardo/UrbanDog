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
        // Only on UrbanDog admin pages
        if (strpos($hook, 'urbandog') === false) {
            return;
        }

        wp_enqueue_style('urbandog-admin', plugin_dir_url(__DIR__) . 'assets/css/admin.css', [], '1.0.0');

        // Enqueue payment scripts on payment confirmation page
        if (strpos($hook, 'urbandog_page_urbandog-payments') !== false) {
            wp_enqueue_script(
                'urbandog-payments-admin',
                get_template_directory_uri() . '/assets/js/payments.js',
                ['jquery'],
                '1.0.0',
                true
            );

            wp_localize_script('urbandog-payments-admin', 'udPayments', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ud_admin_nonce')
            ]);
        }
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
                $reason = isset($_POST['ud_rejection_reason']) ? sanitize_textarea_field(wp_unslash($_POST['ud_rejection_reason'])) : '';

                if (in_array($action, ['approved', 'rejected'], true)) {
                    update_user_meta($user_id, 'ud_walker_verification_status', $action);

                    if ($action === 'rejected' && $reason) {
                        update_user_meta($user_id, 'ud_walker_rejection_reason', $reason);
                    }

                    // Send email notification
                    self::send_verification_email($user_id, $action, $reason);

                    $message = $action === 'approved'
                        ? __('Paseador aprobado exitosamente.', 'urbandog')
                        : __('Paseador rechazado.', 'urbandog');
                    echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
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
                                <?php esc_html_e('Documentos', 'urbandog'); ?>
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
                                    echo $linkedin ? '<a href="' . esc_url($linkedin) . '" target="_blank">LinkedIn</a>' : '—';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $doc_dni = get_user_meta($walker->ID, 'ud_walker_doc_dni', true);
                                    $antecedentes = get_user_meta($walker->ID, 'ud_walker_doc_antecedentes', true);
                                    $domicilio = get_user_meta($walker->ID, 'ud_walker_doc_domicilio', true);

                                    if ($doc_dni) {
                                        printf('<a href="%s" target="_blank" class="button button-small" style="margin-bottom: 5px; display: inline-block; background: #2271b1; color: white; border: none;">%s</a><br>', esc_url($doc_dni), __('Ver DNI', 'urbandog'));
                                    }
                                    if ($antecedentes) {
                                        printf('<a href="%s" target="_blank" class="button button-small" style="margin-bottom: 5px; display: inline-block;">%s</a><br>', esc_url($antecedentes), __('Antecedentes', 'urbandog'));
                                    }
                                    if ($domicilio) {
                                        printf('<a href="%s" target="_blank" class="button button-small">%s</a>', esc_url($domicilio), __('Certificado Domicilio', 'urbandog'));
                                    }
                                    if (!$doc_dni && !$antecedentes && !$domicilio) {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;" onsubmit="return handleWalkerAction(event, this);">
                                        <?php wp_nonce_field('ud_walker_approval'); ?>
                                        <input type="hidden" name="ud_walker_user_id" value="<?php echo (int) $walker->ID; ?>">
                                        <input type="hidden" name="ud_rejection_reason" id="reason-<?php echo (int) $walker->ID; ?>"
                                            value="">
                                        <button type="submit" name="ud_walker_action" value="approved" class="button button-primary">
                                            <?php esc_html_e('Aprobar', 'urbandog'); ?>
                                        </button>
                                        <button type="button" class="button"
                                            onclick="showRejectionModal(<?php echo (int) $walker->ID; ?>)">
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
        // Get pending transactions (status = 'pending')
        $pending_transactions = get_posts([
            'post_type' => 'ud_transaction',
            'post_status' => 'publish',
            'meta_key' => 'ud_transaction_status',
            'meta_value' => 'pending',
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        // Get confirmed transactions (for "mark as paid" section)
        $confirmed_transactions = get_posts([
            'post_type' => 'ud_transaction',
            'post_status' => 'publish',
            'meta_key' => 'ud_transaction_status',
            'meta_value' => 'confirmed',
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        ?>
        <div class="wrap">
            <h1>💳 <?php esc_html_e('Gestión de Pagos', 'urbandog'); ?></h1>
            <p><?php esc_html_e('Revisa y confirma los pagos enviados por los clientes.', 'urbandog'); ?></p>

            <!-- Pending Payments Section -->
            <h2><?php esc_html_e('Pagos Pendientes de Confirmación', 'urbandog'); ?></h2>

            <?php if (empty($pending_transactions)): ?>
                <div class="notice notice-info">
                    <p><?php esc_html_e('No hay pagos pendientes de confirmación.', 'urbandog'); ?></p>
                </div>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Dueño', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Paseador', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Fecha Paseo', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Monto Total', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Método', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Referencia', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Comprobante', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Acciones', 'urbandog'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_transactions as $transaction):
                            $transaction_id = $transaction->ID;
                            $booking_id = get_post_meta($transaction_id, 'ud_transaction_booking_id', true);
                            $owner_id = get_post_meta($transaction_id, 'ud_transaction_owner_id', true);
                            $walker_id = get_post_meta($transaction_id, 'ud_transaction_walker_id', true);
                            $amount_total = get_post_meta($transaction_id, 'ud_transaction_amount_total', true);
                            $amount_walker = get_post_meta($transaction_id, 'ud_transaction_amount_walker', true);
                            $amount_platform = get_post_meta($transaction_id, 'ud_transaction_amount_platform', true);
                            $method = get_post_meta($transaction_id, 'ud_transaction_method', true);
                            $reference = get_post_meta($transaction_id, 'ud_transaction_reference', true);
                            $proof_image = get_post_meta($transaction_id, 'ud_transaction_proof_image', true);
                            $booking_date = get_post_meta($booking_id, 'ud_booking_date', true);

                            $owner = get_userdata((int) $owner_id);
                            $walker = get_userdata((int) $walker_id);
                            ?>
                            <tr>
                                <td><strong>#<?php echo (int) $transaction_id; ?></strong></td>
                                <td><?php echo esc_html($owner ? $owner->display_name : '—'); ?></td>
                                <td><?php echo esc_html($walker ? $walker->display_name : '—'); ?></td>
                                <td><?php echo esc_html($booking_date ?: '—'); ?></td>
                                <td>
                                    <strong>S/ <?php echo esc_html(number_format($amount_total, 2)); ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        Paseador: S/ <?php echo esc_html(number_format($amount_walker, 2)); ?> (75%)<br>
                                        Plataforma: S/ <?php echo esc_html(number_format($amount_platform, 2)); ?> (25%)
                                    </small>
                                </td>
                                <td>
                                    <span
                                        style="display: inline-block; padding: 4px 8px; background: #e0e7ff; color: #3730a3; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                        <?php echo esc_html(strtoupper($method)); ?>
                                    </span>
                                </td>
                                <td><code><?php echo esc_html($reference ?: '—'); ?></code></td>
                                <td>
                                    <?php if ($proof_image): ?>
                                        <button type="button" class="button ud-view-proof-btn"
                                            data-image-url="<?php echo esc_url($proof_image); ?>">
                                            👁️ Ver Comprobante
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #999;">Sin comprobante</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="ud-payment-actions" style="min-width: 250px;">
                                        <textarea class="ud-admin-notes"
                                            placeholder="<?php esc_attr_e('Notas opcionales...', 'urbandog'); ?>"
                                            style="width: 100%; margin-bottom: 8px; font-size: 12px;" rows="2"></textarea>
                                        <button type="button" class="button button-primary ud-confirm-payment-btn"
                                            data-transaction-id="<?php echo (int) $transaction_id; ?>" style="margin-right: 4px;">
                                            ✅ Confirmar
                                        </button>
                                        <button type="button" class="button ud-reject-payment-btn"
                                            data-transaction-id="<?php echo (int) $transaction_id; ?>"
                                            style="background: #dc2626; color: white; border-color: #dc2626;">
                                            ❌ Rechazar
                                        </button>
                                        <br>
                                        <textarea class="ud-rejection-reason"
                                            placeholder="<?php esc_attr_e('Motivo del rechazo (requerido para rechazar)...', 'urbandog'); ?>"
                                            style="width: 100%; margin-top: 8px; font-size: 12px; display: none;" rows="2"></textarea>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Confirmed Payments (Mark as Paid to Walker) -->
            <h2 style="margin-top: 40px;">
                <?php esc_html_e('Pagos Confirmados - Pendientes de Envío al Paseador', 'urbandog'); ?>
            </h2>

            <?php if (empty($confirmed_transactions)): ?>
                <div class="notice notice-info">
                    <p><?php esc_html_e('No hay pagos confirmados pendientes de envío.', 'urbandog'); ?></p>
                </div>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Paseador', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Monto Paseador', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Confirmado', 'urbandog'); ?></th>
                            <th><?php esc_html_e('Acción', 'urbandog'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($confirmed_transactions as $transaction):
                            $transaction_id = $transaction->ID;
                            $walker_id = get_post_meta($transaction_id, 'ud_transaction_walker_id', true);
                            $amount_walker = get_post_meta($transaction_id, 'ud_transaction_amount_walker', true);
                            $confirmed_at = get_post_meta($transaction_id, 'ud_transaction_confirmed_at', true);
                            $walker = get_userdata((int) $walker_id);
                            ?>
                            <tr>
                                <td><strong>#<?php echo (int) $transaction_id; ?></strong></td>
                                <td><?php echo esc_html($walker ? $walker->display_name : '—'); ?></td>
                                <td><strong>S/ <?php echo esc_html(number_format($amount_walker, 2)); ?></strong></td>
                                <td><?php echo esc_html($confirmed_at ? date('d/m/Y H:i', strtotime($confirmed_at)) : '—'); ?></td>
                                <td>
                                    <button type="button" class="button button-primary ud-mark-paid-btn"
                                        data-transaction-id="<?php echo (int) $transaction_id; ?>">
                                        💸 Marcar como Pagado al Paseador
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Enqueue payment scripts -->
            <script>
                // Show/hide rejection reason textarea
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.ud-reject-payment-btn').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const actions = this.closest('.ud-payment-actions');
                            const reasonTextarea = actions.querySelector('.ud-rejection-reason');
                            if (reasonTextarea.style.display === 'none') {
                                reasonTextarea.style.display = 'block';
                                reasonTextarea.focus();
                            }
                        });
                    });
                });
            </script>
            <style>
                .ud-payment-actions {
                    display: flex;
                    flex-direction: column;
                }

                .widefat th,
                .widefat td {
                    vertical-align: top;
                    padding: 12px;
                }
            </style>
        </div>
        <?php
    }

    /**
     * Send verification email to walker.
     */
    private static function send_verification_email(int $user_id, string $status, string $reason = ''): void
    {
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }

        $to = $user->user_email;
        $site_name = get_bloginfo('name');

        if ($status === 'approved') {
            $subject = sprintf(__('[%s] Tu cuenta de paseador ha sido aprobada', 'urbandog'), $site_name);
            $message = sprintf(
                __("Hola %s,\n\n¡Excelentes noticias! Tu cuenta de paseador en %s ha sido aprobada.\n\nYa puedes empezar a recibir solicitudes de paseo. Ingresa a tu dashboard para configurar tu disponibilidad y precios.\n\nDashboard: %s\n\n¡Bienvenido al equipo UrbanDog!\n\nSaludos,\nEl equipo de %s", 'urbandog'),
                $user->display_name,
                $site_name,
                home_url('/dashboard-paseador/'),
                $site_name
            );
        } else {
            $subject = sprintf(__('[%s] Actualización sobre tu solicitud de paseador', 'urbandog'), $site_name);
            $reason_text = $reason ? "\n\nMotivo: " . $reason : '';
            $message = sprintf(
                __("Hola %s,\n\nLamentablemente, tu solicitud para ser paseador en %s no ha sido aprobada en este momento.%s\n\nSi tienes preguntas, por favor contáctanos.\n\nSaludos,\nEl equipo de %s", 'urbandog'),
                $user->display_name,
                $site_name,
                $reason_text,
                $site_name
            );
        }

        wp_mail($to, $subject, $message);
    }
}
