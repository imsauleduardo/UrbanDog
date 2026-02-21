<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon.svg" type="image/svg+xml">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="site-header">
        <div class="container header-container">
            <div class="site-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center"
                    style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                    <i data-lucide="dog" style="color: var(--ud-primary); width: 32px; height: 32px;"></i>
                    <span
                        style="font-size: 1.25rem; font-weight: 700; color: var(--ud-primary); letter-spacing: -0.025em; line-height: 1;">URBANDOG</span>
                </a>
            </div>

            <nav class="nav-menu">
                <a href="<?php echo esc_url(home_url('/')); ?>"
                    class="nav-link <?php echo is_front_page() ? 'active' : ''; ?>"><?php _e('Inicio', 'urbandog'); ?></a>
                <a href="<?php echo esc_url(home_url('/buscar/')); ?>"
                    class="nav-link"><?php _e('Buscar Paseador', 'urbandog'); ?></a>

                <?php if (is_user_logged_in()): ?>
                    <?php if (current_user_can('administrator')): ?>
                        <a href="<?php echo esc_url(home_url('/panel-paseador/')); ?>"
                            class="nav-link font-bold text-ud-primary">
                            <i data-lucide="layout-dashboard"
                                class="w-4 h-4 inline mr-1"></i><?php _e('Panel Paseador', 'urbandog'); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/panel-dueno/')); ?>"
                            class="nav-link"><?php _e('Mi Panel', 'urbandog'); ?></a>
                    <?php elseif (UD_Roles::is_walker()): ?>
                        <a href="<?php echo esc_url(home_url('/panel-paseador/')); ?>" class="nav-link text-ud-primary">
                            <?php _e('Mi Panel', 'urbandog'); ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo esc_url(home_url('/panel-dueno/')); ?>"
                            class="nav-link"><?php _e('Mi Panel', 'urbandog'); ?></a>
                    <?php endif; ?>
                    <a href="<?php echo wp_logout_url(home_url()); ?>"
                        class="nav-link text-slate-400 text-sm"><?php _e('Salir', 'urbandog'); ?></a>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/registro-paseador/')); ?>"
                        class="nav-link"><?php _e('Ser Paseador', 'urbandog'); ?></a>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="btn btn-primary"
                        style="margin-left: 1rem;"><?php _e('Ingresar / Registrarse', 'urbandog'); ?></a>
                <?php endif; ?>
            </nav>

            <button id="mobile-menu-button" class="mobile-menu-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-nav">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Inicio', 'urbandog'); ?></a>
            <a href="<?php echo esc_url(home_url('/buscar/')); ?>"><?php _e('Buscar Paseador', 'urbandog'); ?></a>
            <?php if (is_user_logged_in()): ?>
                <?php if (current_user_can('administrator')): ?>
                    <a
                        href="<?php echo esc_url(home_url('/panel-paseador/')); ?>"><?php _e('Panel Paseador', 'urbandog'); ?></a>
                    <a href="<?php echo esc_url(home_url('/panel-dueno/')); ?>"><?php _e('Mi Panel', 'urbandog'); ?></a>
                <?php elseif (UD_Roles::is_walker()): ?>
                    <a href="<?php echo esc_url(home_url('/panel-paseador/')); ?>"><?php _e('Mi Panel', 'urbandog'); ?></a>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/panel-dueno/')); ?>"><?php _e('Mi Panel', 'urbandog'); ?></a>
                <?php endif; ?>
                <a href="<?php echo wp_logout_url(home_url()); ?>"><?php _e('Salir', 'urbandog'); ?></a>
            <?php else: ?>
                <a
                    href="<?php echo esc_url(home_url('/registro-paseador/')); ?>"><?php _e('Ser Paseador', 'urbandog'); ?></a>
                <a href="<?php echo esc_url(home_url('/registro/')); ?>"><?php _e('Registrarse', 'urbandog'); ?></a>
                <a href="<?php echo esc_url(home_url('/login/')); ?>"><?php _e('Ingresar', 'urbandog'); ?></a>
            <?php endif; ?>
        </div>
    </header>