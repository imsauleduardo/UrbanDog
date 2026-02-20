<?php
/**
 * Template Name: Walker Dashboard
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect if not logged in or not a walker
if (!is_user_logged_in() || !UD_Roles::is_walker()) {
    wp_safe_redirect(home_url());
    exit;
}

get_header();

$walker_id = get_current_user_id();
$walker_name = get_userdata($walker_id)->display_name;
$is_verified = UD_Roles::is_walker_verified($walker_id);

// Query Pending Bookings
$pending_bookings = new WP_Query([
    'post_type' => 'ud_booking',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'ud_booking_walker_id',
            'value' => $walker_id,
        ],
        [
            'key' => 'ud_booking_status',
            'value' => 'pending_request',
        ]
    ],
    'posts_per_page' => -1,
]);

// Query Confirmed Bookings
$confirmed_bookings = new WP_Query([
    'post_type' => 'ud_booking',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'ud_booking_walker_id',
            'value' => $walker_id,
        ],
        [
            'key' => 'ud_booking_status',
            'value' => 'accepted',
        ]
    ],
    'posts_per_page' => -1,
]);
?>

<main class="ud-dashboard-wrapper ud-walker-dashboard">
    <div class="ud-container">
        
        <!-- Header Section -->
        <header class="ud-dashboard-header">
            <div class="ud-welcome-text">
                <h1 class="ud-h1">
                    <?php printf(__('¡Hola, %s! 🐾', 'urbandog'), esc_html($walker_name)); ?>
                </h1>
                <p class="ud-subtitle"><?php _e('Gestiona tus servicios y solicitudes de paseo.', 'urbandog'); ?></p>
            </div>
            
            <div class="ud-header-actions">
                <?php if ($is_verified) : ?>
                    <span class="ud-badge ud-badge-verified">
                        <i data-lucide="check-circle"></i>
                        <?php _e('Perfil Verificado', 'urbandog'); ?>
                    </span>
                <?php else : ?>
                    <span class="ud-badge ud-badge-pending">
                        <i data-lucide="clock"></i>
                        <?php _e('Verificación Pendiente', 'urbandog'); ?>
                    </span>
                <?php endif; ?>
                
                <a href="<?php echo get_author_posts_url($walker_id); ?>" class="ud-btn ud-btn-secondary ud-btn-sm">
                    <i data-lucide="external-link"></i>
                    <?php _e('Ver Mi Perfil Público', 'urbandog'); ?>
                </a>
            </div>
        </header>

        <div class="ud-dashboard-layout">
            
            <!-- Main Content: Bookings -->
            <div class="ud-main-col">
                
                <!-- Pending Requests -->
                <section class="ud-section">
                    <h2 class="ud-section-title">
                        <i data-lucide="bell" class="text-amber"></i>
                        <?php _e('Solicitudes Pendientes', 'urbandog'); ?>
                        <?php if ($pending_bookings->have_posts()) : ?>
                            <span class="ud-counter text-amber"><?php echo $pending_bookings->post_count; ?></span>
                        <?php endif; ?>
                    </h2>
                    
                    <div class="ud-list-stack">
                        <?php if ($pending_bookings->have_posts()) : ?>
                            <?php while ($pending_bookings->have_posts()) : $pending_bookings->the_post(); 
                                $booking_id = get_the_ID();
                                $owner_id = get_post_meta($booking_id, 'ud_booking_owner_id', true);
                                $owner_data = get_userdata($owner_id);
                                $date = get_post_meta($booking_id, 'ud_booking_date', true);
                                $time = get_post_meta($booking_id, 'ud_booking_time', true);
                                $modality = get_post_meta($booking_id, 'ud_booking_modality', true);
                                $price = get_post_meta($booking_id, 'ud_booking_price', true);
                                $dogs = get_post_meta($booking_id, 'ud_booking_dogs', true);
                            ?>
                                <div class="ud-booking-card ud-card">
                                    <div class="ud-user-info">
                                        <div class="ud-avatar">
                                            <i data-lucide="user"></i>
                                        </div>
                                        <div class="ud-user-details">
                                            <h3 class="ud-h3"><?php echo esc_html($owner_data->display_name); ?></h3>
                                            <div class="ud-meta">
                                                <span><i data-lucide="calendar"></i><?php echo esc_html($date); ?></span>
                                                <span><i data-lucide="clock"></i><?php echo esc_html($time); ?></span>
                                                <span><i data-lucide="dog"></i><?php printf(_n('%d perro', '%d perros', $dogs, 'urbandog'), $dogs); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="ud-booking-amount">
                                        <div class="ud-price">S/ <?php echo esc_html($price); ?></div>
                                        <div class="ud-label uppercase"><?php echo $modality === 'group' ? __('Paseo Grupal', 'urbandog') : __('Paseo Individual', 'urbandog'); ?></div>
                                    </div>

                                    <div class="ud-actions">
                                        <button onclick="updateBookingStatus(<?php echo $booking_id; ?>, 'accepted')" class="ud-btn ud-btn-primary ud-btn-sm">
                                            <?php _e('Aceptar', 'urbandog'); ?>
                                        </button>
                                        <button onclick="updateBookingStatus(<?php echo $booking_id; ?>, 'rejected')" class="ud-btn ud-btn-outline ud-btn-sm">
                                            <?php _e('Rechazar', 'urbandog'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; wp_reset_postdata(); ?>
                        <?php else : ?>
                            <div class="ud-empty-state">
                                <i data-lucide="inbox"></i>
                                <p><?php _e('No tienes solicitudes nuevas.', 'urbandog'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Upcoming Walks -->
                <section class="ud-section">
                    <h2 class="ud-section-title">
                        <i data-lucide="calendar-check" class="text-emerald"></i>
                        <?php _e('Próximos Paseos', 'urbandog'); ?>
                    </h2>
                    
                    <div class="ud-list-stack">
                        <?php if ($confirmed_bookings->have_posts()) : ?>
                            <?php while ($confirmed_bookings->have_posts()) : $confirmed_bookings->the_post(); 
                                $booking_id = get_the_ID();
                                $owner_id = get_post_meta($booking_id, 'ud_booking_owner_id', true);
                                $owner_data = get_userdata($owner_id);
                                $date = get_post_meta($booking_id, 'ud_booking_date', true);
                                $time = get_post_meta($booking_id, 'ud_booking_time', true);
                                $status = get_post_meta($booking_id, 'ud_booking_status', true);
                            ?>
                                <div class="ud-booking-card-simple ud-card ud-border-emerald">
                                    <div class="ud-user-info-simple">
                                        <div class="ud-avatar-sm ud-bg-emerald">
                                            <i data-lucide="dog"></i>
                                        </div>
                                        <div class="ud-user-details">
                                            <h3 class="ud-h3"><?php echo esc_html($owner_data->display_name); ?></h3>
                                            <div class="ud-meta-sm">
                                                <?php echo esc_html($date); ?> a las <?php echo esc_html($time); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="ud-actions-simple">
                                        <?php if ($status === 'accepted') : ?>
                                            <span class="ud-status-badge ud-status-emerald"><?php _e('CONFIRMADO', 'urbandog'); ?></span>
                                        <?php endif; ?>
                                        <button class="ud-icon-btn">
                                            <i data-lucide="message-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; wp_reset_postdata(); ?>
                        <?php else : ?>
                            <div class="ud-empty-state-compact">
                                <p><?php _e('No tienes paseos confirmados aún.', 'urbandog'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

            </div>

            <!-- Sidebar -->
            <aside class="ud-sidebar">
                
                <!-- Earnings -->
                <div class="ud-card ud-stats-card">
                    <h3 class="ud-h3"><?php _e('Resumen de Ganancias', 'urbandog'); ?></h3>
                    <div class="ud-stats-content">
                        <div class="ud-stat-main">
                            <div class="ud-stat-label"><?php _e('Este Mes', 'urbandog'); ?></div>
                            <div class="ud-stat-value">S/ 0.00</div>
                        </div>
                        <div class="ud-stat-grid">
                            <div class="ud-stat-item">
                                <div class="ud-stat-label-sm"><?php _e('Paseos Hoy', 'urbandog'); ?></div>
                                <div class="ud-stat-value-sm">0</div>
                            </div>
                            <div class="ud-stat-item">
                                <div class="ud-stat-label-sm"><?php _e('Rating', 'urbandog'); ?></div>
                                <div class="ud-stat-value-sm">5.0 ⭐</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Management -->
                <div class="ud-promo-card ud-dark-card">
                    <div class="ud-promo-content">
                        <h3 class="ud-h3"><?php _e('Gestión de Perfil', 'urbandog'); ?></h3>
                        <p class="ud-text-sm"><?php _e('Mantén tus horarios y zonas actualizados para recibir más solicitudes.', 'urbandog'); ?></p>
                        <a href="#" class="ud-link-emerald">
                            <?php _e('Editar Zonas y Horarios', 'urbandog'); ?>
                            <i data-lucide="chevron-right"></i>
                        </a>
                    </div>
                    <i data-lucide="map-pin" class="ud-decorative-icon"></i>
                </div>

                <!-- Tips -->
                <div class="ud-card ud-tip-card">
                    <h3 class="ud-h3">
                        <i data-lucide="lightbulb" class="text-amber"></i>
                        <?php _e('Tip UrbanDog', 'urbandog'); ?>
                    </h3>
                    <p class="ud-text-sm">
                        <?php _e('¿Sabías que los paseadores que responden en menos de 10 minutos tienen un 40% más de probabilidad de confirmar el paseo?', 'urbandog'); ?>
                    </p>
                </div>
            </aside>

        </div>
    </div>
</main>

<script>
async function updateBookingStatus(bookingId, status) {
    if (!confirm('<?php _e('¿Estás seguro de realizar esta acción?', 'urbandog'); ?>')) return;

    const formData = new FormData();
    formData.append('action', 'ud_update_booking_status');
    formData.append('booking_id', bookingId);
    formData.append('status', status);
    formData.append('nonce', '<?php echo wp_create_nonce('ud_booking_nonce'); ?>');

    try {
        const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert(data.data.message || 'Error');
        }
    } catch (e) {
        console.error(e);
        alert('Error de conexión');
    }
}
</script>

<?php get_footer(); ?>
