/**
 * CART.JS - Gerenciamento do Carrinho de Compras (Refatorado)
 * Dependências: Utils.js, CartState.js, CartView.js
 */

const CardapioCart = {

    // Proxy para acessar itens do State
    get items() {
        return CartState.items;
    },

    // Setter para compatibilidade
    set items(val) {
        CartState.setItems(val);
    },

    // ==========================================
    // MÉTODOS PÚBLICOS - ADICIONAR
    // ==========================================

    /**
     * Adicionar item (geral)
     */
    add: function (options) {
        CartState.add(options);
        this.updateUI();
    },

    /**
     * Adicionar direto (sem modal)
     */
    addDirect: function (id, name, price) {
        this.add({ id, name, price });
    },

    /**
     * Adicionar bebida
     */
    addDrink: function (id, name, price) {
        this.add({ id, name, price });
        CartView.pulseButton(`.suggestion-drink-btn[data-id="${id}"]`);
    },

    /**
     * Adicionar molho
     */
    addSauce: function (id, name, price) {
        this.add({
            productId: 'sauce_' + id,
            name: 'Molho: ' + name,
            price
        });
        CartView.pulseButton(`.suggestion-sauce-btn[data-id="${id}"]`);
    },

    /**
     * Adicionar combo
     */
    addCombo: function (id, name, price) {
        this.add({
            productId: 'combo_' + id,
            name,
            price,
            isCombo: true
        });
    },

    // ==========================================
    // MÉTODOS PÚBLICOS - GERENCIAR
    // ==========================================

    /**
     * Remover item
     */
    remove: function (itemId) {
        CartState.remove(itemId);
        this.updateUI();
    },

    /**
     * Limpar tudo
     */
    clear: function () {
        CartState.clear();
        this.updateUI();
    },

    /**
     * Calcular totais
     */
    getTotals: function () {
        return CartState.getTotals();
    },

    // ==========================================
    // UI - ATUALIZAÇÃO
    // ==========================================

    /**
     * Atualizar Interface via CartView
     */
    updateUI: function () {
        CartView.update(CartState.getTotals(), CartState.getItems());
    }
};

// ==========================================
// EXPOR GLOBALMENTE
// ==========================================

// Mantém compatibilidade com window.cart (Legado)
try {
    Object.defineProperty(window, 'cart', {
        get: () => CartState.items,
        set: (val) => CartState.setItems(val),
        configurable: true
    });
} catch (e) {
    window.cart = CartState.items;
}

// Funções Legado Mapeadas
window.addToCartDirect = (id, name, price, img) => CardapioCart.addDirect(id, name, price);
window.addDrinkToCart = (id, name, price, img) => CardapioCart.addDrink(id, name, price);
window.addSauceToCart = (id, name, price) => CardapioCart.addSauce(id, name, price);
window.addComboToCart = (id, name, price, img) => CardapioCart.addCombo(id, name, price);
window.removeFromCart = (id) => CardapioCart.remove(id);
window.updateCartDisplay = () => CardapioCart.updateUI();
window.updateSuggestionsCartDisplay = () => CardapioCart.updateUI();

// Exposição do objeto principal
window.CardapioCart = CardapioCart;
