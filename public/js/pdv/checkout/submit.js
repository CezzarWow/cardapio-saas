/**
 * PDV CHECKOUT - Submit
 * Envio de pedidos ao servidor
 * 
 * Dependências: CheckoutState, CheckoutTotals, CheckoutUI, PDVState, PDVCart
 */

const CheckoutSubmit = {

    _getCsrf: function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    },

    /**
     * Envia venda finalizada ao servidor
     */
    submitSale: function () {
        const tableId = document.getElementById('current_table_id').value;
        const clientId = document.getElementById('current_client_id').value;

        let endpoint = '/admin/loja/venda/finalizar';
        const keepOpenStr = document.getElementById('keep_open_value') ? document.getElementById('keep_open_value').value : 'false';
        const keepOpen = keepOpenStr === 'true';

        // Usa o carrinho global (pdv-core.js) ou PDVCart
        let cartItems = [];
        if (typeof cart !== 'undefined' && Array.isArray(cart)) {
            cartItems = cart;
        } else if (typeof PDVCart !== 'undefined') {
            cartItems = PDVCart.items;
        }

        // Detecta qual tipo de pedido está selecionado
        const orderTypeCards = document.querySelectorAll('.order-type-card.active');
        let selectedOrderType = 'balcao'; // Default para venda de balcão (não aparece em Delivery)

        // Só muda o tipo se tiver um card ativo E cliente/mesa selecionado
        const hasClientOrTable = !!(clientId || tableId);

        orderTypeCards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('retirada')) selectedOrderType = 'pickup';
            else if (label.includes('entrega')) selectedOrderType = 'delivery';
            else if (label.includes('local') && hasClientOrTable) selectedOrderType = 'local';
            // Se 'local' mas sem cliente/mesa, mantém 'balcao'
        });

        // Se tem pagamentos registrados, significa que pagou
        let isPaid = (CheckoutState.currentPayments && CheckoutState.currentPayments.length > 0) ? 1 : 0;

        const payload = {
            cart: cartItems,
            table_id: tableId ? parseInt(tableId) : null,
            client_id: clientId ? parseInt(clientId) : null,
            payments: CheckoutState.currentPayments,
            discount: CheckoutState.discountValue,
            keep_open: keepOpen,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: isPaid,
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0
        };

        // Se for Entrega, adiciona dados de entrega
        if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
            const deliveryData = getDeliveryData();
            if (deliveryData) {
                payload.delivery_data = deliveryData;
            }
        }

        // Lógica de Estado (Mesa/Comanda/Inclusão)
        const { modo, fechandoConta } = PDVState.getState();

        let isPaidLoop = false;
        if (window.isPaidOrderInclusion && typeof editingPaidOrderId !== 'undefined') {
            payload.order_id = editingPaidOrderId;
            payload.save_account = true;
            isPaidLoop = true;
        } else if (modo === 'mesa' && fechandoConta) {
            endpoint = '/admin/loja/mesa/fechar';
        } else if (modo === 'comanda' && fechandoConta) {
            endpoint = '/admin/loja/venda/fechar-comanda';
            payload.order_id = CheckoutState.closingOrderId;
        }

        const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + endpoint;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this._getCsrf()
            },
            body: JSON.stringify(payload)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    CheckoutUI.showSuccessModal();
                    PDVCart.clear();
                    // Limpa o carrinho global também
                    if (typeof cart !== 'undefined') cart.length = 0;

                    setTimeout(() => {
                        if (isPaidLoop) {
                            window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(err => alert('Erro de conexão: ' + err.message));
    },

    /**
     * Entrega imediata de comanda paga
     * @param {number} orderId 
     */
    forceDelivery: function (orderId) {
        fetch('venda/fechar-comanda', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this._getCsrf()
            },
            body: JSON.stringify({ order_id: orderId, payments: [], keep_open: false })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Entregue!');
                    window.location.href = BASE_URL + '/admin/loja/mesas';
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    },

    /**
     * Salva Comanda Aberta (Botão Laranja)
     */
    saveClientOrder: function () {
        const clientId = document.getElementById('current_client_id').value;
        const tableId = document.getElementById('current_table_id').value;
        const orderId = document.getElementById('current_order_id') ? document.getElementById('current_order_id').value : null;

        if (PDVCart.items.length === 0) return alert('Carrinho vazio!');

        // Aceita cliente OU mesa
        if (!clientId && !tableId) return alert('Selecione um cliente ou mesa!');

        PDVState.set({ modo: tableId ? 'mesa' : 'comanda', clienteId: clientId ? parseInt(clientId) : null, mesaId: tableId ? parseInt(tableId) : null });

        fetch('venda/finalizar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this._getCsrf()
            },
            body: JSON.stringify({
                cart: PDVCart.items,
                client_id: clientId || null,
                table_id: tableId || null,
                order_id: orderId,
                save_account: true
            })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    CheckoutUI.showSuccessModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else { alert('Erro: ' + data.message); }
            });
    },

    /**
     * Salva pedido de Retirada/Entrega sem pagamento (pagar depois)
     * Unificado de pickup.js
     */
    savePickupOrder: function () {
        // Detecta qual tipo de pedido está selecionado
        const orderTypeCards = document.querySelectorAll('.order-type-card.active');
        let selectedOrderType = 'pickup';

        orderTypeCards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('retirada')) selectedOrderType = 'pickup';
            else if (label.includes('entrega')) selectedOrderType = 'delivery';
        });

        // Para Entrega, verifica se dados foram preenchidos
        if (selectedOrderType === 'delivery') {
            const isFilled = typeof CheckoutEntrega !== 'undefined'
                ? CheckoutEntrega.isDataFilled()
                : (typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled);

            if (!isFilled) {
                alert('Preencha os dados de entrega primeiro!');
                if (typeof openDeliveryPanel === 'function') openDeliveryPanel();
                return;
            }
        }

        // Pega o carrinho
        const cartItems = this._getCartItems();
        if (cartItems.length === 0) {
            alert('Carrinho vazio!');
            return;
        }

        // Pega a forma de pagamento selecionada (para mostrar no Kanban)
        const selectedPaymentMethod = CheckoutState.selectedMethod || 'dinheiro';

        // Taxa de entrega (apenas para delivery)
        const deliveryFee = (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0;

        const payload = {
            cart: cartItems,
            table_id: null,
            client_id: document.getElementById('current_client_id')?.value || null,
            payments: [],
            discount: CheckoutState.discountValue || 0,
            delivery_fee: deliveryFee,
            keep_open: false,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: 0,
            payment_method_expected: selectedPaymentMethod
        };

        // Se for Entrega, adiciona dados de entrega
        if (selectedOrderType === 'delivery') {
            const deliveryData = typeof CheckoutEntrega !== 'undefined'
                ? CheckoutEntrega.getData()
                : (typeof getDeliveryData === 'function' ? getDeliveryData() : null);

            if (deliveryData) {
                payload.delivery_data = deliveryData;
            }
        }

        const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/venda/finalizar';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this._getCsrf()
            },
            body: JSON.stringify(payload)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    CheckoutUI.showSuccessModal();
                    PDVCart.clear();
                    if (typeof cart !== 'undefined') cart.length = 0;

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(err => alert('Erro de conexão: ' + err.message));
    },

    /**
     * Helper: Pega itens do carrinho (compatível com cart global e PDVCart)
     * @private
     */
    _getCartItems: function () {
        if (typeof cart !== 'undefined' && Array.isArray(cart)) {
            return cart;
        } else if (typeof PDVCart !== 'undefined') {
            return PDVCart.items;
        }
        return [];
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutSubmit = CheckoutSubmit;

// Alias para compatibilidade com HTML
window.savePickupOrder = () => CheckoutSubmit.savePickupOrder();
