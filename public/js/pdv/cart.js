/**
 * CART.JS - Orquestrador do Carrinho PDV
 * 
 * Este arquivo inicializa o objeto PDVCart e expõe as funções globais.
 * As implementações estão em arquivos separados:
 * - cart-core.js (Lógica de manipulação de itens)
 * - cart-ui.js (Renderização do carrinho)
 * - cart-extras-modal.js (Modal de adicionais)
 * 
 * Dependências: PDVState
 * ORDEM DE CARREGAMENTO:
 * 1. cart.js (este arquivo) - cria o objeto base
 * 2. cart-core.js - estende com funções de manipulação
 * 3. cart-ui.js - estende com funções de UI
 * 4. cart-extras-modal.js - modal de extras
 */

const PDVCart = {
    // Estado será inicializado pelo cart-core.js
    items: [],
    backupItems: [],

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================
    init: function () {
        // Listeners globais seriam configurados aqui
    }
};

// ==========================================
// EXPOR GLOBALMENTE
// ==========================================
window.PDVCart = PDVCart;

// ==========================================
// NAMESPACE PDV
// ==========================================
window.PDV = window.PDV || {};

/**
 * Função Global para clique no produto (chamada pelo HTML onclick)
 */
window.PDV.clickProduct = function (id, name, price, hasExtras, encodedExtras = '[]') {
    const hasExtrasBool = (hasExtras === true || hasExtras === 'true' || hasExtras === 1 || hasExtras === '1');
    if (hasExtrasBool) {
        pendingProduct = { id, name, price: parseFloat(price) };
        openExtrasModal(id);
    } else {
        PDVCart.add(id, name, parseFloat(price));
    }
};

// ==========================================
// COMPATIBILIDADE LEGADA
// ==========================================

// Mapeia array global 'cart' para PDVCart.items
window.cart = PDVCart.items;
Object.defineProperty(window, 'cart', {
    get: () => PDVCart.items,
    set: (val) => PDVCart.setItems(val)
});

// Funções Globais
window.addToCart = function (id, name, price, hasExtras = false) {
    if (hasExtras) {
        pendingProduct = { id, name, price };
        openExtrasModal(id);
    } else {
        PDVCart.add(id, name, price);
    }
};

window.removeFromCart = (id) => {
    const item = PDVCart.items.find(i => i.id === id);
    if (item) PDVCart.remove(item.cartItemId);
};

window.updateCartUI = () => PDVCart.updateUI();
window.calculateTotal = () => PDVCart.calculateTotal();
window.clearCart = () => PDVCart.clear();

// ==========================================
// INICIALIZAÇÃO
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // Carrega carrinho recuperado do PHP se existir
    if (typeof recoveredCart !== 'undefined' && recoveredCart.length > 0) {
        PDVCart.setItems(recoveredCart);
    }
});
