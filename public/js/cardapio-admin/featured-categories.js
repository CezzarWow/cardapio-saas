/**
 * FEATURED-CATEGORIES.JS - Ordenação de Categorias
 * 
 * Gerencia ordenação de categorias na aba Destaques.
 * Parte do namespace CardapioAdmin.Destaques.
 */

(function (CardapioAdmin) {
    'use strict';

    // Garante namespace
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {};

    /**
     * Move categoria para cima ou para baixo
     */
    CardapioAdmin.Destaques.moveCategory = function (categoryId, direction) {
        const list = document.getElementById('categoryList');
        if (!list) return;

        const currentRow = list.querySelector(`[data-category-id="${categoryId}"]`);
        if (!currentRow) return;

        const rows = Array.from(list.querySelectorAll('.cardapio-admin-destaques-category-row'));
        const currentIndex = rows.indexOf(currentRow);

        if (direction === 'up' && currentIndex > 0) {
            const prevRow = rows[currentIndex - 1];
            list.insertBefore(currentRow, prevRow);
        } else if (direction === 'down' && currentIndex < rows.length - 1) {
            const nextRow = rows[currentIndex + 1];
            list.insertBefore(nextRow, currentRow);
        }

        this.updateCategoryOrder();
    };

    /**
     * Atualiza os inputs hidden com a nova ordem
     */
    CardapioAdmin.Destaques.updateCategoryOrder = function () {
        const list = document.getElementById('categoryList');
        if (!list) return;

        const rows = Array.from(list.querySelectorAll('.cardapio-admin-destaques-category-row'));

        rows.forEach((row, index) => {
            const input = row.querySelector('[data-order-input]');
            if (input) input.value = index;

            const btnUp = row.querySelector('.cardapio-admin-destaques-arrow-btn:first-child');
            const btnDown = row.querySelector('.cardapio-admin-destaques-arrow-btn:last-child');

            if (btnUp) btnUp.disabled = (index === 0);
            if (btnDown) btnDown.disabled = (index === rows.length - 1);
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
