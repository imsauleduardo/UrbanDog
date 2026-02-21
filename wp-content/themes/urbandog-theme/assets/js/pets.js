/**
 * Pet Management Handler
 */
jQuery(document).ready(function ($) {
    const $modal = $('#ud-pet-modal');
    const $form = $('#ud-pet-form');
    const $grid = $('#ud-pets-grid');
    const $addBtn = $('#ud-add-pet-btn');
    const $modalClose = $('.ud-modal-close');
    const $imageInput = $('#pet-image');
    const $imagePreview = $('#ud-pet-image-preview');

    // Open Modal (Add)
    $addBtn.on('click', function () {
        resetForm();
        $modal.find('h2').text('Agregar Nueva Mascota');
        $modal.addClass('active');
    });

    // Close Modal
    $modalClose.on('click', function () {
        $modal.removeClass('active');
    });

    $(window).on('click', function (e) {
        if ($(e.target).is($modal)) {
            $modal.removeClass('active');
        }
    });

    // Image Preview
    $imagePreview.on('click', function () {
        $imageInput.click();
    });

    $imageInput.on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $imagePreview.html(`<img src="${e.target.result}" alt="Preview">`);
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle Resize for grid consistency (optional enhancement)

    // Form submission (Add/Update)
    $form.on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const isUpdate = formData.get('pet_id') !== '';
        formData.append('action', isUpdate ? 'ud_update_pet' : 'ud_add_pet');
        formData.append('nonce', udPets.nonce);

        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: udPets.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $modal.removeClass('active');
                    location.reload(); // Simple approach for MVP
                } else {
                    alert(response.data.message || 'Error al guardar.');
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function () {
                alert('Error de conexión.');
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Edit Pet
    $(document).on('click', '.ud-edit-pet', function () {
        const petId = $(this).data('id');
        const petData = $(this).closest('.ud-pet-card').data('pet');

        resetForm();
        $modal.find('h2').text('Editar Mascota');

        // Fill form
        $('#pet-id').val(petId);
        $('#pet-name').val(petData.name);
        $('#pet-breed').val(petData.breed);
        $('#pet-age').val(petData.age);
        $('#pet-weight').val(petData.weight);
        $('#pet-temperament').val(petData.temperament);
        $('#pet-needs').val(petData.needs);

        if (petData.image) {
            $imagePreview.html(`<img src="${petData.image}" alt="Pet">`);
        }

        $modal.addClass('active');
    });

    // Delete Pet
    $(document).on('click', '.ud-delete-pet', function () {
        const petId = $(this).data('id');
        const petName = $(this).data('name');

        if (!confirm(`¿Estás seguro de que quieres eliminar a ${petName}?`)) {
            return;
        }

        $.ajax({
            url: udPets.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ud_delete_pet',
                pet_id: petId,
                nonce: udPets.nonce
            },
            success: function (response) {
                if (response.success) {
                    $(`.ud-pet-card[data-id="${petId}"]`).fadeOut();
                } else {
                    alert(response.data.message || 'Error al eliminar.');
                }
            }
        });
    });

    function resetForm() {
        $form[0].reset();
        $('#pet-id').val('');
        $imagePreview.html(`
            <div class="placeholder">
                <i data-lucide="camera"></i>
                <p>Haz clic para subir foto</p>
            </div>
        `);
        lucide.createIcons();
    }

    // Check for URL parameters to auto-open modal (e.g., after Guest M&G)
    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');
    const petId = urlParams.get('pet_id');

    if (action === 'edit_pet' && petId) {
        console.log('UrbanDog: Attempting to auto-open modal for pet ID:', petId);
        // Polling mechanism to wait for the element to be ready
        let attempts = 0;
        const maxAttempts = 10; // Try for 5 seconds

        const checkInterval = setInterval(() => {
            attempts++;
            const $editBtn = $(`.ud-edit-pet[data-id="${petId}"]`);
            console.log(`UrbanDog: Attempt ${attempts}. Button found:`, $editBtn.length > 0);

            if ($editBtn.length) {
                clearInterval(checkInterval);
                console.log('UrbanDog: Button found, clicking...');
                $editBtn.click();
                // Clean URL only after successful click
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.warn('UrbanDog: Could not find pet to edit after timeout.');
            }
        }, 500);
    }
});
