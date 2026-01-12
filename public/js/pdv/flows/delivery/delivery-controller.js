/**
 * Controller do fluxo Delivery
 * 
 * Coordena UI, State e Submit.
 */
const DeliveryController = {

    init() {
        DeliveryState.reset();
        this.bindEvents();
        console.log('[DELIVERY] Controller inicializado');
    },

    bindEvents() {
        const btnCriar = document.getElementById('btn-criar-delivery');
        if (btnCriar) {
            btnCriar.addEventListener('click', (e) => {
                e.preventDefault();
                DeliverySubmit.create();
            });
        }
    },

    setClient(clientId, clientName, phone) {
        DeliveryState.setClient(clientId, clientName, phone);
        this.updateUI();
    },

    setAddress(address, number, complement, neighborhood, reference) {
        DeliveryState.setAddress(address, number, complement, neighborhood, reference);
        this.updateUI();
    },

    setDeliveryFee(fee) {
        DeliveryState.setDeliveryFee(fee);
        this.updateUI();
    },

    addToCart(product) {
        DeliveryState.addItem(product);
        this.updateUI();
    },

    removeFromCart(productId) {
        DeliveryState.removeItem(productId);
        this.updateUI();
    },

    updateQuantity(productId, quantity) {
        DeliveryState.updateQuantity(productId, quantity);
        this.updateUI();
    },

    addPayment(method, amount) {
        DeliveryState.addPayment(method, amount);
        this.updateUI();
    },

    setDiscount(value) {
        DeliveryState.setDiscount(value);
        this.updateUI();
    },

    setObservation(text) {
        DeliveryState.setObservation(text);
    },

    setChangeFor(value) {
        DeliveryState.setChangeFor(value);
    },

    async updateOrderStatus(orderId, newStatus) {
        return await DeliverySubmit.updateStatus(orderId, newStatus);
    },

    updateUI() {
        const clientEl = document.getElementById('delivery-client-name');
        if (clientEl) {
            clientEl.textContent = DeliveryState.clientName || '-';
        }

        const addressEl = document.getElementById('delivery-address');
        if (addressEl) {
            addressEl.textContent = DeliveryState.address || '-';
        }

        const cartTotalEl = document.getElementById('delivery-cart-total');
        if (cartTotalEl) {
            cartTotalEl.textContent = 'R$ ' + DeliveryState.getCartTotal().toFixed(2);
        }

        const feeEl = document.getElementById('delivery-fee');
        if (feeEl) {
            feeEl.textContent = 'R$ ' + DeliveryState.deliveryFee.toFixed(2);
        }

        const totalEl = document.getElementById('delivery-grand-total');
        if (totalEl) {
            totalEl.textContent = 'R$ ' + DeliveryState.getGrandTotal().toFixed(2);
        }

        const countEl = document.getElementById('delivery-cart-count');
        if (countEl) {
            countEl.textContent = DeliveryState.cart.length;
        }
    },

    clear() {
        DeliveryState.reset();
        this.updateUI();
    }
};

window.DeliveryController = DeliveryController;
