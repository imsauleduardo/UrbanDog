<?php
/**
 * Template Name: Walker Settings
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in() || !UD_Roles::is_walker()) {
    wp_safe_redirect(home_url());
    exit;
}

get_header();

$walker_id = get_current_user_id();
$profile_id = UD_Roles::get_walker_profile_id($walker_id);

// Current Data
$rates = [
    'ind_30' => get_post_meta($profile_id, 'ud_walker_price_30', true),
    'ind_60' => get_post_meta($profile_id, 'ud_walker_price_60', true),
    'grp_30' => get_post_meta($profile_id, 'ud_walker_price_group_30', true),
    'grp_60' => get_post_meta($profile_id, 'ud_walker_price_group_60', true),
];

$max_dogs = get_post_meta($profile_id, 'ud_walker_max_dogs', true) ?: 5;
$schedules = json_decode(get_post_meta($profile_id, 'ud_walker_custom_schedules', true), true) ?: [];
$day_map = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
?>

<main class="ud-dashboard-wrapper ud-walker-settings">
    <div class="ud-container">
        <!-- Dashboard Layout -->
        <div class="ud-dashboard-layout">

            <!-- Main Column -->
            <div class="ud-main-col">

                <header class="ud-dashboard-header">
                    <div class="ud-welcome-text">
                        <h1 class="ud-h1">
                            <?php _e('Ajustes de Perfil', 'urbandog'); ?>
                        </h1>
                        <p class="ud-subtitle">
                            <?php _e('Configura tus tarifas, horarios y zonas de trabajo.', 'urbandog'); ?>
                        </p>
                    </div>
                </header>

                <form id="ud-walker-settings-form" class="ud-card p-6">
                    <input type="hidden" name="action" value="ud_save_walker_settings">
                    <input type="hidden" name="nonce"
                        value="<?php echo wp_create_nonce('ud_walker_settings_nonce'); ?>">

                    <!-- Rates Section -->
                    <section class="settings-section mb-10">
                        <h2 class="ud-h2 mb-4 border-bottom pb-2">
                            <i data-lucide="dollar-sign"></i>
                            <?php _e('Mis Tarifas (S/.)', 'urbandog'); ?>
                        </h2>
                        <div class="ud-settings-grid">
                            <div class="rate-group">
                                <label class="ud-label">
                                    <?php _e('Paseo Individual', 'urbandog'); ?>
                                </label>
                                <div class="flex gap-4">
                                    <div class="flex-1">
                                        <span class="ud-text-xs">30 min</span>
                                        <input type="number" name="rate_ind_30" class="ud-input"
                                            value="<?php echo esc_attr($rates['ind_30']); ?>" step="0.5">
                                    </div>
                                    <div class="flex-1">
                                        <span class="ud-text-xs">60 min</span>
                                        <input type="number" name="rate_ind_60" class="ud-input"
                                            value="<?php echo esc_attr($rates['ind_60']); ?>" step="0.5">
                                    </div>
                                </div>
                            </div>
                            <div class="rate-group">
                                <label class="ud-label">
                                    <?php _e('Paseo Grupal', 'urbandog'); ?>
                                </label>
                                <div class="flex gap-4">
                                    <div class="flex-1">
                                        <span class="ud-text-xs">30 min</span>
                                        <input type="number" name="rate_grp_30" class="ud-input"
                                            value="<?php echo esc_attr($rates['grp_30']); ?>" step="0.5">
                                    </div>
                                    <div class="flex-1">
                                        <span class="ud-text-xs">60 min</span>
                                        <input type="number" name="rate_grp_60" class="ud-input"
                                            value="<?php echo esc_attr($rates['grp_60']); ?>" step="0.5">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Availability Section -->
                    <section class="settings-section mb-10">
                        <h2 class="ud-h2 mb-4 border-bottom pb-2">
                            <i data-lucide="calendar"></i>
                            <?php _e('Horarios de Disponibilidad', 'urbandog'); ?>
                        </h2>
                        <p class="ud-text-sm text-slate-500 mb-4">
                            <?php _e('Define el rango de horas en el que estás disponible cada día (Ejem: 08:00-18:00). Escribe "Cerrado" para días no laborables.', 'urbandog'); ?>
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php foreach ($day_map as $day): ?>
                                <div class="ud-form-group">
                                    <label class="ud-label">
                                        <?php echo $day; ?>
                                    </label>
                                    <input type="text" name="schedule[<?php echo $day; ?>]" class="ud-input"
                                        placeholder="08:00-18:00" value="<?php echo esc_attr($schedules[$day] ?? ''); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Bio Section -->
                    <section class="settings-section mb-10">
                        <h2 class="ud-h2 mb-4 border-bottom pb-2">
                            <i data-lucide="user"></i>
                            <?php _e('Información del Perfil', 'urbandog'); ?>
                        </h2>
                        <div class="ud-form-group mb-6">
                            <label class="ud-label">
                                <?php _e('Sobre mí (Bio)', 'urbandog'); ?>
                            </label>
                            <textarea name="bio" class="ud-textarea"
                                rows="5"><?php echo esc_textarea(get_post_field('post_content', $profile_id)); ?></textarea>
                        </div>
                        <div class="ud-form-group">
                            <label class="ud-label">
                                <?php _e('Capacidad Máxima (Perros por paseo)', 'urbandog'); ?>
                            </label>
                            <input type="number" name="max_dogs" class="ud-input w-40"
                                value="<?php echo esc_attr($max_dogs); ?>">
                        </div>
                        
                        <div class="ud-form-group mt-6">
                            <label class="ud-label mb-3">
                                <?php _e('Tamaños de mascotas que acepto', 'urbandog'); ?>
                            </label>
                            <?php 
                            $accepted_sizes = json_decode(get_post_meta($profile_id, 'ud_walker_pet_sizes', true), true) ?: [];
                            $size_options = [
                                'small'  => __('Pequeño (0-10kg)', 'urbandog'),
                                'medium' => __('Mediano (11-20kg)', 'urbandog'),
                                'large'  => __('Grande (21-40kg)', 'urbandog'),
                                'giant'  => __('Gigante (+40kg)', 'urbandog'),
                            ];
                            ?>
                            <div class="grid grid-cols-2 gap-4">
                                <?php foreach ($size_options as $value => $label): ?>
                                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                        <input type="checkbox" name="accepted_sizes[]" value="<?php echo $value; ?>"
                                            <?php checked(in_array($value, $accepted_sizes)); ?>
                                            class="w-5 h-5 text-primary rounded border-slate-300 focus:ring-primary">
                                        <span class="text-sm font-medium text-slate-700"><?php echo $label; ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>

                    <!-- Zone Section -->
                    <section class="settings-section mb-10">
                        <h2 class="ud-h2 mb-4 border-bottom pb-2">
                            <i data-lucide="map-pin"></i>
                            <?php _e('Zonas de Trabajo', 'urbandog'); ?>
                        </h2>
                        <div class="ud-settings-grid">
                            <div class="ud-form-group">
                                <label class="ud-label"><?php _e('Nombre de tu zona principal', 'urbandog'); ?></label>
                                <input type="text" name="zone" class="ud-input"
                                    placeholder="Ejem: San Juan de Lurigancho"
                                    value="<?php echo esc_attr(get_post_meta($profile_id, 'ud_walker_zone', true)); ?>">
                            </div>
                            <div class="ud-form-group">
                                <label class="ud-label"><?php _e('Radio de acción (km)', 'urbandog'); ?></label>
                                <input type="number" name="radius_km" class="ud-input" step="0.1"
                                    value="<?php echo esc_attr(get_post_meta($profile_id, 'ud_walker_radius_km', true) ?: 1.0); ?>">
                            </div>
                        </div>
                        <div class="ud-settings-grid mt-4">
                            <div class="ud-form-group">
                                <label class="ud-label"><?php _e('Latitud (Centro)', 'urbandog'); ?></label>
                                <input type="text" name="lat" class="ud-input"
                                    value="<?php echo esc_attr(get_post_meta($profile_id, 'ud_walker_lat', true)); ?>">
                            </div>
                            <div class="ud-form-group">
                                <label class="ud-label"><?php _e('Longitud (Centro)', 'urbandog'); ?></label>
                                <input type="text" name="lng" class="ud-input"
                                    value="<?php echo esc_attr(get_post_meta($profile_id, 'ud_walker_lng', true)); ?>">
                            </div>
                        </div>

                        <!-- Leaflet Map Container -->
                        <div class="mt-6">
                            <label
                                class="ud-label mb-2"><?php _e('Selecciona tu zona en el mapa', 'urbandog'); ?></label>
                            <div id="ud-walker-zone-map"
                                style="height: 300px; border-radius: 12px; border: 1px solid #e2e8f0; z-index: 10;">
                            </div>
                            <p class="ud-text-xs text-slate-500 mt-2">
                                <?php _e('Haz clic en el mapa o arrastra el marcador para definir el centro de tu zona de trabajo.', 'urbandog'); ?>
                            </p>
                        </div>
                    </section>
191: 
192:                     <!-- Service Policies Section -->
193:                     <section class="settings-section mb-10">
194:                         <h2 class="ud-h2 mb-4 border-bottom pb-2">
195:                             <i data-lucide="shield-check"></i>
196:                             <?php _e('Políticas de Servicio', 'urbandog'); ?>
197:                         </h2>
198:                         <div class="ud-form-group">
199:                             <label class="ud-label flex items-center gap-3 cursor-pointer">
200:                                 <input type="checkbox" name="requires_meetgreet" value="yes" 
201:                                        <?php checked(get_post_meta($profile_id, 'ud_walker_requires_meetgreet', true), 'yes'); ?>
202:                                        class="w-5 h-5 text-primary rounded border-slate-300 focus:ring-primary">
203:                                 <span><?php _e('Requiero Meet & Greet antes de aceptar nuevas mascotas', 'urbandog'); ?></span>
204:                             </label>
205:                             <p class="text-slate-500 text-sm mt-3 ml-8 leading-relaxed">
206:                                 <?php _e('Al activar esta opción, los dueños deberán agendar una reunión inicial gratuita de 15 minutos para que conozcas a la mascota antes de poder reservar paseos regulares.', 'urbandog'); ?>
207:                             </p>
208:                         </div>
209:                     </section>
210: 
211:                     <div class="border-top pt-6">
                        <div class="flex flex-col md:flex-row gap-4 items-center">
                            <button type="submit" class="ud-btn ud-btn-primary ud-btn-lg w-full md:w-auto">
                                <i data-lucide="save"></i>
                                <?php _e('Guardar Todos los Cambios', 'urbandog'); ?>
                            </button>
                            <a href="<?php echo home_url('/panel-paseador/'); ?>"
                                class="ud-btn ud-btn-secondary ud-btn-lg w-full md:w-auto">
                                <?php _e('Cancelar', 'urbandog'); ?>
                            </a>
                        </div>
                        <div id="settings-alert" class="mt-4 p-4 rounded-lg hidden"></div>
                    </div>
                </form>

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
                            <span class="ud-stat-value">--</span>
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

                <!-- Training & Achievements Card (Relocated) -->
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

                <!-- Tip Section -->
                <div class="ud-card ud-tip-card-emerald mt-6">
                    <div class="ud-tip-header">
                        <i data-lucide="lightbulb"></i>
                        <h4 class="ud-h4"><?php _e('Tip de Profesional', 'urbandog'); ?></h4>
                    </div>
                    <p class="ud-tip-text">
                        "<?php _e('Mantén tus horarios actualizados para asegurar más reservas y mejorar tu ranking.', 'urbandog'); ?>"
                    </p>
                </div>

            </aside>
        </div> <!-- .ud-dashboard-layout -->
    </div> <!-- .ud-container -->
</main>

<?php get_footer(); ?>