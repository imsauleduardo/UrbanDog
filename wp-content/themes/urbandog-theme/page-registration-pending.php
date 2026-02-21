<?php
/**
 * Template Name: Registration Pending
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<main class="ud-registration-wrapper">
    <div class="ud-registration-container" style="text-align: center; padding: 4rem 2rem;">
        <div class="ud-pending-icon" style="margin-bottom: 2rem;">
            <div
                style="background: #fef3c7; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i data-lucide="clock" style="width: 40px; height: 40px; color: #d97706;"></i>
            </div>
        </div>

        <h1 style="font-size: 2rem; color: #111827; margin-bottom: 1rem;">
            <?php _e('¡Gracias por registrarte!', 'urbandog'); ?>
        </h1>

        <p style="font-size: 1.125rem; color: #4b5563; line-height: 1.6; max-width: 500px; margin: 0 auto 2rem;">
            <?php _e('Tu cuenta de paseador ha sido recibida correctamente. Actualmente nos encontramos verificando tus documentos y antecedentes para garantizar la seguridad de nuestra comunidad.', 'urbandog'); ?>
        </p>

        <div
            style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; max-width: 450px; margin: 0 auto 2rem; text-align: left;">
            <h3 style="font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 0.75rem;">
                <?php _e('¿Qué sigue?', 'urbandog'); ?>
            </h3>
            <ul style="color: #6b7280; font-size: 0.95rem; margin: 0; padding-left: 1.25rem;">
                <li style="margin-bottom: 0.5rem;">
                    <?php _e('Revisaremos tu DNI y antecedentes (24-48 horas).', 'urbandog'); ?>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <?php _e('Nos pondremos en contacto contigo si necesitamos más información.', 'urbandog'); ?>
                </li>
                <li>
                    <?php _e('Te enviaremos un correo electrónico una vez que tu cuenta sea aprobada.', 'urbandog'); ?>
                </li>
            </ul>
        </div>

        <a href="<?php echo home_url(); ?>" class="ud-btn-submit"
            style="display: inline-block; max-width: 200px; text-decoration: none;">
            <?php _e('Volver al Inicio', 'urbandog'); ?>
        </a>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<?php get_footer(); ?>