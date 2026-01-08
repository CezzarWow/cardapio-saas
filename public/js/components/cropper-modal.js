/**
 * CROPPER-MODAL.JS - Modal de Recorte de Imagem
 * 
 * Componente reutilizável para recorte de imagem usando Cropper.js
 * Usado em: stock/edit.php, stock/create.php
 * 
 * DEPENDÊNCIAS:
 * - Cropper.js (CDN ou local)
 * - cropper.min.css
 */

(function () {
    'use strict';

    let cropper;
    let originalFile = null;
    let isInternalChange = false;

    // ==========================================
    // ABRIR MODAL DE CROPPER
    // ==========================================
    window.openCropper = function (source) {
        const modal = document.getElementById('cropperModal');
        const image = document.getElementById('imageToCrop');

        if (!source || !modal || !image) return;

        // Se for File object ou Blob
        if (source instanceof Blob || source instanceof File) {
            const reader = new FileReader();
            reader.onload = function (evt) {
                image.src = evt.target.result;
                startCropper(modal, image);
            };
            reader.readAsDataURL(source);
        } else if (typeof source === 'string') {
            // Se for URL
            image.src = source;
            image.crossOrigin = 'anonymous';
            startCropper(modal, image);
        }
    };

    // ==========================================
    // INICIALIZAR CROPPER.JS
    // ==========================================
    function startCropper(modal, image) {
        modal.style.display = 'flex';

        // Destrói anterior se existir
        if (cropper) cropper.destroy();

        // Inicia Cropper
        cropper = new Cropper(image, {
            aspectRatio: 1, // Quadrado
            viewMode: 0,
            dragMode: 'move',
            autoCropArea: 0.8,
            guides: true,
            background: true,
            responsive: true
        });
    }

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================
    document.addEventListener('DOMContentLoaded', function () {
        const imageInput = document.getElementById('imageInput');
        const modal = document.getElementById('cropperModal');
        const cancelBtn = document.getElementById('cancelCrop');
        const confirmBtn = document.getElementById('confirmCrop');

        if (!imageInput || !modal) return;

        // Evento de seleção de arquivo
        imageInput.addEventListener('change', function (e) {
            if (isInternalChange) {
                isInternalChange = false;
                return;
            }

            const files = e.target.files;
            if (files && files.length > 0) {
                originalFile = files[0];

                // Valida se é imagem
                if (!originalFile.type.startsWith('image/')) return;

                openCropper(originalFile);

                // Limpa o input temporariamente para permitir cancelar
                this.value = '';
            }
        });

        // Botão Cancelar
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                modal.style.display = 'none';
                if (cropper) cropper.destroy();
            });
        }

        // Botão Confirmar
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (!cropper) return;

                // Obtém canvas cortado
                const canvas = cropper.getCroppedCanvas({
                    width: 600,
                    height: 600,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                canvas.toBlob(function (blob) {
                    // Cria novo arquivo (PNG para preservar transparência)
                    const file = new File([blob], "cropped_image.png", { type: "image/png" });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);

                    isInternalChange = true;
                    imageInput.files = dataTransfer.files;

                    // Mostra preview ou esconde a opção de ícone
                    const optionDiv = document.getElementById('iconAsPhotoOption');
                    if (optionDiv) {
                        optionDiv.style.display = 'none';
                        const checkbox = document.getElementById('iconAsPhotoCheckbox');
                        if (checkbox) checkbox.checked = false;
                    }

                    modal.style.display = 'none';
                    if (cropper) cropper.destroy();

                }, 'image/png');
            });
        }
    });

})();
