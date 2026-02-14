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
                <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-link active">Inicio</a>
                <a href="#" class="nav-link">Buscar Paseador</a>
                <a href="#" class="nav-link">Ser Paseador</a>
                <a href="#" class="btn btn-primary" style="margin-left: 1rem;">Reservar Ahora</a>
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
            <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
            <a href="#">Buscar Paseador</a>
            <a href="#">Ser Paseador</a>
        </div>
    </header>