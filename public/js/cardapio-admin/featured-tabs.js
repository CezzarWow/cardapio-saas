/**
 * FEATURED-TABS.JS - Abas e Highlight de Destaques
 * 
 * Gerencia troca de abas e toggle de highlight em produtos.
 * Parte do namespace CardapioAdmin.Destaques.
 */

(function (CardapioAdmin) {
    'use strict';

    // Garante namespace
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {};

    /**
     * Troca entre abas de categorias
     */
    CardapioAdmin.Destaques.switchTab = function (categoryName) {
        const tabs = document.querySelectorAll('.cardapio-admin-destaques-tab-btn');
        tabs.forEach(tab => {
            if (tab.dataset.categoryTab === categoryName) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });

        const contents = document.querySelectorAll('.cardapio-admin-destaques-tab-content');
        contents.forEach(content => {
            if (content.dataset.categoryContent === categoryName) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    /**
     * Adiciona/remove produto dos destaques
     */
    CardapioAdmin.Destaques.toggleHighlight = function (productId) {
        const cards = document.querySelectorAll(`[data-product-id="${productId}"]`);
        const inputs = document.querySelectorAll(`[data-featured-input="${productId}"]`);

        if (inputs.length === 0) return;

        const isCurrentlyFeatured = inputs[0].checked;

        inputs.forEach(input => {
            input.checked = !isCurrentlyFeatured;
        });

        cards.forEach(card => {
            const btn = card.querySelector('.cardapio-admin-destaques-highlight-btn');
            const star = card.querySelector('.cardapio-admin-destaques-star');

            if (!isCurrentlyFeatured) {
                card.classList.add('featured');
                if (btn) {
                    btn.classList.add('active');
                    btn.innerHTML = '<i data-lucide="x" style="width: 16px; height: 16px;"></i> Remover';
                }
                if (!star) {
                    const info = card.querySelector('.cardapio-admin-destaques-product-info');
                    if (info) {
                        const newStar = document.createElement('span');
                        newStar.className = 'cardapio-admin-destaques-star';
                        newStar.textContent = '⭐';
                        info.insertBefore(newStar, info.firstChild);
                    }
                }
            } else {
                card.classList.remove('featured');
                if (btn) {
                    btn.classList.remove('active');
                    btn.innerHTML = '<i data-lucide="star" style="width: 16px; height: 16px;"></i> Destacar';
                }
                if (star) star.remove();
            }
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
        this.refreshFeaturedTab();
    };

    /**
     * Atualiza a aba Destaques
     */
    CardapioAdmin.Destaques.refreshFeaturedTab = function () {
        const featuredContent = document.querySelector('[data-category-content="featured"]');
        if (!featuredContent) return;

        let grid = featuredContent.querySelector('.cardapio-admin-destaques-products-grid');
        const emptyMsg = featuredContent.querySelector('.cardapio-admin-destaques-empty');

        if (!grid) {
            grid = document.createElement('div');
            grid.className = 'cardapio-admin-destaques-products-grid';
            grid.dataset.sortableArea = 'featured';
            featuredContent.insertBefore(grid, emptyMsg);
        }

        const allFeaturedInputs = document.querySelectorAll('[data-featured-input]');

        allFeaturedInputs.forEach(input => {
            const productId = input.dataset.featuredInput;
            const isChecked = input.checked;
            const existsInFeaturedTab = grid.querySelector(`[data-product-id="${productId}"]`);

            if (isChecked && !existsInFeaturedTab) {
                const sourceCard = document.querySelector(`[data-category-content]:not([data-category-content="featured"]) [data-product-id="${productId}"]`);
                if (sourceCard) {
                    const clonedCard = sourceCard.cloneNode(true);
                    const btn = clonedCard.querySelector('.cardapio-admin-destaques-highlight-btn');
                    if (btn) {
                        btn.classList.add('active');
                        btn.innerHTML = '<i data-lucide="x" style="width: 16px; height: 16px;"></i> Remover';
                    }
                    const info = clonedCard.querySelector('.cardapio-admin-destaques-product-info');
                    if (info && !info.querySelector('.cardapio-admin-destaques-star')) {
                        const star = document.createElement('span');
                        star.className = 'cardapio-admin-destaques-star';
                        star.textContent = '⭐';
                        info.insertBefore(star, info.firstChild);
                    }
                    clonedCard.querySelectorAll('input').forEach(inp => inp.remove());
                    grid.appendChild(clonedCard);
                }
            } else if (!isChecked && existsInFeaturedTab) {
                existsInFeaturedTab.remove();
            }
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();

        const remainingCards = grid.querySelectorAll('.cardapio-admin-destaques-product-card').length;
        if (remainingCards === 0) {
            grid.style.display = 'none';
            if (emptyMsg) {
                emptyMsg.style.display = 'block';
            } else {
                grid.insertAdjacentHTML('afterend', '<p class="cardapio-admin-destaques-empty">Nenhum produto em destaque. Use as outras abas para adicionar.</p>');
            }
        } else {
            grid.style.display = 'grid';
            if (emptyMsg) emptyMsg.style.display = 'none';
        }
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
