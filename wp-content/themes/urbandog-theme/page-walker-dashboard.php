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
$user_data = get_userdata($walker_id);
$walker_name = $user_data->first_name ?: $user_data->display_name;
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

// Check for profile completeness
$profile_id = UD_Roles::get_walker_profile_id($walker_id);

// Query Pending Pet Approvals
global $wpdb;
$approvals_table = $wpdb->prefix . 'ud_pet_walker_approvals';
$pending_approvals = [];
if ($wpdb->get_var("SHOW TABLES LIKE '$approvals_table'") === $approvals_table) {
    $pending_approvals = $wpdb->get_results($wpdb->prepare(
        "SELECT a.*, p.post_title as pet_name 
         FROM $approvals_table a 
         JOIN $wpdb->posts p ON a.pet_id = p.ID 
         WHERE a.walker_id = %d AND a.status = 'pending_approval'
         ORDER BY a.updated_at DESC",
        $profile_id
    ));
}
$has_rates = get_post_meta($profile_id, 'ud_walker_price_30', true) || get_post_meta($profile_id, 'ud_walker_price_60', true);
$has_zone = get_post_meta($profile_id, 'ud_walker_zone', true);
$has_bio = !empty(strip_tags(get_post_field('post_content', $profile_id)));
$is_profile_complete = ($has_rates && $has_zone && $has_bio);
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
                <a href="<?php echo home_url('/ajustes-paseador/'); ?>" class="ud-btn ud-btn-primary">
                    <i data-lucide="settings"></i>
                    <?php _e('Configurar Perfil', 'urbandog'); ?>
                </a>

                <?php
                $profile_url = $profile_id ? get_permalink($profile_id) : get_author_posts_url($walker_id);
                ?>
                <a href="<?php echo esc_url($profile_url); ?>" class="ud-btn ud-btn-secondary" target="_blank">
                    <i data-lucide="external-link"></i>
                    <?php _e('Ver Mi Perfil', 'urbandog'); ?>
                </a>
            </div>
        </header>

        <!-- Profile Completion Banner -->
        <?php if (!$is_profile_complete): ?>
            <div class="ud-card ud-dark-card mb-8">
                <div class="ud-promo-content">
                    <div class="flex items-start gap-4">
                        <div class="ud-stat-icon ud-stat-icon-amber mt-1">
                            <i data-lucide="alert-triangle"></i>
                        </div>
                        <div>
                            <h2 class="ud-h3 text-white mb-2">
                                <?php _e('¡Completa tu perfil para empezar a ganar!', 'urbandog'); ?>
                            </h2>
                            <p class="ud-text-sm text-slate-300 mb-4">
                                <?php _e('Para que los dueños puedan contratarte, necesitas configurar tus tarifas, zonas de trabajo y una breve descripción sobre ti.', 'urbandog'); ?>
                            </p>
                            <div class="flex gap-4">
                                <a href="<?php echo home_url('/ajustes-paseador/'); ?>"
                                    class="ud-btn ud-btn-primary ud-btn-sm">
                                    <?php _e('Configurar ahora', 'urbandog'); ?>
                                </a>
                                <div class="flex items-center gap-4 ud-text-xs mt-1">
                                    <span
                                        class="flex items-center gap-1 <?php echo $has_rates ? 'text-emerald-400' : 'text-slate-400'; ?>">
                                        <i data-lucide="<?php echo $has_rates ? 'check-circle' : 'circle'; ?>"
                                            class="w-3 h-3"></i> <?php _e('Tarifas', 'urbandog'); ?>
                                    </span>
                                    <span
                                        class="flex items-center gap-1 <?php echo $has_zone ? 'text-emerald-400' : 'text-slate-400'; ?>">
                                        <i data-lucide="<?php echo $has_zone ? 'check-circle' : 'circle'; ?>"
                                            class="w-3 h-3"></i> <?php _e('Zonas', 'urbandog'); ?>
                                    </span>
                                    <span
                                        class="flex items-center gap-1 <?php echo $has_bio ? 'text-emerald-400' : 'text-slate-400'; ?>">
                                        <i data-lucide="<?php echo $has_bio ? 'check-circle' : 'circle'; ?>"
                                            class="w-3 h-3"></i> <?php _e('Bio', 'urbandog'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="ud-dashboard-layout">

            <!-- Main Content: Bookings -->
            <div class="ud-main-col">

                <!-- Pending Requests -->
                <section class="ud-section">
                    <h2 class="ud-section-title">
                        <i data-lucide="bell" class="text-amber"></i>
                        <?php _e('Solicitudes Pendientes', 'urbandog'); ?>
                        <?php if ($pending_bookings->have_posts()): ?>
                            <span class="ud-counter text-amber"><?php echo $pending_bookings->post_count; ?></span>
                        <?php endif; ?>
                    </h2>

                    <div class="ud-list-stack">
                        <?php if ($pending_bookings->have_posts()): ?>
                            <?php while ($pending_bookings->have_posts()):
                                $pending_bookings->the_post();
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
                                                <span><i
                                                        data-lucide="dog"></i><?php printf(_n('%d perro', '%d perros', $dogs, 'urbandog'), $dogs); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ud-booking-amount">
                                        <div class="ud-price">S/ <?php echo esc_html($price); ?></div>
                                        <div class="ud-label uppercase">
                                            <?php echo $modality === 'group' ? __('Paseo Grupal', 'urbandog') : __('Paseo Individual', 'urbandog'); ?>
                                        </div>
                                    </div>

                                    <div class="ud-actions">
                                        <button onclick="updateBookingStatus(<?php echo $booking_id; ?>, 'accepted')"
                                            class="ud-btn ud-btn-primary ud-btn-sm">
                                            <?php _e('Aceptar', 'urbandog'); ?>
                                        </button>
                                        <button onclick="updateBookingStatus(<?php echo $booking_id; ?>, 'rejected')"
                                            class="ud-btn ud-btn-outline ud-btn-sm">
                                            <?php _e('Rechazar', 'urbandog'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        <?php else: ?>
                            <div class="ud-empty-state-card">
                                <div class="ud-empty-icon">
                                    <i data-lucide="bell-off"></i>
                                </div>
                                <h3 class="ud-h3"><?php _e('Sin solicitudes nuevas', 'urbandog'); ?></h3>
                                <p class="ud-subtitle">
                                    <?php _e('Por ahora no tienes solicitudes pendientes. ¡Asegúrate de que tu perfil esté completo para atraer más dueños!', 'urbandog'); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Pending Pet Approvals (Meet & Greet) -->
                <?php if (!empty($pending_approvals)): ?>
                    <section class="ud-section ud-mt-12">
                        <h2 class="ud-section-title">
                            <i data-lucide="shield-check" class="text-primary"></i>
                                <?php _e('Mascotas por Aprobar (Post Meet & Greet)', 'urbandog'); ?>
                            <span class="ud-counter text-primary"><?php echo count($pending_approvals); ?></span>
                        </h2>

                        <div class="ud-list-stack">
                                <?php foreach ($pending_approvals as $approval):
                                    $pet_id = $approval->pet_id;
                                    $pet_name = $approval->pet_name;
                                    $owner_name = get_userdata(get_post_field('post_author', $pet_id))->display_name;
                                    $pet_thumbnail = get_the_post_thumbnail_url($pet_id, 'thumbnail');
                                    ?>
                                <div class="ud-booking-card ud-card ud-border-primary">
                                    <div class="ud-user-info">
                                        <div class="ud-avatar">
                                         <?php if ($pet_thumbnail): ?>
                                                <img src="<?php echo esc_url($pet_thumbnail); ?>"
                                                    alt="<?php echo esc_attr($pet_name); ?>">
                                          <?php else: ?>
                                                <i data-lucide="dog"></i>
                                          <?php endif; ?>
                                        </div>
                                        <div class="ud-user-details">
                                            <h3 class="ud-h3"><?php echo esc_html($pet_name); ?></h3>
                                            <p class="ud-meta">
                                           <?php printf(__('Dueño: %s', 'urbandog'), esc_html($owner_name)); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="ud-actions">
                                        <button
                                            onclick="approvePet(<?php echo $pet_id; ?>, <?php echo $profile_id; ?>, 'approve')"
                                            class="ud-btn ud-btn-primary ud-btn-sm">
                                            <i data-lucide="thumbs-up"></i>
                                         <?php _e('Aprobar', 'urbandog'); ?>
                                        </button>
                                        <button
                                            onclick="approvePet(<?php echo $pet_id; ?>, <?php echo $profile_id; ?>, 'reject')"
                                            class="ud-btn ud-btn-outline ud-btn-sm text-red-500 hover:bg-red-50"
                                            style="color: #ef4444;">
                                            <i data-lucide="thumbs-down"></i>
                                         <?php _e('Rechazar', 'urbandog'); ?>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Upcoming Walks -->
                <section class="ud-section ud-mt-12">
                    <h2 class="ud-section-title">
                        <i data-lucide="calendar-check" class="text-emerald"></i>
                        <?php _e('Próximos Paseos', 'urbandog'); ?>
                    </h2>

                    <div class="ud-list-stack">
                        <?php if ($confirmed_bookings->have_posts()): ?>
                            <?php while ($confirmed_bookings->have_posts()):
                                $confirmed_bookings->the_post();
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
                                        <?php if ($status === 'accepted'): ?>
                                            <button onclick="updateBookingStatus(<?php echo $booking_id; ?>, 'completed')"
                                                class="ud-btn ud-btn-primary ud-btn-sm">
                                                <i data-lucide="check"></i>
                                                <?php _e('Finalizar Paseo', 'urbandog'); ?>
                                            </button>
                                        <?php endif; ?>
                                        <button class="ud-icon-btn">
                                            <i data-lucide="message-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        <?php else: ?>
                            <div class="ud-empty-state-card">
                                <div class="ud-empty-icon text-emerald">
                                    <i data-lucide="calendar-off"></i>
                                </div>
                                <h3 class="ud-h3"><?php _e('Agenda libre', 'urbandog'); ?></h3>
                                <p class="ud-subtitle">
                                    <?php _e('No tienes paseos confirmados para los próximos días.', 'urbandog'); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Completed Walks (for rating owners) -->

                <?php
                $completed_walker_query = new WP_Query([
                    'post_type' => 'ud_booking',
                    'meta_query' => [
                        'relation' => 'AND',
                        ['key' => 'ud_booking_walker_id', 'value' => $walker_id],
                        ['key' => 'ud_booking_status', 'value' => 'completed']
                    ],
                    'posts_per_page' => 5,
                ]);
                ?>
                <section class="ud-section ud-mt-12">
                    <h2 class="ud-section-title">
                        <i data-lucide="history" class="text-slate"></i>
                        <?php _e('Paseos Recientes', 'urbandog'); ?>
                    </h2>
                    <div class="ud-list-stack">
                        <?php if ($completed_walker_query->have_posts()): ?>
                            <?php while ($completed_walker_query->have_posts()):
                                $completed_walker_query->the_post();
                                $booking_id = get_the_ID();
                                $owner_id = get_post_meta($booking_id, 'ud_booking_owner_id', true);
                                $owner_name = get_userdata($owner_id)->display_name;
                                ?>
                                <div class="ud-booking-card-simple ud-card bg-slate-50">
                                    <div class="ud-user-info-simple">
                                        <div class="ud-avatar-sm bg-slate-200">
                                            <i data-lucide="user"></i>
                                        </div>
                                        <div class="ud-user-details">
                                            <h3 class="ud-h3"><?php echo esc_html($owner_name); ?></h3>
                                            <p class="ud-meta-sm"><?php _e('Paseo Completado', 'urbandog'); ?></p>
                                        </div>
                                    </div>

                                    <div class="ud-actions-simple">
                                        <?php
                                        $can_rate = UD_Ratings::can_rate_booking($booking_id, $walker_id, 'walker_to_owner');
                                        if ($can_rate):
                                            ?>
                                            <button type="button" class="ud-btn ud-btn-secondary ud-btn-sm ud-open-rating"
                                                data-booking-id="<?php echo $booking_id; ?>"
                                                data-to-user-id="<?php echo $owner_id; ?>"
                                                data-to-name="<?php echo esc_attr($owner_name); ?>" data-type="walker_to_owner">
                                                <i data-lucide="star" class="w-4 h-4 mr-1"></i>
                                                <?php _e('Calificar', 'urbandog'); ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="ud-meta-sm italic flex items-center">
                                                <i data-lucide="check-circle" class="w-4 h-4 mr-1 text-emerald-500"></i>
                                                <?php _e('Ya calificado', 'urbandog'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        <?php else: ?>
                            <div class="ud-empty-state-card">
                                <div class="ud-empty-icon opacity-20">
                                    <i data-lucide="history"></i>
                                </div>
                                <h3 class="ud-h3"><?php _e('Sin historial visible', 'urbandog'); ?></h3>
                                <p class="ud-subtitle"><?php _e('Aún no has completado paseos.', 'urbandog'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>


            </div> <!-- .ud-main-col -->

            <!-- Sidebar -->
            <aside class="ud-sidebar">

                <!-- Stats Card -->
                <div class="ud-card ud-stats-card">
                    <h3 class="ud-h3 mb-4"><?php _e('Tu Resumen', 'urbandog'); ?></h3>
                    <div class="ud-stats-list">
                        <div class="ud-stat-row">
                            <div class="ud-stat-info">
                                <div class="ud-stat-icon ud-stat-icon-primary">
                                    <i data-lucide="check-circle"></i>
                                </div>
                                <span class="ud-stat-label"><?php _e('Paseos Completados', 'urbandog'); ?></span>
                            </div>
                            <span
                                class="ud-stat-value"><?php echo esc_html($completed_walker_query->found_posts); ?></span>
                        </div>
                        <div class="ud-stat-row">
                            <div class="ud-stat-info">
                                <div class="ud-stat-icon ud-stat-icon-amber">
                                    <i data-lucide="star"></i>
                                </div>
                                <span class="ud-stat-label"><?php _e('Calificación Promedio', 'urbandog'); ?></span>
                            </div>
                            <span class="ud-stat-value">5.0</span>
                        </div>
                        <div class="ud-stat-row">
                            <div class="ud-stat-info">
                                <div class="ud-stat-icon ud-stat-icon-emerald">
                                    <i data-lucide="trending-up"></i>
                                </div>
                                <span class="ud-stat-label"><?php _e('Nivel Actual', 'urbandog'); ?></span>
                            </div>
                            <span
                                class="ud-stat-value"><?php echo esc_html(get_user_meta($walker_id, 'ud_walker_level', true) ?: 'Novato'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Training & Achievements Card (New) -->
                <div class="ud-training-card">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="ud-h3 text-base"><?php _e('Capacitación', 'urbandog'); ?></h3>
                        <a href="#"
                            class="text-primary hover:text-primary-dark text-xs font-bold uppercase tracking-wide">
                            <?php _e('Ir a Zona', 'urbandog'); ?>
                        </a>
                    </div>

                    <div class="ud-badge-grid-sidebar">
                        <div class="ud-badge-item">
                            <div class="ud-badge-icon">
                                <i data-lucide="shield-check"></i>
                            </div>
                            <span class="ud-badge-name"><?php _e('Verificado', 'urbandog'); ?></span>
                        </div>
                        <div class="ud-badge-item">
                            <div class="ud-badge-icon">
                                <i data-lucide="zap"></i>
                            </div>
                            <span class="ud-badge-name"><?php _e('Rápido', 'urbandog'); ?></span>
                        </div>
                        <div class="ud-badge-item">
                            <div class="ud-badge-icon">
                                <i data-lucide="heart"></i>
                            </div>
                            <span class="ud-badge-name"><?php _e('Pet Lover', 'urbandog'); ?></span>
                        </div>
                        <div class="ud-badge-item">
                            <div class="ud-badge-icon">
                                <i data-lucide="star"></i>
                            </div>
                            <span class="ud-badge-name"><?php _e('Top Class', 'urbandog'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="ud-promo-card ud-dark-card mt-6">
                    <div class="ud-promo-content">
                        <h3 class="ud-h3 text-white"><?php _e('Gestión de Perfil', 'urbandog'); ?></h3>
                        <p class="ud-text-sm text-slate-300">
                            <?php _e('Configura tus tarifas, zonas de trabajo y disponibilidad.', 'urbandog'); ?>
                        </p>
                        <a href="<?php echo home_url('/ajustes-paseador/'); ?>" class="ud-promo-link">
                            <span><?php _e('Ir a Configuración', 'urbandog'); ?></span>
                            <i data-lucide="chevron-right"></i>
                        </a>
                    </div>
                    <div class="ud-decorative-glow"></div>
                </div>

                <!-- Tip Section -->
                <div class="ud-card ud-tip-card-emerald mt-6">
                    <div class="ud-tip-header">
                        <i data-lucide="lightbulb"></i>
                        <h4 class="ud-h4"><?php _e('Tip de Profesional', 'urbandog'); ?></h4>
                    </div>
                    <p class="ud-tip-text">
                        "<?php _e('Mantener tus fotos y bio actualizadas aumenta tus probabilidades de ser contratado en un 40%.', 'urbandog'); ?>"
                    </p>
                </div>

            </aside>

            <script>
                async function updateBookingStatus(bookingId, status) {
                    if (!confirm('<?php _e('¿Confirmas cambiar el estado de esta reserva?', 'urbandog'); ?>')) return;

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

                async function approvePet(petId, walkerId, decision) {
                    const message = decision === 'approve' 
                        ? '<?php _e('¿Estás seguro de que deseas aprobar a esta mascota?', 'urbandog'); ?>'
                        : '<?php _e('¿Estás seguro de que deseas rechazar a esta mascota?', 'urbandog'); ?>';
                    
                    if (!confirm(message)) return;

                    const formData = new FormData();
                    formData.append('action', 'ud_handle_pet_approval');
                    formData.append('pet_id', petId);
                    formData.append('walker_id', walkerId);
                    formData.append('decision', decision);
                    formData.append('notes', '');
                    formData.append('nonce', '<?php echo wp_create_nonce('ud_meetgreet_nonce'); ?>');

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
        </div> <!-- .ud-dashboard-layout -->
    </div> <!-- .ud-container -->
</main> <!-- .ud-dashboard-wrapper -->

<?php get_footer(); ?>