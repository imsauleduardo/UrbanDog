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
        pet_size: [], // No default selection
        dog_count: 0, // Default to 0
        puppy_count: 0, // Default to 0
        dates: [],
        frequency: 'once',
        start_date: new Date().toISOString().split('T')[0],
        time_slots: ['midday'],
        min_price: 1,
        max_price: 250
    };

    // --- Pets Modal Logic ---
    const petsModal = document.getElementById('modal-pets');
    if (petsModal) {
        // Multi-select Size Cards
        petsModal.querySelectorAll('.size-card').forEach(card => {
            card.addEventListener('click', function () {
                this.classList.toggle('active');
                const size = this.dataset.size;
                if (this.classList.contains('active')) {
                    if (!activeFilters.pet_size.includes(size)) activeFilters.pet_size.push(size);
                } else {
                    activeFilters.pet_size = activeFilters.pet_size.filter(s => s !== size);
                }
                console.log('Pet sizes changed:', activeFilters.pet_size);
                updateFilterLabels();
            });
        });

        // Circle Counter Buttons (Dogs/Puppies)
        petsModal.querySelectorAll('.cnt-btn-circle').forEach(btn => {
            btn.addEventListener('click', function () {
                const type = this.dataset.type; // 'dog' or 'puppy'
                const action = this.dataset.action; // 'plus' or 'minus'
                const valEl = document.getElementById(`${type}-count-val`);
                let val = parseInt(valEl.textContent);

                if (action === 'plus') {
                    val++;
                } else if (action === 'minus') {
                    if (val > 0) val--;
                }

                valEl.textContent = val;

                if (type === 'dog') activeFilters.dog_count = val;
                else activeFilters.puppy_count = val;

                console.log(`${type} count changed:`, val);
                updateFilterLabels();
            });
        });

        // Apply/Save Button
        const savePetsBtn = petsModal.querySelector('.modal-footer .btn-primary');
        if (savePetsBtn) {
            savePetsBtn.addEventListener('click', function () {
                console.log('Saving pet filters:', activeFilters);
                doSearch();
                closeModal();
            });
        }
    }

    // --- Dates Modal Logic ---
    const datesModal = document.getElementById('modal-dates');
    if (datesModal) {
        // Frequency Switcher
        datesModal.querySelectorAll('#frequency-selector .segment').forEach(btn => {
            btn.addEventListener('click', function () {
                datesModal.querySelectorAll('#frequency-selector .segment').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeFilters.frequency = this.dataset.value;
                console.log('Frequency changed:', activeFilters.frequency);
                updateFilterLabels();
            });
        });

        // Day Selector (Multi-select)
        datesModal.querySelectorAll('#day-selector .day-segment').forEach(btn => {
            btn.addEventListener('click', function () {
                this.classList.toggle('active');
                const day = this.dataset.day;
                if (this.classList.contains('active')) {
                    if (!activeFilters.dates.includes(day)) activeFilters.dates.push(day);
                } else {
                    activeFilters.dates = activeFilters.dates.filter(d => d !== day);
                }
                console.log('Dates changed:', activeFilters.dates);
                updateFilterLabels();
            });
        });

        // Start Date
        const startDateInput = document.getElementById('start-date');
        if (startDateInput) {
            startDateInput.addEventListener('change', function () {
                activeFilters.start_date = this.value;
                console.log('Start date changed:', activeFilters.start_date);
            });
        }

        // Time Slots (Multi-select)
        datesModal.querySelectorAll('#time-selector .segment').forEach(btn => {
            btn.addEventListener('click', function () {
                this.classList.toggle('active');
                const slot = this.dataset.value;
                if (this.classList.contains('active')) {
                    if (!activeFilters.time_slots.includes(slot)) activeFilters.time_slots.push(slot);
                } else {
                    activeFilters.time_slots = activeFilters.time_slots.filter(s => s !== slot);
                }
                console.log('Time slots changed:', activeFilters.time_slots);
                updateFilterLabels();
            });
        });

        // Apply Button
        const applyDatesBtn = document.getElementById('apply-dates-btn');
        if (applyDatesBtn) {
            applyDatesBtn.addEventListener('click', function () {
                console.log('Applying all date filters:', activeFilters);
                doSearch();
                closeModal();
            });
        }
    }

    // --- Search Page Districts Modal ---
    const modalInput = document.getElementById('input-location');
    const modalError = document.getElementById('modal-district-error');
    const applyLocationBtn = document.getElementById('apply-location');

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
            updateFilterLabels();
            doSearch();
        });
    });

    // --- More Filters Modal (Price Slider & Reset) ---
    const filtersModal = document.getElementById('modal-filters');
    if (filtersModal) {
        const priceMax = document.getElementById('price-max');
        const priceMaxLabel = document.getElementById('price-max-label');
        const sliderTrack = document.getElementById('slider-track');

        function updatePriceSlider() {
            if (!priceMax) return;
            const maxVal = parseInt(priceMax.value);

            // Update Labels
            if (priceMaxLabel) priceMaxLabel.textContent = `S/ ${maxVal}`;

            // Update Track (Emerald fill from left)
            if (sliderTrack) {
                const percent = ((maxVal - priceMax.min) / (priceMax.max - priceMax.min)) * 100;
                sliderTrack.style.width = percent + '%';
            }

            // Update State
            activeFilters.max_price = maxVal;
            activeFilters.min_price = 1;
            updateFilterCount();
        }

        if (priceMax) {
            priceMax.addEventListener('input', updatePriceSlider);
            // Initialize Slider
            updatePriceSlider();
        }

        // Reset All Filters
        const resetBtn = document.getElementById('reset-all-filters');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                // Reset State
                activeFilters = {
                    service_type: 'walk',
                    pet_size: [],
                    dog_count: 0,
                    puppy_count: 0,
                    dates: [],
                    frequency: 'once',
                    start_date: new Date().toISOString().split('T')[0],
                    time_slots: ['midday'],
                    min_price: 1,
                    max_price: 250
                };

                // Sync UI: Pets
                const petsModal = document.getElementById('modal-pets');
                if (petsModal) {
                    petsModal.querySelectorAll('.size-card').forEach(c => c.classList.remove('active'));
                    const dogVal = document.getElementById('dog-count-val');
                    const puppyVal = document.getElementById('puppy-count-val');
                    if (dogVal) dogVal.textContent = '0';
                    if (puppyVal) puppyVal.textContent = '0';
                }

                // Sync UI: Dates
                const datesModal = document.getElementById('modal-dates');
                if (datesModal) {
                    datesModal.querySelectorAll('.day-segment, .segment').forEach(s => s.classList.remove('active'));
                    datesModal.querySelector('.segment[data-value="once"]')?.classList.add('active');
                    datesModal.querySelector('.segment[data-value="midday"]')?.classList.add('active');
                    const startDate = document.getElementById('start-date');
                    if (startDate) startDate.value = activeFilters.start_date;
                }

                // Sync UI: Price Slider
                if (priceMax) {
                    priceMax.value = 250;
                    updatePriceSlider();
                }

                // Refresh and Search
                updateFilterCount();
                doSearch();
                console.log('Filters reset complete');
            });
        }

        // Apply Button
        const applyFiltersBtn = document.getElementById('apply-filters-btn');
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', function () {
                doSearch();
                closeModal();
            });
        }
    }

    function updateFilterCount() {
        let count = 0;
        if (activeFilters.pet_size.length > 0) count++;
        if (activeFilters.dog_count > 0 || activeFilters.puppy_count > 0) count++;
        if (activeFilters.dates.length > 0) count++;
        if (activeFilters.min_price > 1 || activeFilters.max_price < 250) count++;
        // service_type is mandatory, so we don't count it as an "active filter" for the badge usually

        const badge = document.getElementById('filter-count-badge');
        if (badge) badge.textContent = `(${count})`;
    }

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

            // Multi-select for pet size
            formData.append('pet_size', activeFilters.pet_size.join(','));
            formData.append('dog_count', activeFilters.dog_count);
            formData.append('puppy_count', activeFilters.puppy_count);
            formData.append('min_price', activeFilters.min_price);
            formData.append('max_price', activeFilters.max_price);

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

    // --- Synchronization Logic ---
    function updateFilterLabels() {
        // Service
        const serviceLabel = document.getElementById('label-service');
        if (serviceLabel) {
            const labels = {
                'walk': 'Paseo de Perros',
                'boarding': 'Alojamiento',
                'visit': 'Cuidado en Casa'
            };
            serviceLabel.textContent = labels[activeFilters.service_type] || 'Servicio';
        }

        // Dates
        const datesLabel = document.getElementById('label-dates');
        if (datesLabel) {
            if (activeFilters.dates.length > 0) {
                datesLabel.textContent = `Fechas (${activeFilters.dates.length})`;
            } else {
                datesLabel.textContent = 'Fechas';
            }
        }

        // Pets
        const petsLabel = document.getElementById('label-pets');
        if (petsLabel) {
            const total = activeFilters.dog_count + activeFilters.puppy_count;
            if (total > 0) {
                petsLabel.textContent = `Mascotas (${total})`;
            } else {
                petsLabel.textContent = 'Mascotas';
            }
        }
    }

    // Modal Logic & State Sync
    window.openModal = function (id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        // Sync Modal UI with activeFilters state before showing
        if (id === 'modal-pets') {
            modal.querySelectorAll('.size-card').forEach(card => {
                card.classList.toggle('active', activeFilters.pet_size.includes(card.dataset.size));
            });
            const dogVal = document.getElementById('dog-count-val');
            const puppyVal = document.getElementById('puppy-count-val');
            if (dogVal) dogVal.textContent = activeFilters.dog_count;
            if (puppyVal) puppyVal.textContent = activeFilters.puppy_count;
        }

        if (id === 'modal-dates') {
            modal.querySelectorAll('#day-selector .day-segment').forEach(btn => {
                btn.classList.toggle('active', activeFilters.dates.includes(btn.dataset.day));
            });
            modal.querySelectorAll('#frequency-selector .segment').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.value === activeFilters.frequency);
            });
            modal.querySelectorAll('#time-selector .segment').forEach(btn => {
                btn.classList.toggle('active', activeFilters.time_slots.includes(btn.dataset.value));
            });
            const startDate = document.getElementById('start-date');
            if (startDate) startDate.value = activeFilters.start_date;
        }

        if (id === 'modal-service') {
            modal.querySelectorAll('.service-option').forEach(opt => {
                const radio = opt.querySelector('input');
                if (radio) opt.classList.toggle('active', radio.value === activeFilters.service_type);
            });
        }

        modal.style.display = 'flex';
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

    // Initial search and sync
    updateFilterLabels();
    doSearch();
});
