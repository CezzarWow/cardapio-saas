/**
 * MODALS-COMBO.JS - Modal de Combo
 * Dependências: CardapioModals (modals.js), CardapioCart, Utils
 * 
 * Este módulo estende CardapioModals com as funções do modal de combo.
 */

(function () {
    'use strict';

    // ==========================================
    // ESTADO DO MODAL DE COMBO
    // ==========================================
    CardapioModals.currentCombo = null;
    CardapioModals.comboQuantity = 1;
    CardapioModals.comboSelections = {}; // { productId: [ {id, name, price} ] }

    // ==========================================
    // HELPERS INTERNOS
    // ==========================================

    /**
     * Calcula total de extras selecionados
     */
    CardapioModals.calculateExtrasTotal = function () {
        let total = 0;
        Object.values(this.comboSelections).forEach(list => {
            list.forEach(item => total += item.price);
        });
        return total;
    };

    /**
     * Cria o header de um item do combo
     * @private
     */
    CardapioModals._createComboItemHeader = function (item, hasAdditionals, wrapper) {
        const header = document.createElement('div');
        header.className = 'combo-product-header';

        if (hasAdditionals) {
            header.onclick = () => wrapper.classList.toggle('open');
        } else {
            wrapper.classList.remove('open');
        }

        header.innerHTML = `
            <div class="combo-product-info">
                <span>${item.product_name}</span>
                ${hasAdditionals ? `<span class="combo-extras-badge" id="badge-${item.product_id}">0 extras</span>` : ''}
            </div>
            ${hasAdditionals ? `<i data-lucide="chevron-down" class="combo-toggle-icon" size="16"></i>` : ''}
        `;

        return header;
    };

    /**
     * Configura um input de adicional clonado
     * @private
     */
    CardapioModals._setupAdditionalInput = function (input, item, clone) {
        const addId = input.getAttribute('data-additional-id');
        const price = parseFloat(input.getAttribute('data-additional-price'));
        const name = input.getAttribute('data-additional-name');
        const uniqueId = `combo-${item.product_id}-${addId}`;

        input.id = uniqueId;
        input.name = `combo_extras_${item.product_id}[]`;
        input.checked = false;

        // Atualiza Label
        let label = clone.querySelector(`label[for="${input.getAttribute('id')}"]`);
        if (!label) label = input.nextElementSibling;
        if (label && label.tagName === 'LABEL') {
            label.setAttribute('for', uniqueId);
        } else {
            const parentLabel = input.closest('label');
            if (parentLabel) parentLabel.setAttribute('for', uniqueId);
        }

        // Eventos
        input.onclick = (e) => e.stopPropagation();
        input.onchange = (e) => {
            this.toggleComboAdditional(item.product_id, addId, name, price, e.target.checked);
        };

        // Remove classe global
        input.classList.remove('cardapio-additional-checkbox');
    };

    /**
     * Cria o body de adicionais para um item do combo
     * @private
     */
    CardapioModals._createComboItemBody = function (item, fullProduct, relations) {
        const body = document.createElement('div');
        body.className = 'combo-product-body';

        const list = document.createElement('div');
        list.className = 'combo-additional-list';

        const groupIds = [...new Set(relations[fullProduct.id])];

        groupIds.forEach(groupId => {
            const originalGroup = document.querySelector(`.cardapio-additional-group[data-group-id="${groupId}"]`);
            if (originalGroup) {
                const clone = originalGroup.cloneNode(true);
                clone.style.display = 'block';

                clone.querySelectorAll('input').forEach(input => {
                    this._setupAdditionalInput(input, item, clone);
                });

                list.appendChild(clone);
            }
        });

        body.appendChild(list);
        return body;
    };

    // ==========================================
    // ABRIR MODAL DE COMBO
    // ==========================================
    CardapioModals.openCombo = function (comboId) {
        const combo = (typeof combos !== 'undefined') ? combos.find(c => c.id == comboId) : null;

        if (!combo) {
            console.error('Combo não encontrado:', comboId);
            return;
        }

        this.currentCombo = combo;
        this.comboQuantity = 1;
        this.comboSelections = {};

        if (combo.items) {
            combo.items.forEach(item => {
                this.comboSelections[item.product_id] = [];
            });
        }

        // Header
        document.getElementById('modalComboName').textContent = combo.name;
        document.getElementById('modalComboDescription').textContent = combo.description || '';
        document.getElementById('modalComboPrice').textContent = Utils.formatCurrency(parseFloat(combo.price));

        const imgEl = document.getElementById('modalComboImage');
        if (combo.image) {
            imgEl.src = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/uploads/' + combo.image;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }

        // Instrução
        this._ensureInstructionText();

        // Renderiza Produtos
        const container = document.getElementById('comboProductsContainer');
        container.innerHTML = '';

        if (combo.items && combo.items.length > 0) {
            combo.items.forEach(item => {
                container.appendChild(this.renderComboProductItem(item));
            });
        } else {
            container.innerHTML = '<p style="color:#666; font-style:italic;">Nenhum item neste combo.</p>';
        }

        // Reset
        document.getElementById('modalComboQuantity').textContent = '1';
        const obsInput = document.getElementById('modalComboObservation');
        if (obsInput) obsInput.value = '';
        this.updateComboPrice();

        // Abre
        document.getElementById('comboModal').classList.add('show');
        Utils.initIcons();
    };

    /**
     * Garante que o texto de instrução existe
     * @private
     */
    CardapioModals._ensureInstructionText = function () {
        let instruction = document.getElementById('comboInstructionText');
        if (!instruction) {
            instruction = document.createElement('p');
            instruction.id = 'comboInstructionText';
            instruction.style.cssText = 'text-align:left; margin-bottom:15px; margin-top:10px; font-weight:700; font-size:0.9rem; color:#e63946;';
            instruction.textContent = 'Itens inclusos no combo. Clique para adicionar.';

            const container = document.getElementById('comboProductsContainer');
            container.parentNode.insertBefore(instruction, container);
        }
    };

    CardapioModals.closeCombo = function () {
        document.getElementById('comboModal').classList.remove('show');
        this.currentCombo = null;
    };

    // ==========================================
    // RENDERIZAR ITEM DO COMBO (Refatorado)
    // ==========================================
    CardapioModals.renderComboProductItem = function (item) {
        const fullProduct = (typeof products !== 'undefined') ? products.find(p => p.id == item.product_id) : null;
        const relations = (typeof PRODUCT_RELATIONS !== 'undefined') ? PRODUCT_RELATIONS : {};
        const hasAdditionals = (item.allow_additionals == 1) && fullProduct && relations[fullProduct.id] && relations[fullProduct.id].length > 0;

        const wrapper = document.createElement('div');
        wrapper.className = 'combo-product-collapse open';

        // Header
        wrapper.appendChild(this._createComboItemHeader(item, hasAdditionals, wrapper));

        // Body (se tiver adicionais)
        if (hasAdditionals) {
            wrapper.appendChild(this._createComboItemBody(item, fullProduct, relations));
        }

        return wrapper;
    };

    // ==========================================
    // TOGGLE ADICIONAL DO COMBO
    // ==========================================
    CardapioModals.toggleComboAdditional = function (productId, addId, name, price, isChecked) {
        if (!this.comboSelections[productId]) this.comboSelections[productId] = [];

        if (isChecked) {
            this.comboSelections[productId].push({ id: addId, name, price });
        } else {
            this.comboSelections[productId] = this.comboSelections[productId].filter(a => a.id != addId);
        }

        const count = this.comboSelections[productId].length;
        const badge = document.getElementById(`badge-${productId}`);
        if (badge) {
            badge.textContent = `${count} extra${count !== 1 ? 's' : ''}`;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }

        this.updateComboPrice();
    };

    // ==========================================
    // CONTROLE DE QUANTIDADE DO COMBO
    // ==========================================
    CardapioModals.increaseComboQty = function () {
        this.comboQuantity++;
        this.updateComboPrice();
    };

    CardapioModals.decreaseComboQty = function () {
        if (this.comboQuantity > 1) {
            this.comboQuantity--;
            this.updateComboPrice();
        }
    };

    CardapioModals.updateComboPrice = function () {
        const totalExtras = this.calculateExtrasTotal();
        const total = (parseFloat(this.currentCombo.price) + totalExtras) * this.comboQuantity;

        document.getElementById('modalComboQuantity').textContent = this.comboQuantity;
        document.getElementById('modalComboTotalPrice').textContent = Utils.formatCurrency(total);
    };

    // ==========================================
    // ADICIONAR COMBO AO CARRINHO
    // ==========================================
    CardapioModals.addComboToCart = function () {
        if (!this.currentCombo) return;

        const observation = document.getElementById('modalComboObservation').value.trim();
        const totalExtras = this.calculateExtrasTotal();

        const productsList = this.currentCombo.items.map(item => {
            const extras = this.comboSelections[item.product_id] || [];
            return {
                id: item.product_id,
                name: item.product_name,
                additionals: extras
            };
        });

        const cartItem = {
            id: Date.now() + Math.random(),
            isCombo: true,
            comboId: this.currentCombo.id,
            name: this.currentCombo.name,
            image: this.currentCombo.image,
            basePrice: parseFloat(this.currentCombo.price),
            quantity: this.comboQuantity,
            products: productsList,
            observation: observation,
            unitPrice: parseFloat(this.currentCombo.price) + totalExtras
        };

        CardapioCart.add(cartItem);
        this.closeCombo();
    };

})();
