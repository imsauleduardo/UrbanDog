<?php
/**
 * Template Name: Owner Dashboard
 * 
 * @package UrbanDog
 */

if (!is_user_logged_in()) {
    wp_safe_redirect(wp_login_url(home_url('/mis-paseos/')));
    exit;
}

get_header();

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
$completed_walks = new WP_Query([
    'post_type' => 'ud_booking',
    'author' => $owner_id,
    'meta_key' => 'ud_booking_status',
    'meta_value' => 'completed',
    'posts_per_page' => -1
])->found_posts;

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
                        <?php if ($active_bookings->have_posts()): ?>
                            <?php while ($active_bookings->have_posts()):
                                $active_bookings->the_post();
                                $booking_id = get_the_ID();
                                $status = get_post_meta($booking_id, 'ud_booking_status', true);
                                $date = get_post_meta($booking_id, 'ud_booking_date', true);
                                $time = get_post_meta($booking_id, 'ud_booking_time', true);
                                $walker_id = get_post_meta($booking_id, 'ud_booking_walker_id', true);
                                $walker = get_userdata($walker_id);
                                $price = get_post_meta($booking_id, 'ud_booking_price', true);
                                ?>
                                <div class="ud-booking-card ud-card">
                                    <div class="ud-booking-info">
                                        <div class="ud-status-block">
                                            <span
                                                class="ud-badge <?php echo $status === 'accepted' ? 'ud-badge-emerald' : 'ud-badge-amber'; ?>">
                                                <?php echo $status === 'accepted' ? __('Confirmado', 'urbandog') : __('Pendiente', 'urbandog'); ?>
                                            </span>
                                            <span class="ud-meta-separator">•</span>
                                            <span class="ud-meta-text"><?php echo esc_html($date); ?></span>
                                            <span class="ud-meta-separator">•</span>
                                            <span class="ud-meta-text"><?php echo esc_html($time); ?></span>
                                        </div>
                                        <h3 class="ud-h3">
                                            <?php printf(__('Paseo con %s', 'urbandog'), esc_html($walker->display_name ?? 'Paseador')); ?>
                                        </h3>
                                        <div class="ud-excerpt italic">
                                            <i data-lucide="info"></i>
                                            <?php the_excerpt(); ?>
                                        </div>
                                    </div>
                                    <div class="ud-booking-summary">
                                        <div class="ud-price-block">
                                            <div class="ud-label uppercase"><?php _e('Precio', 'urbandog'); ?></div>
                                            <div class="ud-price">S/ <?php echo esc_html($price); ?></div>
                                        </div>
                                        <a href="#" class="ud-btn ud-btn-outline ud-btn-sm">
                                            <?php _e('Ver detalles', 'urbandog'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        <?php else: ?>
                            <div class="ud-empty-state-card">
                                <div class="ud-empty-icon">
                                    <i data-lucide="calendar-x"></i>
                                </div>
                                <h3 class="ud-h3"><?php _e('No tienes paseos activos', 'urbandog'); ?></h3>
                                <p class="ud-subtitle">
                                    <?php _e('Encuentra al paseador ideal para tu mejor amigo.', 'urbandog'); ?>
                                </p>
                                <a href="<?php echo esc_url(home_url('/buscar/')); ?>"
                                    class="ud-btn ud-btn-primary ud-mt-4">
                                    <?php _e('Empezar a buscar', 'urbandog'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Pets Section -->
                <section class="ud-section ud-mt-12">
                    <div class="ud-section-header-flex">
                        <h2 class="ud-section-title">
                            <i data-lucide="dog" class="text-primary"></i>
                            <?php _e('Tu Manada', 'urbandog'); ?>
                        </h2>
                        <button type="button" class="ud-link-primary font-bold" onclick="openPetModal()">
                            <i data-lucide="plus"></i><?php _e('Agregar Perrito', 'urbandog'); ?>
                        </button>
                    </div>

                    <div class="ud-grid-2" id="pet-list-container">
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
                                    'vaccines' => get_post_meta($p_id, 'ud_pet_vaccines', true),
                                    'image' => get_the_post_thumbnail_url($p_id, 'medium') ?: ''
                                ];
                                ?>
                                <div class="ud-pet-card ud-card" id="pet-card-<?php echo $p_id; ?>">
                                    <div class="ud-pet-avatar">
                                        <?php if (has_post_thumbnail()):
                                            the_post_thumbnail('thumbnail');
                                        else: ?>
                                            <i data-lucide="dog"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ud-pet-info">
                                        <h4 class="ud-h4"><?php the_title(); ?></h4>
                                        <p class="ud-text-sm">
                                            <?php echo esc_html($p_data['breed']); ?>
                                        </p>
                                    </div>
                                    <div class="ud-pet-actions ml-auto">
                                        <button class="ud-icon-btn" onclick='openPetModal(<?php echo json_encode($p_data); ?>)'>
                                            <i data-lucide="edit-3"></i>
                                        </button>
                                        <button class="ud-icon-btn ud-text-red" onclick="deletePet(<?php echo $p_id; ?>)">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        else: ?>
                            <div class="ud-empty-state-grid">
                                <p class="ud-text-sm"><?php _e('Aún no has registrado a tus perritos.', 'urbandog'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

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