/**
 * PDV-EVENTS.JS - Gerenciador de Eventos (Delegation)
 * 
 * Centraliza os listeners de eventos do PDV para remover 'onclick' do HTML.
 * Padrão: data-action="nomeAcao" e data-payload="{json}"
 * 
 * [REFACTOR SPA] Agora suporta init() idempotente com removeEventListener.
 */

const PDVEvents = {
    // Armazena referência para desvincular eventos
    clickHandler: null,
    keydownHandler: null,

    init: function () {
        this.bindGlobalClicks();
        this.bindKeyboardShortcuts();
    },

    bindGlobalClicks: function () {
        // 1. Remove listener anterior se existir (Limpeza)
        if (this.clickHandler) {
            document.removeEventListener('click', this.clickHandler);
        }

        // 2. Cria nova referência vinculada (Bind)
        this.clickHandler = (e) => this.handleDocumentClick(e);

        // 3. Adiciona
        document.addEventListener('click', this.clickHandler);
    },

    bindKeyboardShortcuts: function () {
        if (this.keydownHandler) {
            document.removeEventListener('keydown', this.keydownHandler);
        }

        this.keydownHandler = (e) => this.handleDocumentKeydown(e);
        document.addEventListener('keydown', this.keydownHandler);
    },

    // ===========================================
    // ROUTERS (Event Routing)
    // ===========================================

    handleDocumentClick: function (e) {
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
    },

    handleDocumentKeydown: function (e) {
        // F2 is already handled in pdv-search.js
        if (e.key === 'Escape') {
            if (window.closeExtrasModal) window.closeExtrasModal();
            if (window.closeFichaModal) window.closeFichaModal();
        }
    },

    // ===========================================
    // HANDLERS
    // ===========================================

    handleAddProduct: function (el) {
        // Proteção contra duplicação de clique rápido
        if (el.classList.contains('processing-click')) return;
        el.classList.add('processing-click');
        setTimeout(() => el.classList.remove('processing-click'), 300);

        const id = el.dataset.id;
        const name = el.dataset.name;
        const price = parseFloat(el.dataset.price);
        const hasExtras = el.dataset.hasExtras === 'true';

        // Animação visual
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

            if (window.playBeep) window.playBeep();
        } else {
            console.error('PDVCart module not loaded');
        }
    },

    handleAction: function (action, el) {
        switch (action) {
            // CART ACTIONS
            case 'cart-undo':
                if (window.PDVCart) PDVCart.undoClear();
                break;
            case 'cart-clear':
                if (window.PDVCart) PDVCart.clear();
                break;
            case 'cart-remove-item':
                if (window.PDVCart) PDVCart.remove(el.dataset.id);
                break;

            // EXTRAS MODAL
            case 'extras-close':
                if (window.PDVExtras) PDVExtras.close();
                break;
            case 'extras-confirm':
                if (window.PDVExtras) PDVExtras.confirm();
                break;
            case 'extras-increase':
                if (window.PDVExtras) PDVExtras.increaseQty();
                break;
            case 'extras-decrease':
                if (window.PDVExtras) PDVExtras.decreaseQty();
                break;

            // TABLE/CLIENT ACTIONS
            case 'ficha-open':
                if (window.openFichaModal) window.openFichaModal();
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
                else console.error('saveClientOrder not found');
                break;
            case 'order-include-paid': // Incluir no Pago
                if (window.includePaidOrderItems) window.includePaidOrderItems();
                break;
            case 'order-finalize-quick': // Balcão Finalizar
                if (window.finalizeSale) window.finalizeSale();
                else console.error('finalizeSale not found');
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

            default:
                console.warn('[PDV-EVENTS] Unknown action:', action);
        }
    }
};

// ==========================================
// EXPORTAR GLOBALMENTE
// ==========================================
window.PDVEvents = PDVEvents;
