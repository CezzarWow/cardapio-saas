/**
 * Controller do fluxo Mesa
 * 
 * Coordena UI, State e Submit.
 * NÃO decide fluxo, apenas executa operações Mesa.
 */
const MesaController = {

    /**
     * Inicializa o fluxo Mesa
     */
    init() {
        MesaState.reset();
        this.bindEvents();
        console.log('[MESA] Controller inicializado');
    },

    /**
     * Liga eventos aos elementos do DOM
     */
    bindEvents() {
        // Botão Abrir Mesa
        const btnAbrir = document.getElementById('btn-abrir-mesa');
        if (btnAbrir) {
            btnAbrir.addEventListener('click', (e) => {
                e.preventDefault();
                MesaSubmit.open();
            });
        }

        // Botão Adicionar Itens
        const btnAdd = document.getElementById('btn-add-mesa-items');
        if (btnAdd) {
            btnAdd.addEventListener('click', (e) => {
                e.preventDefault();
                MesaSubmit.addItems();
            });
        }

        // Botão Fechar Mesa
        const btnFechar = document.getElementById('btn-fechar-mesa');
        if (btnFechar) {
            btnFechar.addEventListener('click', (e) => {
                e.preventDefault();
                MesaSubmit.close();
            });
        }
    },

    /**
     * Seleciona mesa
     */
    selectTable(tableId, tableNumber, orderId = null, existingItems = [], existingTotal = 0) {
        MesaState.setTable(tableId, tableNumber);

        if (orderId) {
            MesaState.setOrder(orderId, existingItems, existingTotal);
        }

        this.updateUI();
    },

    /**
     * Adiciona produto ao carrinho
     */
    addToCart(product) {
        MesaState.addItem(product);
        this.updateUI();
    },

    /**
     * Remove produto do carrinho
     */
    removeFromCart(productId) {
        MesaState.removeItem(productId);
        this.updateUI();
    },

    /**
     * Atualiza quantidade
     */
    updateQuantity(productId, quantity) {
        MesaState.updateQuantity(productId, quantity);
        this.updateUI();
    },

    /**
     * Adiciona pagamento
     */
    addPayment(method, amount) {
        MesaState.addPayment(method, amount);
        this.updateUI();
    },

    /**
     * Define desconto
     */
    setDiscount(value) {
        MesaState.setDiscount(value);
        this.updateUI();
    },

    /**
     * Atualiza elementos visuais
     */
    updateUI() {
        // Número da mesa
        const mesaNumEl = document.getElementById('mesa-selected-number');
        if (mesaNumEl) {
            mesaNumEl.textContent = MesaState.tableNumber || '-';
        }

        // Status (aberta ou nova)
        const statusEl = document.getElementById('mesa-status');
        if (statusEl) {
            statusEl.textContent = MesaState.isOpen() ? 'Conta Aberta' : 'Nova';
        }

        // Total existente
        const existingEl = document.getElementById('mesa-existing-total');
        if (existingEl) {
            existingEl.textContent = 'R$ ' + MesaState.existingTotal.toFixed(2);
        }

        // Total novos itens
        const newItemsEl = document.getElementById('mesa-new-items-total');
        if (newItemsEl) {
            newItemsEl.textContent = 'R$ ' + MesaState.getNewItemsTotal().toFixed(2);
        }

        // Total geral
        const totalEl = document.getElementById('mesa-grand-total');
        if (totalEl) {
            totalEl.textContent = 'R$ ' + MesaState.getGrandTotal().toFixed(2);
        }

        // Valor pago
        const paidEl = document.getElementById('mesa-paid-amount');
        if (paidEl) {
            paidEl.textContent = 'R$ ' + MesaState.getPaidAmount().toFixed(2);
        }

        // Quantidade de itens no carrinho
        const countEl = document.getElementById('mesa-cart-count');
        if (countEl) {
            countEl.textContent = MesaState.cart.length;
        }
    },

    /**
     * Limpa tudo
     */
    clear() {
        MesaState.reset();
        this.updateUI();
    }
};

// Export
window.MesaController = MesaController;
