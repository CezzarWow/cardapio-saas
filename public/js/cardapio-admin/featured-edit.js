/**
 * FEATURED-EDIT.JS - Modo de Edição de Destaques
 * 
 * Gerencia ativação/cancelamento/save do modo edição.
 * Parte do namespace CardapioAdmin.Destaques.
 */

(function (CardapioAdmin) {
    'use strict';

    // Garante namespace
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {};

    /**
     * Ativa modo de edição para produtos
     */
    CardapioAdmin.Destaques.enableEditMode = function () {
        const container = document.querySelector('.cardapio-admin-destaques-content-wrapper');
        const editBtn = document.querySelector('.cardapio-admin-btn-edit');
        const saveGroup = document.querySelector('.cardapio-admin-save-group');
        const viewHint = document.querySelector('.view-hint');
        const editHint = document.querySelector('.edit-hint');

        if (container) {
            container.classList.remove('disabled-overlay');
            container.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(card => {
                card.setAttribute('draggable', 'true');
                const handle = card.querySelector('.cardapio-admin-destaques-drag-handle');
                if (handle) handle.style.display = 'block';
            });
        }

        if (editBtn) editBtn.style.display = 'none';
        if (saveGroup) saveGroup.style.display = 'flex';
        if (viewHint) viewHint.style.display = 'none';
        if (editHint) editHint.style.display = 'inline';

        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    /**
     * Cancela edição
     */
    CardapioAdmin.Destaques.cancelEditMode = function () {
        window.location.reload();
    };

    /**
     * Salva alterações de destaques (ordem e seleção)
     */
    CardapioAdmin.Destaques.saveDestaques = function () {
        const form = document.getElementById('formCardapio');
        if (form) form.submit();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
