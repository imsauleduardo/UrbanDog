<?php get_header(); ?>

<main id="primary" class="site-main">

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="blob-container" style="position: absolute; inset: 0; z-index: -1;">
            <div class="absolute top-0 right-0 transform translate-x-1/3 -translate-y-1/4" style="opacity: 0.3;">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"
                    style="width: 800px; height: 800px; fill: #D1FAE5;">
                    <path
                        d="M44.7,-76.4C58.9,-69.2,71.8,-59.1,81.6,-46.6C91.4,-34.1,98.1,-19.2,95.8,-5.3C93.5,8.6,82.2,21.5,70.6,32.2C59,42.9,47.1,51.4,34.8,58.6C22.5,65.8,9.8,71.7,-1.8,74.8C-13.4,77.9,-23.9,78.2,-34.7,73.4C-45.5,68.6,-56.6,58.7,-65.4,47.1C-74.2,35.5,-80.7,22.2,-81.8,8.5C-82.9,-5.2,-78.6,-19.3,-70.5,-31.2C-62.4,-43.1,-50.5,-52.8,-37.8,-60.6C-25.1,-68.4,-11.6,-74.3,2.6,-78.8C16.8,-83.3,30.5,-83.6,44.7,-76.4Z"
                        transform="translate(100 100)" />
                </svg>
            </div>
        </div>

        <div class="container hero-grid">
            <div class="hero-content">
                <?php
                $hero_title = get_post_meta(get_the_ID(), 'ud_hero_title', true) ?: __('Paseos que hacen mover la colita 🐕', 'urbandog');
                $hero_subtitle = get_post_meta(get_the_ID(), 'ud_hero_subtitle', true) ?: __('Encuentra al paseador ideal en tu distrito y dale a tu mejor amigo la aventura que se merece.', 'urbandog');
                $search_placeholder = get_post_meta(get_the_ID(), 'ud_hero_placeholder', true) ?: __('Ingresa tu distrito (ej. Los Olivos)', 'urbandog');
                ?>
                <span class="hero-badge">
                    <svg style="width: 1rem; height: 1rem; margin-right: 0.25rem; fill: currentColor;"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon
                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                    </svg>
                    <?php _e('#1 en Paseos Seguros', 'urbandog'); ?>
                </span>
                <h1 class="hero-title"><?php echo esc_html($hero_title); ?></h1>
                <p class="hero-desc"><?php echo esc_html($hero_subtitle); ?></p>

                <form id="hero-search" class="hero-search-form" action="<?php echo esc_url(home_url('/buscar/')); ?>"
                    method="get">
                    <div class="search-field">
                        <svg style="height: 1.5rem; width: 1.5rem; color: #10b981; margin-right: 0.75rem; flex-shrink: 0;"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <div class="ud-autocomplete-container"
                            style="position: relative; width: 100%; text-align: left;">
                            <label
                                style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; text-align: left;"><?php _e('Tu ubicación', 'urbandog'); ?></label>
                            <input type="text" name="distrito" id="hero-distrito-input" autocomplete="off"
                                placeholder="<?php echo esc_attr($search_placeholder); ?>">
                            <ul id="hero-autocomplete-results" class="ud-autocomplete-results"></ul>
                            <div id="hero-district-error" class="district-error">
                                <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i>
                                <?php _e('No hay cobertura en este momento', 'urbandog'); ?>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-slate" style="height: 3rem; min-width: 8rem;">
                        <svg style="height: 1rem; width: 1rem; margin-right: 0.5rem;" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <?php _e('Buscar', 'urbandog'); ?>
                    </button>
                </form>

                <div class="hero-social-proof">
                    <div class="avatar-stack">
                        <img class="avatar" src="https://i.pravatar.cc/100?img=11" alt="User" />
                        <img class="avatar" src="https://i.pravatar.cc/100?img=12" alt="User" />
                        <img class="avatar" src="https://i.pravatar.cc/100?img=13" alt="User" />
                        <img class="avatar" src="https://i.pravatar.cc/100?img=14" alt="User" />
                    </div>
                    <p><?php _e('+2,000 dueños confían en nosotros', 'urbandog'); ?></p>
                </div>
            </div>

            <div class="hero-visual">
                <div style="position: relative; width: 100%; max-width: 28rem;">
                    <!-- Animated Blobs -->
                    <div class="absolute top-0 -left-4 w-48 h-48 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"
                        style="background-color: #d8b4fe; position: absolute; top: 0; left: -1rem; border-radius: 9999px; filter: blur(24px);">
                    </div>
                    <div class="absolute top-0 -right-4 w-48 h-48 bg-brand-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"
                        style="background-color: #6ee7b7; position: absolute; top: 0; right: -1rem; border-radius: 9999px; filter: blur(24px);">
                    </div>
                    <div class="absolute -bottom-8 left-10 w-48 h-48 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"
                        style="background-color: #f9a8d4; position: absolute; bottom: -2rem; left: 2.5rem; border-radius: 9999px; filter: blur(24px);">
                    </div>

                    <img src="https://images.unsplash.com/photo-1601758124510-52d02ddb7cbd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                        alt="Dog walker" class="hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- Value Props -->
    <section class="benefits-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php _e('¿Por qué elegir URBANDOG?', 'urbandog'); ?></h2>
                <p class="section-desc">
                    <?php _e('No somos solo una app, somos una comunidad de amantes de los perros comprometidos con el bienestar animal.', 'urbandog'); ?>
                </p>
            </div>

            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon bg-blue-50">
                        <svg style="width: 2rem; height: 2rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem;">
                        <?php _e('Confianza y Seguridad', 'urbandog'); ?>
                    </h3>
                    <p style="color: #64748b; font-size: 0.875rem; line-height: 1.6;">
                        <?php _e('Todos los paseadores pasan por un riguroso proceso de verificación de identidad y antecedentes.', 'urbandog'); ?>
                    </p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon bg-brand-50">
                        <svg style="width: 2rem; height: 2rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" />
                            <line x1="8" y1="2" x2="8" y2="18" />
                            <line x1="16" y1="6" x2="16" y2="22" />
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem;"><?php _e('Rastreo GPS', 'urbandog'); ?></h3>
                    <p style="color: #64748b; font-size: 0.875rem; line-height: 1.6;">
                        <?php _e('Sigue el paseo de tu perro en tiempo real a través de nuestro mapa interactivo y recibe fotos.', 'urbandog'); ?>
                    </p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon bg-red-50">
                        <svg style="width: 2rem; height: 2rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem;"><?php _e('Amor Garantizado', 'urbandog'); ?>
                    </h3>
                    <p style="color: #64748b; font-size: 0.875rem; line-height: 1.6;">
                        <?php _e('Si tú o tu perro no están 100% satisfechos con el servicio, te devolvemos tu dinero.', 'urbandog'); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container steps-grid">
            <div>
                <?php
                $how_title = get_post_meta(get_the_ID(), 'ud_how_title', true) ?: __('Tan fácil como <span style="color:#10b981">1, 2, 3</span>', 'urbandog');
                ?>
                <h2 style="font-size: 2.25rem; margin-bottom: 2rem;">
                    <?php echo wp_kses_post($how_title); ?>
                </h2>
                <div class="steps-list">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div>
                            <h4 class="step-title"><?php _e('Busca en tu zona', 'urbandog'); ?></h4>
                            <p class="step-desc">
                                <?php _e('Explora perfiles de paseadores cercanos, lee reseñas y verifica sus especialidades.', 'urbandog'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div>
                            <h4 class="step-title"><?php _e('Reserva y Conoce', 'urbandog'); ?></h4>
                            <p class="step-desc">
                                <?php _e('Agenda un paseo o un encuentro previo ("Meet & Greet") gratuito para asegurar la química.', 'urbandog'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div>
                            <h4 class="step-title"><?php _e('Relájate', 'urbandog'); ?></h4>
                            <p class="step-desc">
                                <?php _e('Recibe notificaciones de inicio y fin, mapa del recorrido y un reporte con fotos.', 'urbandog'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div style="position: relative;">
                <div
                    style="position: absolute; inset: 0; background-color: #10b981; transform: rotate(6deg); border-radius: 1.5rem; opacity: 0.2; filter: blur(8px);">
                </div>
                <img src="https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                    alt="Happy dog"
                    style="position: relative; border-radius: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 4px solid #1e293b;">
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <!-- Icon Mosaic Pattern -->
        <div class="cta-pattern">
            <!-- Paw Icons -->
            <i data-lucide="paw-print" class="cta-icon" style="--rotation: -15deg; --delay: 0s;"></i>
            <i data-lucide="paw-print" class="cta-icon" style="--rotation: 45deg; --delay: 0.5s;"></i>
            <!-- Bone Icons -->
            <i data-lucide="bone" class="cta-icon" style="--rotation: 20deg; --delay: 1s;"></i>
            <i data-lucide="bone" class="cta-icon" style="--rotation: -30deg; --delay: 1.5s;"></i>
            <!-- Dog Silhouette (using dog icon for consistency) -->
            <i data-lucide="dog" class="cta-icon" style="--rotation: -5deg; --delay: 2s;"></i>
        </div>

        <div class="container" style="max-width: 56rem; position: relative; z-index: 10;">
            <?php
            $cta_title = get_post_meta(get_the_ID(), 'ud_cta_title', true) ?: __('¿Listo para empezar?', 'urbandog');
            $cta_subtitle = get_post_meta(get_the_ID(), 'ud_cta_subtitle', true) ?: __('Tu perro se merece los mejores paseos de la ciudad. Únete a URBANDOG hoy mismo.', 'urbandog');
            ?>
            <h2 class="cta-title"><?php echo esc_html($cta_title); ?></h2>
            <p class="cta-desc">
                <?php echo esc_html($cta_subtitle); ?>
            </p>
            <a href="#" class="btn"
                style="background: white; color: #059669; padding: 1.25rem 3rem; font-size: 1.25rem; font-weight: 700; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <?php _e('Encontrar un Paseador', 'urbandog'); ?>
            </a>
        </div>
    </section>

</main>

<?php get_footer(); ?>