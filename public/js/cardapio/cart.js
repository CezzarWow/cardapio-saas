/**
 * CART.JS - Gerenciamento do Carrinho de Compras
 * Dependências: Utils.js
 */

const CardapioCart = {
    items: [],

    // ==========================================
    // HELPERS INTERNOS
    // ==========================================

    /**
     * Factory: Cria objeto de item do carrinho
     * @private
     */
    _createItem: function (options) {
        return {
            id: Date.now() + Math.random(),
            productId: options.productId || options.id,
            name: options.name,
            basePrice: options.price,
            quantity: options.quantity || 1,
            additionals: options.additionals || [],
            observation: options.observation || '',
            unitPrice: options.price,
            isCombo: options.isCombo || false
        };
    },

    /**
     * Aplica feedback visual em um botão
     * @private
     */
    _pulseButton: function (selector) {
        const btn = document.querySelector(selector);
        if (btn) {
            btn.classList.add('btn-pulse');
            setTimeout(() => btn.classList.remove('btn-pulse'), 300);
        }
    },

    // ==========================================
    // MÉTODOS PÚBLICOS - ADICIONAR
    // ==========================================

    /**
     * Adicionar item (geral)
     */
    add: function (item) {
        this.items.push(item);
        this.updateUI();
        this.triggerPulse();
    },

    /**
     * Adicionar direto (sem modal)
     */
    addDirect: function (id, name, price, image) {
        this.add(this._createItem({ id, name, price }));
    },

    /**
     * Adicionar bebida
     */
    addDrink: function (id, name, price, image) {
        this.add(this._createItem({ id, name, price }));
        this._pulseButton(`.suggestion-drink-btn[data-id="${id}"]`);
    },

    /**
     * Adicionar molho
     */
    addSauce: function (id, name, price) {
        this.add(this._createItem({
            productId: 'sauce_' + id,
            name: 'Molho: ' + name,
            price
        }));
        this._pulseButton(`.suggestion-sauce-btn[data-id="${id}"]`);
    },

    /**
     * Adicionar combo
     */
    addCombo: function (id, name, price, image) {
        this.add(this._createItem({
            productId: 'combo_' + id,
            name,
            price,
            isCombo: true
        }));
    },

    // ==========================================
    // MÉTODOS PÚBLICOS - GERENCIAR
    // ==========================================

    /**
     * Remover item
     */
    remove: function (itemId) {
        this.items = this.items.filter(item => item.id !== itemId);
        this.updateUI();
    },

    /**
     * Limpar tudo
     */
    clear: function () {
        this.items.length = 0; // Mantém a referência do array
        this.updateUI();
    },

    /**
     * Calcular totais
     */
    getTotals: function () {
        return {
            count: this.items.reduce((sum, item) => sum + item.quantity, 0),
            value: this.items.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0)
        };
    },

    // ==========================================
    // UI - ATUALIZAÇÃO
    // ==========================================

    /**
     * Atualizar Interface
     */
    updateUI: function () {
        const totals = this.getTotals();

        // Botão Flutuante
        const cartTotalEl = document.getElementById('cartTotal');
        if (cartTotalEl) cartTotalEl.textContent = Utils.formatCurrency(totals.value);

        const floatBtn = document.getElementById('floatingCart');
        if (floatBtn) floatBtn.classList.toggle('show', totals.count > 0);

        // Modal do Carrinho
        const cartModalTotal = document.getElementById('cartModalTotal');
        if (cartModalTotal) cartModalTotal.textContent = Utils.formatCurrency(totals.value);

        // Botão Flutuante (Sugestões)
        const suggTotal = document.getElementById('suggestionsCartTotal');
        if (suggTotal) suggTotal.textContent = Utils.formatCurrency(totals.value);

        // Lista
        this.renderList();
    },

    /**
     * Renderizar lista de itens
     */
    renderList: function () {
        const container = document.getElementById('cartItemsContainer');
        if (!container) return;

        if (this.items.length === 0) {
            container.innerHTML = this._renderEmptyCart();
            Utils.initIcons();
            return;
        }

        container.innerHTML = this.items.map(item => this._renderCartItem(item)).join('');
        Utils.initIcons();
    },

    /**
     * Renderiza carrinho vazio
     * @private
     */
    _renderEmptyCart: function () {
        return `
            <div class="cardapio-cart-empty">
                <div class="cardapio-cart-empty-icon">
                    <i data-lucide="shopping-bag" size="48"></i>
                </div>
                <p>Seu carrinho está vazio</p>
            </div>
        `;
    },

    /**
     * Renderiza um item do carrinho
     * @private
     */
    _renderCartItem: function (item) {
        const comboDetails = item.isCombo && item.products ? this._renderComboDetails(item.products) : '';
        const additionals = !item.isCombo && item.additionals?.length > 0
            ? `<p class="cardapio-cart-item-additionals">Extras: ${item.additionals.map(a => a.name).join(', ')}</p>`
            : '';
        const observation = item.observation
            ? `<p class="cardapio-cart-item-obs">Obs: ${item.observation}</p>`
            : '';

        return `
            <div class="cardapio-cart-item">
                <div class="cardapio-cart-item-info">
                    <p class="cardapio-cart-item-name">${item.quantity}x ${item.name}</p>
                    ${comboDetails}
                    ${additionals}
                    ${observation}
                </div>
                <div class="cardapio-cart-item-actions">
                    <span class="cardapio-cart-item-price">
                        ${Utils.formatCurrency(item.unitPrice * item.quantity)}
                    </span>
                    <button class="cardapio-cart-remove-icon-btn" onclick="CardapioCart.remove(${item.id})">
                        <i data-lucide="trash-2" size="18"></i>
                    </button>
                </div>
            </div>
        `;
    },

    /**
     * Renderiza detalhes de combo
     * @private
     */
    _renderComboDetails: function (products) {
        const items = products.map(p => {
            const extras = p.additionals?.length > 0
                ? `<span style="font-size: 0.8rem; color: #888;">(+ ${p.additionals.map(a => a.name).join(', ')})</span>`
                : '';
            return `<div><span>• ${p.name}</span>${extras}</div>`;
        }).join('');

        return `
            <div class="cardapio-cart-combo-details" style="font-size: 0.85rem; color: #666; margin-top: 4px; padding-left: 8px; border-left: 2px solid #eee;">
                ${items}
            </div>
        `;
    },

    /**
     * Animação do botão flutuante
     */
    triggerPulse: function () {
        const floatBtn = document.getElementById('floatingCart');
        if (floatBtn) {
            floatBtn.classList.add('pulse');
            setTimeout(() => floatBtn.classList.remove('pulse'), 500);
        }
    }
};

// ==========================================
// EXPOR VARIÁVEIS PARA COMPATIBILIDADE GLOBAL
// ==========================================
try {
    Object.defineProperty(window, 'cart', {
        get: () => CardapioCart.items,
        set: (val) => CardapioCart.items = val,
        configurable: true
    });
} catch (e) {
    console.warn('[CardapioCart] Falha ao definir getter global "cart":', e);
    window.cart = CardapioCart.items;
}

// Funções Legado Mapeadas
window.addToCartDirect = (id, name, price, img) => CardapioCart.addDirect(id, name, price, img);
window.addDrinkToCart = (id, name, price, img) => CardapioCart.addDrink(id, name, price, img);
window.addSauceToCart = (id, name, price) => CardapioCart.addSauce(id, name, price);
window.addComboToCart = (id, name, price, img) => CardapioCart.addCombo(id, name, price, img);
window.removeFromCart = (id) => CardapioCart.remove(id);
window.updateCartDisplay = () => CardapioCart.updateUI();
window.updateSuggestionsCartDisplay = () => CardapioCart.updateUI();

// Exposição do objeto principal
window.CardapioCart = CardapioCart;
