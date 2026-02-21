/**
 * UrbanDog Payment System - Frontend JavaScript
 * Handles AJAX operations for payment proof uploads, confirmations, and rejections
 */

(function ($) {
    'use strict';

    // Payment System Object
    const UrbanDogPayments = {

        /**
         * Initialize payment system
         */
        init: function () {
            this.bindEvents();
            this.initializeImagePreviews();
        },

        /**
         * Bind event listeners
         */
        bindEvents: function () {
            // Owner: Upload payment proof
            $(document).on('submit', '.ud-payment-form', this.handlePaymentSubmit.bind(this));

            // Admin: Confirm payment
            $(document).on('click', '.ud-confirm-payment-btn', this.handleConfirmPayment.bind(this));

            // Admin: Reject payment
            $(document).on('click', '.ud-reject-payment-btn', this.handleRejectPayment.bind(this));

            // Admin: Mark paid to walker
            $(document).on('click', '.ud-mark-paid-btn', this.handleMarkPaid.bind(this));

            // Image preview
            $(document).on('change', '.ud-proof-image-input', this.handleImagePreview.bind(this));

            // View proof image modal
            $(document).on('click', '.ud-view-proof-btn', this.handleViewProof.bind(this));
        },

        /**
         * Initialize image preview functionality
         */
        initializeImagePreviews: function () {
            $('.ud-proof-image-input').each(function () {
                const $input = $(this);
                const $preview = $input.closest('.ud-payment-form').find('.ud-image-preview');

                if ($preview.length) {
                    $preview.hide();
                }
            });
        },

        /**
         * Handle payment proof submission (Owner)
         */
        handlePaymentSubmit: function (e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const $submitBtn = $form.find('button[type="submit"]');
            const transactionId = $form.data('transaction-id');
            const formData = new FormData($form[0]);

            // Validate form
            if (!this.validatePaymentForm($form)) {
                return;
            }

            // Disable submit button
            $submitBtn.prop('disabled', true).html('<i data-lucide="loader-2" class="animate-spin"></i> Enviando...');

            // Prepare AJAX data
            formData.append('action', 'ud_upload_payment_proof');
            formData.append('nonce', udPayments.nonce);
            formData.append('transaction_id', transactionId);

            // Send AJAX request
            $.ajax({
                url: udPayments.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.showNotification('success', response.data.message || 'Comprobante enviado exitosamente');

                        // Reload page after 1.5 seconds
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showNotification('error', response.data.message || 'Error al enviar el comprobante');
                        $submitBtn.prop('disabled', false).html('Enviar Comprobante');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', error);
                    this.showNotification('error', 'Error de conexión. Por favor, intenta de nuevo.');
                    $submitBtn.prop('disabled', false).html('Enviar Comprobante');
                }
            });
        },

        /**
         * Validate payment form
         */
        validatePaymentForm: function ($form) {
            const method = $form.find('input[name="method"]:checked').val();
            const reference = $form.find('input[name="reference"]').val().trim();
            const proofImage = $form.find('input[name="proof_image"]')[0].files[0];

            if (!method) {
                this.showNotification('error', 'Por favor, selecciona un método de pago (Yape o Plin)');
                return false;
            }

            if (!reference) {
                this.showNotification('error', 'Por favor, ingresa el número de operación');
                return false;
            }

            if (!proofImage) {
                this.showNotification('error', 'Por favor, sube una imagen del comprobante');
                return false;
            }

            // Validate image size (max 5MB)
            if (proofImage.size > 5 * 1024 * 1024) {
                this.showNotification('error', 'La imagen es muy grande. Máximo 5MB.');
                return false;
            }

            // Validate image type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!validTypes.includes(proofImage.type)) {
                this.showNotification('error', 'Formato de imagen no válido. Usa JPG, PNG o WEBP.');
                return false;
            }

            return true;
        },

        /**
         * Handle image preview
         */
        handleImagePreview: function (e) {
            const $input = $(e.currentTarget);
            const file = e.target.files[0];
            const $preview = $input.closest('.ud-payment-form').find('.ud-image-preview');
            const $previewImg = $preview.find('img');

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    $previewImg.attr('src', e.target.result);
                    $preview.fadeIn();
                };

                reader.readAsDataURL(file);
            } else {
                $preview.fadeOut();
            }
        },

        /**
         * Handle confirm payment (Admin)
         */
        handleConfirmPayment: function (e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const transactionId = $btn.data('transaction-id');
            const notes = $btn.closest('.ud-payment-actions').find('.ud-admin-notes').val() || '';

            if (!confirm('¿Estás seguro de confirmar este pago? Se enviará un recibo al cliente.')) {
                return;
            }

            // Disable button
            $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="animate-spin"></i> Confirmando...');

            // Send AJAX request
            $.ajax({
                url: udPayments.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ud_confirm_payment',
                    nonce: udPayments.adminNonce,
                    transaction_id: transactionId,
                    notes: notes
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification('success', response.data.message || 'Pago confirmado exitosamente');

                        // Remove transaction row or reload
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showNotification('error', response.data.message || 'Error al confirmar el pago');
                        $btn.prop('disabled', false).html('Confirmar Pago');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', error);
                    this.showNotification('error', 'Error de conexión. Por favor, intenta de nuevo.');
                    $btn.prop('disabled', false).html('Confirmar Pago');
                }
            });
        },

        /**
         * Handle reject payment (Admin)
         */
        handleRejectPayment: function (e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const transactionId = $btn.data('transaction-id');
            const reason = $btn.closest('.ud-payment-actions').find('.ud-rejection-reason').val() || '';

            if (!reason.trim()) {
                this.showNotification('error', 'Por favor, ingresa el motivo del rechazo');
                return;
            }

            if (!confirm('¿Estás seguro de rechazar este pago? Se notificará al cliente.')) {
                return;
            }

            // Disable button
            $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="animate-spin"></i> Rechazando...');

            // Send AJAX request
            $.ajax({
                url: udPayments.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ud_reject_payment',
                    nonce: udPayments.adminNonce,
                    transaction_id: transactionId,
                    reason: reason
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification('success', response.data.message || 'Pago rechazado');

                        // Remove transaction row or reload
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showNotification('error', response.data.message || 'Error al rechazar el pago');
                        $btn.prop('disabled', false).html('Rechazar Pago');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', error);
                    this.showNotification('error', 'Error de conexión. Por favor, intenta de nuevo.');
                    $btn.prop('disabled', false).html('Rechazar Pago');
                }
            });
        },

        /**
         * Handle mark paid to walker (Admin)
         */
        handleMarkPaid: function (e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const transactionId = $btn.data('transaction-id');

            if (!confirm('¿Confirmas que ya enviaste el pago al paseador?')) {
                return;
            }

            // Disable button
            $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="animate-spin"></i> Marcando...');

            // Send AJAX request
            $.ajax({
                url: udPayments.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ud_mark_paid_to_walker',
                    nonce: udPayments.adminNonce,
                    transaction_id: transactionId
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification('success', response.data.message || 'Marcado como pagado');

                        // Reload page
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showNotification('error', response.data.message || 'Error al marcar como pagado');
                        $btn.prop('disabled', false).html('Marcar como Pagado');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', error);
                    this.showNotification('error', 'Error de conexión. Por favor, intenta de nuevo.');
                    $btn.prop('disabled', false).html('Marcar como Pagado');
                }
            });
        },

        /**
         * Handle view proof image
         */
        handleViewProof: function (e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const imageUrl = $btn.data('image-url');

            if (!imageUrl) {
                this.showNotification('error', 'No hay imagen de comprobante');
                return;
            }

            // Open in modal or new tab
            window.open(imageUrl, '_blank');
        },

        /**
         * Show notification
         */
        showNotification: function (type, message) {
            // Remove existing notifications
            $('.ud-notification').remove();

            const iconMap = {
                success: 'check-circle',
                error: 'alert-circle',
                warning: 'alert-triangle',
                info: 'info'
            };

            const colorMap = {
                success: 'bg-emerald-500',
                error: 'bg-red-500',
                warning: 'bg-amber-500',
                info: 'bg-blue-500'
            };

            const icon = iconMap[type] || 'info';
            const color = colorMap[type] || 'bg-blue-500';

            const $notification = $(`
                <div class="ud-notification fixed top-4 right-4 ${color} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 z-50 animate-slide-in">
                    <i data-lucide="${icon}"></i>
                    <span>${message}</span>
                </div>
            `);

            $('body').append($notification);

            // Initialize lucide icon
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Auto-remove after 5 seconds
            setTimeout(() => {
                $notification.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        UrbanDogPayments.init();
    });

    // Expose to global scope
    window.UrbanDogPayments = UrbanDogPayments;

})(jQuery);
