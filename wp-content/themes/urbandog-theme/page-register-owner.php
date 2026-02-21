<?php
/**
 * Template Name: Register Owner
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_safe_redirect(home_url('/panel-dueno/'));
    exit;
}

get_header(); ?>

<main class="ud-registration-wrapper">
    <div class="ud-registration-container">
        <div class="ud-registration-header">
            <h1>
                <?php _e('Únete a UrbanDog 🐾', 'urbandog'); ?>
            </h1>
            <p>
                <?php _e('Regístrate como dueño para encontrar el mejor paseador para tu perro.', 'urbandog'); ?>
            </p>
        </div>

        <div id="ud-registration-alert" class="ud-alert"></div>

        <form id="ud-registration-form" class="ud-form">
            <input type="hidden" name="role" value="ud_owner">

            <div class="ud-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="ud-form-group">
                    <label for="first_name">
                        <?php _e('Nombre', 'urbandog'); ?>
                    </label>
                    <input type="text" id="first_name" name="first_name" class="ud-form-control" required>
                </div>
                <div class="ud-form-group">
                    <label for="last_name">
                        <?php _e('Apellido', 'urbandog'); ?>
                    </label>
                    <input type="text" id="last_name" name="last_name" class="ud-form-control" required>
                </div>
            </div>

            <div class="ud-form-group">
                <label for="email">
                    <?php _e('Correo Electrónico', 'urbandog'); ?>
                </label>
                <input type="email" id="email" name="email" class="ud-form-control" required>
            </div>

            <div class="ud-form-group">
                <label for="phone">
                    <?php _e('Celular / WhatsApp', 'urbandog'); ?>
                </label>
                <input type="tel" id="phone" name="phone" class="ud-form-control" required>
            </div>

            <div class="ud-form-group">
                <label for="password">
                    <?php _e('Contraseña', 'urbandog'); ?>
                </label>
                <input type="password" id="password" name="password" class="ud-form-control" required minlength="8">
                <div id="password-strength" class="ud-strength-meter">
                    <div class="ud-strength-bar"></div>
                    <span class="ud-strength-text"></span>
                </div>
            </div>

            <div class="ud-form-group">
                <label for="confirm_password">
                    <?php _e('Confirmar Contraseña', 'urbandog'); ?>
                </label>
                <input type="password" id="confirm_password" name="confirm_password" class="ud-form-control" required
                    minlength="8">
            </div>

            <button type="submit" class="ud-btn-submit">
                <?php _e('Registrarse', 'urbandog'); ?>
            </button>
        </form>

        <div class="ud-registration-footer">
            <p>
                <?php _e('¿Ya tienes cuenta?', 'urbandog'); ?> <a href="<?php echo wp_login_url(); ?>">
                    <?php _e('Inicia sesión aquí', 'urbandog'); ?>
                </a>
            </p>
        </div>
    </div>
</main>

<?php get_footer(); ?>