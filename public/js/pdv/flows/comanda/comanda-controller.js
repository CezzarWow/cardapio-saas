/**
 * Controller do fluxo Comanda
 * 
 * Coordena UI, State e Submit.
 */
const ComandaController = {

    init() {
        ComandaState.reset();
        this.bindEvents();
        console.log('[COMANDA] Controller inicializado');
    },

    bindEvents() {
        const btnAbrir = document.getElementById('btn-abrir-comanda');
        if (btnAbrir) {
            btnAbrir.addEventListener('click', (e) => {
                e.preventDefault();
                ComandaSubmit.open();
            });
        }

        const btnAdd = document.getElementById('btn-add-comanda-items');
        if (btnAdd) {
            btnAdd.addEventListener('click', (e) => {
                e.preventDefault();
                ComandaSubmit.addItems();
            });
        }

        const btnFechar = document.getElementById('btn-fechar-comanda');
        if (btnFechar) {
            btnFechar.addEventListener('click', (e) => {
                e.preventDefault();
                ComandaSubmit.close();
            });
        }
    },

    selectClient(clientId, clientName, orderId = null, existingItems = [], existingTotal = 0) {
        ComandaState.setClient(clientId, clientName);

        if (orderId) {
            ComandaState.setOrder(orderId, existingItems, existingTotal);
        }

        this.updateUI();
    },

    addToCart(product) {
        ComandaState.addItem(product);
        this.updateUI();
    },

    removeFromCart(productId) {
        ComandaState.removeItem(productId);
        this.updateUI();
    },

    updateQuantity(productId, quantity) {
        ComandaState.updateQuantity(productId, quantity);
        this.updateUI();
    },

    addPayment(method, amount) {
        ComandaState.addPayment(method, amount);
        this.updateUI();
    },

    setDiscount(value) {
        ComandaState.setDiscount(value);
        this.updateUI();
    },

    updateUI() {
        const clientEl = document.getElementById('comanda-client-name');
        if (clientEl) {
            clientEl.textContent = ComandaState.clientName || '-';
        }

        const statusEl = document.getElementById('comanda-status');
        if (statusEl) {
            statusEl.textContent = ComandaState.isOpen() ? 'Aberta' : 'Nova';
        }

        const existingEl = document.getElementById('comanda-existing-total');
        if (existingEl) {
            existingEl.textContent = 'R$ ' + ComandaState.existingTotal.toFixed(2);
        }

        const newItemsEl = document.getElementById('comanda-new-items-total');
        if (newItemsEl) {
            newItemsEl.textContent = 'R$ ' + ComandaState.getNewItemsTotal().toFixed(2);
        }

        const totalEl = document.getElementById('comanda-grand-total');
        if (totalEl) {
            totalEl.textContent = 'R$ ' + ComandaState.getGrandTotal().toFixed(2);
        }

        const paidEl = document.getElementById('comanda-paid-amount');
        if (paidEl) {
            paidEl.textContent = 'R$ ' + ComandaState.getPaidAmount().toFixed(2);
        }

        const countEl = document.getElementById('comanda-cart-count');
        if (countEl) {
            countEl.textContent = ComandaState.cart.length;
        }
    },

    clear() {
        ComandaState.reset();
        this.updateUI();
    }
};

window.ComandaController = ComandaController;
