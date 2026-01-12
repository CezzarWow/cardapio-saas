/**
 * Estado ISOLADO do fluxo Comanda
 * 
 * NÃO compartilha com Balcão, Mesa ou Delivery.
 * COMANDA é vinculada obrigatoriamente a cliente.
 */
const ComandaState = {
    _clientId: null,
    _clientName: null,
    _orderId: null,
    _cart: [],
    _payments: [],
    _existingItems: [],
    _existingTotal: 0,
    _discount: 0,

    // ============ GETTERS ============

    get clientId() {
        return this._clientId;
    },

    get clientName() {
        return this._clientName;
    },

    get orderId() {
        return this._orderId;
    },

    get cart() {
        return [...this._cart];
    },

    get payments() {
        return [...this._payments];
    },

    get existingItems() {
        return [...this._existingItems];
    },

    get existingTotal() {
        return this._existingTotal;
    },

    get discount() {
        return this._discount;
    },

    // ============ SETTERS DE CONTEXTO ============

    /**
     * Define cliente selecionado
     */
    setClient(clientId, clientName) {
        this._clientId = clientId;
        this._clientName = clientName;
    },

    /**
     * Define comanda existente
     */
    setOrder(orderId, items, total) {
        this._orderId = orderId;
        this._existingItems = items || [];
        this._existingTotal = parseFloat(total) || 0;
    },

    /**
     * Limpa contexto
     */
    clearContext() {
        this._clientId = null;
        this._clientName = null;
        this._orderId = null;
        this._existingItems = [];
        this._existingTotal = 0;
    },

    // ============ MÉTODOS DE MUTAÇÃO - CARRINHO ============

    addItem(product) {
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

    removeItem(productId) {
        this._cart = this._cart.filter(item => item.id !== productId);
    },

    updateQuantity(productId, quantity) {
        const item = this._cart.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, parseInt(quantity) || 1);
        }
    },

    clearCart() {
        this._cart = [];
    },

    // ============ MÉTODOS DE MUTAÇÃO - PAGAMENTOS ============

    addPayment(method, amount) {
        this._payments.push({
            method: method,
            amount: parseFloat(amount) || 0
        });
    },

    clearPayments() {
        this._payments = [];
    },

    setDiscount(value) {
        this._discount = Math.max(0, parseFloat(value) || 0);
    },

    // ============ CÁLCULOS ============

    getNewItemsTotal() {
        return this._cart.reduce((sum, item) =>
            sum + (item.price * item.quantity), 0);
    },

    getGrandTotal() {
        return Math.max(0, this._existingTotal + this.getNewItemsTotal() - this._discount);
    },

    getPaidAmount() {
        return this._payments.reduce((sum, p) => sum + p.amount, 0);
    },

    isPaymentSufficient() {
        return this.getPaidAmount() >= this.getGrandTotal();
    },

    isOpen() {
        return this._orderId !== null && this._orderId > 0;
    },

    hasClient() {
        return this._clientId !== null && this._clientId > 0;
    },

    // ============ RESET ============

    reset() {
        this._clientId = null;
        this._clientName = null;
        this._orderId = null;
        this._cart = [];
        this._payments = [];
        this._existingItems = [];
        this._existingTotal = 0;
        this._discount = 0;
    }
};

window.ComandaState = ComandaState;
