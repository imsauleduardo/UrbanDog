<?php
/**
 * Template Name: Custom Login
 *
 * @package UrbanDog
 */

if (is_user_logged_in()) {
    wp_safe_redirect(home_url());
    exit;
}

get_header();
?>

<main class="ud-auth-page-wrapper">
    <div class="ud-container ud-auth-container">
        <div class="ud-auth-card">
            <div class="ud-auth-header">
                <i data-lucide="log-in" class="ud-auth-icon"></i>
                <h1 class="ud-h1">
                    <?php _e('Iniciar Sesión', 'urbandog'); ?>
                </h1>
                <p class="ud-auth-subtitle">
                    <?php _e('Bienvenido de nuevo a UrbanDog.', 'urbandog'); ?>
                </p>
            </div>

            <?php
            if (isset($_GET['login']) && $_GET['login'] === 'failed') {
                echo '<div class="ud-alert ud-alert-error mb-6">' . esc_html__('Credenciales incorrectas. Por favor, intenta de nuevo.', 'urbandog') . '</div>';
            }
            ?>

            <form name="loginform" id="loginform"
                action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post"
                class="ud-auth-form">
                <div class="ud-form-group">
                    <label for="user_login">
                        <?php _e('Correo Electrónico', 'urbandog'); ?>
                    </label>
                    <input type="text" name="log" id="user_login" class="ud-form-control" value="" size="20" required>
                </div>

                <div class="ud-form-group">
                    <label for="user_pass">
                        <?php _e('Contraseña', 'urbandog'); ?>
                    </label>
                    <input type="password" name="pwd" id="user_pass" class="ud-form-control" value="" size="20"
                        required>
                </div>

                <div class="ud-form-options">
                    <label for="rememberme" class="ud-remember-me">
                        <input name="rememberme" type="checkbox" id="rememberme" value="forever">
                        <span>
                            <?php _e('Recordarme', 'urbandog'); ?>
                        </span>
                    </label>
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="ud-forgot-password">
                        <?php _e('¿Olvidaste tu contraseña?', 'urbandog'); ?>
                    </a>
                </div>

                <button type="submit" name="wp-submit" id="wp-submit" class="ud-btn-submit">
                    <?php _e('Entrar', 'urbandog'); ?>
                </button>

                <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url()); ?>">
            </form>

            <div class="ud-auth-footer">
                <p>
                    <?php _e('¿Aún no tienes cuenta?', 'urbandog'); ?> <a
                        href="<?php echo esc_url(home_url('/registro/')); ?>">
                        <?php _e('Regístrate aquí', 'urbandog'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>