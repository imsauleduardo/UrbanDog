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
    wp_enqueue_style('urbandog-main', get_template_directory_uri() . '/assets/css/main.css', [], '1.0.2');

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
        wp_enqueue_style('urbandog-bookings', get_template_directory_uri() . '/assets/css/bookings.css', ['urbandog-profile'], '1.0.0');

        // Profile also needs Leaflet for its map
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);

        // Booking script
        wp_enqueue_script('urbandog-bookings', get_template_directory_uri() . '/assets/js/bookings.js', ['jquery'], '1.0.0', true);

        $walker_id = get_the_ID();
        wp_localize_script('urbandog-bookings', 'udBookings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ud_booking_nonce'),
            'walkerId' => $walker_id,
            'dashboardUrl' => home_url('/panel-dueno/'),
            'rates' => [
                'ind_30' => get_post_meta($walker_id, 'ud_walker_price_30', true) ?: 0,
                'ind_60' => get_post_meta($walker_id, 'ud_walker_price_60', true) ?: 0,
                'grp_30' => get_post_meta($walker_id, 'ud_walker_price_group_30', true) ?: 0,
                'grp_60' => get_post_meta($walker_id, 'ud_walker_price_group_60', true) ?: 0,
            ]
        ]);
    }

    // Walker Settings Assets
    if (is_page_template('page-walker-settings.php')) {
        // Dashboard styles are required for the layout
        wp_enqueue_style('urbandog-dashboard', get_template_directory_uri() . '/assets/css/dashboard.css', ['urbandog-main'], '1.0.2');

        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
        wp_enqueue_script('urbandog-walker-settings', get_template_directory_uri() . '/assets/js/walker-settings.js', ['jquery', 'leaflet-js'], '1.0.0', true);

        wp_localize_script('urbandog-walker-settings', 'udWalkerSettings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ud_walker_settings_nonce'),
        ]);
    }
    // Walker Dashboard
    if (is_page_template('page-walker-dashboard.php')) {
        wp_enqueue_style('urbandog-dashboard', get_template_directory_uri() . '/assets/css/dashboard.css', ['urbandog-main'], '1.0.3');
        wp_enqueue_script('urbandog-dashboard', get_template_directory_uri() . '/assets/js/dashboard.js', ['jquery'], '1.0.0', true);

        // Pass PHP data to JS
        wp_localize_script('urbandog-dashboard', 'urbandog_dashboard_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ud_dashboard_nonce')
        ]);
    }

    // Owner Dashboard
    if (is_page_template('page-owner-dashboard.php')) {
        wp_enqueue_style('urbandog-dashboard', get_template_directory_uri() . '/assets/css/dashboard.css', ['urbandog-main'], '1.0.3');
        wp_enqueue_style('urbandog-dashboard-owner', get_template_directory_uri() . '/assets/css/dashboard-owner.css', ['urbandog-dashboard'], '1.0.3');
        wp_enqueue_style('urbandog-payments', get_template_directory_uri() . '/assets/css/payments.css', ['urbandog-dashboard'], '1.0.0');
        wp_enqueue_style('urbandog-pets', get_template_directory_uri() . '/assets/css/pets.css', ['urbandog-dashboard'], '1.0.0');
        wp_enqueue_style('urbandog-ratings', get_template_directory_uri() . '/assets/css/ratings.css', ['urbandog-main'], '1.0.0');

        // Ratings script
        wp_enqueue_script('urbandog-ratings', get_template_directory_uri() . '/assets/js/ratings.js', ['jquery'], '1.0.0', true);
        wp_localize_script('urbandog-ratings', 'udRatings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ud_rating_nonce'),
        ]);

        // Payment scripts for owner
        wp_enqueue_script('urbandog-payments', get_template_directory_uri() . '/assets/js/payments.js', ['jquery'], '1.0.0', true);
        wp_localize_script('urbandog-payments', 'udPayments', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ud_payment_nonce'),
        ]);

        // Pet Management scripts
        wp_enqueue_script('urbandog-pets', get_template_directory_uri() . '/assets/js/pets.js', ['jquery'], '1.0.0', true);
        wp_localize_script('urbandog-pets', 'udPets', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ud_pet_nonce'),
        ]);
    }

    // Registration & Login Assets
    if (is_page_template('page-register-owner.php') || is_page_template('page-register-walker.php') || is_page_template('page-login.php') || is_page_template('page-lost-password.php')) {
        wp_enqueue_style('urbandog-registration', get_template_directory_uri() . '/assets/css/registration.css', ['urbandog-main'], '1.0.0');

        if (!is_page_template('page-login.php')) {
            wp_enqueue_script('urbandog-registration', get_template_directory_uri() . '/assets/js/registration.js', ['jquery'], '1.0.0', true);
            wp_localize_script('urbandog-registration', 'udRegistration', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ud_registration_nonce'),
            ]);
        }
    }

    // Main Scripts
    wp_enqueue_script('ud-main-script', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], '1.0.0', true);

    // Lucide Icons
    wp_enqueue_script('lucide-icons', 'https://unpkg.com/lucide@latest', [], null, true);
    wp_add_inline_script('lucide-icons', 'lucide.createIcons();');

    // Ratings Assets (Required in both dashboards)
    if (is_page_template('page-owner-dashboard.php') || is_page_template('page-walker-dashboard.php') || is_singular('ud_walker_profile')) {
        wp_enqueue_style('urbandog-ratings', get_template_directory_uri() . '/assets/css/ratings.css', [], '1.0.0');
        wp_enqueue_script('urbandog-ratings', get_template_directory_uri() . '/assets/js/ratings.js', ['jquery'], '1.0.0', true);
        wp_localize_script('urbandog-ratings', 'ud_ratings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ud_rating_nonce'),
        ]);
    }

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
    $owner_dashboard_slug = 'panel-dueno';
    if (!get_page_by_path($owner_dashboard_slug)) {
        wp_insert_post([
            'post_title' => __('Mi Panel', 'urbandog'),
            'post_name' => $owner_dashboard_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-owner-dashboard.php'
        ]);
    }

    // Register Owner Page
    $register_owner_slug = 'registro';
    if (!get_page_by_path($register_owner_slug)) {
        wp_insert_post([
            'post_title' => __('Registro de Cliente', 'urbandog'),
            'post_name' => $register_owner_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-register-owner.php'
        ]);
    }

    // Register Walker Page
    $register_walker_slug = 'registro-paseador';
    if (!get_page_by_path($register_walker_slug)) {
        wp_insert_post([
            'post_title' => __('Registro de Paseador', 'urbandog'),
            'post_name' => $register_walker_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-register-walker.php'
        ]);
    }

    // Walker Settings Page
    $walker_settings_slug = 'ajustes-paseador';
    if (!get_page_by_path($walker_settings_slug)) {
        wp_insert_post([
            'post_title' => __('Ajustes de Perfil', 'urbandog'),
            'post_name' => $walker_settings_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-walker-settings.php'
        ]);
    }

    // Custom Login Page
    $login_slug = 'login';
    if (!get_page_by_path($login_slug)) {
        wp_insert_post([
            'post_title' => __('Entrar', 'urbandog'),
            'post_name' => $login_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-login.php'
        ]);
    }

    // Registration Pending Page
    $pending_slug = 'registro-pendiente';
    if (!get_page_by_path($pending_slug)) {
        wp_insert_post([
            'post_title' => __('Registro en Proceso', 'urbandog'),
            'post_name' => $pending_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-registration-pending.php'
        ]);
    }

    // Lost Password Page
    $lost_password_slug = 'recuperar-contrasena';
    if (!get_page_by_path($lost_password_slug)) {
        wp_insert_post([
            'post_title' => __('Recuperar Contraseña', 'urbandog'),
            'post_name' => $lost_password_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-lost-password.php'
        ]);
    }
}
add_action('after_switch_theme', 'ud_ensure_dashboard_pages');
// Also run on init once for safety during development
add_action('init', 'ud_ensure_dashboard_pages');

/**
 * Custom Login Redirects & Logic
 */
function ud_custom_login_logic()
{
    global $pagenow;

    // Redirect wp-login.php to /login/
    if ('wp-login.php' === $pagenow && !isset($_POST['wp-submit']) && !isset($_GET['action']) && !is_user_logged_in()) {
        wp_safe_redirect(home_url('/login/'));
        exit;
    }

    // Handle Login Failure
    add_action('wp_login_failed', function () {
        wp_safe_redirect(home_url('/login/?login=failed'));
        exit;
    });

    // Redirect WooCommerce/Default Lost Password to Custom URL
    if (isset($_GET['action']) && 'lostpassword' === $_GET['action'] && 'wp-login.php' === $pagenow) {
        wp_safe_redirect(home_url('/recuperar-contrasena/'));
        exit;
    }
}
add_action('init', 'ud_custom_login_logic');

/**
 * Filter Lost Password URL
 */
add_filter('lostpassword_url', function ($url) {
    return home_url('/recuperar-contrasena/');
}, 10, 1);

/**
 * Render rating modal in footer for dashboard pages.
 */
function ud_render_rating_modal()
{
    if (is_page_template('page-owner-dashboard.php') || is_page_template('page-walker-dashboard.php')) {
        get_template_part('template-parts/modal-rating');
    }
}
add_action('wp_footer', 'ud_render_rating_modal');

