<?php
/**
 * Template Name: Owner Dashboard
 * 
 * @package UrbanDog
 */

if (!is_user_logged_in()) {
    wp_safe_redirect(wp_login_url(home_url('/panel-dueno/')));
    exit;
}

get_header();
?>

<style>
    /* Force Modal Styles for Owner Dashboard to override conflicts */
    #ud-pet-modal.ud-modal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        max-width: none !important;
        border-radius: 0 !important;
        background: rgba(0, 0, 0, 0.5) !important;
        z-index: 9999 !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
    }

    #ud-pet-modal.ud-modal.active {
        display: flex !important;
    }

    #ud-pet-modal .ud-modal-content {
        background: white !important;
        width: 90% !important;
        max-width: 550px !important;
        border-radius: 1.25rem !important;
        max-height: 90vh !important;
        overflow-y: auto !important;
        position: relative !important;
        padding: 2rem !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        margin: auto !important;
        top: auto !important;
        left: auto !important;
        transform: none !important;
    }

    /* Premium Form Styles for Pet Modal */
    #ud-pet-form label {
        display: block !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
        margin-bottom: 0.5rem !important;
        font-family: 'Inter', sans-serif !important;
    }

    #ud-pet-form .ud-form-control {
        width: 100% !important;
        padding: 0.75rem 1rem !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        font-size: 0.9375rem !important;
        color: #0f172a !important;
        background-color: #fff !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
        font-family: 'Inter', sans-serif !important;
        box-sizing: border-box !important;
        outline: none !important;
        -webkit-appearance: none !important;
        appearance: none !important;
    }

    #ud-pet-form .ud-form-control:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important;
    }

    #ud-pet-form .ud-form-control::placeholder {
        color: #94a3b8 !important;
    }

    #ud-pet-form .ud-btn-submit {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        padding: 0.875rem 1.5rem !important;
        border-radius: 0.75rem !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        transition: opacity 0.2s, transform 0.2s !important;
        background: #4f46e5 !important;
        color: #fff !important;
        border: none !important;
        font-size: 1rem !important;
        margin-top: 1.5rem !important;
        font-family: 'Inter', sans-serif !important;
        letter-spacing: -0.01em !important;
    }

    #ud-pet-form .ud-btn-submit:hover {
        opacity: 0.9 !important;
        transform: translateY(-1px) !important;
    }

    /* Modal Header */
    #ud-pet-modal .ud-modal-header h2 {
        font-size: 1.375rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        margin-bottom: 0.25rem !important;
        letter-spacing: -0.025em !important;
    }

    #ud-pet-modal .ud-modal-header p {
        font-size: 0.9rem !important;
        color: #64748b !important;
    }

    /* Form Row Spacing */
    #ud-pet-form .ud-form-group {
        margin-bottom: 1.25rem !important;
    }
</style>

<?php

$owner_id = get_current_user_id();
$user_data = get_userdata($owner_id);

// Fetch Active Bookings (Pending or Accepted)
$active_bookings = new WP_Query([
    'post_type' => 'ud_booking',
    'post_status' => 'publish',
    'author' => $owner_id,
    'posts_per_page' => 10,
    'meta_query' => [
        [
            'key' => 'ud_booking_status',
            'value' => ['pending_request', 'accepted'],
            'compare' => 'IN'
        ]
    ]
]);

// Fetch Pets
$pets = new WP_Query([
    'post_type' => 'ud_pet',
    'post_status' => 'publish',
    'author' => $owner_id,
    'posts_per_page' => -1
]);

// Statistics
$completed_walks_query = new WP_Query([
    'post_type' => 'ud_booking',
    'author' => $owner_id,
    'meta_key' => 'ud_booking_status',
    'meta_value' => 'completed',
    'posts_per_page' => -1
]);
$completed_walks = $completed_walks_query->found_posts;

?>

<main class="ud-dashboard-wrapper ud-owner-dashboard">
    <div class="ud-container">

        <!-- Header Section -->
        <header class="ud-dashboard-header">
            <div class="ud-welcome-text">
                <h1 class="ud-h1">
                    <?php printf(__('¡Hola, %s! 🐾', 'urbandog'), esc_html($user_data->display_name)); ?>
                </h1>
                <p class="ud-subtitle">
                    <?php _e('Gestiona tus paseos y mantén a tu manada feliz.', 'urbandog'); ?>
                </p>
            </div>
            <div class="ud-header-actions">
                <a href="<?php echo esc_url(home_url('/buscar/')); ?>" class="ud-btn ud-btn-primary">
                    <i data-lucide="search"></i>
                    <?php _e('Buscar Paseador', 'urbandog'); ?>
                </a>
            </div>
        </header>

        <div class="ud-dashboard-layout">

            <!-- Main Content: Bookings -->
            <div class="ud-main-col">

                <section class="ud-section">
                    <h2 class="ud-section-title">
                        <i data-lucide="calendar" class="text-primary"></i>
                        <?php _e('Tus Próximos Paseos', 'urbandog'); ?>
                    </h2>

                    <div class="ud-list-stack">

                        <?php
                        // ── MOCK CARDS (solo en desarrollo) ──────────────────────────────
                        if (defined('WP_DEBUG') && WP_DEBUG):
                            ?>
                            <!-- MOCK: Meet & Greet -->
                            <div class="ud-booking-card ud-booking-card--mg">
                                <div class="ud-booking-accent"></div>
                                <div class="ud-booking-body">
                                    <div class="ud-booking-service">
                                        <div class="ud-booking-service-icon">
                                            <i data-lucide="handshake"></i>
                                        </div>
                                        <span class="ud-booking-service-name">Meet &amp; Greet</span>
                                        <span class="ud-booking-status-badge ud-booking-status-badge--coordinating"
                                            style="margin-left:auto;">
                                            En coordinación
                                        </span>
                                    </div>
                                    <div class="ud-booking-walker">
                                        <div class="ud-booking-walker-avatar">M</div>
                                        <div>
                                            <div class="ud-booking-walker-name">María García</div>
                                            <div class="ud-booking-walker-label">Paseadora verificada</div>
                                        </div>
                                    </div>
                                    <div class="ud-booking-banner ud-booking-banner--info">
                                        <i data-lucide="info"></i>
                                        <span><?php _e('La paseadora se pondrá en contacto contigo pronto para coordinar los detalles de tu reunión gratuita.', 'urbandog'); ?></span>
                                    </div>
                                </div>
                                <div class="ud-booking-footer">
                                    <div class="ud-booking-price-block">
                                        <span class="ud-booking-price-label"><?php _e('Precio', 'urbandog'); ?></span>
                                        <span class="ud-booking-price-value is-free">Gratis</span>
                                    </div>
                                    <a href="#" class="ud-btn-booking ud-btn-booking--blue">
                                        <i data-lucide="calendar-check"></i>
                                        <?php _e('Ver detalles', 'urbandog'); ?>
                                    </a>
                                </div>
                            </div>

                            <!-- MOCK: Paseo programado 1 -->
                            <div class="ud-booking-card ud-booking-card--walk">
                                <div class="ud-booking-accent"></div>
                                <div class="ud-booking-body">
                                    <div class="ud-booking-service">
                                        <div class="ud-booking-service-icon">
                                            <i data-lucide="footprints"></i>
                                        </div>
                                        <span class="ud-booking-service-name">Paseo de 30 min</span>
                                        <span class="ud-booking-status-badge ud-booking-status-badge--confirmed"
                                            style="margin-left:auto;">
                                            Confirmado
                                        </span>
                                    </div>
                                    <div class="ud-booking-walker">
                                        <div class="ud-booking-walker-avatar">M</div>
                                        <div>
                                            <div class="ud-booking-walker-name">María García</div>
                                            <div class="ud-booking-walker-label">Paseadora verificada</div>
                                        </div>
                                    </div>
                                    <div class="ud-booking-chips">
                                        <span class="ud-booking-chip">
                                            <i data-lucide="calendar"></i>
                                            Lun 24 Feb 2026
                                        </span>
                                        <span class="ud-booking-chip">
                                            <i data-lucide="clock"></i>
                                            8:00 AM
                                        </span>
                                    </div>
                                </div>
                                <div class="ud-booking-footer">
                                    <div class="ud-booking-price-block">
                                        <span class="ud-booking-price-label"><?php _e('Precio', 'urbandog'); ?></span>
                                        <span class="ud-booking-price-value">S/ 35</span>
                                    </div>
                                    <a href="#" class="ud-btn-booking ud-btn-booking--primary">
                                        <i data-lucide="arrow-right"></i>
                                        <?php _e('Ver detalles', 'urbandog'); ?>
                                    </a>
                                </div>
                            </div>

                            <!-- MOCK: Paseo programado 2 -->
                            <div class="ud-booking-card ud-booking-card--walk">
                                <div class="ud-booking-accent"></div>
                                <div class="ud-booking-body">
                                    <div class="ud-booking-service">
                                        <div class="ud-booking-service-icon">
                                            <i data-lucide="footprints"></i>
                                        </div>
                                        <span class="ud-booking-service-name">Paseo de 60 min</span>
                                        <span class="ud-booking-status-badge ud-booking-status-badge--pending"
                                            style="margin-left:auto;">
                                            Pendiente
                                        </span>
                                    </div>
                                    <div class="ud-booking-walker">
                                        <div class="ud-booking-walker-avatar">C</div>
                                        <div>
                                            <div class="ud-booking-walker-name">Carlos Mendoza</div>
                                            <div class="ud-booking-walker-label">Paseador verificado</div>
                                        </div>
                                    </div>
                                    <div class="ud-booking-chips">
                                        <span class="ud-booking-chip">
                                            <i data-lucide="calendar"></i>
                                            Mié 26 Feb 2026
                                        </span>
                                        <span class="ud-booking-chip">
                                            <i data-lucide="clock"></i>
                                            3:00 PM
                                        </span>
                                    </div>
                                </div>
                                <div class="ud-booking-footer">
                                    <div class="ud-booking-price-block">
                                        <span class="ud-booking-price-label"><?php _e('Precio', 'urbandog'); ?></span>
                                        <span class="ud-booking-price-value">S/ 55</span>
                                    </div>
                                    <a href="#" class="ud-btn-booking ud-btn-booking--outline">
                                        <i data-lucide="arrow-right"></i>
                                        <?php _e('Ver detalles', 'urbandog'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; // end WP_DEBUG mock cards ?>

                        <?php if ($active_bookings->have_posts()): ?>
                            <?php while ($active_bookings->have_posts()):
                                $active_bookings->the_post();
                                $booking_id = get_the_ID();
                                $status = get_post_meta($booking_id, 'ud_booking_status', true);
                                $service_type = get_post_meta($booking_id, 'ud_booking_service_type', true) ?: 'walk';
                                $date = get_post_meta($booking_id, 'ud_booking_date', true);
                                $time = get_post_meta($booking_id, 'ud_booking_time', true);
                                $walker_id = get_post_meta($booking_id, 'ud_booking_walker_id', true);
                                $walker = get_userdata($walker_id);
                                $price = get_post_meta($booking_id, 'ud_booking_price', true);

                                $is_mg = ($service_type === 'meet_greet');
                                $is_coordinating = ($date === __('Por coordinar', 'urbandog'));

                                // Card modifier class
                                $card_mod = $is_mg ? 'ud-booking-card--mg' : 'ud-booking-card--walk';

                                // Status badge
                                if ($status === 'accepted') {
                                    $badge_mod = 'ud-booking-status-badge--confirmed';
                                    $badge_text = __('Confirmado', 'urbandog');
                                } elseif ($is_coordinating) {
                                    $badge_mod = 'ud-booking-status-badge--coordinating';
                                    $badge_text = __('En coordinación', 'urbandog');
                                } else {
                                    $badge_mod = 'ud-booking-status-badge--pending';
                                    $badge_text = __('Pendiente', 'urbandog');
                                }

                                // Walker initial for avatar fallback
                                $walker_name = $walker->display_name ?? __('Paseador', 'urbandog');
                                $walker_initial = mb_strtoupper(mb_substr($walker_name, 0, 1));
                                $walker_photo = get_user_meta($walker_id, 'ud_walker_photo', true);

                                // CTA button
                                if ($is_mg && $is_coordinating) {
                                    $btn_class = 'ud-btn-booking--blue';
                                    $btn_icon = 'calendar-check';
                                } elseif ($status === 'accepted') {
                                    $btn_class = 'ud-btn-booking--primary';
                                    $btn_icon = 'arrow-right';
                                } else {
                                    $btn_class = 'ud-btn-booking--outline';
                                    $btn_icon = 'arrow-right';
                                }

                                // Service label
                                $service_label = $is_mg ? __('Meet & Greet', 'urbandog') : __('Paseo', 'urbandog');
                                $service_icon = $is_mg ? 'handshake' : 'footprints';
                                ?>
                                <div class="ud-booking-card <?php echo esc_attr($card_mod); ?>">
                                    <div class="ud-booking-accent"></div>
                                    <div class="ud-booking-body">
                                        <!-- Service type + status -->
                                        <div class="ud-booking-service">
                                            <div class="ud-booking-service-icon">
                                                <i data-lucide="<?php echo esc_attr($service_icon); ?>"></i>
                                            </div>
                                            <span class="ud-booking-service-name"><?php echo esc_html($service_label); ?></span>
                                            <span class="ud-booking-status-badge <?php echo esc_attr($badge_mod); ?>"
                                                style="margin-left:auto;">
                                                <?php echo esc_html($badge_text); ?>
                                            </span>
                                        </div>

                                        <!-- Walker -->
                                        <div class="ud-booking-walker">
                                            <div class="ud-booking-walker-avatar">
                                                <?php if ($walker_photo): ?>
                                                    <img src="<?php echo esc_url($walker_photo); ?>"
                                                        alt="<?php echo esc_attr($walker_name); ?>">
                                                <?php else: ?>
                                                    <?php echo esc_html($walker_initial); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="ud-booking-walker-name"><?php echo esc_html($walker_name); ?></div>
                                                <div class="ud-booking-walker-label">
                                                    <?php _e('Paseador verificado', 'urbandog'); ?></div>
                                            </div>
                                        </div>

                                        <!-- Date/time chips or M&G banner -->
                                        <?php if ($is_coordinating): ?>
                                            <div class="ud-booking-banner ud-booking-banner--info">
                                                <i data-lucide="info"></i>
                                                <span><?php _e('El paseador se pondrá en contacto contigo pronto para coordinar los detalles de tu reunión gratuita de Meet &amp; Greet.', 'urbandog'); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="ud-booking-chips">
                                                <span class="ud-booking-chip">
                                                    <i data-lucide="calendar"></i>
                                                    <?php echo esc_html($date); ?>
                                                </span>
                                                <?php if ($time): ?>
                                                    <span class="ud-booking-chip">
                                                        <i data-lucide="clock"></i>
                                                        <?php echo esc_html($time); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Footer: price + CTA -->
                                    <div class="ud-booking-footer">
                                        <div class="ud-booking-price-block">
                                            <span class="ud-booking-price-label"><?php _e('Precio', 'urbandog'); ?></span>
                                            <?php if (!$price || $price == 0): ?>
                                                <span
                                                    class="ud-booking-price-value is-free"><?php _e('Gratis', 'urbandog'); ?></span>
                                            <?php else: ?>
                                                <span class="ud-booking-price-value">S/ <?php echo esc_html($price); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="#" class="ud-btn-booking <?php echo esc_attr($btn_class); ?>">
                                            <i data-lucide="<?php echo esc_attr($btn_icon); ?>"></i>
                                            <?php _e('Ver detalles', 'urbandog'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        <?php else: ?>
                            <?php if (!defined('WP_DEBUG') || !WP_DEBUG): ?>
                                <div class="ud-empty-state-card">
                                    <div class="ud-empty-icon">
                                        <i data-lucide="paw-print"></i>
                                    </div>
                                    <h3 class="ud-h3"><?php _e('¿Listos para una aventura?', 'urbandog'); ?></h3>
                                    <p class="ud-subtitle">
                                        <?php _e('Tu perro está esperando su próximo paseo. Encuentra a un paseador verificado ahora.', 'urbandog'); ?>
                                    </p>
                                    <a href="<?php echo esc_url(home_url('/buscar/')); ?>"
                                        class="ud-btn ud-btn-primary ud-mt-4">
                                        <i data-lucide="search"></i>
                                        <?php _e('Buscar Paseador ahora', 'urbandog'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                </section>

                <!-- Pending Payments Section -->
                <?php
                // Query for pending transactions (awaiting payment or pending confirmation)
                $pending_transactions = new WP_Query([
                    'post_type' => 'ud_transaction',
                    'post_status' => 'publish',
                    'posts_per_page' => 10,
                    'meta_query' => [
                        'relation' => 'AND',
                        [
                            'key' => 'ud_transaction_owner_id',
                            'value' => $owner_id,
                            'compare' => '='
                        ],
                        [
                            'key' => 'ud_transaction_status',
                            'value' => ['awaiting_payment', 'pending'],
                            'compare' => 'IN'
                        ]
                    ]
                ]);

                if ($pending_transactions->have_posts()):
                    ?>
                    <section class="ud-section ud-mt-12">
                        <h2 class="ud-section-title">
                            <i data-lucide="credit-card" class="text-amber"></i>
                            <?php _e('Pagos Pendientes', 'urbandog'); ?>
                            <span class="ud-counter text-amber"><?php echo $pending_transactions->post_count; ?></span>
                        </h2>

                        <div class="ud-list-stack">
                            <?php while ($pending_transactions->have_posts()):
                                $pending_transactions->the_post();
                                $transaction_id = get_the_ID();
                                $booking_id = get_post_meta($transaction_id, 'ud_transaction_booking_id', true);
                                $walker_id = get_post_meta($transaction_id, 'ud_transaction_walker_id', true);
                                $amount = get_post_meta($transaction_id, 'ud_transaction_amount_total', true);
                                $status = get_post_meta($transaction_id, 'ud_transaction_status', true);
                                $walker = get_userdata($walker_id);
                                $booking_date = get_post_meta($booking_id, 'ud_booking_date', true);
                                ?>
                                <div class="ud-card ud-payment-card">
                                    <div class="ud-payment-header">
                                        <h3 class="ud-h3">
                                            <?php printf(__('Pago para paseo con %s', 'urbandog'), esc_html($walker->display_name ?? 'Paseador')); ?>
                                        </h3>
                                        <span
                                            class="ud-transaction-status <?php echo $status === 'pending' ? 'pending' : 'awaiting'; ?>">
                                            <?php echo $status === 'pending' ? __('Pendiente de Confirmación', 'urbandog') : __('Esperando Pago', 'urbandog'); ?>
                                        </span>
                                    </div>

                                    <div class="ud-payment-info">
                                        <p class="ud-text-sm">
                                            <i data-lucide="calendar"></i>
                                            <?php printf(__('Fecha del paseo: %s', 'urbandog'), esc_html($booking_date)); ?>
                                        </p>
                                        <p class="ud-payment-amount">S/ <?php echo number_format($amount, 2); ?></p>
                                    </div>

                                    <?php if ($status === 'awaiting_payment'): ?>
                                        <!-- Payment Form -->
                                        <form class="ud-payment-form" data-transaction-id="<?php echo $transaction_id; ?>">
                                            <h4 class="ud-h4"><?php _e('Completa tu pago', 'urbandog'); ?></h4>

                                            <!-- Payment Method Selector -->
                                            <div class="ud-payment-method-selector">
                                                <div class="ud-payment-method-option">
                                                    <input type="radio" name="method" value="yape"
                                                        id="method-yape-<?php echo $transaction_id; ?>">
                                                    <label for="method-yape-<?php echo $transaction_id; ?>"
                                                        class="ud-payment-method-label">
                                                        <div class="ud-payment-method-logo">
                                                            <i data-lucide="smartphone" style="width: 60px; height: 60px;"></i>
                                                        </div>
                                                        <span class="ud-payment-method-name">Yape</span>
                                                    </label>
                                                </div>
                                                <div class="ud-payment-method-option">
                                                    <input type="radio" name="method" value="plin"
                                                        id="method-plin-<?php echo $transaction_id; ?>">
                                                    <label for="method-plin-<?php echo $transaction_id; ?>"
                                                        class="ud-payment-method-label">
                                                        <div class="ud-payment-method-logo">
                                                            <i data-lucide="wallet" style="width: 60px; height: 60px;"></i>
                                                        </div>
                                                        <span class="ud-payment-method-name">Plin</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- QR Code (Placeholder) -->
                                            <div class="ud-qr-code-container">
                                                <div class="ud-qr-code-image">
                                                    <i data-lucide="qr-code" style="width: 100%; height: 100%;"></i>
                                                </div>
                                                <p class="ud-payment-instructions">
                                                    <?php _e('Escanea el código QR con tu app de Yape o Plin y realiza el pago de:', 'urbandog'); ?>
                                                </p>
                                                <p class="ud-payment-amount">S/ <?php echo number_format($amount, 2); ?></p>
                                            </div>

                                            <!-- Reference Number -->
                                            <div class="ud-form-group">
                                                <label for="reference-<?php echo $transaction_id; ?>" class="ud-label">
                                                    <?php _e('Número de Operación', 'urbandog'); ?> *
                                                </label>
                                                <input type="text" name="reference" id="reference-<?php echo $transaction_id; ?>"
                                                    class="ud-input" placeholder="<?php _e('Ej: 123456789', 'urbandog'); ?>"
                                                    required>
                                            </div>

                                            <!-- Proof Image Upload -->
                                            <div class="ud-form-group">
                                                <label class="ud-label">
                                                    <?php _e('Comprobante de Pago', 'urbandog'); ?> *
                                                </label>
                                                <div class="ud-image-upload-area">
                                                    <input type="file" name="proof_image" accept="image/*"
                                                        class="ud-proof-image-input" style="display: none;"
                                                        id="proof-<?php echo $transaction_id; ?>" required>
                                                    <label for="proof-<?php echo $transaction_id; ?>" style="cursor: pointer;">
                                                        <div class="ud-upload-icon">
                                                            <i data-lucide="upload"></i>
                                                        </div>
                                                        <p class="ud-text-sm">
                                                            <?php _e('Haz clic para subir una captura de pantalla', 'urbandog'); ?>
                                                        </p>
                                                        <p class="ud-text-xs text-gray-500">
                                                            <?php _e('JPG, PNG o WEBP (máx. 5MB)', 'urbandog'); ?>
                                                        </p>
                                                    </label>
                                                </div>
                                                <div class="ud-image-preview" style="display: none;">
                                                    <img src="" alt="Preview">
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <button type="submit" class="ud-btn ud-btn-primary ud-btn-block">
                                                <i data-lucide="send"></i>
                                                <?php _e('Enviar Comprobante', 'urbandog'); ?>
                                            </button>
                                        </form>
                                    <?php elseif ($status === 'pending'): ?>
                                        <div class="ud-alert ud-alert-info">
                                            <i data-lucide="clock"></i>
                                            <p><?php _e('Tu comprobante está siendo revisado por nuestro equipo. Te notificaremos por email cuando sea confirmado.', 'urbandog'); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Historial de Paseos Section -->
                <?php if ($completed_walks_query->have_posts()): ?>
                    <section class="ud-section ud-mt-12">
                        <h2 class="ud-section-title">
                            <i data-lucide="history" class="text-slate-500"></i>
                            <?php _e('Historial de Paseos', 'urbandog'); ?>
                            <span class="ud-counter"><?php echo $completed_walks_query->post_count; ?></span>
                        </h2>

                        <div class="ud-list-stack">
                            <?php while ($completed_walks_query->have_posts()):
                                $completed_walks_query->the_post();
                                $booking_id = get_the_ID();
                                $date = get_post_meta($booking_id, 'ud_booking_date', true);
                                $walker_id = get_post_meta($booking_id, 'ud_booking_walker_id', true);
                                $walker = get_userdata($walker_id);
                                $can_rate = UD_Ratings::can_rate_booking($booking_id, $owner_id, 'owner_to_walker');
                                ?>
                                <div class="ud-card ud-history-card">
                                    <div class="ud-history-info">
                                        <div class="ud-status-block">
                                            <span class="ud-badge ud-badge-slate"><?php _e('Completado', 'urbandog'); ?></span>
                                            <span class="ud-meta-separator">•</span>
                                            <span class="ud-meta-text"><?php echo esc_html($date); ?></span>
                                        </div>
                                        <h3 class="ud-h3">
                                            <?php printf(__('Paseo con %s', 'urbandog'), esc_html($walker->display_name ?? 'Paseador')); ?>
                                        </h3>
                                    </div>
                                    <div class="ud-history-actions">
                                        <?php if ($can_rate): ?>
                                            <button type="button" class="ud-btn ud-btn-primary ud-btn-sm ud-open-rating"
                                                data-booking-id="<?php echo $booking_id; ?>"
                                                data-to-user-id="<?php echo $walker_id; ?>"
                                                data-to-name="<?php echo esc_attr($walker->display_name ?? 'Paseador'); ?>"
                                                data-type="owner_to_walker">
                                                <i data-lucide="star" class="w-4 h-4 mr-1"></i>
                                                <?php _e('Calificar', 'urbandog'); ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="ud-meta-text italic flex items-center">
                                                <i data-lucide="check-circle" class="inline w-4 h-4 mr-1 text-emerald-500"></i>
                                                <?php _e('Ya calificado', 'urbandog'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Pets Section -->
                <section class="ud-section ud-mt-12">
                    <div class="ud-section-header-flex">
                        <h2 class="ud-section-title">
                            <i data-lucide="dog" class="text-primary"></i>
                            <?php _e('Tu Manada', 'urbandog'); ?>
                        </h2>
                        <button type="button" class="ud-btn-add" id="ud-add-pet-btn">
                            <i data-lucide="plus"></i><?php _e('Agregar Perrito', 'urbandog'); ?>
                        </button>
                    </div>

                    <div class="ud-pets-grid" id="ud-pets-grid">
                        <?php if ($pets->have_posts()):
                            while ($pets->have_posts()):
                                $pets->the_post();
                                $p_id = get_the_ID();
                                $p_data = [
                                    'id' => $p_id,
                                    'name' => get_the_title(),
                                    'breed' => get_post_meta($p_id, 'ud_pet_breed', true),
                                    'age' => (int) get_post_meta($p_id, 'ud_pet_age', true),
                                    'weight' => (float) get_post_meta($p_id, 'ud_pet_weight', true),
                                    'temperament' => get_post_meta($p_id, 'ud_pet_temperament', true),
                                    'needs' => get_post_meta($p_id, 'ud_pet_special_needs', true),

                                    'image' => get_the_post_thumbnail_url($p_id, 'medium') ?: ''
                                ];
                                ?>
                                <div class="ud-pet-card" data-id="<?php echo $p_id; ?>"
                                    data-pet='<?php echo json_encode($p_data); ?>'>
                                    <div class="ud-pet-actions">
                                        <button class="ud-btn-icon ud-edit-pet" data-id="<?php echo $p_id; ?>">
                                            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                                        </button>
                                        <button class="ud-btn-icon delete ud-delete-pet" data-id="<?php echo $p_id; ?>"
                                            data-name="<?php echo esc_attr($p_data['name']); ?>">
                                            <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                        </button>
                                    </div>

                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail('medium', ['class' => 'ud-pet-image']); ?>
                                    <?php else: ?>
                                        <div class="ud-pet-no-image">
                                            <i data-lucide="dog" style="width: 48px; height: 48px;"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="ud-pet-info">
                                        <h3 class="ud-pet-name"><?php the_title(); ?></h3>
                                        <p class="ud-pet-breed"><?php echo esc_html($p_data['breed']); ?></p>

                                        <div class="ud-pet-meta">
                                            <div class="ud-pet-meta-item">
                                                <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                                                <?php printf(__('%d años', 'urbandog'), $p_data['age']); ?>
                                            </div>
                                            <div class="ud-pet-meta-item">
                                                <i data-lucide="weight" style="width: 14px; height: 14px;"></i>
                                                <?php printf('%.1f kg', $p_data['weight']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        else: ?>
                            <div class="ud-empty-state-card" style="grid-column: 1 / -1;">
                                <div class="ud-empty-icon"><i data-lucide="heart" style="color: #ef4444;"></i></div>
                                <h3 class="ud-h3"><?php _e('¡Tu manada se siente un poco vacía!', 'urbandog'); ?></h3>
                                <p><?php _e('Registra a tu perro ahora para que podamos encontrarle al paseador perfecto.', 'urbandog'); ?>
                                </p>
                                <button type="button" class="ud-btn ud-btn-primary ud-mt-4" id="ud-add-pet-btn-empty">
                                    <i data-lucide="plus-circle"></i>
                                    <?php _e('Agregar a mi mejor amigo', 'urbandog'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Pet Modal -->
                <div id="ud-pet-modal" class="ud-modal">
                    <div class="ud-modal-content">
                        <button type="button" class="ud-modal-close"><i data-lucide="x"></i></button>
                        <div class="ud-modal-header">
                            <h2><?php _e('Agregar Perrito', 'urbandog'); ?></h2>
                            <p><?php _e('Cuéntanos más sobre tu mejor amigo.', 'urbandog'); ?></p>
                        </div>

                        <form id="ud-pet-form" enctype="multipart/form-data">
                            <input type="hidden" name="pet_id" id="pet-id" value="">

                            <div class="ud-image-upload-preview" id="ud-pet-image-preview">
                                <div class="placeholder">
                                    <i data-lucide="camera"></i>
                                    <p><?php _e('Haz clic para subir foto', 'urbandog'); ?></p>
                                </div>
                            </div>
                            <input type="file" name="image" id="pet-image" accept="image/*" style="display: none;">

                            <div class="ud-form-row">
                                <div class="ud-form-group">
                                    <label for="pet-name"><?php _e('Nombre de la mascota', 'urbandog'); ?> *</label>
                                    <input type="text" name="name" id="pet-name" class="ud-form-control" required>
                                </div>
                                <div class="ud-form-group">
                                    <label for="pet-breed"><?php _e('Raza', 'urbandog'); ?></label>
                                    <input type="text" name="breed" id="pet-breed" class="ud-form-control">
                                </div>
                            </div>

                            <div class="ud-form-row">
                                <div class="ud-form-group">
                                    <label for="pet-age"><?php _e('Edad (años)', 'urbandog'); ?></label>
                                    <input type="number" name="age" id="pet-age" class="ud-form-control" min="0">
                                </div>
                                <div class="ud-form-group">
                                    <label for="pet-weight"><?php _e('Peso (kg)', 'urbandog'); ?></label>
                                    <input type="number" step="0.1" name="weight" id="pet-weight"
                                        class="ud-form-control" min="0">
                                </div>
                            </div>

                            <div class="ud-form-group">
                                <label for="pet-temperament"><?php _e('Temperamento', 'urbandog'); ?></label>
                                <input type="text" name="temperament" id="pet-temperament" class="ud-form-control"
                                    placeholder="<?php _e('Ej: Muy juguetón, tranquilo, sociable', 'urbandog'); ?>">
                            </div>

                            <div class="ud-form-group">
                                <label
                                    for="pet-needs"><?php _e('Necesidades especiales o alergias', 'urbandog'); ?></label>
                                <textarea name="needs" id="pet-needs" class="ud-form-control" rows="2"></textarea>
                            </div>



                            <button type="submit" class="ud-btn-submit">
                                <?php _e('Guardar Mascota', 'urbandog'); ?>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <aside class="ud-sidebar">

                <!-- Stats -->
                <div class="ud-card ud-stats-card">
                    <h3 class="ud-h3"><?php _e('Tu Historial', 'urbandog'); ?></h3>
                    <div class="ud-stats-list">
                        <div class="ud-stat-row">
                            <div class="ud-stat-info">
                                <div class="ud-stat-icon ud-stat-icon-primary">
                                    <i data-lucide="check-circle"></i>
                                </div>
                                <span class="ud-stat-label"><?php _e('Paseos Completados', 'urbandog'); ?></span>
                            </div>
                            <span class="ud-stat-value"><?php echo esc_html($completed_walks); ?></span>
                        </div>
                        <div class="ud-stat-row">
                            <div class="ud-stat-info">
                                <div class="ud-stat-icon ud-stat-icon-amber">
                                    <i data-lucide="star"></i>
                                </div>
                                <span class="ud-stat-label"><?php _e('Reseñas dadas', 'urbandog'); ?></span>
                            </div>
                            <span class="ud-stat-value">0</span>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="ud-promo-card ud-dark-card">
                    <div class="ud-promo-content">
                        <h3 class="ud-h3"><?php _e('Gestión de Perfil', 'urbandog'); ?></h3>
                        <p class="ud-text-sm"><?php _e('Actualiza tu dirección y datos de contacto.', 'urbandog'); ?>
                        </p>
                        <a href="#" class="ud-promo-link">
                            <span><?php _e('Configuración', 'urbandog'); ?></span>
                            <i data-lucide="chevron-right"></i>
                        </a>
                    </div>
                    <div class="ud-decorative-glow"></div>
                </div>

                <!-- Tip -->
                <div class="ud-card ud-tip-card-emerald">
                    <div class="ud-tip-header">
                        <i data-lucide="info"></i>
                        <h4 class="ud-h4"><?php _e('Tip UrbanDog', 'urbandog'); ?></h4>
                    </div>
                    <p class="ud-tip-text">
                        "<?php _e('Recuerda tener lista la correa y el collar antes de que llegue el paseador para aprovechar al máximo el tiempo de salida.', 'urbandog'); ?>"
                    </p>
                </div>

            </aside>
        </div>
    </div>
</main>

<?php get_footer(); ?>