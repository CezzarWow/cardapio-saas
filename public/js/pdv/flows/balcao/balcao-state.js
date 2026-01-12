/**
 * Estado ISOLADO do fluxo Balcão
 * 
 * NÃO compartilha com Mesa, Comanda ou Delivery.
 * Estado imutável via métodos (ajuste obrigatório #6).
 */
const BalcaoState = {
    _cart: [],
    _payments: [],
    _discount: 0,

    // ============ GETTERS ============

    get cart() {
        return [...this._cart]; // Cópia para evitar mutação externa
    },

    get payments() {
        return [...this._payments];
    },

    get discount() {
        return this._discount;
    },

    // ============ MÉTODOS DE MUTAÇÃO ============

    /**
     * Adiciona item ao carrinho
     * @param {Object} product {id, name, price, quantity}
     */
    addItem(product) {
        // Verificar se já existe
        const existing = this._cart.find(item => item.id === product.id);

        if (existing) {
            existing.quantity += (product.quantity || 1);
        } else {
            this._cart.push({
                id: product.id,
                name: product.name || 'Produto',
                price: parseFloat(product.price) || 0,
                quantity: parseInt(product.quantity) || 1
            });
        }
    },

    /**
     * Remove item do carrinho
     * @param {number} productId
     */
    removeItem(productId) {
        this._cart = this._cart.filter(item => item.id !== productId);
    },

    /**
     * Atualiza quantidade de item
     * @param {number} productId
     * @param {number} quantity
     */
    updateQuantity(productId, quantity) {
        const item = this._cart.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, parseInt(quantity) || 1);
        }
    },

    /**
     * Adiciona pagamento
     * @param {string} method (pix, dinheiro, credito, debito)
     * @param {number} amount
     */
    addPayment(method, amount) {
        this._payments.push({
            method: method,
            amount: parseFloat(amount) || 0
        });
    },

    /**
     * Remove todos os pagamentos
     */
    clearPayments() {
        this._payments = [];
    },

    /**
     * Define desconto
     * @param {number} value
     */
    setDiscount(value) {
        this._discount = Math.max(0, parseFloat(value) || 0);
    },

    // ============ CÁLCULOS ============

    /**
     * Calcula subtotal (sem desconto)
     */
    getSubtotal() {
        return this._cart.reduce((sum, item) =>
            sum + (item.price * item.quantity), 0);
    },

    /**
     * Calcula total final
     */
    getTotal() {
        return Math.max(0, this.getSubtotal() - this._discount);
    },

    /**
     * Calcula total já pago
     */
    getPaidAmount() {
        return this._payments.reduce((sum, p) => sum + p.amount, 0);
    },

    /**
     * Verifica se pagamento é suficiente
     */
    isPaymentSufficient() {
        return this.getPaidAmount() >= this.getTotal();
    },

    // ============ RESET ============

    /**
     * Limpa todo o estado
     */
    reset() {
        this._cart = [];
        this._payments = [];
        this._discount = 0;
    }
};

// Export para uso global
window.BalcaoState = BalcaoState;
