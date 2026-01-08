/**
 * FEATURED-DRAGDROP.JS - Drag and Drop de Destaques
 * 
 * Gerencia drag and drop de cards de produtos.
 * Parte do namespace CardapioAdmin.Destaques.
 */

(function (CardapioAdmin) {
    'use strict';

    // Garante namespace
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {};
    CardapioAdmin.Destaques.draggedItem = null;

    CardapioAdmin.Destaques.dragStart = function (e) {
        CardapioAdmin.Destaques.draggedItem = e.currentTarget;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
        setTimeout(() => {
            CardapioAdmin.Destaques.draggedItem.classList.add('dragging');
        }, 0);
    };

    CardapioAdmin.Destaques.dragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        const target = e.currentTarget;
        const draggedItem = CardapioAdmin.Destaques.draggedItem;

        if (target && draggedItem && target !== draggedItem && target.classList.contains('cardapio-admin-destaques-product-card')) {
            document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
            target.classList.add('drag-over');
        }
    };

    CardapioAdmin.Destaques.drop = function (e) {
        e.stopPropagation();
        e.preventDefault();

        const target = e.currentTarget;
        const draggedItem = CardapioAdmin.Destaques.draggedItem;

        if (draggedItem && target && draggedItem !== target) {
            const sourceArea = draggedItem.closest('.cardapio-admin-destaques-products-grid');
            const targetArea = target.closest('.cardapio-admin-destaques-products-grid');

            if (sourceArea === targetArea) {
                const allCards = Array.from(targetArea.querySelectorAll('.cardapio-admin-destaques-product-card'));
                const draggedIndex = allCards.indexOf(draggedItem);
                const targetIndex = allCards.indexOf(target);

                if (draggedIndex < targetIndex) {
                    targetArea.insertBefore(draggedItem, target.nextSibling);
                } else {
                    targetArea.insertBefore(draggedItem, target);
                }

                CardapioAdmin.Destaques.updateProductOrder(targetArea);
            }
        }

        document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
        return false;
    };

    CardapioAdmin.Destaques.dragEnd = function (e) {
        const draggedItem = CardapioAdmin.Destaques.draggedItem;
        if (draggedItem) {
            draggedItem.classList.remove('dragging');
        }
        document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
        CardapioAdmin.Destaques.draggedItem = null;
    };

    CardapioAdmin.Destaques.updateProductOrder = function (container) {
        const cards = container.querySelectorAll('.cardapio-admin-destaques-product-card');

        cards.forEach((card, index) => {
            const productId = card.dataset.productId;
            const newValue = index;

            const inputs = document.querySelectorAll(`[data-order-input="${productId}"]`);
            inputs.forEach(input => {
                input.value = newValue;
            });
        });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
