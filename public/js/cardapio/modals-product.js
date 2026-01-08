/**
 * MODALS-PRODUCT.JS - Modal de Produto Individual
 * Dependências: CardapioModals (modals.js), CardapioCart, Utils
 * 
 * Este módulo estende CardapioModals com as funções do modal de produto.
 */

(function () {
    'use strict';

    // ==========================================
    // ESTADO DO MODAL DE PRODUTO
    // ==========================================
    CardapioModals.currentProduct = null;
    CardapioModals.currentQuantity = 1;
    CardapioModals.selectedAdditionals = [];
    CardapioModals.basePrice = 0;

    // ==========================================
    // HELPERS INTERNOS
    // ==========================================

    /**
     * Busca produto por ID (array global ou fallback DOM)
     * @private
     */
    CardapioModals._findProduct = function (productId) {
        // 1. Tenta buscar no array global
        if (typeof window.products !== 'undefined' && Array.isArray(window.products)) {
            const product = window.products.find(p => p.id == productId);
            if (product) return product;
        }

        // 2. Fallback: DOM
        console.warn('[CardapioModals] Produto não encontrado no array. Tentando DOM. ID:', productId);
        const card = document.querySelector(`.cardapio-product-card[data-product-id="${productId}"]`);

        if (card) {
            return {
                id: productId,
                name: card.getAttribute('data-product-name'),
                price: parseFloat(card.getAttribute('data-product-price')),
                description: card.getAttribute('data-product-description'),
                image: card.getAttribute('data-product-image'),
                additionals: []
            };
        }

        console.error('[CardapioModals] Produto não encontrado.', productId);
        return null;
    };

    /**
     * Verifica se produto tem adicionais vinculados
     * @private
     */
    CardapioModals._getProductRelations = function (productId) {
        const relations = (typeof window.PRODUCT_RELATIONS !== 'undefined') ? window.PRODUCT_RELATIONS : {};
        return relations[productId] || [];
    };

    /**
     * Preenche informações básicas no modal
     * @private
     */
    CardapioModals._fillProductInfo = function (product) {
        document.getElementById('modalProductName').textContent = product.name;
        document.getElementById('modalProductDescription').textContent = product.description || '';
        document.getElementById('modalProductPrice').textContent = Utils.formatCurrency(this.basePrice);

        const imgEl = document.getElementById('modalProductImage');
        if (product.image) {
            imgEl.src = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/uploads/' + product.image;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }
    };

    /**
     * Reseta inputs do modal
     * @private
     */
    CardapioModals._resetModalInputs = function () {
        const obsInput = document.getElementById('modalObservation');
        if (obsInput) obsInput.value = '';
        document.getElementById('modalQuantity').textContent = '1';
    };

    /**
     * Abre modal e reseta scroll
     * @private
     */
    CardapioModals._showModal = function () {
        const modal = document.getElementById('productModal');
        modal.classList.add('show');

        requestAnimationFrame(() => {
            const content = modal.querySelector('.cardapio-modal-content');
            const body = modal.querySelector('.cardapio-modal-body');
            if (content) content.scrollTop = 0;
            if (body) body.scrollTop = 0;
        });
    };

    // ==========================================
    // ABRIR MODAL DE PRODUTO (Refatorado)
    // ==========================================
    CardapioModals.openProduct = function (productId) {
        const product = this._findProduct(productId);
        if (!product) return;

        const relatedGroups = this._getProductRelations(product.id);
        const hasAdditionals = relatedGroups.length > 0;

        // Se não tem adicionais, adiciona direto
        if (!hasAdditionals) {
            CardapioCart.addDirect(product.id, product.name, parseFloat(product.price), product.image);
            return;
        }

        // Inicializa estado
        this.currentProduct = product;
        this.currentQuantity = 1;
        this.selectedAdditionals = [];
        this.basePrice = parseFloat(product.price);

        // Preenche modal
        this._fillProductInfo(product);
        this.renderAdditionals(relatedGroups);
        this._resetModalInputs();
        this.updateModalPrice();

        // Abre
        this._showModal();
    };

    CardapioModals.closeProduct = function () {
        document.getElementById('productModal').classList.remove('show');
        this.currentProduct = null;
    };

    // ==========================================
    // CONTROLE DE QUANTIDADE
    // ==========================================
    CardapioModals.increaseQty = function () {
        this.currentQuantity++;
        this.updateQtyDisplay();
    };

    CardapioModals.decreaseQty = function () {
        if (this.currentQuantity > 1) {
            this.currentQuantity--;
            this.updateQtyDisplay();
        }
    };

    CardapioModals.updateQtyDisplay = function () {
        document.getElementById('modalQuantity').textContent = this.currentQuantity;
        this.updateModalPrice();
    };

    // ==========================================
    // CONTROLE DE ADICIONAIS (Refatorado)
    // ==========================================
    CardapioModals.renderAdditionals = function (groupIds) {
        // Reset
        this.selectedAdditionals = [];
        document.querySelectorAll('.cardapio-additional-checkbox').forEach(cb => cb.checked = false);

        const groups = document.querySelectorAll('.cardapio-additional-group');
        const title = document.querySelector('.cardapio-modal-additionals-title');

        // Se não tem grupos
        if (!groupIds || !Array.isArray(groupIds) || groupIds.length === 0) {
            if (title) title.style.display = 'none';
            groups.forEach(g => g.style.display = 'none');
            return;
        }

        if (title) title.style.display = 'block';

        // Mostra apenas grupos permitidos
        const allowedGroupIds = new Set(groupIds.map(id => String(id)));
        let hasVisible = false;

        groups.forEach(group => {
            const isAllowed = allowedGroupIds.has(String(group.getAttribute('data-group-id')));
            group.style.display = isAllowed ? 'block' : 'none';
            if (isAllowed) hasVisible = true;
        });

        if (!hasVisible && title) title.style.display = 'none';
    };

    CardapioModals.toggleAdditional = function (id, name, price, isChecked) {
        if (isChecked) {
            this.selectedAdditionals.push({ id, name, price });
        } else {
            this.selectedAdditionals = this.selectedAdditionals.filter(a => a.id !== id);
        }
        this.updateModalPrice();
    };

    CardapioModals.updateModalPrice = function () {
        const addTotal = this.selectedAdditionals.reduce((sum, item) => sum + item.price, 0);
        const finalPrice = (this.basePrice + addTotal) * this.currentQuantity;
        document.getElementById('modalTotalPrice').textContent = Utils.formatCurrency(finalPrice);
    };

    // ==========================================
    // ADICIONAR AO CARRINHO
    // ==========================================
    CardapioModals.addToCartAction = function () {
        if (!this.currentProduct) return;

        const observation = document.getElementById('modalObservation').value.trim();
        const addTotal = this.selectedAdditionals.reduce((sum, a) => sum + a.price, 0);

        const item = {
            id: Date.now() + Math.random(),
            productId: this.currentProduct.id,
            name: this.currentProduct.name,
            basePrice: this.basePrice,
            quantity: this.currentQuantity,
            additionals: [...this.selectedAdditionals],
            observation: observation,
            unitPrice: this.basePrice + addTotal
        };

        CardapioCart.add(item);
        this.closeProduct();
    };

})();
