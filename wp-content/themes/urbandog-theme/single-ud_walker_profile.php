<?php
/**
 * The template for displaying single walker profiles.
 *
 * @package UrbanDog
 */

get_header();

$walker_id = get_the_ID();
$author_id = get_post_field('post_author', $walker_id);
$zone = get_post_meta($walker_id, 'ud_walker_zone', true);

// Pricing
$price_ind_30 = get_post_meta($walker_id, 'ud_walker_price_30', true);
$price_ind_60 = get_post_meta($walker_id, 'ud_walker_price_60', true);
$price_grp_30 = get_post_meta($walker_id, 'ud_walker_price_group_30', true);
$price_grp_60 = get_post_meta($walker_id, 'ud_walker_price_group_60', true);

// Bio & Capacity
$bio = get_the_content();
$max_dogs = get_post_meta($walker_id, 'ud_walker_max_dogs', true) ?: 0;
$pet_sizes = get_post_meta($walker_id, 'ud_walker_pet_sizes', true); // csv: pequeno, mediano...

// Badges & Zones
$certifications_json = get_post_meta($walker_id, 'ud_walker_certifications', true);
$certifications = json_decode($certifications_json, true) ?: [];
$extra_coverage_json = get_post_meta($walker_id, 'ud_walker_extra_coverage', true);

$thumbnail_url = get_the_post_thumbnail_url($walker_id, 'large');
$has_photo = !empty($thumbnail_url);

// Helper function to generate initials
function ud_get_initials($name)
{
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return strtoupper(substr($parts[0], 0, 1));
}

// Helper function to get avatar color based on name
function ud_get_avatar_color($name)
{
    $colors = ['#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#ef4444', '#14b8a6', '#6366f1'];
    $hash = 0;
    for ($i = 0; $i < strlen($name); $i++) {
        $hash = ord($name[$i]) + (($hash << 5) - $hash);
    }
    return $colors[abs($hash) % count($colors)];
}

$lat = get_post_meta($walker_id, 'ud_walker_lat', true);
$lng = get_post_meta($walker_id, 'ud_walker_lng', true);
$radius = get_post_meta($walker_id, 'ud_walker_radius_km', true) ?: 0.5;
$custom_schedules_json = get_post_meta($walker_id, 'ud_walker_custom_schedules', true) ?: '{}';
?>

<main class="walker-profile-page">
    <!-- Header/Hero Profile -->
    <section class="profile-hero">
        <div class="container profile-header-container">
            <div class="profile-main-info">
                <div class="profile-photo-gallery">
                    <?php if ($has_photo): ?>
                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php the_title(); ?>"
                            class="main-profile-img">
                    <?php else:
                        $initials = ud_get_initials(get_the_title());
                        $bg_color = ud_get_avatar_color(get_the_title());
                        ?>
                        <div class="main-profile-img"
                            style="background: <?php echo esc_attr($bg_color); ?>; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 4rem;">
                            <?php echo esc_html($initials); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-title-section">
                    <?php if (UD_Roles::is_walker_verified($author_id)): ?>
                        <div class="profile-badge-verified">
                            <i data-lucide="shield-check"></i>
                            <?php _e('Paseador Verificado', 'urbandog'); ?>
                        </div>
                    <?php endif; ?>
                    <h1 class="profile-name">
                        <?php the_title(); ?>
                        <?php if (!empty($certifications)): ?>
                            <div class="profile-certs-inline">
                                <?php foreach ($certifications as $cert): ?>
                                    <span class="cert-badge-pill" title="<?php echo esc_attr($cert); ?>">
                                        <i data-lucide="award"></i>
                                        <?php echo esc_html($cert); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </h1>
                    <div class="profile-meta-top">
                        <?php
                        $ratings_data = UD_Ratings::get_user_ratings($author_id);
                        ?>
                        <div class="profile-rating">
                            <i data-lucide="star" class="fill-current text-amber-400"></i>
                            <span class="rating-val"><?php echo number_format($ratings_data['average'], 1); ?></span>
                            <span
                                class="reviews-count">(<?php printf(_n('%d reseña', '%d reseñas', $ratings_data['count'], 'urbandog'), $ratings_data['count']); ?>)</span>
                        </div>
                        <div class="profile-location">
                            <i data-lucide="map-pin"></i>
                            <?php echo esc_html($zone); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container profile-layout-grid">
        <!-- Main Content Column -->
        <article class="profile-content">
            <section class="profile-section">
                <h2 class="section-title">
                    <?php _e('Sobre mí', 'urbandog'); ?>
                </h2>
                <div class="bio-text mb-6">
                    <?php echo wp_kses_post($bio); ?>
                </div>
                <div class="walker-stats-badges">
                    <?php if ($max_dogs): ?>
                        <div class="stat-badge">
                            <span class="label"><?php _e('Capacidad Máxima', 'urbandog'); ?></span>
                            <span
                                class="value"><?php printf(_n('%d perro', '%d perros', $max_dogs, 'urbandog'), $max_dogs); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php
                    $pet_sizes_data = json_decode($pet_sizes, true) ?: [];
                    if (!empty($pet_sizes_data)):
                        $size_labels = [
                            'small' => __('Pequeño', 'urbandog'),
                            'medium' => __('Mediano', 'urbandog'),
                            'large' => __('Grande', 'urbandog'),
                            'giant' => __('Gigante', 'urbandog'),
                        ];
                        ?>
                        <div class="stat-badge">
                            <span class="label"><?php _e('Tamaños Aceptados', 'urbandog'); ?></span>
                            <div class="sizes-row">
                                <?php foreach ($pet_sizes_data as $size_key):
                                    $label = $size_labels[$size_key] ?? $size_key;
                                    ?>
                                    <span class="size-tag"><?php echo esc_html($label); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="profile-section">
                <h2 class="section-title">
                    <?php _e('Servicios y Tarifas', 'urbandog'); ?>
                </h2>
                <div class="services-list">
                    <!-- Group Walk (Default/Promoted) -->
                    <div class="service-row-item promoted-service">
                        <span class="service-popular-label"><?php _e('Más popular', 'urbandog'); ?></span>
                        <div class="service-left">
                            <i data-lucide="users"></i>
                            <div class="service-info">
                                <h3 class="service-title-main"><?php _e('Paseo Grupal', 'urbandog'); ?></h3>
                                <p class="service-tagline">
                                    <?php _e('Socialización y ejercicio con otros amigos.', 'urbandog'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="service-right">
                            <div class="price-box text-right">
                                <span class="price-from"><?php _e('Desde', 'urbandog'); ?></span>
                                <div class="price-main highlighted">
                                    <span class="currency">S/</span>
                                    <span class="amount"><?php echo esc_html($price_grp_30); ?></span>
                                </div>
                                <span class="price-unit">/ 30 min</span>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Walk -->
                    <div class="service-row-item">
                        <div class="service-left">
                            <i data-lucide="user"></i>
                            <div class="service-info">
                                <h3 class="service-title-main"><?php _e('Paseo Individual', 'urbandog'); ?></h3>
                                <p class="service-tagline">
                                    <?php _e('Atencion personalizada 1 a 1 para tu perro.', 'urbandog'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="service-right">
                            <div class="price-box text-right">
                                <span class="price-from"><?php _e('Desde', 'urbandog'); ?></span>
                                <div class="price-main">
                                    <span class="currency">S/</span>
                                    <span class="amount"><?php echo esc_html($price_ind_30); ?></span>
                                </div>
                                <span class="price-unit">/ 30 min</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="profile-section">
                <h2 class="section-title">
                    <?php _e('Ubicación de ', 'urbandog'); ?>
                    <?php the_title(); ?>
                </h2>
                <p class="text-sm text-slate-500 mb-4">
                    <?php _e('Aproximadamente en la zona de ', 'urbandog'); ?>
                    <?php echo esc_html($zone); ?>
                </p>
                <div id="profile-map" class="profile-map-box"></div>
            </section>

            <!-- Ratings Section -->
            <section class="profile-section">
                <div class="ud-section-header flex items-center gap-3 mb-6">
                    <h2 class="section-title !mb-0"><?php _e('Reseñas de Clientes', 'urbandog'); ?></h2>
                    <span class="ud-reviews-count-tag">
                        <?php echo $ratings_data['count']; ?>
                    </span>
                </div>

                <div class="ud-reviews-summary-v2">
                    <div class="flex items-baseline gap-3">
                        <div class="ud-partial-stars" style="--rating: <?php echo $ratings_data['average']; ?>;">
                            <div class="stars-outer">
                                <?php for ($i = 0; $i < 5; $i++): ?><i data-lucide="star"></i><?php endfor; ?>
                            </div>
                            <div class="stars-inner">
                                <?php for ($i = 0; $i < 5; $i++): ?><i data-lucide="star"></i><?php endfor; ?>
                            </div>
                        </div>
                        <span class="ud-average-score"><?php echo $ratings_data['average']; ?></span>
                    </div>
                </div>

                <div class="ud-reviews-list" id="ud-reviews-container">
                    <?php if (!empty($ratings_data['ratings'])): ?>
                        <?php foreach ($ratings_data['ratings'] as $index => $review):
                            $is_hidden = $index >= 5;
                            ?>
                            <div class="ud-review-item <?php echo $is_hidden ? 'is-hidden-pagination' : ''; ?>"
                                data-index="<?php echo $index; ?>">
                                <div class="ud-review-header">
                                    <div class="ud-rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i data-lucide="star" class="<?php echo $i <= $review['score'] ? 'active' : ''; ?> w-3.5 h-3.5"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <?php /* Date hidden as requested */ ?>
                                </div>
                                <div class="ud-review-body">
                                    <?php
                                    $comment = esc_html($review['comment']);
                                    $is_long = strlen($comment) > 220;
                                    ?>
                                    <div class="ud-review-comment <?php echo $is_long ? 'is-collapsed' : ''; ?>">
                                        <?php echo strip_tags($comment); ?>
                                    </div>
                                    <?php if ($is_long): ?>
                                        <button class="ud-review-toggle"><?php _e('Ver más', 'urbandog'); ?></button>
                                    <?php endif; ?>
                                </div>
                                <div class="ud-review-meta-bottom mt-4">
                                    <span class="text-sm font-extrabold text-slate-800"><?php echo esc_html($review['from_user_name']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($ratings_data['ratings']) > 5): ?>
                            <div class="ud-reviews-pagination mt-8 text-center">
                                <button id="ud-load-more-reviews" class="ud-btn-pagination">
                                    <?php _e('Ver más reseñas', 'urbandog'); ?>
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-500 italic">
                            <?php _e('Aún no tiene reseñas. ¡Sé el primero en calificar su trabajo!', 'urbandog'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </section>
        </article>

        <!-- Sidebar Column -->
        <aside class="profile-sidebar">
            <?php
            $requires_mg = UD_MeetGreet::walker_requires_meetgreet($walker_id);
            $is_logged_in = is_user_logged_in();
            $user_id = get_current_user_id();
            $needs_mg = false;

            if ($requires_mg) {
                if ($is_logged_in) {
                    $needs_mg = UD_MeetGreet::needs_meetgreet($user_id, $walker_id);
                } else {
                    $needs_mg = true; // Invitados siempre necesitan M&G si el paseador lo requiere
                }
            }
            ?>
            <div class="ud-booking-widget <?php echo $needs_mg ? 'ud-widget-mg' : ''; ?>">
                <div class="ud-booking-widget-header">
                    <?php if ($needs_mg): ?>
                        <div class="ud-mg-header-notice">
                            <i data-lucide="info" width="40"></i>
                            <div class="ud-mg-header-content">
                                <h4 class="ud-mg-header-title"><?php _e('Meet & Greet', 'urbandog'); ?></h4>
                                <p><?php _e('Este paseador requiere una reunión previa (Meet & Greet) gratuita antes de aceptar nuevos paseos.', 'urbandog'); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="ud-booking-price-badge">
                            <span class="amount">S/ <?php echo esc_html($price_grp_30); ?></span>
                            <span class="label"><?php _e('precio base', 'urbandog'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <form id="ud-booking-form" class="ud-booking-form">
                    <input type="hidden" name="booking_type" value="<?php echo $needs_mg ? 'meetgreet' : 'walk'; ?>">
                    <input type="hidden" name="walker_id" value="<?php echo esc_attr($walker_id); ?>">
                    
                    <?php if ($needs_mg && !$is_logged_in): ?>
                        <!-- Quick Registration for Guests -->
                        <div class="ud-quick-reg-section">
                            
                            <div class="ud-form-row">
                                <div class="ud-form-group">
                                    <label for="reg-first-name"><?php _e('Nombre', 'urbandog'); ?></label>
                                    <input type="text" name="first_name" id="reg-first-name" class="ud-input" placeholder="Ej. Juan" required>
                                </div>
                                <div class="ud-form-group">
                                    <label for="reg-last-name"><?php _e('Apellido', 'urbandog'); ?></label>
                                    <input type="text" name="last_name" id="reg-last-name" class="ud-input" placeholder="Ej. Pérez" required>
                                </div>
                            </div>
                            <div class="ud-form-group">
                                <label for="reg-email"><?php _e('Tu Email', 'urbandog'); ?></label>
                                <input type="email" name="email" id="reg-email" class="ud-input" placeholder="juan@ejemplo.com" required>
                            </div>
                            <div class="ud-form-group">
                                <label for="reg-phone"><?php _e('Teléfono/WhatsApp', 'urbandog'); ?></label>
                                <input type="tel" name="phone" id="reg-phone" class="ud-input" placeholder="Ej. 987654321" required>
                            </div>
                            <div class="ud-form-group">
                                <label for="reg-password"><?php _e('Crea una Contraseña', 'urbandog'); ?></label>
                                <input type="password" name="password" id="reg-password" class="ud-input" placeholder="Mín. 8 caracteres" required>
                            </div>
                            <div class="ud-form-group">
                                <label for="reg-password-confirm"><?php _e('Confirma tu Contraseña', 'urbandog'); ?></label>
                                <input type="password" name="password_confirm" id="reg-password-confirm" class="ud-input" placeholder="Repite tu contraseña" required>
                            </div>
                            <input type="hidden" name="is_guest" value="1">
                        </div>
                    <?php endif; ?>

                    <?php if (!$needs_mg): ?>
                        <div class="ud-form-group">
                            <label><?php _e('Modalidad del paseo', 'urbandog'); ?></label>
                            <div class="ud-modality-selector">
                                <div class="ud-modality-option">
                                    <input type="radio" name="modality" value="group" id="mod-group" checked>
                                    <label for="mod-group"
                                        class="ud-modality-label"><?php _e('Grupal', 'urbandog'); ?></label>
                                </div>
                                <div class="ud-modality-option">
                                    <input type="radio" name="modality" value="individual" id="mod-ind">
                                    <label for="mod-ind"
                                        class="ud-modality-label"><?php _e('Individual', 'urbandog'); ?></label>
                                </div>
                            </div>
                        </div>

                        <div class="ud-form-group">
                            <label for="booking-duration"><?php _e('Duración', 'urbandog'); ?></label>
                            <select name="duration" id="booking-duration" class="ud-select">
                                <option value="30" selected><?php _e('30 minutos', 'urbandog'); ?></option>
                                <option value="60"><?php _e('60 minutos', 'urbandog'); ?></option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_logged_in): ?>
                        <div class="ud-form-group">
                            <label for="pet-selector"><?php _e('Tus Mascotas', 'urbandog'); ?></label>
                            <?php
                            $owner_pets = get_posts([
                                'post_type' => 'ud_pet',
                                'author' => $user_id,
                                'numberposts' => -1
                            ]);
                            ?>
                            <?php if (!empty($owner_pets)): ?>
                                <select name="pets[]" id="pet-selector" class="ud-select" multiple required>
                                    <?php foreach ($owner_pets as $pet): ?>
                                        <option value="<?php echo $pet->ID; ?>"><?php echo esc_html($pet->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="ud-text-xs mt-1 text-slate-500">
                                    <?php _e('Mantén presionado Ctrl/Cmd para seleccionar varios.', 'urbandog'); ?>
                                </p>
                            <?php else: ?>
                                <div class="ud-alert-mini">
                                    <p><?php printf(__('No tienes mascotas. <a href="%s">Agrega una aquí</a>.', 'urbandog'), home_url('/panel-dueno/')); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_logged_in || !$needs_mg): ?>
                        <div class="ud-form-group">
                            <label for="booking-date"><?php _e('Fecha y Hora del M&G', 'urbandog'); ?></label>
                            <div class="ud-datetime-row" style="display: flex; gap: 0.5rem;">
                                <input type="date" name="date" id="booking-date" class="ud-input"
                                    value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                <select name="time" id="booking-time" class="ud-select" required>
                                    <option value=""><?php _e('Horario', 'urbandog'); ?></option>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($needs_mg): ?>
                        <input type="hidden" name="modality" value="group">
                        <input type="hidden" name="duration" value="30">
                    <?php endif; ?>

                    <div class="ud-booking-summary-box <?php echo $needs_mg ? 'ud-hidden' : ''; ?>"
                        id="ud-booking-summary">
                        <div class="ud-summary-row">
                            <span><?php _e('Subtotal', 'urbandog'); ?></span>
                            <span>S/ <span id="ud-summary-subtotal">--</span></span>
                        </div>
                        <div class="ud-summary-row total">
                            <span><?php _e('Total Estimado', 'urbandog'); ?></span>
                            <span>S/ <span id="ud-summary-total">--</span></span>
                        </div>
                        <input type="hidden" name="price" id="final-price-input" value="">
                    </div>

                    <button type="submit" class="ud-btn-book <?php echo $needs_mg ? 'ud-btn-mg' : ''; ?>">
                        <i data-lucide="<?php echo $needs_mg ? 'shield-check' : 'calendar'; ?>"></i>
                        <?php
                        if ($needs_mg) {
                            _e('Solicitar Meet & Greet', 'urbandog');
                        } else {
                            echo is_user_logged_in() ? __('Solicitar Paseo', 'urbandog') : __('Reservar Paseo', 'urbandog');
                        }
                        ?>
                    </button>

                    <div id="ud-booking-alert" class="ud-booking-alert"></div>
                </form>

                <p class="widget-footer-note mt-4">
                    <i data-lucide="zap"></i>
                    <?php echo $needs_mg ? __('Esta primera reunión dura 15 min y es gratuita.', 'urbandog') : __('No te preocupes, no se te cobrará nada aún.', 'urbandog'); ?>
                </p>
            </div>
        </aside>
    </div>
</main>

<script>
    // Data for Map and Schedules (Booking logic moved to bookings.js)
    const customSchedules = <?php echo $custom_schedules_json; ?>;
    const dayMap = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];

    function updateTimeSlots(dateString) {
        if (!dateString) return;
        const date = new Date(dateString + 'T00:00:00');
        const dayName = dayMap[date.getDay()];
        const range = customSchedules[dayName];
        const select = document.getElementById('booking-time');
        if (!select) return;
        select.innerHTML = '';

        if (!range || range.toLowerCase() === 'cerrado') {
            const opt = document.createElement('option');
            opt.value = '';
            opt.innerText = '<?php _e('Sin disponibilidad', 'urbandog'); ?>';
            select.appendChild(opt);
            return;
        }

        try {
            const [start, end] = range.split('-');
            let [startH, startM] = start.split(':').map(Number);
            const [endH, endM] = end.split(':').map(Number);
            let currentH = startH;
            let currentM = startM;
            const endTotal = endH * 60 + endM;

            while ((currentH * 60 + currentM) < endTotal) {
                const timeStr = `${String(currentH).padStart(2, '0')}:${String(currentM).padStart(2, '0')}`;
                const opt = document.createElement('option');
                opt.value = timeStr;
                let ampm = currentH >= 12 ? 'PM' : 'AM';
                let displayH = currentH % 12 || 12;
                opt.innerText = `${displayH}:${String(currentM).padStart(2, '0')} ${ampm}`;
                select.appendChild(opt);
                currentM += 30;
                if (currentM >= 60) { currentH++; currentM = 0; }
            }
        } catch (e) { console.error("Error parsing range", e); }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Init slots for today
        const dateInput = document.getElementById('booking-date');
        if (dateInput) updateTimeSlots(dateInput.value);

        // Map initialization
        if (typeof L !== 'undefined' && document.getElementById('profile-map')) {
            const lat = <?php echo !empty($lat) ? $lat : -12.046374; ?>;
            const lng = <?php echo !empty($lng) ? $lng : -77.042793; ?>;
            const radius = <?php echo $radius; ?> * 1000; // km to meters

            const map = L.map('profile-map', {
                zoomControl: true,
                dragging: true,
                scrollWheelZoom: false,
                doubleClickZoom: true
            }).setView([lat, lng], 13);

            L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Main coverage area
            L.circle([lat, lng], {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.2,
                radius: radius
            }).addTo(map);

            // Extra Zones
            const extraZonesRaw = '<?php echo esc_js($extra_coverage_json); ?>';
            if (extraZonesRaw) {
                try {
                    const extraZones = JSON.parse(extraZonesRaw);
                    if (Array.isArray(extraZones)) {
                        extraZones.forEach(zone => {
                            if (zone.lat && zone.lng) {
                                L.circle([zone.lat, zone.lng], {
                                    color: '#3b82f6',
                                    fillColor: '#3b82f6',
                                    fillOpacity: 0.15,
                                    radius: (zone.radius || 0.5) * 1000
                                }).addTo(map);
                            }
                        });
                    }
                } catch (e) { console.error("Error parsing extra zones", e); }
            }
        }

        // Reviews Toggle
        document.querySelectorAll('.ud-review-toggle').forEach(btn => {
            btn.addEventListener('click', function () {
                const comment = this.previousElementSibling;
                if (comment.classList.contains('is-collapsed')) {
                    comment.classList.remove('is-collapsed');
                    comment.classList.add('is-expanded');
                    this.textContent = '<?php _e("Ver menos", "urbandog"); ?>';
                } else {
                    comment.classList.add('is-collapsed');
                    comment.classList.remove('is-expanded');
                    this.textContent = '<?php _e("Ver más", "urbandog"); ?>';
                }
            });
        });

        // Reviews Pagination
        const loadMoreBtn = document.getElementById('ud-load-more-reviews');
        if (loadMoreBtn) {
            let currentIndex = 5;
            const itemsPerBatch = 5;
            const container = document.getElementById('ud-reviews-container');
            const totalItems = container.querySelectorAll('.ud-review-item').length;

            loadMoreBtn.addEventListener('click', function () {
                const nextBatch = container.querySelectorAll(`.ud-review-item.is-hidden-pagination`);

                for (let i = 0; i < itemsPerBatch && i < nextBatch.length; i++) {
                    nextBatch[i].classList.remove('is-hidden-pagination');
                }

                currentIndex += itemsPerBatch;

                if (currentIndex >= totalItems) {
                    this.parentElement.style.display = 'none';
                }
            });
        }

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>

<?php get_footer(); ?>