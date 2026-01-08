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
    // ABRIR MODAL DE PRODUTO
    // ==========================================
    CardapioModals.openProduct = function (productId) {
        let product = null;

        // 1. Tenta buscar no array global (melhor cenário, tem adicionais)
        if (typeof window.products !== 'undefined' && Array.isArray(window.products)) {
            product = window.products.find(p => p.id == productId);
        }

        // 2. Fallback: Busca no DOM (se array falhar ou produto não achado)
        if (!product) {
            console.warn('[CardapioModals] Produto não encontrado no array global. Tentando ler do DOM. ID:', productId);
            const card = document.querySelector(`.cardapio-product-card[data-product-id="${productId}"]`);

            if (card) {
                product = {
                    id: productId,
                    name: card.getAttribute('data-product-name'),
                    price: parseFloat(card.getAttribute('data-product-price')),
                    description: card.getAttribute('data-product-description'),
                    image: card.getAttribute('data-product-image'),
                    additionals: [] // DOM não tem dados de adicionais complexos
                };
            }
        }

        if (!product) {
            console.error('[CardapioModals] Produto não encontrado nem no array nem no DOM.', productId);
            return;
        }

        // ============================================
        // LÓGICA ORIGINAL: SE NÃO TEM ADICIONAIS, ADICIONA DIRETO
        // ============================================
        const productRelations = (typeof window.PRODUCT_RELATIONS !== 'undefined') ? window.PRODUCT_RELATIONS : {};
        const relatedGroups = productRelations[product.id] || [];
        const hasAdditionals = relatedGroups.length > 0;

        if (!hasAdditionals) {
            // Adiciona direto ao carrinho sem abrir modal
            CardapioCart.addDirect(product.id, product.name, parseFloat(product.price), product.image);
            return;
        }

        // ============================================
        // SE TEM ADICIONAIS: ABRE O MODAL
        // ============================================
        this.currentProduct = product;
        this.currentQuantity = 1;
        this.selectedAdditionals = [];
        this.basePrice = parseFloat(product.price);

        // Preenche info básica
        document.getElementById('modalProductName').textContent = product.name;
        document.getElementById('modalProductDescription').textContent = product.description || '';
        document.getElementById('modalProductPrice').textContent = Utils.formatCurrency(this.basePrice);

        // Imagem
        const imgEl = document.getElementById('modalProductImage');
        if (product.image) {
            imgEl.src = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/uploads/' + product.image;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }

        // Renderiza Adicionais usando PRODUCT_RELATIONS
        this.renderAdditionals(relatedGroups);

        // Reset inputs
        const obsInput = document.getElementById('modalObservation');
        if (obsInput) obsInput.value = '';
        document.getElementById('modalQuantity').textContent = '1';

        // Atualiza preço total
        this.updateModalPrice();

        // Abre modal (adiciona classe e scroll reset)
        const modal = document.getElementById('productModal');
        modal.classList.add('show');

        // Fix de Scroll
        requestAnimationFrame(() => {
            const content = modal.querySelector('.cardapio-modal-content');
            const body = modal.querySelector('.cardapio-modal-body');
            if (content) content.scrollTop = 0;
            if (body) body.scrollTop = 0;
        });
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
    // CONTROLE DE ADICIONAIS
    // ==========================================
    CardapioModals.renderAdditionals = function (groupIds) {
        // 1. Resetar Checkboxes e Estado
        this.selectedAdditionals = [];
        document.querySelectorAll('.cardapio-additional-checkbox').forEach(cb => {
            cb.checked = false;
        });

        const container = document.getElementById('modalAdditionals');
        const groups = document.querySelectorAll('.cardapio-additional-group');
        const title = document.querySelector('.cardapio-modal-additionals-title');

        // Se não tem grupos vinculados
        if (!groupIds || !Array.isArray(groupIds) || groupIds.length === 0) {
            if (title) title.style.display = 'none';
            groups.forEach(g => g.style.display = 'none');
            return;
        }

        if (title) title.style.display = 'block';

        // 2. Converter para Set de strings para comparação rápida
        const allowedGroupIds = new Set(groupIds.map(id => String(id)));

        // 3. Mostrar apenas os grupos permitidos
        let hasVisible = false;
        groups.forEach(group => {
            const groupId = group.getAttribute('data-group-id');
            if (allowedGroupIds.has(String(groupId))) {
                group.style.display = 'block';
                hasVisible = true;
            } else {
                group.style.display = 'none';
            }
        });

        // Se nenhum grupo for visível, esconde título
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
    // ADICIONAR AO CARRINHO (AÇÃO DO MODAL)
    // ==========================================
    CardapioModals.addToCartAction = function () {
        if (!this.currentProduct) return;

        const observation = document.getElementById('modalObservation').value.trim();

        // Cria objeto item compatível com Cart.js
        const item = {
            id: Date.now() + Math.random(),
            productId: this.currentProduct.id,
            name: this.currentProduct.name,
            basePrice: this.basePrice,
            quantity: this.currentQuantity,
            additionals: [...this.selectedAdditionals],
            observation: observation,
            unitPrice: this.basePrice + this.selectedAdditionals.reduce((sum, item) => sum + item.price, 0)
        };

        CardapioCart.add(item);
        this.closeProduct();
    };

})();
