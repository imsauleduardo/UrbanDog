jQuery(document).ready(function ($) {
    const $form = $('#ud-walker-settings-form');
    const $alert = $('#settings-alert');
    const $btn = $form.find('button[type="submit"]');

    // 1. AJAX Form Submission
    $form.on('submit', function (e) {
        e.preventDefault();

        $btn.prop('disabled', true).text('Guardando...');
        $alert.hide();

        $.post(udWalkerSettings.ajaxUrl, $(this).serialize(), function (response) {
            if (response.success) {
                const successHtml = `
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                        <div>
                            <p class="font-bold">¡Cambios Guardados!</p>
                            <p class="text-sm">${response.data.message}</p>
                        </div>
                    </div>
                `;
                $alert.removeClass('bg-red-50 text-red-700 border-red-200')
                    .addClass('bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm')
                    .html(successHtml)
                    .fadeIn();

                $btn.prop('disabled', false).html('<i data-lucide="save"></i> Guardar Todos los Cambios');
                lucide.createIcons();

                // Success animation/scroll
                $('html, body').animate({ scrollTop: $alert.offset().top - 100 }, 500);
            } else {
                const errorHtml = `
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-6 h-6"></i>
                        <div>
                            <p class="font-bold">Error al guardar</p>
                            <p class="text-sm">${response.data.message}</p>
                        </div>
                    </div>
                `;
                $alert.removeClass('bg-emerald-50 text-emerald-700 border-emerald-200')
                    .addClass('bg-red-50 text-red-700 border border-red-200 shadow-sm')
                    .html(errorHtml)
                    .fadeIn();

                $btn.prop('disabled', false).html('<i data-lucide="save"></i> Guardar Todos los Cambios');
                lucide.createIcons();
            }
        });
    });

    // 2. Leaflet Map Integration
    const $mapContainer = $('#ud-walker-zone-map');
    if ($mapContainer.length && typeof L !== 'undefined') {
        const initialLat = parseFloat($('input[name="lat"]').val()) || -12.046374;
        const initialLng = parseFloat($('input[name="lng"]').val()) || -77.042793;
        const initialRadius = parseFloat($('input[name="radius_km"]').val()) * 1000 || 1000;

        const map = L.map('ud-walker-zone-map').setView([initialLat, initialLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
        let circle = L.circle([initialLat, initialLng], {
            color: '#4f46e5',
            fillColor: '#4f46e5',
            fillOpacity: 0.2,
            radius: initialRadius
        }).addTo(map);

        // Update inputs on marker drag
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            $('input[name="lat"]').val(position.lat.toFixed(6));
            $('input[name="lng"]').val(position.lng.toFixed(6));
            circle.setLatLng(position);
        });

        // Update circle on radius change
        $('input[name="radius_km"]').on('input', function () {
            const radius = parseFloat($(this).val()) * 1000;
            if (!isNaN(radius)) {
                circle.setRadius(radius);
            }
        });

        // Click on map to move marker
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            $('input[name="lat"]').val(e.latlng.lat.toFixed(6));
            $('input[name="lng"]').val(e.latlng.lng.toFixed(6));
        });
    }
});
