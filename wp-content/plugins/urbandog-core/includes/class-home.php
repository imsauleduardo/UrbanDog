<?php
/**
 * UrbanDog Home Page Meta Boxes
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Home
{

    /**
     * Initialize Home hooks.
     */
    public static function init(): void
    {
        add_action('add_meta_boxes', [__CLASS__, 'register_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_meta_boxes']);
    }

    /**
     * Register meta boxes for Page post type.
     */
    public static function register_meta_boxes(): void
    {
        global $post;

        // Only show on the front page
        $front_page_id = (int) get_option('page_on_front');
        if ($post->ID !== $front_page_id) {
            return;
        }

        add_meta_box(
            'ud_home_hero',
            __('Contenido del Hero', 'urbandog'),
            [__CLASS__, 'render_hero_meta_box'],
            'page',
            'normal',
            'high'
        );
    }

    /**
     * Render Hero meta box.
     */
    public static function render_hero_meta_box(WP_Post $post): void
    {
        wp_nonce_field('ud_home_meta', 'ud_home_meta_nonce');

        $fields = [
            'ud_hero_title' => [
                'label' => __('Título del Hero', 'urbandog'),
                'type' => 'text',
                'description' => __('El título principal que aparece en el Hero.', 'urbandog')
            ],
            'ud_hero_subtitle' => [
                'label' => __('Subtítulo del Hero', 'urbandog'),
                'type' => 'textarea',
                'description' => __('El texto descriptivo debajo del título.', 'urbandog')
            ],
            'ud_hero_placeholder' => [
                'label' => __('Placeholder del Buscador', 'urbandog'),
                'type' => 'text',
                'description' => __('Ejemplo: "Ingresa tu distrito (ej. Los Olivos)"', 'urbandog')
            ],
            'ud_how_title' => [
                'label' => __('Título "Cómo Funciona"', 'urbandog'),
                'type' => 'text',
                'description' => __('Título de la sección de pasos (ej: Tan fácil como 1, 2, 3)', 'urbandog')
            ],
            'ud_cta_title' => [
                'label' => __('Título del CTA Final', 'urbandog'),
                'type' => 'text',
                'description' => __('Título de la sección verde al final (ej: ¿Listo para empezar?)', 'urbandog')
            ],
            'ud_cta_subtitle' => [
                'label' => __('Subtítulo del CTA Final', 'urbandog'),
                'type' => 'textarea',
                'description' => __('Descripción corta al final.', 'urbandog')
            ],
        ];

        echo '<table class="form-table">';
        foreach ($fields as $key => $config) {
            $value = get_post_meta($post->ID, $key, true);
            echo '<tr>';
            echo '<th><label for="' . esc_attr($key) . '">' . esc_html($config['label']) . '</label></th>';
            echo '<td>';
            if ($config['type'] === 'textarea') {
                printf('<textarea name="%s" id="%s" rows="3" class="large-text">%s</textarea>', esc_attr($key), esc_attr($key), esc_textarea($value));
            } else {
                printf('<input type="%s" name="%s" id="%s" value="%s" class="regular-text" style="width:100%%" />', esc_attr($config['type']), esc_attr($key), esc_attr($key), esc_attr($value));
            }
            if (!empty($config['description'])) {
                printf('<p class="description">%s</p>', esc_html($config['description']));
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

        // Verify nonce
        if (!isset($_POST['ud_home_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ud_home_meta_nonce'])), 'ud_home_meta')) {
            return;
        }

        $keys = [
            'ud_hero_title',
            'ud_hero_subtitle',
            'ud_hero_placeholder',
            'ud_how_title',
            'ud_cta_title',
            'ud_cta_subtitle'
        ];

        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$key])));
            }
        }
    }
}
