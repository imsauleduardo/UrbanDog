<?php
/**
 * UrbanDog Custom Post Types
 *
 * Registers: ud_pet, ud_walker_profile, ud_booking, ud_visit, ud_review.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_CPT
{

    /**
     * Initialize CPT hooks.
     */
    public static function init(): void
    {
        add_action('init', [__CLASS__, 'register_post_types']);
        add_action('init', [__CLASS__, 'register_taxonomies']);
        add_action('add_meta_boxes', [__CLASS__, 'register_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_meta_boxes']);
    }

    /**
     * Register all custom post types.
     */
    public static function register_post_types(): void
    {
        self::register_pet_cpt();
        self::register_walker_profile_cpt();
        self::register_booking_cpt();
        self::register_visit_cpt();
        self::register_review_cpt();
    }

    /**
     * CPT: Mascotas (ud_pet)
     */
    private static function register_pet_cpt(): void
    {
        register_post_type('ud_pet', [
            'labels' => [
                'name' => __('Mascotas', 'urbandog'),
                'singular_name' => __('Mascota', 'urbandog'),
                'add_new' => __('Agregar Mascota', 'urbandog'),
                'add_new_item' => __('Agregar Nueva Mascota', 'urbandog'),
                'edit_item' => __('Editar Mascota', 'urbandog'),
                'view_item' => __('Ver Mascota', 'urbandog'),
                'all_items' => __('Todas las Mascotas', 'urbandog'),
                'search_items' => __('Buscar Mascotas', 'urbandog'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'urbandog',
            'show_in_rest' => true,
            'supports' => ['title', 'thumbnail', 'author'],
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'post',
        ]);
    }

    /**
     * CPT: Perfil de Paseador (ud_walker_profile)
     */
    private static function register_walker_profile_cpt(): void
    {
        register_post_type('ud_walker_profile', [
            'labels' => [
                'name' => __('Perfiles de Paseadores', 'urbandog'),
                'singular_name' => __('Perfil de Paseador', 'urbandog'),
                'add_new_item' => __('Agregar Perfil de Paseador', 'urbandog'),
                'edit_item' => __('Editar Perfil de Paseador', 'urbandog'),
                'view_item' => __('Ver Perfil de Paseador', 'urbandog'),
                'all_items' => __('Todos los Paseadores', 'urbandog'),
                'search_items' => __('Buscar Paseadores', 'urbandog'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'urbandog',
            'show_in_rest' => true,
            'supports' => ['title', 'thumbnail', 'author'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'paseadores'],
            'capability_type' => 'post',
        ]);
    }

    /**
     * CPT: Reservas de Paseo (ud_booking)
     */
    private static function register_booking_cpt(): void
    {
        register_post_type('ud_booking', [
            'labels' => [
                'name' => __('Reservas', 'urbandog'),
                'singular_name' => __('Reserva', 'urbandog'),
                'add_new_item' => __('Nueva Reserva', 'urbandog'),
                'edit_item' => __('Editar Reserva', 'urbandog'),
                'view_item' => __('Ver Reserva', 'urbandog'),
                'all_items' => __('Todas las Reservas', 'urbandog'),
                'search_items' => __('Buscar Reservas', 'urbandog'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'urbandog',
            'show_in_rest' => true,
            'supports' => ['title', 'author'],
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'post',
        ]);
    }

    /**
     * CPT: Visitas de Reconocimiento (ud_visit)
     */
    private static function register_visit_cpt(): void
    {
        register_post_type('ud_visit', [
            'labels' => [
                'name' => __('Visitas', 'urbandog'),
                'singular_name' => __('Visita', 'urbandog'),
                'add_new_item' => __('Nueva Visita', 'urbandog'),
                'edit_item' => __('Editar Visita', 'urbandog'),
                'view_item' => __('Ver Visita', 'urbandog'),
                'all_items' => __('Todas las Visitas', 'urbandog'),
                'search_items' => __('Buscar Visitas', 'urbandog'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'urbandog',
            'show_in_rest' => true,
            'supports' => ['title', 'author'],
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'post',
        ]);
    }

    /**
     * CPT: Calificaciones (ud_review)
     */
    private static function register_review_cpt(): void
    {
        register_post_type('ud_review', [
            'labels' => [
                'name' => __('Calificaciones', 'urbandog'),
                'singular_name' => __('Calificación', 'urbandog'),
                'add_new_item' => __('Nueva Calificación', 'urbandog'),
                'edit_item' => __('Editar Calificación', 'urbandog'),
                'view_item' => __('Ver Calificación', 'urbandog'),
                'all_items' => __('Todas las Calificaciones', 'urbandog'),
                'search_items' => __('Buscar Calificaciones', 'urbandog'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'urbandog',
            'show_in_rest' => true,
            'supports' => ['title', 'author'],
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'post',
        ]);
    }

    /**
     * Register taxonomies.
     */
    public static function register_taxonomies(): void
    {
        // Tamaño de mascota
        register_taxonomy('ud_pet_size', 'ud_pet', [
            'labels' => [
                'name' => __('Tamaños', 'urbandog'),
                'singular_name' => __('Tamaño', 'urbandog'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
        ]);

        // Raza de mascota
        register_taxonomy('ud_pet_breed', 'ud_pet', [
            'labels' => [
                'name' => __('Razas', 'urbandog'),
                'singular_name' => __('Raza', 'urbandog'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
        ]);

        // Estado de booking
        register_taxonomy('ud_booking_status', 'ud_booking', [
            'labels' => [
                'name' => __('Estados de Reserva', 'urbandog'),
                'singular_name' => __('Estado', 'urbandog'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
        ]);
    }

    /**
     * Register meta boxes for CPTs.
     */
    public static function register_meta_boxes(): void
    {
        // Mascota meta
        add_meta_box('ud_pet_details', __('Detalles de la Mascota', 'urbandog'), [__CLASS__, 'render_pet_meta_box'], 'ud_pet', 'normal', 'high');

        // Walker profile meta
        add_meta_box('ud_walker_details', __('Detalles del Paseador', 'urbandog'), [__CLASS__, 'render_walker_meta_box'], 'ud_walker_profile', 'normal', 'high');

        // Booking meta
        add_meta_box('ud_booking_details', __('Detalles de la Reserva', 'urbandog'), [__CLASS__, 'render_booking_meta_box'], 'ud_booking', 'normal', 'high');

        // Visit meta
        add_meta_box('ud_visit_details', __('Detalles de la Visita', 'urbandog'), [__CLASS__, 'render_visit_meta_box'], 'ud_visit', 'normal', 'high');

        // Review meta
        add_meta_box('ud_review_details', __('Detalles de la Calificación', 'urbandog'), [__CLASS__, 'render_review_meta_box'], 'ud_review', 'normal', 'high');
    }

    /**
     * Render Pet meta box.
     */
    public static function render_pet_meta_box(WP_Post $post): void
    {
        wp_nonce_field('ud_pet_meta', 'ud_pet_meta_nonce');

        $fields = [
            'ud_pet_name' => ['label' => __('Nombre', 'urbandog'), 'type' => 'text'],
            'ud_pet_temperament' => ['label' => __('Temperamento', 'urbandog'), 'type' => 'text'],
            'ud_pet_age' => ['label' => __('Edad (años)', 'urbandog'), 'type' => 'number'],
            'ud_pet_weight' => ['label' => __('Peso (kg)', 'urbandog'), 'type' => 'number'],
            'ud_pet_special_needs' => ['label' => __('Necesidades Especiales', 'urbandog'), 'type' => 'textarea'],
            'ud_pet_vaccines' => ['label' => __('Vacunas', 'urbandog'), 'type' => 'textarea'],
        ];

        self::render_meta_fields($post->ID, $fields);
    }

    /**
     * Render Walker Profile meta box.
     */
    public static function render_walker_meta_box(WP_Post $post): void
    {
        wp_nonce_field('ud_walker_meta', 'ud_walker_meta_nonce');

        $fields = [
            'ud_walker_zone' => ['label' => __('Zona de Cobertura', 'urbandog'), 'type' => 'text'],
            'ud_walker_lat' => ['label' => __('Latitud', 'urbandog'), 'type' => 'text'],
            'ud_walker_lng' => ['label' => __('Longitud', 'urbandog'), 'type' => 'text'],
            'ud_walker_radius_km' => ['label' => __('Radio (km)', 'urbandog'), 'type' => 'number'],
            'ud_walker_schedule' => ['label' => __('Horario Disponible', 'urbandog'), 'type' => 'textarea'],
            'ud_walker_price_30' => ['label' => __('Precio 30 min (S/.)', 'urbandog'), 'type' => 'number'],
            'ud_walker_price_60' => ['label' => __('Precio 60 min (S/.)', 'urbandog'), 'type' => 'number'],
            'ud_walker_price_group_30' => ['label' => __('Precio Grupal 30 min (S/.)', 'urbandog'), 'type' => 'number'],
            'ud_walker_price_group_60' => ['label' => __('Precio Grupal 60 min (S/.)', 'urbandog'), 'type' => 'number'],
            'ud_walker_max_dogs' => ['label' => __('Máx. perros simultáneos', 'urbandog'), 'type' => 'number'],
            'ud_walker_pet_sizes' => ['label' => __('Tamaños aceptados (pequeño, mediano, grande, gigante)', 'urbandog'), 'type' => 'text'],
            'ud_walker_services' => ['label' => __('Servicios (walk, boarding, visit)', 'urbandog'), 'type' => 'text'],
        ];

        self::render_meta_fields($post->ID, $fields);
    }

    /**
     * Render Booking meta box.
     */
    public static function render_booking_meta_box(WP_Post $post): void
    {
        wp_nonce_field('ud_booking_meta', 'ud_booking_meta_nonce');

        $statuses = [
            'pending_request' => __('Solicitud Pendiente', 'urbandog'),
            'accepted' => __('Aceptada', 'urbandog'),
            'rejected' => __('Rechazada', 'urbandog'),
            'visit_scheduled' => __('Visita Agendada', 'urbandog'),
            'visit_completed' => __('Visita Completada', 'urbandog'),
            'walk_scheduled' => __('Paseo Agendado', 'urbandog'),
            'pending_payment' => __('Pago Pendiente', 'urbandog'),
            'payment_confirmed' => __('Pago Confirmado', 'urbandog'),
            'in_progress' => __('En Curso', 'urbandog'),
            'completed' => __('Completado', 'urbandog'),
            'cancelled' => __('Cancelado', 'urbandog'),
        ];

        $current_status = get_post_meta($post->ID, 'ud_booking_status', true);
        echo '<p><label><strong>' . esc_html__('Estado', 'urbandog') . '</strong></label><br>';
        echo '<select name="ud_booking_status">';
        foreach ($statuses as $key => $label) {
            printf('<option value="%s" %s>%s</option>', esc_attr($key), selected($current_status, $key, false), esc_html($label));
        }
        echo '</select></p>';

        $fields = [
            'ud_booking_owner_id' => ['label' => __('ID del Dueño', 'urbandog'), 'type' => 'number'],
            'ud_booking_walker_id' => ['label' => __('ID del Paseador', 'urbandog'), 'type' => 'number'],
            'ud_booking_pet_ids' => ['label' => __('IDs de Mascotas (separados por coma)', 'urbandog'), 'type' => 'text'],
            'ud_booking_date' => ['label' => __('Fecha del Paseo', 'urbandog'), 'type' => 'date'],
            'ud_booking_time' => ['label' => __('Hora del Paseo', 'urbandog'), 'type' => 'time'],
            'ud_booking_duration' => ['label' => __('Duración (minutos)', 'urbandog'), 'type' => 'number'],
            'ud_booking_modality' => ['label' => __('Modalidad (individual/grupal)', 'urbandog'), 'type' => 'text'],
            'ud_booking_price' => ['label' => __('Precio Total (S/.)', 'urbandog'), 'type' => 'number'],
            'ud_booking_commission' => ['label' => __('Comisión UrbanDog (S/.)', 'urbandog'), 'type' => 'number'],
            'ud_booking_feedback' => ['label' => __('Feedback post-paseo', 'urbandog'), 'type' => 'textarea'],
        ];

        self::render_meta_fields($post->ID, $fields);
    }

    /**
     * Render Visit meta box.
     */
    public static function render_visit_meta_box(WP_Post $post): void
    {
        wp_nonce_field('ud_visit_meta', 'ud_visit_meta_nonce');

        $fields = [
            'ud_visit_owner_id' => ['label' => __('ID del Dueño', 'urbandog'), 'type' => 'number'],
            'ud_visit_walker_id' => ['label' => __('ID del Paseador', 'urbandog'), 'type' => 'number'],
            'ud_visit_pet_id' => ['label' => __('ID de la Mascota', 'urbandog'), 'type' => 'number'],
            'ud_visit_date' => ['label' => __('Fecha', 'urbandog'), 'type' => 'date'],
            'ud_visit_time' => ['label' => __('Hora', 'urbandog'), 'type' => 'time'],
            'ud_visit_status' => ['label' => __('Estado (scheduled/completed/cancelled)', 'urbandog'), 'type' => 'text'],
            'ud_visit_result' => ['label' => __('Resultado (compatible/not_compatible)', 'urbandog'), 'type' => 'text'],
            'ud_visit_notes' => ['label' => __('Notas', 'urbandog'), 'type' => 'textarea'],
        ];

        self::render_meta_fields($post->ID, $fields);
    }

    /**
     * Render Review meta box.
     */
    public static function render_review_meta_box(WP_Post $post): void
    {
        wp_nonce_field('ud_review_meta', 'ud_review_meta_nonce');

        $fields = [
            'ud_review_author_id' => ['label' => __('ID del Autor', 'urbandog'), 'type' => 'number'],
            'ud_review_recipient_id' => ['label' => __('ID del Destinatario', 'urbandog'), 'type' => 'number'],
            'ud_review_booking_id' => ['label' => __('ID de la Reserva', 'urbandog'), 'type' => 'number'],
            'ud_review_rating' => ['label' => __('Puntuación (1-5)', 'urbandog'), 'type' => 'number'],
            'ud_review_comment' => ['label' => __('Comentario', 'urbandog'), 'type' => 'textarea'],
        ];

        self::render_meta_fields($post->ID, $fields);
    }

    /**
     * Helper: render meta fields.
     */
    private static function render_meta_fields(int $post_id, array $fields): void
    {
        echo '<table class="form-table">';
        foreach ($fields as $key => $config) {
            $value = get_post_meta($post_id, $key, true);
            echo '<tr>';
            echo '<th><label for="' . esc_attr($key) . '">' . esc_html($config['label']) . '</label></th>';
            echo '<td>';
            if ($config['type'] === 'textarea') {
                printf('<textarea name="%s" id="%s" rows="3" class="large-text">%s</textarea>', esc_attr($key), esc_attr($key), esc_textarea($value));
            } else {
                printf('<input type="%s" name="%s" id="%s" value="%s" class="regular-text" />', esc_attr($config['type']), esc_attr($key), esc_attr($key), esc_attr($value));
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    /**
     * Save meta box data.
     */
    public static function save_meta_boxes(int $post_id): void
    {
        // Skip autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Meta keys per CPT
        $cpt_meta = [
            'ud_pet' => [
                'nonce' => 'ud_pet_meta_nonce',
                'action' => 'ud_pet_meta',
                'keys' => ['ud_pet_name', 'ud_pet_temperament', 'ud_pet_age', 'ud_pet_weight', 'ud_pet_special_needs', 'ud_pet_vaccines'],
            ],
            'ud_walker_profile' => [
                'nonce' => 'ud_walker_meta_nonce',
                'action' => 'ud_walker_meta',
                'keys' => ['ud_walker_zone', 'ud_walker_lat', 'ud_walker_lng', 'ud_walker_radius_km', 'ud_walker_schedule', 'ud_walker_price_30', 'ud_walker_price_60', 'ud_walker_price_group_30', 'ud_walker_price_group_60', 'ud_walker_max_dogs', 'ud_walker_pet_sizes', 'ud_walker_services'],
            ],
            'ud_booking' => [
                'nonce' => 'ud_booking_meta_nonce',
                'action' => 'ud_booking_meta',
                'keys' => ['ud_booking_status', 'ud_booking_owner_id', 'ud_booking_walker_id', 'ud_booking_pet_ids', 'ud_booking_date', 'ud_booking_time', 'ud_booking_duration', 'ud_booking_modality', 'ud_booking_price', 'ud_booking_commission', 'ud_booking_feedback'],
            ],
            'ud_visit' => [
                'nonce' => 'ud_visit_meta_nonce',
                'action' => 'ud_visit_meta',
                'keys' => ['ud_visit_owner_id', 'ud_visit_walker_id', 'ud_visit_pet_id', 'ud_visit_date', 'ud_visit_time', 'ud_visit_status', 'ud_visit_result', 'ud_visit_notes'],
            ],
            'ud_review' => [
                'nonce' => 'ud_review_meta_nonce',
                'action' => 'ud_review_meta',
                'keys' => ['ud_review_author_id', 'ud_review_recipient_id', 'ud_review_booking_id', 'ud_review_rating', 'ud_review_comment'],
            ],
        ];

        $post_type = get_post_type($post_id);
        if (!isset($cpt_meta[$post_type])) {
            return;
        }

        $config = $cpt_meta[$post_type];

        // Verify nonce
        if (!isset($_POST[$config['nonce']]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$config['nonce']])), $config['action'])) {
            return;
        }

        // Save each meta field
        foreach ($config['keys'] as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$key])));
            }
        }
    }
}
