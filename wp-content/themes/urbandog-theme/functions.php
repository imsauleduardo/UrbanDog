<?php
/**
 * UrbanDog Theme Functions
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue scripts and styles.
 */
function ud_theme_scripts()
{
    // Fonts: Inter for the whole site
    wp_enqueue_style('ud-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap', [], null);

    // Custom Styles
    wp_enqueue_style('urbandog-main', get_template_directory_uri() . '/assets/css/main.css', [], '1.0.0');

    // Search Page & Home Hero Assets
    if (is_page_template('page-search.php') || is_front_page()) {
        wp_enqueue_style('urbandog-search', get_template_directory_uri() . '/assets/css/search.css', ['urbandog-main'], '1.0.0');

        if (is_page_template('page-search.php')) {
            // Leaflet JS (only on search page)
            wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
            wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
            wp_enqueue_script('urbandog-search-js', get_template_directory_uri() . '/assets/js/search.js', ['ud-main-script', 'leaflet-js'], '1.0.0', true);
        }
    }

    // Profile Page Assets
    if (is_singular('ud_walker_profile')) {
        wp_enqueue_style('urbandog-profile', get_template_directory_uri() . '/assets/css/profile.css', ['urbandog-main'], '1.0.0');
        // Profile also needs Leaflet for its map
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
    }
    // Dashboard Assets
    if (is_page_template('page-walker-dashboard.php')) {
        wp_enqueue_style('urbandog-dashboard', get_template_directory_uri() . '/assets/css/dashboard.css', ['urbandog-main'], '1.0.0');
    }
    if (is_page_template('page-owner-dashboard.php')) {
        wp_enqueue_style('urbandog-dashboard', get_template_directory_uri() . '/assets/css/dashboard.css', ['urbandog-main'], '1.0.0');
        wp_enqueue_style('urbandog-dashboard-owner', get_template_directory_uri() . '/assets/css/dashboard-owner.css', ['urbandog-dashboard'], '1.0.0');
    }

    // Main Scripts
    wp_enqueue_script('ud-main-script', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], '1.0.0', true);

    // Lucide Icons
    wp_enqueue_script('lucide-icons', 'https://unpkg.com/lucide@latest', [], null, true);
    wp_add_inline_script('lucide-icons', 'lucide.createIcons();');

    // Pass AJAX info to JS
    wp_localize_script('ud-main-script', 'ud_ajax', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ud_nonce'),
    ]);
    // Also provide as urbandog_params for consistency if needed
    wp_localize_script('ud-main-script', 'urbandog_params', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'ud_theme_scripts');

/**
 * Theme Support
 */
function ud_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);

    register_nav_menus([
        'primary' => __('Menú Principal', 'urbandog'),
        'footer' => __('Menú Footer', 'urbandog'),
    ]);
}
add_action('after_setup_theme', 'ud_theme_setup');

/**
 * Automatic Page Setup for UrbanDog
 */
function ud_ensure_dashboard_pages()
{
    // Walker Dashboard
    $walker_dashboard_slug = 'panel-paseador';
    if (!get_page_by_path($walker_dashboard_slug)) {
        wp_insert_post([
            'post_title' => __('Panel del Paseador', 'urbandog'),
            'post_name' => $walker_dashboard_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-walker-dashboard.php'
        ]);
    }

    // Owner Dashboard
    $owner_dashboard_slug = 'mis-paseos';
    if (!get_page_by_path($owner_dashboard_slug)) {
        wp_insert_post([
            'post_title' => __('Mis Paseos', 'urbandog'),
            'post_name' => $owner_dashboard_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-owner-dashboard.php'
        ]);
    }
}
add_action('after_switch_theme', 'ud_ensure_dashboard_pages');
// Also run on init once for safety during development
add_action('init', 'ud_ensure_dashboard_pages');

