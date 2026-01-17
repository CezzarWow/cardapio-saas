/**
 * CATALOG RENDERER
 * Responsável por gerar HTML dos produtos no cliente (reproduzindo products.php)
 */
(function () {
    'use strict';

    const CatalogRenderer = {

        /**
         * Renderiza uma categoria inteira
         *Filter window.products by category and append HTML
         */
        renderCategory: function (categoryName, container) {
            if (!window.products) return;

            // Filtra produtos desta categoria
            const products = window.products.filter(p => p.category === categoryName);

            if (products.length === 0) return;

            // Gera HTML
            const html = products.map(p => this.renderProductCard(p)).join('');

            // Injeta no container
            container.innerHTML = html;
            container.classList.add('rendered'); // Marca como renderizado

            // Reinicializa ícones Lucide nos novos elementos
            if (typeof lucide !== 'undefined') lucide.createIcons({
                root: container
            });
        },

        /**
         * Gera HTML de um Card de Produto
         * Deve estar sincronizado com views/cardapio/partials/products.php
         */
        renderProductCard: function (p) {
            // Formata preço
            const priceFormatted = parseFloat(p.price).toFixed(2).replace('.', ',');

            // Imagem ou Placeholder
            let imageHtml = '';
            if (p.image) {
                imageHtml = `<img src="${window.BASE_URL}/uploads/${p.image}" class="cardapio-product-image" loading="lazy" alt="${this.escapeHtml(p.name)}">`;
            } else if (p.icon_as_photo && p.icon) {
                imageHtml = `
                    <div class="cardapio-product-icon-placeholder" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; border-radius: 8px;">
                        <span style="font-size: 3rem;">${p.icon}</span>
                    </div>
                `;
            } else {
                imageHtml = `<div class="cardapio-product-image-placeholder"><i data-lucide="image" size="24"></i></div>`;
            }

            return `
                <div 
                    class="cardapio-product-card" 
                    data-product-id="${p.id}"
                    data-product-name="${this.escapeHtml(p.name)}"
                    data-product-price="${p.price}"
                    onclick="openProductModal(${p.id})" 
                    style="cursor: pointer; display: flex;"
                >
                    <div class="cardapio-product-image-wrapper">
                        ${imageHtml}
                    </div>
                    
                    <div class="cardapio-product-info">
                        <h3 class="cardapio-product-name">${this.escapeHtml(p.name)}</h3>
                        <p class="cardapio-product-description">${this.escapeHtml(p.description)}</p>
                        <div class="cardapio-product-footer">
                            <span class="cardapio-product-price">R$ ${priceFormatted}</span>
                        </div>
                    </div>
                    <button class="cardapio-add-btn"><i data-lucide="plus" size="16"></i></button>
                </div>
            `;
        },

        escapeHtml: function (text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    };

    window.CatalogRenderer = CatalogRenderer;
})();
