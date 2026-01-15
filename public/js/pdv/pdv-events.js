/**
 * PDV-EVENTS.JS - Gerenciador de Eventos (Delegation)
 * 
 * Centraliza os listeners de eventos do PDV para remover 'onclick' do HTML.
 * Padrão: data-action="nomeAcao" e data-payload="{json}"
 */

const PDVEvents = {
    init: function () {
        this.bindGlobalClicks();
        this.bindKeyboardShortcuts();
        console.log('PDV Events Initialized');
    },

    bindGlobalClicks: function () {
        document.addEventListener('click', (e) => {
            // PRODUCTS GRID
            const productCard = e.target.closest('.js-add-product');
            if (productCard) {
                this.handleAddProduct(productCard);
                return;
            }

            // GENERIC ACTIONS (data-action)
            const actionBtn = e.target.closest('[data-action]');
            if (actionBtn) {
                const action = actionBtn.dataset.action;
                this.handleAction(action, actionBtn);
            }
        });
    },

    bindKeyboardShortcuts: function () {
        // F2 is already handled in pdv-search.js, but we can add global shortcuts here if needed
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (window.closeExtrasModal) window.closeExtrasModal();
                if (window.closeFichaModal) window.closeFichaModal();
            }
        });
    },

    // ===========================================
    // HANDLERS
    // ===========================================

    handleAddProduct: function (el) {
        const id = el.dataset.id;
        const name = el.dataset.name; // Name strings are safe via dataset
        const price = parseFloat(el.dataset.price);
        const hasExtras = el.dataset.hasExtras === 'true';

        // Animação de clique
        el.classList.add('clicked');
        setTimeout(() => el.classList.remove('clicked'), 150);

        if (window.PDVCart) {
            if (hasExtras) {
                if (window.PDVExtras) {
                    PDVExtras.open(id, name, price);
                } else {
                    console.error('PDVExtras module not loaded');
                    alert('Erro: Módulo de adicionais não carregado');
                }
            } else {
                PDVCart.add(id, name, price);
            }

            // Toca som de bip se existir (opcional)
            if (window.playBeep) window.playBeep();
        } else {
            console.error('PDVCart module not loaded');
        }
    },

    handleAction: function (action, el) {
        switch (action) {
            // CART ACTIONS
            case 'cart-undo':
                PDVCart.undoClear();
                break;
            case 'cart-clear':
                if (confirm('Limpar carrinho?')) PDVCart.clear();
                break;
            case 'cart-remove-item':
                PDVCart.remove(el.dataset.id);
                break;

            // EXTRAS MODAL
            case 'extras-close':
                PDVExtras.close();
                break;
            case 'extras-confirm':
                PDVExtras.confirm();
                break;
            case 'extras-increase':
                PDVExtras.increaseQty();
                break;
            case 'extras-decrease':
                PDVExtras.decreaseQty();
                break;

            // TABLE/CLIENT ACTIONS
            case 'ficha-open':
                if (window.openFichaModal) window.openFichaModal(); // Legacy/Ficha.js
                break;
            case 'ficha-close':
                if (window.closeFichaModal) window.closeFichaModal();
                break;
            case 'ficha-print':
                if (window.printFicha) window.printFicha();
                break;

            // ITEM SAVED ACTIONS (Tables)
            case 'saved-item-delete':
                if (window.deleteSavedItem) window.deleteSavedItem(el.dataset.id, el.dataset.orderId);
                break;
            case 'table-cancel':
                if (window.cancelTableOrder) window.cancelTableOrder(el.dataset.tableId, el.dataset.orderId);
                break;

            // ORDER FLOW
            case 'order-save': // Salvar Comanda
                if (window.saveClientOrder) window.saveClientOrder();
                break;
            case 'order-include-paid': // Incluir no Pago
                if (window.includePaidOrderItems) window.includePaidOrderItems();
                break;
            case 'order-finalize-quick': // Balcão Finalizar
                if (window.finalizeSale) window.finalizeSale();
                break;
            case 'order-close-table': // Fechar Mesa
                if (window.fecharContaMesa) window.fecharContaMesa(el.dataset.tableId);
                break;
            case 'order-close-command': // Fechar Comanda
                if (window.fecharComanda) window.fecharComanda(el.dataset.orderId);
                break;

            // CLIENT MODAL
            case 'client-new':
                const m = document.getElementById('clientModal');
                if (m) {
                    document.body.appendChild(m);
                    m.style.display = 'flex';
                    m.style.zIndex = '9999';
                    setTimeout(() => document.getElementById('new_client_name')?.focus(), 50);
                }
                break;
            case 'client-clear':
                if (window.clearClient) window.clearClient();
                break;
        }
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    PDVEvents.init();
});
