(function ($) {
    // --- Global Districts Data ---
    window.udDistricts = [
        "Ate", "Barranco", "Breña", "Cercado de Lima", "Chaclacayo", "Chorrillos",
        "Independencia", "Jesús María", "La Molina", "La Victoria", "Lince",
        "Los Olivos", "Magdalena del Mar", "Miraflores", "Pueblo Libre", "Rímac",
        "San Borja", "San Isidro", "San Juan de Lurigancho", "San Juan de Miraflores",
        "San Luis", "San Martín de Porres", "San Miguel", "Santa Anita",
        "Santiago de Surco", "Surquillo"
    ];

    window.udValidateDistrict = function (inputEl, errorEl, submitBtn) {
        if (!inputEl) return true;
        const $input = $(inputEl);
        const $error = $(errorEl);
        const $btn = $(submitBtn);
        const val = $input.val().trim().toLowerCase();

        // Find the text span to update message
        let $msg = $error.find('.error-msg');
        if (!$msg.length) {
            // If not found, look for text after the icon
            $msg = $error;
        }

        if (!val) {
            // Empty state: show specific message
            $error.html('<i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i> <span class="error-msg">Por favor, ingresa tu distrito</span>');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            $error.css('display', 'flex');
            if ($btn.length) $btn.addClass('btn-disabled');
            return false;
        }

        const isValid = window.udDistricts.some(d => d.toLowerCase() === val);
        if (isValid) {
            $error.hide();
            if ($btn.length) $btn.removeClass('btn-disabled');
        } else {
            $error.html('<i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i> <span class="error-msg">No hay cobertura en este momento</span>');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            $error.css('display', 'flex');
            if ($btn.length) $btn.addClass('btn-disabled');
        }
        return isValid;
    };

    // Custom Autocomplete Logic
    window.udInitAutocomplete = function (inputSelector, resultsSelector, errorSelector, submitBtnSelector) {
        const $input = $(inputSelector);
        const $results = $(resultsSelector);
        const $error = $(errorSelector);
        const $btn = $(submitBtnSelector);

        if (!$input.length || !$results.length) return;

        $input.on('input', function () {
            const query = $(this).val().trim().toLowerCase();
            $error.hide(); // Hide error while typing
            if ($btn.length) $btn.removeClass('btn-disabled');

            if (query.length < 1) {
                $results.hide().empty();
                return;
            }

            const filtered = window.udDistricts.filter(d => d.toLowerCase().includes(query));

            if (filtered.length > 0) {
                const html = filtered.map(d => `<li>${d}</li>`).join('');
                $results.html(html).show();
            } else {
                $results.hide().empty();
            }
        });

        // Handle item selection
        $results.on('click', 'li', function () {
            $input.val($(this).text());
            $results.hide().empty();
            $error.hide();
            if ($btn.length) $btn.removeClass('btn-disabled');
            $input.trigger('change');
        });

        // Close dropdown on click outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.ud-autocomplete-container').length) {
                $results.hide().empty();
            }
        });
    };

    $(document).ready(function ($) {
        // Mobile Menu Toggle
        $('#mobile-menu-button').on('click', function () {
            $('#mobile-menu').toggleClass('open');
        });

        // Header scroll effect
        $(window).scroll(function () {
            if ($(this).scrollTop() > 50) {
                $('.site-header').addClass('scrolled');
            } else {
                $('.site-header').removeClass('scrolled');
            }
        });

        // Smooth scroll for anchors
        $('a[href^="#"]').on('click', function (e) {
            e.preventDefault();
            var target = this.hash;
            var $target = $(target);
            $('html, body').stop().animate({
                'scrollTop': $target.offset().top - 80
            }, 900, 'swing');
        });

        // Micro-animations on scroll
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('reveal');
                }
            });
        }, observerOptions);

        $('.step-card-v2, .ud-diff-content, .testimonial-card-v2').each(function () {
            observer.observe(this);
        });

        // FAQ Accordion
        $('.faq-answer-v2').hide();
        $('.faq-question-v2').on('click', function () {
            const $item = $(this).closest('.faq-item-v2');
            const $answer = $(this).next('.faq-answer-v2');
            const $icon = $(this).find('i');

            $('.faq-answer-v2').not($answer).slideUp();
            $('.faq-item-v2').not($item).removeClass('active');
            $('.faq-question-v2 i').not($icon).css('transform', 'rotate(0deg)');

            $answer.slideToggle();
            $item.toggleClass('active');

            const isVisible = $answer.is(':visible');
            $icon.css('transform', isVisible ? 'rotate(180deg)' : 'rotate(0deg)');
        });

        // Auto-attach to Home Hero Search
        const $heroInput = $('#hero-distrito-input');
        const $heroError = $('#hero-district-error');
        const $heroForm = $('#hero-search');
        const $heroBtn = $heroForm.find('button[type="submit"]');

        if ($heroInput.length && $heroError.length) {
            window.udInitAutocomplete('#hero-distrito-input', '#hero-autocomplete-results', '#hero-district-error', '#hero-search button[type="submit"]');

            $heroForm.on('submit', function (e) {
                const isValid = window.udValidateDistrict($heroInput, $heroError, $heroBtn);
                if (!isValid) {
                    e.preventDefault();
                    $heroError.css('display', 'flex');
                    if ($heroBtn.length) $heroBtn.addClass('btn-disabled');
                }
            });

            $heroInput.on('blur', function () {
                setTimeout(() => {
                    const val = $heroInput.val().trim();
                    if (val && !window.udValidateDistrict($heroInput, $heroError, $heroBtn)) {
                        $heroError.css('display', 'flex');
                        if ($heroBtn.length) $heroBtn.addClass('btn-disabled');
                    }
                }, 200);
            });
        }
    });
})(jQuery);
