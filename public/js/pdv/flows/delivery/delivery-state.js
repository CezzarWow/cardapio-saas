/**
 * Estado ISOLADO do fluxo Delivery
 * 
 * NÃO compartilha com Balcão, Mesa ou Comanda.
 * DELIVERY é pedido independente, não é conta aberta.
 */
const DeliveryState = {
    _clientId: null,
    _clientName: null,
    _phone: null,
    _address: null,
    _addressNumber: null,
    _complement: null,
    _neighborhood: null,
    _reference: null,
    _orderId: null,
    _cart: [],
    _payments: [],
    _deliveryFee: 0,
    _discount: 0,
    _observation: null,
    _changeFor: null,

    // ============ GETTERS ============

    get clientId() { return this._clientId; },
    get clientName() { return this._clientName; },
    get phone() { return this._phone; },
    get address() { return this._address; },
    get addressNumber() { return this._addressNumber; },
    get complement() { return this._complement; },
    get neighborhood() { return this._neighborhood; },
    get reference() { return this._reference; },
    get orderId() { return this._orderId; },
    get cart() { return [...this._cart]; },
    get payments() { return [...this._payments]; },
    get deliveryFee() { return this._deliveryFee; },
    get discount() { return this._discount; },
    get observation() { return this._observation; },
    get changeFor() { return this._changeFor; },

    // ============ SETTERS DE CONTEXTO ============

    setClient(clientId, clientName, phone) {
        this._clientId = clientId;
        this._clientName = clientName;
        this._phone = phone;
    },

    setAddress(address, number, complement, neighborhood, reference) {
        this._address = address;
        this._addressNumber = number;
        this._complement = complement;
        this._neighborhood = neighborhood;
        this._reference = reference;
    },

    setDeliveryFee(fee) {
        this._deliveryFee = Math.max(0, parseFloat(fee) || 0);
    },

    setDiscount(value) {
        this._discount = Math.max(0, parseFloat(value) || 0);
    },

    setObservation(text) {
        this._observation = text;
    },

    setChangeFor(value) {
        this._changeFor = parseFloat(value) || null;
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

    // ============ CÁLCULOS ============

    getCartTotal() {
        return this._cart.reduce((sum, item) =>
            sum + (item.price * item.quantity), 0);
    },

    getGrandTotal() {
        return Math.max(0, this.getCartTotal() + this._deliveryFee - this._discount);
    },

    getPaidAmount() {
        return this._payments.reduce((sum, p) => sum + p.amount, 0);
    },

    isPaid() {
        return this.getPaidAmount() >= this.getGrandTotal();
    },

    hasAddress() {
        return !!this._address;
    },

    hasClient() {
        return !!(this._clientName || this._clientId);
    },

    // ============ RESET ============

    reset() {
        this._clientId = null;
        this._clientName = null;
        this._phone = null;
        this._address = null;
        this._addressNumber = null;
        this._complement = null;
        this._neighborhood = null;
        this._reference = null;
        this._orderId = null;
        this._cart = [];
        this._payments = [];
        this._deliveryFee = 0;
        this._discount = 0;
        this._observation = null;
        this._changeFor = null;
    }
};

window.DeliveryState = DeliveryState;
