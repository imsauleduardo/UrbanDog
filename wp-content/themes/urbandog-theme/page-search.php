<?php
/**
 * Template Name: Página de Búsqueda (Rover-style)
 *
 * @package UrbanDog
 */

get_header();

get_header();

$distrito = sanitize_text_field($_GET['distrito'] ?? $_GET['location'] ?? '');
?>

<main class="search-page">
    <!-- Filter Bar Header -->
    <header class="search-header">
        <div class="filter-bar">
            <div class="filter-item" onclick="openModal('modal-service')">
                <i data-lucide="dog"></i>
                <span>Paseo de Perros</span>
            </div>
            <div class="filter-item" onclick="openModal('modal-location')">
                <i data-lucide="map-pin"></i>
                <span id="label-location">
                    <?php echo !empty($distrito) ? esc_html($distrito) : 'Distrito'; ?>
                </span>
            </div>
            <div class="filter-item" onclick="openModal('modal-dates')">
                <i data-lucide="calendar"></i>
                <span>Fechas</span>
            </div>
            <div class="filter-item" onclick="openModal('modal-pets')">
                <i data-lucide="paw-print"></i>
                <span>Mascotas</span>
            </div>
            <button class="filter-item active" style="margin-left: auto;">
                <i data-lucide="sliders-horizontal"></i>
                <span>Más filtros</span>
            </button>
        </div>
    </header>

    <div class="search-main">
        <!-- Results List -->
        <section class="search-results-list">
            <div class="results-count">
                <?php _e('Buscando paseadores en ', 'urbandog'); ?> <strong>
                    <?php echo esc_html($distrito ?: 'Lima'); ?>
                </strong>...
            </div>

            <div id="walker-results">
                <!-- Fallback Loading / Placeholder -->
                <div class="text-center py-12" style="text-align: center; padding: 3rem 0; color: #94a3b8;">
                    <i data-lucide="loader-2" class="animate-spin"
                        style="width: 3rem; height: 3rem; margin-bottom: 1rem;"></i>
                    <p>
                        <?php _e('Cargando mejores opciones para tu mascota...', 'urbandog'); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Fixed Map Container -->
        <section class="search-map-container">
            <div id="search-map"></div>
        </section>
    </div>
</main>

<!-- Modals -->

<!-- Service Modal -->
<div id="modal-service" class="ud-modal-overlay" onclick="closeModal(event)">
    <div class="ud-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title"><?php _e('¿Qué servicio necesitas?', 'urbandog'); ?></h3>
            <button class="modal-close" onclick="closeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="service-selector">
                <label class="service-option active">
                    <input type="radio" name="service_type" value="walk" checked>
                    <i data-lucide="dog"></i>
                    <span class="service-name">Paseo para perros</span>
                    <span class="service-desc">En tu vecindario.</span>
                </label>
                <label class="service-option disabled">
                    <input type="radio" name="service_type" value="boarding" disabled>
                    <div class="soon-badge"><?php _e('Muy pronto', 'urbandog'); ?></div>
                    <i data-lucide="luggage"></i>
                    <span class="service-name">Alojamiento</span>
                    <span class="service-desc">En casa de un cuidador.</span>
                </label>
                <label class="service-option disabled">
                    <input type="radio" name="service_type" value="visit" disabled>
                    <div class="soon-badge"><?php _e('Muy pronto', 'urbandog'); ?></div>
                    <i data-lucide="home"></i>
                    <span class="service-name">Cuidado en Casa</span>
                    <span class="service-desc">En tu hogar.</span>
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-full" onclick="closeModal()"><?php _e('Aplicar', 'urbandog'); ?></button>
        </div>
    </div>
</div>

<!-- Location Modal -->
<div id="modal-location" class="ud-modal-overlay" onclick="closeModal(event)">
    <div class="ud-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title"><?php _e('Ubicación', 'urbandog'); ?></h3>
            <button class="modal-close" onclick="closeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group ud-autocomplete-container" style="position: relative;">
                <input type="text" id="input-location" class="form-control" placeholder="Ej: Los Olivos, San Borja..."
                    value="<?php echo esc_attr($distrito); ?>" autocomplete="off">
                <ul id="modal-autocomplete-results" class="ud-autocomplete-results"></ul>
                <div id="modal-district-error" class="district-error">
                    <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i>
                    <?php _e('No hay cobertura en este momento', 'urbandog'); ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-full"
                id="apply-location"><?php _e('Ver resultados', 'urbandog'); ?></button>
        </div>
    </div>
</div>

<!-- Dates Modal -->
<div id="modal-dates" class="ud-modal-overlay" onclick="closeModal(event)">
    <div class="ud-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title"><?php _e('¿Cuándo lo necesitas?', 'urbandog'); ?></h3>
            <button class="modal-close" onclick="closeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="date-selector-grid">
                <!-- Simple weekday selector for now -->
                <?php
                $days = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
                foreach ($days as $day): ?>
                    <button class="day-bubble"><?php echo $day; ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-full"
                onclick="closeModal()"><?php _e('Aplicar fechas', 'urbandog'); ?></button>
        </div>
    </div>
</div>

<!-- Pets Modal -->
<div id="modal-pets" class="ud-modal-overlay" onclick="closeModal(event)">
    <div class="ud-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title"><?php _e('Tus mascotas', 'urbandog'); ?></h3>
            <button class="modal-close" onclick="closeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="pet-counter">
                <div class="counter-item">
                    <span>Perros</span>
                    <div class="counter-controls">
                        <button class="cnt-btn">-</button>
                        <span class="cnt-val">1</span>
                        <button class="cnt-btn">+</button>
                    </div>
                </div>
            </div>
            <div class="pet-sizes mt-6">
                <p class="font-bold mb-3">Tamaño del perro</p>
                <div class="size-selector">
                    <button class="size-btn">0-7kg</button>
                    <button class="size-btn active">7-18kg</button>
                    <button class="size-btn">18-45kg</button>
                    <button class="size-btn">45kg+</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-full" onclick="closeModal()"><?php _e('Aplicar', 'urbandog'); ?></button>
        </div>
    </div>
</div>


<?php get_footer(); ?>