/**
 * Controller do fluxo Balcão
 * 
 * Coordena UI, State e Submit.
 * NÃO decide fluxo, apenas executa operações Balcão.
 */
const BalcaoController = {

    /**
     * Inicializa o fluxo Balcão
     */
    init() {
        BalcaoState.reset();
        this.bindEvents();
        console.log('[BALCAO] Controller inicializado');
    },

    /**
     * Liga eventos aos elementos do DOM
     */
    bindEvents() {
        // Botão Finalizar Balcão
        const btnFinalizar = document.getElementById('btn-finalizar-balcao');
        if (btnFinalizar) {
            btnFinalizar.addEventListener('click', (e) => {
                e.preventDefault();
                BalcaoSubmit.submit();
            });
        }
    },

    /**
     * Adiciona produto ao carrinho
     * @param {Object} product {id, name, price, quantity?}
     */
    addToCart(product) {
        BalcaoState.addItem(product);
        this.updateUI();
    },

    /**
     * Remove produto do carrinho
     * @param {number} productId
     */
    removeFromCart(productId) {
        BalcaoState.removeItem(productId);
        this.updateUI();
    },

    /**
     * Atualiza quantidade
     * @param {number} productId
     * @param {number} quantity
     */
    updateQuantity(productId, quantity) {
        BalcaoState.updateQuantity(productId, quantity);
        this.updateUI();
    },

    /**
     * Adiciona pagamento
     * @param {string} method (pix, dinheiro, credito, debito)
     * @param {number} amount
     */
    addPayment(method, amount) {
        BalcaoState.addPayment(method, amount);
        this.updateUI();
    },

    /**
     * Define desconto
     * @param {number} value
     */
    setDiscount(value) {
        BalcaoState.setDiscount(value);
        this.updateUI();
    },

    /**
     * Atualiza elementos visuais
     */
    updateUI() {
        // Total
        const totalEl = document.getElementById('balcao-total');
        if (totalEl) {
            totalEl.textContent = 'R$ ' + BalcaoState.getTotal().toFixed(2);
        }

        // Subtotal
        const subtotalEl = document.getElementById('balcao-subtotal');
        if (subtotalEl) {
            subtotalEl.textContent = 'R$ ' + BalcaoState.getSubtotal().toFixed(2);
        }

        // Quantidade de itens
        const countEl = document.getElementById('balcao-item-count');
        if (countEl) {
            countEl.textContent = BalcaoState.cart.length;
        }

        // Valor pago
        const paidEl = document.getElementById('balcao-paid-amount');
        if (paidEl) {
            paidEl.textContent = 'R$ ' + BalcaoState.getPaidAmount().toFixed(2);
        }
    },

    /**
     * Limpa tudo
     */
    clear() {
        BalcaoState.reset();
        this.updateUI();
    }
};

// Export para uso global
window.BalcaoController = BalcaoController;

// Auto-init quando DOM pronto (opcional, pode ser chamado manualmente)
// document.addEventListener('DOMContentLoaded', () => BalcaoController.init());
