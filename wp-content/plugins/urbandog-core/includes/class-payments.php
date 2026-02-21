<?php
/**
 * UrbanDog Payments
 *
 * Handles manual payment logic with Yape/Plin, commissions, and receipts.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Payments
{

    /**
     * Initialize payment-related hooks.
     */
    public static function init(): void
    {
        // AJAX handlers
        add_action('wp_ajax_ud_upload_payment_proof', [__CLASS__, 'handle_upload_proof']);
        add_action('wp_ajax_ud_confirm_payment', [__CLASS__, 'handle_confirm_payment']);
        add_action('wp_ajax_ud_reject_payment', [__CLASS__, 'handle_reject_payment']);
        add_action('wp_ajax_ud_mark_paid_to_walker', [__CLASS__, 'handle_mark_paid_to_walker']);

        // Hook into booking status changes
        add_action('ud_booking_visit_completed', [__CLASS__, 'create_transaction_after_visit'], 10, 1);
    }

    /**
     * Create transaction after visit is completed successfully.
     *
     * @param int $booking_id Booking ID.
     */
    public static function create_transaction_after_visit(int $booking_id): void
    {
        // Check if transaction already exists
        $existing = get_posts([
            'post_type' => 'ud_transaction',
            'meta_key' => 'ud_transaction_booking_id',
            'meta_value' => $booking_id,
            'posts_per_page' => 1,
        ]);

        if (!empty($existing)) {
            return; // Transaction already exists
        }

        self::create_transaction($booking_id);
    }

    /**
     * Create a new transaction for a booking.
     *
     * @param int $booking_id Booking ID.
     * @return int|false Transaction ID or false on failure.
     */
    public static function create_transaction(int $booking_id)
    {
        $booking_owner = (int) get_post_meta($booking_id, 'ud_booking_owner_id', true);
        $booking_walker = (int) get_post_meta($booking_id, 'ud_booking_walker_id', true);
        $booking_price = (float) get_post_meta($booking_id, 'ud_booking_price', true);

        if (!$booking_owner || !$booking_walker || $booking_price <= 0) {
            return false;
        }

        $split = self::calculate_split($booking_price);

        $transaction_id = wp_insert_post([
            'post_type' => 'ud_transaction',
            'post_title' => sprintf(__('Transacción - Reserva #%d', 'urbandog'), $booking_id),
            'post_status' => 'publish',
        ]);

        if (is_wp_error($transaction_id)) {
            return false;
        }

        // Save metadata
        update_post_meta($transaction_id, 'ud_transaction_booking_id', $booking_id);
        update_post_meta($transaction_id, 'ud_transaction_owner_id', $booking_owner);
        update_post_meta($transaction_id, 'ud_transaction_walker_id', $booking_walker);
        update_post_meta($transaction_id, 'ud_transaction_amount_total', $booking_price);
        update_post_meta($transaction_id, 'ud_transaction_amount_walker', $split['payout']);
        update_post_meta($transaction_id, 'ud_transaction_amount_platform', $split['commission']);
        update_post_meta($transaction_id, 'ud_transaction_status', 'pending');
        update_post_meta($transaction_id, 'ud_transaction_created_at', current_time('mysql'));

        // Update booking status
        update_post_meta($booking_id, 'ud_booking_status', 'payment_pending');
        update_post_meta($booking_id, 'ud_booking_transaction_id', $transaction_id);

        return $transaction_id;
    }

    /**
     * Calculate payout and commission split.
     * 
     * @param float $total_amount The total paid by the owner.
     * @return array [payout, commission]
     */
    public static function calculate_split(float $total_amount): array
    {
        $commission = round($total_amount * 0.25, 2);
        $payout = $total_amount - $commission;

        return [
            'commission' => $commission,
            'payout' => $payout,
        ];
    }

    /**
     * Handle AJAX upload of payment proof by owner.
     */
    public static function handle_upload_proof(): void
    {
        check_ajax_referer('ud_payment_nonce', 'nonce');

        $user_id = get_current_user_id();
        $transaction_id = (int) ($_POST['transaction_id'] ?? 0);
        $method = sanitize_text_field($_POST['method'] ?? 'yape');
        $reference = sanitize_text_field($_POST['reference'] ?? '');

        if (!$user_id || !UD_Roles::is_owner($user_id)) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        // Validate that user owns this transaction
        $owner_id = (int) get_post_meta($transaction_id, 'ud_transaction_owner_id', true);
        if ($user_id !== $owner_id) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        // Handle image upload
        if (!empty($_FILES['proof_image'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';

            $upload = wp_handle_upload($_FILES['proof_image'], ['test_form' => false]);

            if (!empty($upload['error'])) {
                wp_send_json_error(['message' => $upload['error']]);
            }

            if (!empty($upload['url'])) {
                update_post_meta($transaction_id, 'ud_transaction_proof_image', $upload['url']);
            }
        }

        update_post_meta($transaction_id, 'ud_transaction_method', $method);
        update_post_meta($transaction_id, 'ud_transaction_reference', $reference);
        update_post_meta($transaction_id, 'ud_transaction_status', 'pending');
        update_post_meta($transaction_id, 'ud_transaction_submitted_at', current_time('mysql'));

        // Notify admin
        self::notify_admin_new_payment($transaction_id);

        wp_send_json_success(['message' => __('Comprobante enviado. Espera la confirmación del administrador.', 'urbandog')]);
    }

    /**
     * Handle AJAX payment confirmation by admin.
     */
    public static function handle_confirm_payment(): void
    {
        check_ajax_referer('ud_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $transaction_id = (int) ($_POST['transaction_id'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        $admin_id = get_current_user_id();

        update_post_meta($transaction_id, 'ud_transaction_status', 'confirmed');
        update_post_meta($transaction_id, 'ud_transaction_confirmed_by', $admin_id);
        update_post_meta($transaction_id, 'ud_transaction_confirmed_at', current_time('mysql'));
        update_post_meta($transaction_id, 'ud_transaction_notes', $notes);

        // Update booking status
        $booking_id = (int) get_post_meta($transaction_id, 'ud_transaction_booking_id', true);
        update_post_meta($booking_id, 'ud_booking_status', 'payment_confirmed');

        // Send receipt emails
        self::notify_payment_confirmed($transaction_id);

        wp_send_json_success(['message' => __('Pago confirmado exitosamente.', 'urbandog')]);
    }

    /**
     * Handle AJAX payment rejection by admin.
     */
    public static function handle_reject_payment(): void
    {
        check_ajax_referer('ud_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $transaction_id = (int) ($_POST['transaction_id'] ?? 0);
        $reason = sanitize_textarea_field($_POST['reason'] ?? '');

        update_post_meta($transaction_id, 'ud_transaction_status', 'rejected');
        update_post_meta($transaction_id, 'ud_transaction_rejected_at', current_time('mysql'));
        update_post_meta($transaction_id, 'ud_transaction_rejection_reason', $reason);

        // Notify owner
        self::notify_payment_rejected($transaction_id);

        wp_send_json_success(['message' => __('Pago rechazado.', 'urbandog')]);
    }

    /**
     * Handle AJAX marking payment as paid to walker.
     */
    public static function handle_mark_paid_to_walker(): void
    {
        check_ajax_referer('ud_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No autorizado.', 'urbandog')]);
        }

        $transaction_id = (int) ($_POST['transaction_id'] ?? 0);

        update_post_meta($transaction_id, 'ud_transaction_status', 'paid_to_walker');
        update_post_meta($transaction_id, 'ud_transaction_paid_to_walker_at', current_time('mysql'));

        // Notify walker
        self::notify_walker_paid($transaction_id);

        wp_send_json_success(['message' => __('Pago al paseador registrado.', 'urbandog')]);
    }

    /**
     * Notify admin of new payment pending confirmation.
     *
     * @param int $transaction_id Transaction ID.
     */
    private static function notify_admin_new_payment(int $transaction_id): void
    {
        $admin_email = get_option('admin_email');
        $booking_id = (int) get_post_meta($transaction_id, 'ud_transaction_booking_id', true);
        $amount = get_post_meta($transaction_id, 'ud_transaction_amount_total', true);
        $method = get_post_meta($transaction_id, 'ud_transaction_method', true);

        $subject = sprintf('[UrbanDog] Nuevo pago pendiente - Reserva #%d', $booking_id);
        $message = sprintf(
            "Hay un nuevo pago pendiente de confirmación.\n\n" .
            "Reserva: #%d\n" .
            "Monto: S/ %s\n" .
            "Método: %s\n\n" .
            "Revisa el panel de admin para confirmar:\n%s",
            $booking_id,
            number_format($amount, 2),
            ucfirst($method),
            admin_url('admin.php?page=urbandog-payments')
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Notify owner and walker that payment was confirmed.
     *
     * @param int $transaction_id Transaction ID.
     */
    private static function notify_payment_confirmed(int $transaction_id): void
    {
        $owner_id = (int) get_post_meta($transaction_id, 'ud_transaction_owner_id', true);
        $walker_id = (int) get_post_meta($transaction_id, 'ud_transaction_walker_id', true);
        $booking_id = (int) get_post_meta($transaction_id, 'ud_transaction_booking_id', true);

        $owner = get_userdata($owner_id);
        $walker = get_userdata($walker_id);

        // Generate receipt HTML
        $receipt_html = self::generate_receipt_html($transaction_id);

        // Email to owner with receipt
        $subject_owner = '[UrbanDog] ¡Pago confirmado! - Recibo adjunto';
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($owner->user_email, $subject_owner, $receipt_html, $headers);

        // Email to walker
        $amount_walker = get_post_meta($transaction_id, 'ud_transaction_amount_walker', true);
        $subject_walker = '[UrbanDog] Pago confirmado para tu paseo';
        $message_walker = sprintf(
            "Hola %s,\n\n" .
            "El pago para la reserva #%d ha sido confirmado.\n\n" .
            "Tu ganancia: S/ %s\n" .
            "Recibirás el pago en los próximos días.\n\n" .
            "¡Gracias por ser parte de UrbanDog!\n\n" .
            "Equipo UrbanDog",
            $walker->display_name,
            $booking_id,
            number_format($amount_walker, 2)
        );

        wp_mail($walker->user_email, $subject_walker, $message_walker);
    }

    /**
     * Notify owner that payment was rejected.
     *
     * @param int $transaction_id Transaction ID.
     */
    private static function notify_payment_rejected(int $transaction_id): void
    {
        $owner_id = (int) get_post_meta($transaction_id, 'ud_transaction_owner_id', true);
        $booking_id = (int) get_post_meta($transaction_id, 'ud_transaction_booking_id', true);
        $reason = get_post_meta($transaction_id, 'ud_transaction_rejection_reason', true);

        $owner = get_userdata($owner_id);

        $subject = '[UrbanDog] Pago rechazado - Acción requerida';
        $message = sprintf(
            "Hola %s,\n\n" .
            "Tu comprobante de pago para la reserva #%d ha sido rechazado.\n\n" .
            "Motivo: %s\n\n" .
            "Por favor, verifica los datos y vuelve a enviar el comprobante.\n\n" .
            "Equipo UrbanDog",
            $owner->display_name,
            $booking_id,
            $reason ?: 'No especificado'
        );

        wp_mail($owner->user_email, $subject, $message);
    }

    /**
     * Notify walker that payment has been sent.
     *
     * @param int $transaction_id Transaction ID.
     */
    private static function notify_walker_paid(int $transaction_id): void
    {
        $walker_id = (int) get_post_meta($transaction_id, 'ud_transaction_walker_id', true);
        $amount = get_post_meta($transaction_id, 'ud_transaction_amount_walker', true);

        $walker = get_userdata($walker_id);

        $subject = '[UrbanDog] ¡Pago enviado!';
        $message = sprintf(
            "Hola %s,\n\n" .
            "Tu pago de S/ %s ha sido enviado.\n\n" .
            "Deberías recibirlo en las próximas horas.\n\n" .
            "¡Gracias por ser parte de UrbanDog!\n\n" .
            "Equipo UrbanDog",
            $walker->display_name,
            number_format($amount, 2)
        );

        wp_mail($walker->user_email, $subject, $message);
    }

    /**
     * Generate HTML receipt for email.
     *
     * @param int $transaction_id Transaction ID.
     * @return string HTML receipt.
     */
    private static function generate_receipt_html(int $transaction_id): string
    {
        $booking_id = (int) get_post_meta($transaction_id, 'ud_transaction_booking_id', true);
        $owner_id = (int) get_post_meta($transaction_id, 'ud_transaction_owner_id', true);
        $walker_id = (int) get_post_meta($transaction_id, 'ud_transaction_walker_id', true);
        $amount = get_post_meta($transaction_id, 'ud_transaction_amount_total', true);
        $method = get_post_meta($transaction_id, 'ud_transaction_method', true);
        $reference = get_post_meta($transaction_id, 'ud_transaction_reference', true);
        $confirmed_at = get_post_meta($transaction_id, 'ud_transaction_confirmed_at', true);

        $owner = get_userdata($owner_id);
        $walker = get_userdata($walker_id);

        // Get booking details
        $date = get_post_meta($booking_id, 'ud_booking_date', true);
        $time = get_post_meta($booking_id, 'ud_booking_time', true);
        $duration = get_post_meta($booking_id, 'ud_booking_duration', true);
        $modality = get_post_meta($booking_id, 'ud_booking_modality', true);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 600px;
                    margin: 0 auto;
                    background: #f5f5f5;
                    padding: 20px;
                }

                .receipt {
                    background: white;
                    border: 2px solid #10b981;
                    padding: 30px;
                    border-radius: 10px;
                }

                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #10b981;
                    padding-bottom: 20px;
                }

                .logo {
                    font-size: 28px;
                    font-weight: bold;
                    color: #10b981;
                    margin-bottom: 10px;
                }

                .receipt-number {
                    color: #666;
                    margin-top: 10px;
                    font-size: 14px;
                }

                .status-badge {
                    background: #10b981;
                    color: white;
                    padding: 8px 16px;
                    border-radius: 20px;
                    display: inline-block;
                    margin-top: 10px;
                }

                .section {
                    margin: 25px 0;
                }

                .section-title {
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 15px;
                    font-size: 16px;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 8px;
                }

                .detail-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #f0f0f0;
                }

                .detail-label {
                    color: #666;
                }

                .detail-value {
                    font-weight: 500;
                    color: #333;
                }

                .total {
                    background: #f0fdf4;
                    padding: 20px;
                    border-radius: 8px;
                    margin-top: 25px;
                }

                .total-row {
                    display: flex;
                    justify-content: space-between;
                    font-size: 20px;
                    font-weight: bold;
                    color: #10b981;
                }

                .footer {
                    text-align: center;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    color: #666;
                    font-size: 12px;
                    line-height: 1.6;
                }

                .footer strong {
                    color: #333;
                }
            </style>
        </head>

        <body>
            <div class="receipt">
                <div class="header">
                    <div class="logo">🐾 URBANDOG</div>
                    <div class="receipt-number">Recibo #<?php echo str_pad($transaction_id, 6, '0', STR_PAD_LEFT); ?></div>
                    <div class="status-badge">✅ PAGO CONFIRMADO</div>
                </div>

                <div class="section">
                    <div class="section-title">Información del Cliente</div>
                    <div class="detail-row">
                        <span class="detail-label">Nombre:</span>
                        <span class="detail-value"><?php echo esc_html($owner->display_name); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?php echo esc_html($owner->user_email); ?></span>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Detalles del Servicio</div>
                    <div class="detail-row">
                        <span class="detail-label">Paseador:</span>
                        <span class="detail-value"><?php echo esc_html($walker->display_name); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha del paseo:</span>
                        <span class="detail-value"><?php echo esc_html($date); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hora:</span>
                        <span class="detail-value"><?php echo esc_html($time); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Duración:</span>
                        <span class="detail-value"><?php echo $duration; ?> minutos</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Modalidad:</span>
                        <span class="detail-value"><?php echo $modality === 'individual' ? 'Individual' : 'Grupal'; ?></span>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Información del Pago</div>
                    <div class="detail-row">
                        <span class="detail-label">Método de pago:</span>
                        <span class="detail-value"><?php echo ucfirst($method); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Número de operación:</span>
                        <span class="detail-value"><?php echo esc_html($reference); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha de confirmación:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($confirmed_at)); ?></span>
                    </div>
                </div>

                <div class="total">
                    <div class="total-row">
                        <span>TOTAL PAGADO:</span>
                        <span>S/ <?php echo number_format($amount, 2); ?></span>
                    </div>
                </div>

                <div class="footer">
                    <p>Este es un recibo electrónico generado automáticamente.</p>
                    <p>Para cualquier consulta, contáctanos a <strong>soporte@urbandog.pe</strong></p>
                    <p style="margin-top: 20px;">
                        <strong>UrbanDog</strong><br>
                        San Juan de Lurigancho, Lima, Perú<br>
                        www.urbandog.pe
                    </p>
                </div>
            </div>
        </body>

        </html>
        <?php
        return ob_get_clean();
    }
}
