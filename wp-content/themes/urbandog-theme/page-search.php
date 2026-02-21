<?php
/**
 * Template Name: Página de Búsqueda (Rover-style)
 *
 * @package UrbanDog
 */

get_header();

$distrito = sanitize_text_field($_GET['distrito'] ?? $_GET['location'] ?? '');
?>

<main class="search-page">
    <!-- Filter Bar Header -->
    <header class="search-header">
        <div class="filter-bar">
            <div class="filter-item" onclick="openModal('modal-service')">
                <i data-lucide="dog"></i>
                <span id="label-service">Paseo de Perros</span>
            </div>
            <div class="filter-item" onclick="openModal('modal-location')">
                <i data-lucide="map-pin"></i>
                <span id="label-location">
                    <?php echo !empty($distrito) ? esc_html($distrito) : 'Distrito'; ?>
                </span>
            </div>
            <div class="filter-item" onclick="openModal('modal-dates')">
                <i data-lucide="calendar"></i>
                <span id="label-dates">Fechas</span>
            </div>
            <div class="filter-item" onclick="openModal('modal-pets')">
                <i data-lucide="paw-print"></i>
                <span id="label-pets">Mascotas</span>
            </div>
            <button class="filter-item active" style="margin-left: auto;" onclick="openModal('modal-filters')">
                <i data-lucide="sliders-horizontal"></i>
                <span><?php _e('Más filtros', 'urbandog'); ?></span>
            </button>
        </div>
    </header>

    <div class="search-main">
        <!-- Results List -->
        <section class="search-results-list">
            <div class="results-count">
                <?php if ($distrito): ?>
                    <?php _e('Buscando paseadores en ', 'urbandog'); ?> <strong>
                        <?php echo esc_html($distrito); ?>
                    </strong>...
                <?php else: ?>
                    <?php _e('Encuentra al paseador ideal para tu mascota', 'urbandog'); ?>
                <?php endif; ?>
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
            <h3 class="modal-title font-bold text-slate-800"><?php _e('¿Qué servicio necesitas?', 'urbandog'); ?></h3>
            <button class="modal-close" onclick="closeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="service-selector">
                <label class="service-option active">
                    <input type="radio" name="service_type" value="walk" checked>
                    <i data-lucide="dog" class="w-8 h-8"></i>
                    <div class="service-info-col">
                        <span class="service-name">Paseo para perros</span>
                        <span class="service-desc">En tu vecindario.</span>
                    </div>
                </label>
                <label class="service-option disabled">
                    <input type="radio" name="service_type" value="boarding" disabled>
                    <i data-lucide="luggage" class="w-8 h-8"></i>
                    <div class="service-info-col">
                        <span class="service-name">Alojamiento</span>
                        <span class="service-desc">En casa de un cuidador.</span>
                    </div>
                    <div class="soon-badge"><?php _e('Muy pronto', 'urbandog'); ?></div>
                </label>
                <label class="service-option disabled">
                    <input type="radio" name="service_type" value="visit" disabled>
                    <i data-lucide="home" class="w-8 h-8"></i>
                    <div class="service-info-col">
                        <span class="service-name">Cuidado en Casa</span>
                        <span class="service-desc">En tu hogar.</span>
                    </div>
                    <div class="soon-badge"><?php _e('Muy pronto', 'urbandog'); ?></div>
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
            <h3 class="modal-title font-bold text-slate-800"><?php _e('Ubicación', 'urbandog'); ?></h3>
            <button class="modal-close" onclick="closeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body" style="overflow: visible;">
            <div class="form-group ud-autocomplete-container" style="position: relative;">
                <label class="modal-section-label"><?php _e('¿En qué distrito vives?', 'urbandog'); ?></label>
                <input type="text" id="input-location" class="form-control" placeholder="Ej: Los Olivos, San Borja..."
                    value="<?php echo esc_attr($distrito); ?>" autocomplete="off">
                <ul id="modal-autocomplete-results" class="ud-autocomplete-results"></ul>
                <div id="modal-district-error" class="district-error" style="display: none;">
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
            <h3 class="modal-title font-bold text-slate-800"><?php _e('¿Cuándo lo necesitas?', 'urbandog'); ?></h3>
            <button class="modal-close" onclick="closeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <!-- Frecuencia -->
            <div class="modal-section" style="margin-bottom: 12px; padding-bottom: 20px;">
                <label class="modal-section-label"><?php _e('¿Con qué frecuencia?', 'urbandog'); ?></label>
                <div class="segmented-control" id="frequency-selector">
                    <button class="segment" data-value="once">
                        <i data-lucide="calendar" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                        <?php _e('Una vez', 'urbandog'); ?>
                    </button>
                    <button class="segment active" data-value="weekly">
                        <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                        <?php _e('Repetir semanalmente', 'urbandog'); ?>
                    </button>
                </div>
            </div>

            <!-- Días (Visible always but styling changes based on frequency) -->
            <div class="modal-section" style="margin-bottom: 12px; padding-bottom: 20px;">
                <div class="day-selector-segmented" id="day-selector">
                    <?php
                    $days_map = [
                        'Dom' => 'D',
                        'Lun' => 'L',
                        'Mar' => 'M',
                        'Mié' => 'M',
                        'Jue' => 'J',
                        'Vie' => 'V',
                        'Sáb' => 'S'
                    ];
                    foreach ($days_map as $label => $short): ?>
                        <button class="day-segment"
                            data-day="<?php echo strtolower($label); ?>"><?php echo $label; ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Fecha de inicio -->
            <div class="modal-section" style="margin-bottom: 12px; padding-bottom: 20px;">
                <label class="modal-section-label"><?php _e('Fecha de inicio', 'urbandog'); ?></label>
                <div class="input-with-icon">
                    <input type="date" id="start-date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- Horarios -->
            <div class="modal-section">
                <label class="modal-section-label"><?php _e('¿En qué horario lo necesitas?', 'urbandog'); ?></label>
                <div class="segmented-control-three" id="time-selector">
                    <button class="segment active" data-value="morning">6am-11am</button>
                    <button class="segment" data-value="midday">11am-3pm</button>
                    <button class="segment" data-value="afternoon">3pm-10pm</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-full"
                id="apply-dates-btn"><?php _e('Aplicar fechas', 'urbandog'); ?></button>
        </div>
    </div>
</div>

<!-- Pets Modal -->
<div id="modal-pets" class="ud-modal-overlay" onclick="closeModal(event)">
    <div class="ud-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title font-bold text-slate-800"><?php _e('Tus mascotas', 'urbandog'); ?></h3>
            <button class="modal-close" onclick="closeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-section" style="padding-bottom: 20px; margin-bottom: 12px;">
                <label
                    class="modal-section-label"><?php _e('¿Cuantos perros necesitan cuidado?', 'urbandog'); ?></label>
                <div class="pet-counters-grid grid grid-cols-1 gap-2">
                    <div class="counter-row flex items-center justify-between py-3">
                        <div class="counter-label-group flex items-center gap-3">
                            <i data-lucide="dog" style="width: 24px; height: 24px; color: #1e293b;"></i>
                            <div class="flex flex-col text-left ml-[10px]">
                                <span class="font-bold text-slate-800 text-sm"
                                    style="margin-left: 10px; margin-right: 10px;"><?php _e('Perros', 'urbandog'); ?></span>
                                <span class="text-xs text-slate-500 font-normal"
                                    style="margin-left: 10px; margin-right: 10px;"><?php _e('Mayores a 1 año', 'urbandog'); ?></span>
                            </div>
                        </div>
                        <div class="counter-controls flex items-center gap-5">
                            <button class="cnt-btn-circle" data-type="dog" data-action="minus">
                                <i data-lucide="minus"></i>
                            </button>
                            <span class="cnt-val font-bold text-lg" id="dog-count-val"
                                style="margin-left: 10px; margin-right: 10px;">0</span>
                            <button class="cnt-btn-circle" data-type="dog" data-action="plus">
                                <i data-lucide="plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="counter-row flex items-center justify-between py-3">
                        <div class="counter-label-group flex items-center gap-3">
                            <i data-lucide="dog" style="width: 20px; height: 20px; color: #64748b;"></i>
                            <div class="flex flex-col text-left ml-[10px]">
                                <span class="font-bold text-slate-800 text-sm"
                                    style="margin-left: 10px; margin-right: 10px;"><?php _e('Cachorros', 'urbandog'); ?></span>
                                <span class="text-xs text-slate-500 font-normal"
                                    style="margin-left: 10px; margin-right: 10px;"><?php _e('Menores a 1 año', 'urbandog'); ?></span>
                            </div>
                        </div>
                        <div class="counter-controls flex items-center gap-5">
                            <button class="cnt-btn-circle" data-type="puppy" data-action="minus">
                                <i data-lucide="minus"></i>
                            </button>
                            <span class="cnt-val font-bold text-lg" id="puppy-count-val"
                                style="margin-left: 10px; margin-right: 10px;">0</span>
                            <button class="cnt-btn-circle" data-type="puppy" data-action="plus">
                                <i data-lucide="plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pet-size-section mt-8">
                <label class="modal-section-label"><?php _e('¿De qué tamaño son tus perros?', 'urbandog'); ?></label>

                <div class="size-card-grid">
                    <div class="size-card" data-size="small">
                        <i data-lucide="dog" style="width: 16px; height: 16px;"></i>
                        <span class="size-name"><?php _e('Pequeño', 'urbandog'); ?></span>
                        <span class="size-weight">0 - 7 kg</span>
                    </div>
                    <div class="size-card" data-size="medium">
                        <i data-lucide="dog" style="width: 22px; height: 22px;"></i>
                        <span class="size-name"><?php _e('Mediano', 'urbandog'); ?></span>
                        <span class="size-weight">7 - 18 kg</span>
                    </div>
                    <div class="size-card" data-size="large">
                        <i data-lucide="dog" style="width: 28px; height: 28px;"></i>
                        <span class="size-name"><?php _e('Grande', 'urbandog'); ?></span>
                        <span class="size-weight">18 - 45 kg</span>
                    </div>
                    <div class="size-card" data-size="giant">
                        <i data-lucide="dog" style="width: 34px; height: 34px;"></i>
                        <span class="size-name"><?php _e('Gigante', 'urbandog'); ?></span>
                        <span class="size-weight">+ 45 kg</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-full" onclick="closeModal()"><?php _e('Aplicar', 'urbandog'); ?></button>
        </div>
    </div>
</div>

<!-- More Filters Modal -->
<div id="modal-filters" class="ud-modal-overlay" onclick="closeModal(event)">
    <div class="ud-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title font-bold text-slate-800"><?php _e('Filtros', 'urbandog'); ?> <span
                    id="filter-count-badge" class="font-normal text-slate-500">(0)</span></h3>
            <div class="flex items-center gap-4">
                <button id="reset-all-filters">
                    <?php _e('Restablecer todo', 'urbandog'); ?>
                </button>
                <button class="modal-close text-slate-400 hover:text-slate-600 transition-colors"
                    onclick="closeModal()">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <!-- Rate per walk -->
            <div class="modal-section border-none pt-2">
                <label class="modal-section-label mb-2"><?php _e('Tarifa por paseo', 'urbandog'); ?></label>
                <div class="price-slider-container mt-4 mb-10">
                    <div
                        class="price-slider-values flex items-end justify-between mb-2 text-slate-800 font-bold text-lg">
                        <span id="price-min-label" class="hidden">S/ 1</span>
                        <span
                            class="text-xs text-slate-500 font-normal mb-1"><?php _e('Precio máximo', 'urbandog'); ?></span>
                        <span id="price-max-label">S/ 250</span>
                    </div>
                    <div class="single-range-slider-wrapper">
                        <div class="slider-track" id="slider-track"></div>
                        <input type="range" min="1" max="250" value="250" class="range-input-single" id="price-max">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-full" id="apply-filters-btn"><?php _e('Guardar', 'urbandog'); ?></button>
        </div>
    </div>
</div>

<?php get_footer(); ?>