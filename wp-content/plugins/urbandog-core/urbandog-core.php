<?php
/**
 * Plugin Name: UrbanDog Core
 * Plugin URI: https://urbandog.pe
 * Description: Plugin principal de UrbanDog - Plataforma de conexión entre dueños de mascotas y paseadores de perros.
 * Version: 1.0.0
 * Author: UrbanDog
 * Author URI: https://urbandog.pe
 * Text Domain: urbandog
 * Domain Path: /languages
 * Requires at least: 6.7
 * Requires PHP: 8.2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('UD_VERSION', '1.0.0');
define('UD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main UrbanDog class.
 */
final class UrbanDog
{

    /**
     * Single instance.
     */
    private static ?UrbanDog $instance = null;

    /**
     * Get single instance.
     */
    public static function instance(): UrbanDog
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files.
     */
    private function load_dependencies(): void
    {
        require_once UD_PLUGIN_DIR . 'includes/class-roles.php';
        require_once UD_PLUGIN_DIR . 'includes/class-cpt.php';
        require_once UD_PLUGIN_DIR . 'includes/class-admin.php';
        require_once UD_PLUGIN_DIR . 'includes/class-users.php';
        require_once UD_PLUGIN_DIR . 'includes/class-pets.php';
        require_once UD_PLUGIN_DIR . 'includes/class-walkers.php';
        require_once UD_PLUGIN_DIR . 'includes/class-search.php';
        require_once UD_PLUGIN_DIR . 'includes/class-bookings.php';
        require_once UD_PLUGIN_DIR . 'includes/class-payments.php';
        require_once UD_PLUGIN_DIR . 'includes/class-reviews.php';
        require_once UD_PLUGIN_DIR . 'includes/class-gamification.php';
        require_once UD_PLUGIN_DIR . 'includes/class-home.php';
    }

    /**
     * Register hooks.
     */
    private function init_hooks(): void
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('init', [$this, 'init'], 0);
    }

    /**
     * Plugin activation.
     */
    public function activate(): void
    {
        UD_Roles::create_roles();
        UD_CPT::register_post_types();
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation.
     */
    public function deactivate(): void
    {
        flush_rewrite_rules();
    }

    /**
     * Initialize plugin on 'init'.
     */
    public function init(): void
    {
        UD_Roles::init();
        UD_CPT::init();
        UD_Admin::init();
        UD_Users::init();
        UD_Pets::init();
        UD_Walkers::init();
        UD_Search::init();
        UD_Bookings::init();
        UD_Payments::init();
        UD_Reviews::init();
        UD_Gamification::init();
        UD_Home::init();
    }
}

/**
 * Boot the plugin.
 */
function urbandog(): UrbanDog
{
    return UrbanDog::instance();
}

// Launch!
urbandog();
