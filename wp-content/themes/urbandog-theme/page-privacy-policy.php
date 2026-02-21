<?php
/**
 * Template Name: Política de Privacidad
 * 
 * @package UrbanDog
 */

get_header(); ?>

<main class="ud-main-content ud-privacy-policy">
    <div class="ud-container">
        <header class="ud-page-header">
            <h1 class="ud-page-title">
                <?php _e('Política de Privacidad', 'urbandog'); ?>
            </h1>
        </header>

        <div class="ud-content-block">
            <p>
                <?php _e('En UrbanDog, nos tomamos muy en serio tu privacidad. Esta política describe cómo recopilamos, usamos y protegemos tu información personal.', 'urbandog'); ?>
            </p>

            <h2>
                <?php _e('1. Información que recopilamos', 'urbandog'); ?>
            </h2>
            <p>
                <?php _e('Recopilamos información que nos proporcionas directamente al registrarte, como tu nombre, correo electrónico, número de teléfono y documentos de identidad.', 'urbandog'); ?>
            </p>

            <h2>
                <?php _e('2. Uso de la información', 'urbandog'); ?>
            </h2>
            <p>
                <?php _e('Utilizamos tu información para verificar tu identidad, procesar servicios de paseo, contactarte sobre actualizaciones relevantes y mejorar nuestra plataforma.', 'urbandog'); ?>
            </p>

            <h2>
                <?php _e('3. Protección de datos', 'urbandog'); ?>
            </h2>
            <p>
                <?php _e('Implementamos medidas de seguridad para proteger tus datos personales contra el acceso no autorizado o la divulgación.', 'urbandog'); ?>
            </p>

            <h2>
                <?php _e('4. Tus derechos', 'urbandog'); ?>
            </h2>
            <p>
                <?php _e('Puedes darte de baja de nuestras comunicaciones en cualquier momento y tienes derecho a solicitar el acceso, corrección o eliminación de tus datos personales.', 'urbandog'); ?>
            </p>

            <p>
                <?php _e('Para más información o consultas sobre tu privacidad, contáctanos a soporte@urbandog.pe', 'urbandog'); ?>
            </p>
        </div>
    </div>
</main>

<?php get_footer(); ?>