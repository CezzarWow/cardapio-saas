/**
 * Estado ISOLADO do fluxo Mesa
 * 
 * NÃO compartilha com Balcão, Comanda ou Delivery.
 * Estado imutável via métodos (mesmo padrão do Balcão).
 */
const MesaState = {
    _tableId: null,
    _tableNumber: null,
    _orderId: null,
    _cart: [],
    _payments: [],
    _existingItems: [],
    _existingTotal: 0,
    _discount: 0,

    // ============ GETTERS ============

    get tableId() {
        return this._tableId;
    },

    get tableNumber() {
        return this._tableNumber;
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
     * Define mesa selecionada
     */
    setTable(tableId, tableNumber) {
        this._tableId = tableId;
        this._tableNumber = tableNumber;
    },

    /**
     * Define pedido existente (mesa já aberta)
     */
    setOrder(orderId, items, total) {
        this._orderId = orderId;
        this._existingItems = items || [];
        this._existingTotal = parseFloat(total) || 0;
    },

    /**
     * Limpa contexto de mesa
     */
    clearContext() {
        this._tableId = null;
        this._tableNumber = null;
        this._orderId = null;
        this._existingItems = [];
        this._existingTotal = 0;
    },

    // ============ MÉTODOS DE MUTAÇÃO - CARRINHO ============

    /**
     * Adiciona item ao carrinho
     */
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

    /**
     * Remove item do carrinho
     */
    removeItem(productId) {
        this._cart = this._cart.filter(item => item.id !== productId);
    },

    /**
     * Atualiza quantidade
     */
    updateQuantity(productId, quantity) {
        const item = this._cart.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, parseInt(quantity) || 1);
        }
    },

    /**
     * Limpa carrinho
     */
    clearCart() {
        this._cart = [];
    },

    // ============ MÉTODOS DE MUTAÇÃO - PAGAMENTOS ============

    /**
     * Adiciona pagamento
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
     */
    setDiscount(value) {
        this._discount = Math.max(0, parseFloat(value) || 0);
    },

    // ============ CÁLCULOS ============

    /**
     * Total dos novos itens no carrinho
     */
    getNewItemsTotal() {
        return this._cart.reduce((sum, item) =>
            sum + (item.price * item.quantity), 0);
    },

    /**
     * Total geral (existente + novos - desconto)
     */
    getGrandTotal() {
        return Math.max(0, this._existingTotal + this.getNewItemsTotal() - this._discount);
    },

    /**
     * Total pago
     */
    getPaidAmount() {
        return this._payments.reduce((sum, p) => sum + p.amount, 0);
    },

    /**
     * Verifica se pagamento cobre o total
     */
    isPaymentSufficient() {
        return this.getPaidAmount() >= this.getGrandTotal();
    },

    /**
     * Verifica se mesa está aberta (tem orderId)
     */
    isOpen() {
        return this._orderId !== null && this._orderId > 0;
    },

    // ============ RESET COMPLETO ============

    reset() {
        this._tableId = null;
        this._tableNumber = null;
        this._orderId = null;
        this._cart = [];
        this._payments = [];
        this._existingItems = [];
        this._existingTotal = 0;
        this._discount = 0;
    }
};

// Export
window.MesaState = MesaState;
