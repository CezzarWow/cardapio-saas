<?php
/**
 * CROPPER-MODAL.PHP - Modal de Recorte de Imagem
 *
 * Partial reutilizável para modal de recorte de imagem.
 * Usa Cropper.js (carregado via CDN).
 *
 * Dependências JS: cropper-modal.js
 */
?>

<!-- Cropper.js CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<!-- Modal de Recorte -->
<div id="cropperModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; flex-direction: column;">
    <div style="background: white; padding: 20px; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; gap: 15px;">
        <h3 style="margin: 0; font-weight: 700; color: #1f2937;">Recortar Imagem</h3>
        
        <div style="width: 100%; height: 400px; background: #f3f4f6; overflow: hidden; border-radius: 8px;">
            <img id="imageToCrop" src="" style="max-width: 100%; display: block;">
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" id="cancelCrop" style="padding: 10px 20px; border: 1px solid #d1d5db; background: white; border-radius: 6px; font-weight: 600; cursor: pointer;">Cancelar</button>
            <button type="button" id="confirmCrop" style="padding: 10px 20px; border: none; background: #2563eb; color: white; border-radius: 6px; font-weight: 600; cursor: pointer;">Confirmar Recorte</button>
        </div>
    </div>
</div>
