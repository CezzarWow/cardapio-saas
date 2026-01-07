/**
 * MODALS.JS - Orquestrador de Janelas Modais
 * 
 * Este arquivo inicializa o objeto CardapioModals e expõe as funções globais.
 * As implementações dos modais estão em arquivos separados:
 * - modals-product.js (Modal de Produto)
 * - modals-combo.js (Modal de Combo)
 * 
 * Dependências: Cart.js, Utils.js
 * ORDEM DE CARREGAMENTO:
 * 1. modals.js (este arquivo) - cria o objeto base
 * 2. modals-product.js - estende com funções de produto
 * 3. modals-combo.js - estende com funções de combo
 */

const CardapioModals = {
    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================
    init: function () {
        // Listeners globais de modal seriam configurados aqui ou no main.js
    },

    // ==========================================
    // MODAL DE SUGESTÕES
    // ==========================================
    openSuggestions: function () {
        const modal = document.getElementById('suggestionsModal');
        if (modal) {
            modal.classList.add('show');
            Utils.initIcons();
        }
    },

    closeSuggestions: function () {
        document.getElementById('suggestionsModal').classList.remove('show');
    },

    // ==========================================
    // MODAL DE CARRINHO
    // ==========================================
    openCart: function () {
        document.getElementById('cartModal').classList.add('show');
        Utils.initIcons();
    },

    closeCart: function () {
        document.getElementById('cartModal').classList.remove('show');
    },

    // ==========================================
    // FLUXO DE CHECKOUT
    // ==========================================
    goToCheckout: function () {
        this.closeCart();
        this.openSuggestions();
        CardapioCart.updateUI(); // Sincroniza visual
    }
};

// ==========================================
// EXPOR VARIÁVEIS PARA COMPATIBILIDADE
// ==========================================
window.CardapioModals = CardapioModals;

// Mapeamento Legado (aliases globais)
window.openProductModal = (id) => CardapioModals.openProduct(id);
window.closeProductModal = () => CardapioModals.closeProduct();
window.increaseQuantity = () => CardapioModals.increaseQty();
window.decreaseQuantity = () => CardapioModals.decreaseQty();
window.openCartModal = () => CardapioModals.openCart();
window.closeCartModal = () => CardapioModals.closeCart();
window.openSuggestionsModal = () => CardapioModals.openSuggestions();
window.closeSuggestionsModal = () => CardapioModals.closeSuggestions();
window.goToCheckout = () => CardapioModals.goToCheckout();

// Evento Global para Checkbox de Adicional (Delegado)
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('cardapio-additional-checkbox')) {
        const id = e.target.getAttribute('data-additional-id');
        const name = e.target.getAttribute('data-additional-name');
        const price = parseFloat(e.target.getAttribute('data-additional-price'));
        CardapioModals.toggleAdditional(id, name, price, e.target.checked);
    }
});

// Ação de adicionar do modal
window.addToCart = () => CardapioModals.addToCartAction();
