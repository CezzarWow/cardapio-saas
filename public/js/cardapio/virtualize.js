/**
 * Virtualização simples por categoria — carrega um número limitado de produtos
 * e adiciona botão "Carregar mais" para cada categoria.
 */
(function () {
    'use strict';

    const PAGE_SIZE = 12;

    function groupByCategory(products) {
        const map = {};
        products.forEach(p => {
            const cat = p.category || (p.category_name || 'Sem Categoria');
            if (!map[cat]) map[cat] = [];
            map[cat].push(p);
        });
        return map;
    }

    function formatPrice(v) {
        return 'R$ ' + Number(v).toFixed(2).replace('.', ',');
    }

    function createProductCard(prod) {
        const div = document.createElement('div');
        div.className = 'cardapio-product-card';
        div.setAttribute('data-product-id', prod.id || '');
        div.setAttribute('data-product-name', prod.name || '');
        div.setAttribute('data-product-price', prod.price || 0);
        div.style.cursor = 'pointer';

        const imgWrap = document.createElement('div');
        imgWrap.className = 'cardapio-product-image-wrapper';
        if (prod.image) {
            const img = document.createElement('img');
            img.className = 'cardapio-product-image';
            img.loading = 'lazy';
            img.src = (window.BASE_URL || '') + '/uploads/' + prod.image;
            imgWrap.appendChild(img);
        } else if (prod.icon_as_photo && prod.icon) {
            const d = document.createElement('div');
            d.className = 'cardapio-product-icon-placeholder';
            d.style.display = 'flex';
            d.style.alignItems = 'center';
            d.style.justifyContent = 'center';
            d.innerHTML = '<span style="font-size: 2rem">' + (prod.icon || '') + '</span>';
            imgWrap.appendChild(d);
        } else {
            const ph = document.createElement('div');
            ph.className = 'cardapio-product-image-placeholder';
            ph.innerHTML = '<i data-lucide="image" size="24"></i>';
            imgWrap.appendChild(ph);
        }

        const info = document.createElement('div');
        info.className = 'cardapio-product-info';
        info.innerHTML = '<h3 class="cardapio-product-name">' + (prod.name || '') + '</h3>' +
            '<p class="cardapio-product-description">' + (prod.description || '') + '</p>' +
            '<div class="cardapio-product-footer"><span class="cardapio-product-price">' + formatPrice(prod.price || 0) + '</span></div>';

        const btn = document.createElement('button');
        btn.className = 'cardapio-add-btn';
        btn.innerHTML = '<i data-lucide="plus" size="16"></i>';

        div.appendChild(imgWrap);
        div.appendChild(info);
        div.appendChild(btn);

        // Delegar abertura do modal
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (typeof CardapioModals !== 'undefined' && CardapioModals.openProduct) {
                CardapioModals.openProduct(prod.id);
            } else if (typeof openProductModal !== 'undefined') {
                openProductModal(prod.id);
            }
        });

        // Ativa lucide depois
        return div;
    }

    function init() {
        if (!window.products || !Array.isArray(window.products)) return;

        const productsMap = groupByCategory(window.products);

        Object.keys(productsMap).forEach(catName => {
            // Tenta encontrar a seção correspondente pelo data-category-id ou título
            const sections = Array.from(document.querySelectorAll('.cardapio-category-section'));
            let target = sections.find(sec => (sec.getAttribute('data-category-id') === String(catName)) || (sec.querySelector('.cardapio-category-title') && sec.querySelector('.cardapio-category-title').textContent.trim() === catName));

            // Se não achar, tenta usar a primeira que contenha o nome
            if (!target) {
                target = sections.find(sec => sec.textContent.indexOf(catName) !== -1);
            }
            if (!target) return;

            // Remove produtos servidor-rendered existentes para evitar duplicação
            const existing = Array.from(target.querySelectorAll('.cardapio-product-card'));
            existing.forEach(e => e.remove());

            const listWrapper = document.createElement('div');
            listWrapper.className = 'cardapio-products-list-virtual';
            target.appendChild(listWrapper);

            let offset = 0;
            function renderNext() {
                const slice = productsMap[catName].slice(offset, offset + PAGE_SIZE);
                slice.forEach(prod => listWrapper.appendChild(createProductCard(prod)));
                offset += slice.length;
                if (offset < productsMap[catName].length) {
                    if (!target.querySelector('.cardapio-load-more')) {
                        const more = document.createElement('button');
                        more.className = 'cardapio-load-more';
                        more.textContent = 'Carregar mais';
                        more.style.marginTop = '12px';
                        more.addEventListener('click', () => {
                            renderNext();
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                        target.appendChild(more);
                    }
                } else {
                    const moreBtn = target.querySelector('.cardapio-load-more');
                    if (moreBtn) moreBtn.remove();
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            // Renderiza primeiro bloco
            renderNext();
        });
    }

    document.addEventListener('DOMContentLoaded', init);

    // Export for tests
    window.CardapioVirtualize = { init };

})();
