/**
 * ComboView.js
 * Manipulação do DOM do Modal de Combo
 */
const ComboView = {

    /**
     * Renderiza o conteúdo inicial do modal
     */
    renderModal: function (combo) {
        // Header Info
        document.getElementById('modalComboName').textContent = combo.name;
        document.getElementById('modalComboDescription').textContent = combo.description || '';
        document.getElementById('modalComboPrice').textContent = Utils.formatCurrency(parseFloat(combo.price));

        // Imagem
        const imgEl = document.getElementById('modalComboImage');
        if (combo.image && imgEl) {
            imgEl.src = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/uploads/' + combo.image;
            imgEl.style.display = 'block';
        } else if (imgEl) {
            imgEl.style.display = 'none';
        }

        // Reset Inputs
        document.getElementById('modalComboQuantity').textContent = '1';
        const obs = document.getElementById('modalComboObservation');
        if (obs) obs.value = '';

        // Instrução
        this._ensureInstructionText();

        // Lista de Produtos
        this._renderProductList(combo.items);
    },

    /**
     * Atualiza o preço exibido
     */
    updatePrice: function (totalValue) {
        const el = document.getElementById('modalComboTotalPrice');
        if (el) el.textContent = Utils.formatCurrency(totalValue);
    },

    /**
     * Atualiza quantidade exibida
     */
    updateQuantity: function (qty) {
        const el = document.getElementById('modalComboQuantity');
        if (el) el.textContent = qty;
    },

    /**
     * Atualiza badges de extras (Ex: "2 extras")
     */
    updateBadge: function (productId, count) {
        const badge = document.getElementById(`badge-${productId}`);
        if (badge) {
            badge.textContent = `${count} extra${count !== 1 ? 's' : ''}`;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    },

    open: function () {
        document.getElementById('comboModal').classList.add('show');
        Utils.initIcons();
    },

    close: function () {
        document.getElementById('comboModal').classList.remove('show');
    },

    // --- INTERNOS ---

    _ensureInstructionText: function () {
        let instruction = document.getElementById('comboInstructionText');
        if (!instruction) {
            instruction = document.createElement('p');
            instruction.id = 'comboInstructionText';
            instruction.style.cssText = 'text-align:left; margin-bottom:15px; margin-top:10px; font-weight:700; font-size:0.9rem; color:#e63946;';
            instruction.textContent = 'Itens inclusos no combo. Clique para adicionar.';
            const container = document.getElementById('comboProductsContainer');
            if (container) container.parentNode.insertBefore(instruction, container);
        }
    },

    _renderProductList: function (items) {
        const container = document.getElementById('comboProductsContainer');
        if (!container) return;

        container.innerHTML = '';

        if (!items || items.length === 0) {
            container.innerHTML = '<p style="color:#666; font-style:italic;">Nenhum item neste combo.</p>';
            return;
        }

        items.forEach(item => {
            container.appendChild(this._createProductItemElement(item));
        });
    },

    _createProductItemElement: function (item) {
        const fullProduct = (typeof products !== 'undefined') ? products.find(p => p.id == item.product_id) : null;
        const relations = (typeof PRODUCT_RELATIONS !== 'undefined') ? PRODUCT_RELATIONS : {};
        const hasAdditionals = (item.allow_additionals == 1) && fullProduct && relations[fullProduct.id]?.length > 0;

        const wrapper = document.createElement('div');
        wrapper.className = 'combo-product-collapse open';

        // Header
        const headerHtml = `
            <div class="combo-product-header" ${hasAdditionals ? 'onclick="this.parentNode.classList.toggle(\'open\')"' : ''} style="${hasAdditionals ? 'cursor:pointer' : ''}">
                <div class="combo-product-info">
                    <span>${item.product_name}</span>
                    ${hasAdditionals ? `<span class="combo-extras-badge" id="badge-${item.product_id}" style="display:none">0 extras</span>` : ''}
                </div>
                ${hasAdditionals ? `<i data-lucide="chevron-down" class="combo-toggle-icon" size="16"></i>` : ''}
            </div>`;
        wrapper.innerHTML = headerHtml;

        // Body (Adicionais)
        if (hasAdditionals) {
            const body = document.createElement('div');
            body.className = 'combo-product-body';
            const list = document.createElement('div');
            list.className = 'combo-additional-list';

            [...new Set(relations[fullProduct.id])].forEach(groupId => {
                const original = document.querySelector(`.cardapio-additional-group[data-group-id="${groupId}"]`);
                if (original) {
                    const clone = original.cloneNode(true);
                    clone.style.display = 'block';
                    clone.querySelectorAll('input').forEach(input => this._setupInput(input, item, clone));
                    list.appendChild(clone);
                }
            });
            body.appendChild(list);
            wrapper.appendChild(body);
        }
        return wrapper;
    },

    _setupInput: function (input, item, container) {
        const { additionalId: addId, additionalName: name, additionalPrice: price } = input.dataset;
        const uniqueId = `combo-${item.product_id}-${addId}-${Date.now().toString(36)}`;

        Object.assign(input, {
            id: uniqueId,
            name: `combo_extras_${item.product_id}[]`,
            checked: false
        });

        // Setup Dataset for Controller
        Object.assign(input.dataset, { comboProductId: item.product_id, comboAddId: addId, comboAddName: name, comboAddPrice: price });
        input.classList.remove('cardapio-additional-checkbox');

        // Fix Label
        const label = input.closest('label') || input.nextElementSibling || container.querySelector(`label[for="${input.id}"]`);
        if (label?.tagName === 'LABEL') label.setAttribute('for', uniqueId);
    }
};

window.ComboView = ComboView;
