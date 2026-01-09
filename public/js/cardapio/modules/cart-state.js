/**
 * CartState.js
 * Gerencia apenas os DADOS do carrinho (Model)
 */
const CartState = {
    items: [],

    // --- LOGIC ---

    add: function (options) {
        const item = this._createItem(options);
        this.items.push(item);
        return item;
    },

    remove: function (itemId) {
        this.items = this.items.filter(item => item.id !== itemId);
    },

    clear: function () {
        this.items = []; // Reseta o array
    },

    getTotals: function () {
        return {
            count: this.items.reduce((sum, item) => sum + item.quantity, 0),
            value: this.items.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0)
        };
    },

    getItems: function () {
        return this.items;
    },

    setItems: function (newItems) {
        this.items = newItems;
    },

    // --- FACTORY ---

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
    }
};

window.CartState = CartState;
