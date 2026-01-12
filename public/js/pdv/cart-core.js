/**
 * CART-CORE.JS - Lógica Core do Carrinho
 * Dependências: PDVCart (cart.js), PDVState
 * 
 * Este módulo estende PDVCart com as funções de manipulação de itens.
 */

(function () {
    'use strict';

    // ==========================================
    // ESTADO
    // ==========================================
    PDVCart.items = [];
    PDVCart.backupItems = [];

    // ==========================================
    // SET ITEMS (Carregar do PHP)
    // ==========================================
    PDVCart.setItems = function (newItems) {
        if (!newItems) newItems = [];
        // Migração simples para garantir cartItemId se vier do PHP
        this.items = newItems.map(item => ({
            ...item,
            cartItemId: item.cartItemId || ('legacy_' + item.id + '_' + Math.random()),
            extras: item.extras || [],
            price: parseFloat(item.price) // Garante float
        }));
        this.updateUI();
    };

    // ==========================================
    // ADD ITEM
    // ==========================================
    PDVCart.add = function (id, name, price, quantity = 1, extras = []) {
        this.backupItems = [];
        const numId = parseInt(id);
        const floatPrice = parseFloat(price);

        // Gera chave única baseada nos extras para agrupar iguais
        const extrasKey = JSON.stringify(extras.sort((a, b) => a.id - b.id));

        const existing = this.items.find(item =>
            item.id === numId &&
            JSON.stringify(item.extras.sort((a, b) => a.id - b.id)) === extrasKey
        );

        if (existing) {
            existing.quantity += quantity;
        } else {
            this.items.push({
                cartItemId: 'item_' + Date.now() + '_' + Math.random(),
                id: numId,
                name: name,
                price: floatPrice,
                quantity: quantity,
                extras: extras
            });
        }
        this.updateUI();
    };

    // ==========================================
    // REMOVE ITEM
    // ==========================================
    PDVCart.remove = function (cartItemId) {
        const index = this.items.findIndex(item => item.cartItemId === cartItemId);

        if (index > -1) {
            const item = this.items[index];
            if (item.quantity > 1) {
                item.quantity--;
            } else {
                this.items.splice(index, 1);
            }
        }
        this.updateUI();
    };

    // ==========================================
    // CLEAR CART
    // ==========================================
    PDVCart.clear = function () {
        if (this.items.length > 0) {
            // Salva backup antes de limpar
            this.backupItems = JSON.parse(JSON.stringify(this.items));
        }
        this.items = [];

        // VERIFICA SE ESTAMOS EM CONTEXTO TRAVADO (Mesa ou Comanda Aberta)
        const currentOrderIdInput = document.getElementById('current_order_id');
        const isContextLocked = currentOrderIdInput && currentOrderIdInput.value && currentOrderIdInput.value !== '';

        if (!isContextLocked) {
            // SÓ LIMPA CLIENTE/MESA SE NÃO ESTIVER EM UMA COMANDA JÁ ABERTA

            // Limpa Mesa/Cliente selecionado (sem focar para não abrir dropdown)
            const clientId = document.getElementById('current_client_id');
            const tableId = document.getElementById('current_table_id');
            const clientName = document.getElementById('current_client_name');

            if (clientId) clientId.value = '';
            if (tableId) tableId.value = '';
            if (clientName) clientName.value = '';

            const selectedArea = document.getElementById('selected-client-area');
            const searchArea = document.getElementById('client-search-area');
            const searchInput = document.getElementById('client-search');
            const results = document.getElementById('client-results');

            if (selectedArea) selectedArea.style.display = 'none';
            if (searchArea) searchArea.style.display = 'flex';
            if (searchInput) searchInput.value = '';
            if (results) results.style.display = 'none';

            // Esconde botão Salvar Comanda se existir
            const btnSave = document.getElementById('btn-save-command');
            if (btnSave) btnSave.style.display = 'none';

            // Reseta estado do PDV
            if (typeof PDVState !== 'undefined') {
                PDVState.set({ modo: 'balcao', mesaId: null, clienteId: null });
            }
        }

        // Reseta o botão Finalizar para estado padrão (sempre, pois carrinho ficou vazio)
        const btn = document.getElementById('btn-finalizar');
        if (btn) {
            btn.innerText = 'Finalizar';
            btn.style.backgroundColor = '';
        }

        this.updateUI();
    };

    // ==========================================
    // UNDO CLEAR
    // ==========================================
    PDVCart.undoClear = function () {
        if (this.backupItems.length > 0) {
            this.items = JSON.parse(JSON.stringify(this.backupItems));
            this.backupItems = []; // Consome o backup
            this.updateUI();
        }
    };

    // ==========================================
    // CALCULATE TOTAL
    // ==========================================
    PDVCart.calculateTotal = function () {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    };

})();
