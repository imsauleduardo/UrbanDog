<?php
/**
 * Template Name: Register Walker
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_safe_redirect(home_url('/panel-paseador/'));
    exit;
}

get_header(); ?>

<main class="ud-registration-wrapper">
    <div class="ud-registration-container">
        <div class="ud-registration-header">
            <h1>
                <?php _e('Trabaja como Paseador 🐾', 'urbandog'); ?>
            </h1>
            <p>
                <?php _e('Sé tu propio jefe, elige tus horarios y gana dinero paseando perros.', 'urbandog'); ?>
            </p>
        </div>

        <div class="ud-walker-info">
            <div class="ud-walker-info-title">
                <i data-lucide="info" style="width: 18px; height: 18px; color: #10b981;"></i>
                <?php _e('Importante', 'urbandog'); ?>
            </div>
            <p class="ud-walker-info-text">
                <?php _e('Para ser paseador, necesitamos verificar tu identidad y antecedentes para garantizar la seguridad de nuestra comunidad. Deberás subir tu DNI, Antecedentes Policiales y Certificado de Domicilio (recibo de luz, agua, teléfono fijo).', 'urbandog'); ?>
            </p>
        </div>

        <div id="ud-registration-alert" class="ud-alert"></div>

        <form id="ud-registration-form" class="ud-form" enctype="multipart/form-data">
            <input type="hidden" name="role" value="ud_walker">

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

            <div class="ud-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="ud-form-group">
                    <label for="walker_dni">
                        <?php _e('Número de DNI', 'urbandog'); ?>
                    </label>
                    <input type="text" id="walker_dni" name="walker_dni" class="ud-form-control" required>
                </div>
                <div class="ud-form-group">
                    <label for="walker_linkedin">
                        <?php _e('LinkedIn (Opcional)', 'urbandog'); ?>
                    </label>
                    <input type="url" id="walker_linkedin" name="walker_linkedin" class="ud-form-control"
                        placeholder="https://linkedin.com/in/...">
                </div>
            </div>

            <div class="ud-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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
            </div>

            <div class="ud-form-group">
                <label><?php _e('Foto/PDF de tu DNI (Obligatorio)', 'urbandog'); ?></label>
                <label for="doc_dni" class="ud-file-custom">
                    <span class="ud-file-btn"><?php _e('Seleccionar archivo', 'urbandog'); ?></span>
                    <span class="ud-file-name"><?php _e('Sin archivo seleccionado', 'urbandog'); ?></span>
                    <input type="file" id="doc_dni" name="doc_dni" accept=".pdf,image/*" required>
                </label>
            </div>

            <div class="ud-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="ud-form-group">
                    <label><?php _e('Antecedentes Policiales (PDF/IMG)', 'urbandog'); ?></label>
                    <label for="doc_antecedentes" class="ud-file-custom">
                        <span class="ud-file-btn"><?php _e('Seleccionar', 'urbandog'); ?></span>
                        <span class="ud-file-name"><?php _e('Sin archivo', 'urbandog'); ?></span>
                        <input type="file" id="doc_antecedentes" name="doc_antecedentes" accept=".pdf,image/*" required>
                    </label>
                </div>
                <div class="ud-form-group">
                    <label><?php _e('Certificado de Domicilio (PDF/IMG)', 'urbandog'); ?></label>
                    <label for="doc_domicilio" class="ud-file-custom">
                        <span class="ud-file-btn"><?php _e('Seleccionar', 'urbandog'); ?></span>
                        <span class="ud-file-name"><?php _e('Sin archivo', 'urbandog'); ?></span>
                        <input type="file" id="doc_domicilio" name="doc_domicilio" accept=".pdf,image/*" required>
                    </label>
                </div>
            </div>

            <div class="ud-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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
                    <input type="password" id="confirm_password" name="confirm_password" class="ud-form-control"
                        required minlength="8">
                </div>
            </div>

            <div class="ud-form-disclaimer">
                <?php
                printf(
                    __('Al enviar este formulario acepta que UrbanDog use la información brindada para contactarte sobre contenido, productos y servicios relevantes. Puedes darte de baja en cualquier momento. Para más información, consulta nuestra %s.', 'urbandog'),
                    '<a href="' . esc_url(home_url('/politica-de-privacidad/')) . '">' . __('Política de Privacidad', 'urbandog') . '</a>'
                );
                ?>
            </div>

            <button type="submit" class="ud-btn-submit">
                <?php _e('Unirse como Paseador', 'urbandog'); ?>
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