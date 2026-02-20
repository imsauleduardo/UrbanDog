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

$thumbnail = get_the_post_thumbnail_url($walker_id, 'large') ?: get_template_directory_uri() . '/assets/images/placeholder-walker.jpg';
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
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title(); ?>" class="main-profile-img">
                </div>
                <div class="profile-title-section">
                    <div class="profile-badge-verified">
                        <i data-lucide="shield-check"></i>
                        <?php _e('Paseador Verificado', 'urbandog'); ?>
                    </div>
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
                        <div class="profile-rating">
                            <i data-lucide="star" class="fill-current text-amber-400"></i>
                            <span class="rating-val">4.9</span>
                            <span class="reviews-count">(
                                <?php echo rand(10, 50); ?> reseñas)
                            </span>
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
                    <?php if (!empty($pet_sizes)):
                        $sizes_arr = explode(',', $pet_sizes);
                        ?>
                        <div class="stat-badge">
                            <span class="label"><?php _e('Tamaños Aceptados', 'urbandog'); ?></span>
                            <div class="sizes-row">
                                <?php foreach ($sizes_arr as $size): ?>
                                    <span class="size-tag"><?php echo esc_html(trim($size)); ?></span>
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
                                <span class="price-amount text-emerald-600 font-bold block">S/
                                    <?php echo esc_html($price_grp_30); ?></span>
                                <span class="price-unit text-xs text-slate-500">/ 30 min</span>
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
                                <span class="price-amount font-bold block">S/
                                    <?php echo esc_html($price_ind_30); ?></span>
                                <span class="price-unit text-xs text-slate-500">/ 30 min</span>
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
        </article>

        <!-- Sidebar Column -->
        <aside class="profile-sidebar">
            <div class="booking-widget sticky">
                <div class="widget-header">
                    <div class="widget-price-summary">
                        <span
                            class="label text-slate-500 text-xs block mb-1"><?php _e('Total estimado', 'urbandog'); ?></span>
                        <div class="price-display">
                            <span class="val text-2xl font-bold text-slate-800" id="booking-total-price">S/
                                <?php echo esc_html($price_grp_30); ?></span>
                        </div>
                    </div>
                </div>

                <div class="widget-form">
                    <div class="form-grid-2 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label
                                class="text-xs font-semibold text-slate-600 mb-1 block"><?php _e('Modalidad', 'urbandog'); ?></label>
                            <select class="form-select w-full" id="booking-modality">
                                <option value="group" selected><?php _e('Grupal', 'urbandog'); ?></option>
                                <option value="individual"><?php _e('Individual', 'urbandog'); ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label
                                class="text-xs font-semibold text-slate-600 mb-1 block"><?php _e('Duración', 'urbandog'); ?></label>
                            <select class="form-select w-full" id="booking-duration" onchange="calculateTotal()">
                                <option value="30" selected>30 min</option>
                                <option value="60">60 min</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label
                            class="text-xs font-semibold text-slate-600 mb-1 block"><?php _e('¿Cuántos perros?', 'urbandog'); ?></label>
                        <div class="counter-input-simple flex items-center justify-between border rounded-lg p-2">
                            <button type="button" class="ctrl-btn text-emerald-600" onclick="updateDogs(-1)"><i
                                    data-lucide="minus-circle"></i></button>
                            <input type="number" id="booking-dog-count"
                                class="text-center font-bold bg-transparent border-none w-12" value="1" min="1"
                                max="<?php echo esc_attr($max_dogs); ?>" readonly>
                            <button type="button" class="ctrl-btn text-emerald-600" onclick="updateDogs(1)"><i
                                    data-lucide="plus-circle"></i></button>
                        </div>
                    </div>

                    <div class="form-group mb-6">
                        <label
                            class="text-xs font-semibold text-slate-600 mb-1 block"><?php _e('Fecha y Horario', 'urbandog'); ?></label>
                        <div class="datetime-compact" style="display: flex; gap: 0.5rem;">
                            <input type="date" id="booking-date" class="form-input flex-1"
                                value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>"
                                onchange="updateTimeSlots(this.value)"
                                style="padding: 0.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                            <select id="booking-time-slot" class="form-select flex-1">
                                <option value=""><?php _e('Selecciona fecha', 'urbandog'); ?></option>
                            </select>
                        </div>
                    </div>

                    <button id="submit-booking-btn" class="btn btn-primary btn-full-width mb-4"
                        onclick="submitBooking()">
                        <?php _e('Solicitar Paseo', 'urbandog'); ?>
                    </button>
                    <?php wp_nonce_field('ud_booking_nonce', 'ud_booking_nonce_field'); ?>

                    <div class="weekly-package-promo">
                        <div class="weekly-promo-header">
                            <i data-lucide="calendar"></i>
                            <span><?php _e('¡Ahorra con paquetes!', 'urbandog'); ?></span>
                        </div>
                        <p class="weekly-promo-text">
                            <?php _e('Reserva 5 paseos a la semana y obtén un 10% de descuento automático.', 'urbandog'); ?>
                        </p>
                    </div>

                    <p class="widget-footer-note">
                        <i data-lucide="zap"></i>
                        <?php _e('No te preocupes, no se te cobrará nada aún.', 'urbandog'); ?>
                    </p>
                </div>
            </div>
        </aside>
    </div>
</main>

<script>
    // Data for dynamic pricing from PHP
    const pricing = {
        group: {
            30: <?php echo esc_js($price_grp_30 ?: 0); ?>,
            60: <?php echo esc_js($price_grp_60 ?: 0); ?>
        },
        individual: {
            30: <?php echo esc_js($price_ind_30 ?: 0); ?>,
            60: <?php echo esc_js($price_ind_60 ?: 0); ?>
        }
    };

    function updateDogs(delta) {
        const input = document.getElementById('booking-dog-count');
        if (!input) return;
        const max = parseInt(input.getAttribute('max')) || 10;
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > max) val = max;
        input.value = val;
        calculateTotal();
    }

    function calculateTotal() {
        const modalitySelect = document.getElementById('booking-modality');
        const durationSelect = document.getElementById('booking-duration');
        const countInput = document.getElementById('booking-dog-count');
        const priceDisplay = document.getElementById('booking-total-price');

        if (!modalitySelect || !durationSelect || !countInput || !priceDisplay) return;

        const modality = modalitySelect.value;
        const duration = durationSelect.value;
        const count = parseInt(countInput.value);

        const unitPrice = pricing[modality][duration];
        const total = unitPrice * count;

        priceDisplay.innerText = 'S/ ' + total;
    }

    async function submitBooking() {
        const btn = document.getElementById('submit-booking-btn');
        if (!btn || btn.disabled) return;

        const modality = document.getElementById('booking-modality').value;
        const duration = document.getElementById('booking-duration').value;
        const dogCount = document.getElementById('booking-dog-count').value;
        const date = document.getElementById('booking-date').value;
        const time = document.getElementById('booking-time-slot').value;
        const nonce = document.getElementById('ud_booking_nonce_field').value;
        const walkerId = <?php echo $walker_id; ?>;

        if (!date || !time) {
            alert('<?php _e('Por favor selecciona una fecha y horario disponibles.', 'urbandog'); ?>');
            return;
        }

        // Price from display text
        const priceText = document.getElementById('booking-total-price').innerText;
        const price = parseFloat(priceText.replace('S/ ', ''));

        btn.disabled = true;
        btn.innerText = '<?php _e('Enviando...', 'urbandog'); ?>';

        const formData = new FormData();
        formData.append('action', 'ud_request_walk');
        formData.append('nonce', nonce);
        formData.append('walker_id', walkerId);
        formData.append('date', date);
        formData.append('time', time);
        formData.append('modality', modality);
        formData.append('duration', duration);
        formData.append('dog_count', dogCount);
        formData.append('price', price);

        try {
            const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                btn.classList.remove('btn-primary');
                btn.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
                btn.innerText = '<?php _e('¡Solicitud Enviada!', 'urbandog'); ?>';

                // Optional: redirect or show modal
                setTimeout(() => {
                    alert(data.data.message);
                }, 100);
            } else {
                alert(data.data.message || 'Error al enviar la solicitud.');
                btn.disabled = false;
                btn.innerText = '<?php _e('Solicitar Paseo', 'urbandog'); ?>';
            }
        } catch (error) {
            console.error('Booking error:', error);
            alert('Error de conexión.');
            btn.disabled = false;
            btn.innerText = '<?php _e('Solicitar Paseo', 'urbandog'); ?>';
        }
    }

    const customSchedules = <?php echo $custom_schedules_json; ?>;
    const dayMap = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];

    function updateTimeSlots(dateString) {
        if (!dateString) return;

        // Use T00:00:00 to avoid timezone shifts in local JS Date object
        const date = new Date(dateString + 'T00:00:00');
        const dayName = dayMap[date.getDay()];
        const range = customSchedules[dayName];
        const select = document.getElementById('booking-time-slot');

        if (!select) return;
        select.innerHTML = '';

        if (!range || range.toLowerCase() === 'cerrado') {
            const opt = document.createElement('option');
            opt.value = '';
            opt.innerText = '<?php _e('Sin disponibilidad', 'urbandog'); ?>';
            select.appendChild(opt);
            return;
        }

        // range format expected: "HH:MM-HH:MM"
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

                // Format for display
                let ampm = currentH >= 12 ? 'PM' : 'AM';
                let displayH = currentH % 12 || 12;
                opt.innerText = `${displayH}:${String(currentM).padStart(2, '0')} ${ampm}`;

                select.appendChild(opt);

                // Increment by 30 mins
                currentM += 30;
                if (currentM >= 60) {
                    currentH++;
                    currentM = 0;
                }
            }
        } catch (e) {
            console.error("Error parsing range", range, e);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Init slots for today
        const dateInput = document.getElementById('booking-date');
        if (dateInput) updateTimeSlots(dateInput.value);

        // Init price calculation
        calculateTotal();

        // Attach listeners
        const modalitySelect = document.getElementById('booking-modality');
        const durationSelect = document.getElementById('booking-duration');
        if (modalitySelect) modalitySelect.addEventListener('change', calculateTotal);
        if (durationSelect) durationSelect.addEventListener('change', calculateTotal);

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
    });
</script>

<?php get_footer(); ?>