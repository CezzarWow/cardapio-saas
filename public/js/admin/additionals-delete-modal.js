/**
 * ADDITIONALS-DELETE-MODAL.JS - Modal de Exclusão
 * Dependências: additionals.js
 * 
 * Este módulo gerencia o modal de confirmação de exclusão.
 */

(function () {
    'use strict';

    // ==========================================
    // ABRIR MODAL DE EXCLUSÃO
    // ==========================================
    window.openDeleteModal = function (actionUrl, itemName) {
        const btn = document.getElementById('confirmDeleteBtn');
        if (btn) btn.href = actionUrl;

        const nameSpan = document.getElementById('deleteItemName');
        if (nameSpan) nameSpan.textContent = itemName;

        const modal = document.getElementById('deleteModal');
        if (modal) modal.style.display = 'flex';
    };

    // ==========================================
    // FECHAR MODAL DE EXCLUSÃO
    // ==========================================
    window.closeDeleteModal = function () {
        const modal = document.getElementById('deleteModal');
        if (modal) modal.style.display = 'none';
    };

})();
