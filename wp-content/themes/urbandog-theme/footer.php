<?php
/**
 * The template for displaying the footer
 */
?>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center"
                style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none; margin-bottom: 1.5rem;">
                <i data-lucide="dog" style="color: var(--ud-primary); width: 32px; height: 32px;"></i>
                <span
                    style="font-size: 1.25rem; font-weight: 700; color: var(--ud-primary); letter-spacing: -0.025em; line-height: 1;">URBANDOG</span>
            </a>
            <p class="footer-desc">
                <?php _e('Conectando perros felices con paseadores de confianza en tu vecindario desde 2024.', 'urbandog'); ?>
            </p>
        </div>

        <div class="footer-nav">
            <h4><?php _e('Servicios', 'urbandog'); ?></h4>
            <ul class="footer-links">
                <li><a href="#"><?php _e('Paseos Individuales', 'urbandog'); ?></a></li>
                <li><a href="#"><?php _e('Paseos Grupales', 'urbandog'); ?></a></li>
                <li><a href="#"><?php _e('Cuidado en Casa', 'urbandog'); ?></a></li>
                <li><a href="#"><?php _e('Visitas Rápidas', 'urbandog'); ?></a></li>
            </ul>
        </div>

        <div class="footer-nav">
            <h4><?php _e('Compañía', 'urbandog'); ?></h4>
            <ul class="footer-links">
                <li><a href="#"><?php _e('Sobre Nosotros', 'urbandog'); ?></a></li>
                <li><a href="#"><?php _e('Seguridad', 'urbandog'); ?></a></li>
                <li><a href="#"><?php _e('Blog', 'urbandog'); ?></a></li>
                <li><a href="#"><?php _e('Carreras', 'urbandog'); ?></a></li>
            </ul>
        </div>

        <div class="footer-nav">
            <h4><?php _e('Síguenos', 'urbandog'); ?></h4>
            <div style="display: flex; gap: 1rem;">
                <a href="#" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="width: 24px; height: 24px;">
                        <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                    </svg>
                </a>
                <a href="#" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="width: 24px; height: 24px;">
                        <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5" />
                    </svg>
                </a>
                <a href="#" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="width: 24px; height: 24px;">
                        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z" />
                        <rect width="4" height="12" x="2" y="9" />
                        <circle cx="4" cy="4" r="2" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> URBANDOG. <?php _e('Todos los derechos reservados.', 'urbandog'); ?></p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>