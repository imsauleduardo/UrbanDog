document.addEventListener('DOMContentLoaded', function () {
    console.log('Search JS Initialized');

    const resultsContainer = document.getElementById('walker-results');
    const labelLocation = document.getElementById('label-location');
    let map;
    let markers = [];

    if (!resultsContainer) {
        console.error('ERROR: resultsContainer (#walker-results) not found!');
    }

    // Initialize Map
    try {
        if (document.getElementById('search-map') && typeof L !== 'undefined') {
            if (map) {
                map.remove();
            }
            map = L.map('search-map', {
                zoomControl: false
            }).setView([-12.046374, -77.042793], 13);

            L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.control.zoom({ position: 'bottomright' }).addTo(map);
            console.log('Map initialized');
        } else {
            console.warn('Map element or Leaflet missing');
        }
    } catch (e) {
        console.error('Map init error:', e);
    }

    // Global State for Filters
    let activeFilters = {
        service_type: 'walk',
        pet_size: 'mediano',
        pet_count: 1,
        dates: []
    };

    // Function to perform search
    async function doSearch() {
        console.log('doSearch starting with filters:', activeFilters);
        const urlParams = new URLSearchParams(window.location.search);
        const distrito = urlParams.get('distrito') || urlParams.get('location') || '';

        if (labelLocation) labelLocation.textContent = distrito || 'Distrito';

        if (typeof ud_ajax === 'undefined') {
            console.error('ERROR: ud_ajax is not defined!');
            renderEmpty('Error interno: Configuración AJAX faltante');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'ud_search_walkers');
            formData.append('zone', distrito);
            formData.append('nonce', ud_ajax.nonce);
            formData.append('service_type', activeFilters.service_type);
            formData.append('pet_size', activeFilters.pet_size);

            console.log('Fetching with filters:', distrito, activeFilters);
            const response = await fetch(ud_ajax.url, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();
            console.log('DATA RECEIVED:', data);

            if (data.success && data.data && data.data.length > 0) {
                renderResults(data.data);
                updateMap(data.data);
            } else {
                console.log('No results found for:', distrito);
                renderEmpty();
            }
        } catch (error) {
            console.error('MAJOR Search error:', error);
            renderEmpty('Error al cargar resultados');
        }
    }

    function renderResults(walkers) {
        if (!resultsContainer) return;
        console.log('Rendering results:', walkers.length);

        resultsContainer.innerHTML = walkers.map(walker => `
                <div class="walker-card" data-id="${walker.id}">
                    <img src="${walker.image}" class="walker-photo" alt="${walker.name}">
                    <div class="walker-info">
                        <div class="walker-top">
                            <h3 class="walker-name">${walker.name}</h3>
                            <div class="walker-price">S/ ${walker.price_30 || '25'}<span>/30 min</span></div>
                        </div>
                        <div class="walker-meta">
                            <div class="walker-rating" style="display: flex; align-items: center; gap: 4px; color: #f59e0b;">
                                <i data-lucide="star" style="width: 14px; height: 14px; fill: currentColor;"></i>
                                ${walker.rating || '5.0'}
                            </div>
                            <div class="walker-reviews" style="color: #64748b; font-size: 0.875rem;">(${walker.reviews || '0'} reseñas)</div>
                            ${(walker.badges || []).map(badge => `<span class="walker-badge" style="background: #f0fdf4; color: #16a34a; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-left: 8px;">${badge}</span>`).join('')}
                        </div>
                        <p class="walker-desc" style="color: #475569; font-size: 0.875rem; margin-top: 8px;">Paseador certificado apasionado por los animales. Conozco muy bien la zona de ${walker.zone || 'Lima'} y me encanta jugar con perros.</p>
                    </div>
                </div>
            `).join('');

        safeRefreshIcons();
    }

    function renderEmpty(msg = 'No encontramos paseadores') {
        if (!resultsContainer) return;
        resultsContainer.innerHTML = `
            <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                <i data-lucide="search-x" style="width: 4rem; height: 4rem; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">${msg}</h3>
                <p>Intenta buscando en otro distrito o ajustando tus filtros.</p>
            </div>
        `;
        safeRefreshIcons();
    }

    function safeRefreshIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
            console.log('Icons refreshed');
        }
    }

    function updateMap(walkers) {
        if (!map) return;
        console.log('Updating map with:', walkers.length, 'walkers');

        markers.forEach(m => map.removeLayer(m));
        markers = [];

        const group = L.featureGroup();

        walkers.forEach(walker => {
            const lat = parseFloat(walker.lat) || -12.046374 + (Math.random() - 0.5) * 0.05;
            const lng = parseFloat(walker.lng) || -77.042793 + (Math.random() - 0.5) * 0.05;

            const marker = L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background: white; border: 2px solid #10b981; border-radius: 999px; padding: 2px 8px; font-weight: 800; color: #10b981; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); white-space: nowrap;">S/ ${walker.price_30 || '25'}</div>`,
                iconSize: [60, 30],
                iconAnchor: [30, 15]
            });

            const m = L.marker([lat, lng], { icon: marker }).addTo(map);
            m.bindPopup(`<strong>${walker.name}</strong><br>S/ ${walker.price_30 || '25'} por paseo`);
            markers.push(m);
            group.addLayer(m);
        });

        if (walkers.length > 0) {
            map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }
    }

    // --- Search Page Districts Modal ---
    const modalInput = document.getElementById('input-location');
    const modalError = document.getElementById('modal-district-error');
    const applyLocationBtn = document.getElementById('apply-location');

    // Use global validator and autocomplete from main.js if available
    if (modalInput && modalError) {
        if (window.udInitAutocomplete) {
            window.udInitAutocomplete('#input-location', '#modal-autocomplete-results', '#modal-district-error', '#apply-location');
        }
    }

    if (applyLocationBtn && modalInput) {
        applyLocationBtn.addEventListener('click', function () {
            if (window.udValidateDistrict) {
                const isValid = window.udValidateDistrict(modalInput, modalError, applyLocationBtn);
                if (!isValid) {
                    modalError.style.display = 'flex';
                    if (applyLocationBtn) applyLocationBtn.classList.add('btn-disabled');
                    return;
                }
            }

            const newLoc = modalInput.value.trim();
            if (newLoc) {
                const url = new URL(window.location.href);
                url.searchParams.set('distrito', newLoc);
                window.history.pushState({}, '', url);
                doSearch();
                closeModal();
            }
        });
    }

    // Modal Interactions: Service
    document.querySelectorAll('.service-option').forEach(opt => {
        opt.addEventListener('click', function () {
            if (this.classList.contains('disabled')) return;
            document.querySelectorAll('.service-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            activeFilters.service_type = this.querySelector('input').value;
            doSearch();
        });
    });

    // Modal Interactions: Dates
    document.querySelectorAll('.day-bubble').forEach(btn => {
        btn.addEventListener('click', function () {
            this.classList.toggle('active');
            const day = this.textContent;
            if (this.classList.contains('active')) {
                activeFilters.dates.push(day);
            } else {
                activeFilters.dates = activeFilters.dates.filter(d => d !== day);
            }
            doSearch();
        });
    });

    // Modal Interactions: Pet Size
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Map text to internal value
            const text = this.textContent.toLowerCase();
            if (text.includes('0-7kg')) activeFilters.pet_size = 'pequeño';
            else if (text.includes('25kg+')) activeFilters.pet_size = 'gigante';
            else if (text.includes('18-45kg')) activeFilters.pet_size = 'grande';
            else activeFilters.pet_size = 'mediano';

            doSearch();
        });
    });

    // Modal Interactions: Counter
    document.querySelectorAll('.cnt-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const valEl = this.parentElement.querySelector('.cnt-val');
            let val = parseInt(valEl.textContent);
            if (this.textContent === '+') val++;
            else if (val > 1) val--;
            valEl.textContent = val;
            activeFilters.pet_count = val;
            doSearch();
        });
    });

    // Modal Logic
    window.openModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'flex';
    };

    window.closeModal = function (e) {
        if (!e) {
            document.querySelectorAll('.ud-modal-overlay').forEach(m => m.style.display = 'none');
            return;
        }
        if (e.target.classList.contains('ud-modal-overlay') || e.target.closest('.modal-close')) {
            const overlay = e.target.closest('.ud-modal-overlay');
            if (overlay) overlay.style.display = 'none';
        }
    };

    // Initial search
    doSearch();
});
