/**
 * CART.JS - Gerenciamento do Carrinho de Compras
 * Dependências: Utils.js
 */

const CardapioCart = {
    items: [],

    // Adicionar item (geral)
    add: function (item) {
        this.items.push(item);
        this.updateUI();
        this.triggerPulse();
    },

    // Adicionar direto (sem modal)
    addDirect: function (id, name, price, image) {
        const item = {
            id: Date.now() + Math.random(),
            productId: id,
            name: name,
            basePrice: price,
            quantity: 1,
            additionals: [],
            observation: '',
            unitPrice: price
        };
        this.add(item);
    },

    // Adicionar bebida
    addDrink: function (id, name, price, image) {
        const item = {
            id: Date.now() + Math.random(),
            productId: id,
            name: name,
            basePrice: price,
            quantity: 1,
            additionals: [],
            observation: '',
            unitPrice: price
        };
        this.add(item);

        // Feedback visual no botão específico
        const btn = document.querySelector(`.suggestion-drink-btn[data-id="${id}"]`);
        if (btn) {
            btn.classList.add('btn-pulse');
            setTimeout(() => btn.classList.remove('btn-pulse'), 300);
        }
    },

    // Adicionar molho
    addSauce: function (id, name, price) {
        const item = {
            id: Date.now() + Math.random(),
            productId: 'sauce_' + id,
            name: 'Molho: ' + name,
            basePrice: price,
            quantity: 1,
            additionals: [],
            observation: '',
            unitPrice: price
        };
        this.add(item);

        // Feedback visual
        const btn = document.querySelector(`.suggestion-sauce-btn[data-id="${id}"]`);
        if (btn) {
            btn.classList.add('btn-pulse');
            setTimeout(() => btn.classList.remove('btn-pulse'), 300);
        }
    },

    // Remover item
    remove: function (itemId) {
        this.items = this.items.filter(item => item.id !== itemId);
        this.updateUI();
    },

    // Limpar tudo
    clear: function () {
        this.items.length = 0; // Mantém a referência do array
        this.updateUI();
    },

    // Calcular totais
    getTotals: function () {
        return {
            count: this.items.reduce((sum, item) => sum + item.quantity, 0),
            value: this.items.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0)
        };
    },

    // Atualizar Interface
    updateUI: function () {
        const totals = this.getTotals();

        // 1. Atualizar Botão Flutuante (Principal)
        const cartTotalEl = document.getElementById('cartTotal');
        if (cartTotalEl) cartTotalEl.textContent = Utils.formatCurrency(totals.value);

        const floatBtn = document.getElementById('floatingCart');
        if (floatBtn) {
            if (totals.count > 0) floatBtn.classList.add('show');
            else floatBtn.classList.remove('show');
        }

        // 2. Atualizar Modal do Carrinho
        const cartModalTotal = document.getElementById('cartModalTotal');
        if (cartModalTotal) cartModalTotal.textContent = Utils.formatCurrency(totals.value);

        // 3. Atualizar Botão Flutuante (Sugestões)
        const suggTotal = document.getElementById('suggestionsCartTotal');
        if (suggTotal) suggTotal.textContent = Utils.formatCurrency(totals.value);

        // 4. Renderizar Lista
        this.renderList();
    },

    renderList: function () {
        const container = document.getElementById('cartItemsContainer');
        if (!container) return;

        if (this.items.length === 0) {
            container.innerHTML = `
                <div class="cardapio-cart-empty">
                    <div class="cardapio-cart-empty-icon">
                        <i data-lucide="shopping-bag" size="48"></i>
                    </div>
                    <p>Seu carrinho está vazio</p>
                </div>
            `;
            Utils.initIcons();
            return;
        }

        let html = '';
        this.items.forEach(item => {
            html += `
                <div class="cardapio-cart-item">
                    <div class="cardapio-cart-item-info">
                        <p class="cardapio-cart-item-name">${item.quantity}x ${item.name}</p>
                        ${item.additionals.length > 0 ? `
                            <p class="cardapio-cart-item-additionals">
                                Extras: ${item.additionals.map(a => a.name).join(', ')}
                            </p>
                        ` : ''}
                        ${item.observation ? `
                            <p class="cardapio-cart-item-obs">Obs: ${item.observation}</p>
                        ` : ''}
                        <button class="cardapio-cart-item-remove" onclick="CardapioCart.remove(${item.id})">
                            Remover
                        </button>
                    </div>
                    <span class="cardapio-cart-item-price">
                        ${Utils.formatCurrency(item.unitPrice * item.quantity)}
                    </span>
                </div>
            `;
        });

        container.innerHTML = html;
        Utils.initIcons();
    },

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
// Mapeamento seguro para evitar erros de redefinição
try {
    // Tenta usar getter/setter para manter sincronia perfeita
    Object.defineProperty(window, 'cart', {
        get: () => CardapioCart.items,
        set: (val) => CardapioCart.items = val,
        configurable: true
    });
} catch (e) {
    console.warn('[CardapioCart] Falha ao definir getter global "cart":', e);
    // Fallback: Apenas atribui (pode perder sincronia se CardapioCart trocar a instância do array)
    window.cart = CardapioCart.items;
}

// Funções Legado Mapeadas
window.addToCartDirect = (id, name, price, img) => CardapioCart.addDirect(id, name, price, img);
window.addDrinkToCart = (id, name, price, img) => CardapioCart.addDrink(id, name, price, img);
window.addSauceToCart = (id, name, price) => CardapioCart.addSauce(id, name, price);
window.removeFromCart = (id) => CardapioCart.remove(id);
window.updateCartDisplay = () => CardapioCart.updateUI();
window.updateSuggestionsCartDisplay = () => CardapioCart.updateUI();

// Exposição do objeto principal
window.CardapioCart = CardapioCart;
