<?php
/**
 * Template Name: Lost Password
 * 
 * @package UrbanDog
 */

if (is_user_logged_in()) {
    wp_safe_redirect(home_url());
    exit;
}

get_header(); ?>

<main class="ud-auth-page-wrapper">
    <div class="ud-container ud-auth-container">
        <div class="ud-auth-card">
            <div class="ud-auth-header">
                <i data-lucide="key-round" class="ud-auth-icon"></i>
                <h1 class="ud-h1">
                    <?php _e('Recuperar Contraseña', 'urbandog'); ?>
                </h1>
                <p class="ud-auth-subtitle">
                    <?php _e('Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.', 'urbandog'); ?>
                </p>
            </div>

            <?php
            // Check for success or error messages from WordPress
            if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
                echo '<div class="ud-alert ud-alert-success mb-6">' . esc_html__('Se ha enviado un correo con el enlace de recuperación.', 'urbandog') . '</div>';
            }
            if (isset($_GET['errors'])) {
                echo '<div class="ud-alert ud-alert-error mb-6">' . esc_html__('Hubo un error al procesar tu solicitud. Por favor, verifica el correo.', 'urbandog') . '</div>';
            }
            ?>

            <form name="lostpasswordform" id="lostpasswordform"
                action="<?php echo esc_url(network_site_url('wp-login.php?action=lostpassword', 'login_post')); ?>"
                method="post" class="ud-auth-form">
                <div class="ud-form-group">
                    <label for="user_login">
                        <?php _e('Correo Electrónico', 'urbandog'); ?>
                    </label>
                    <input type="text" name="user_login" id="user_login" class="ud-form-control" value="" size="20"
                        required>
                </div>

                <input type="hidden" name="redirect_to"
                    value="<?php echo esc_url(home_url('/recuperar-contrasena/?reset=success')); ?>">

                <button type="submit" name="wp-submit" id="wp-submit" class="ud-btn-submit">
                    <?php _e('Enviar Enlace', 'urbandog'); ?>
                </button>
            </form>

            <div class="ud-auth-footer">
                <p>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>">
                        <?php _e('Volver al Inicio de Sesión', 'urbandog'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>