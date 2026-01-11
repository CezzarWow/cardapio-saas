/**
 * PDV CHECKOUT - Submit (Refatorado)
 * Controlador de envio de pedidos (Orchestrator)
 * 
 * Dependências: CheckoutService, CheckoutValidator, CheckoutState, PDVState, PDVCart
 */

const CheckoutSubmit = {

    /**
     * 1. FINALIZAR VENDA (Pagamento Realizado)
     */
    submitSale: async function () {
        const tableId = document.getElementById('current_table_id').value;
        const clientId = document.getElementById('current_client_id').value;
        const keepOpen = document.getElementById('keep_open_value')?.value === 'true';

        // 1. Obter Carrinho
        const cartItems = this._getCartItems();

        // 2. Preparar Payload Base
        let endpoint = '/admin/loja/venda/finalizar';
        const hasClientOrTable = !!(clientId || tableId);
        const selectedOrderType = this._determineOrderType(hasClientOrTable);

        // 3. Montar dados
        const payload = {
            cart: cartItems,
            table_id: tableId ? parseInt(tableId) : null,
            client_id: clientId ? parseInt(clientId) : null,
            payments: CheckoutState.currentPayments,
            discount: CheckoutState.discountValue,
            keep_open: keepOpen,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: (CheckoutState.currentPayments?.length > 0) ? 1 : 0,
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0
        };

        // 4. Dados de Entrega
        if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
            payload.delivery_data = getDeliveryData();
        }

        // 5. Ajuste de Endpoint baseado no Estado
        const { modo, fechandoConta } = PDVState.getState();
        let isPaidLoop = false;
        let isMesaClose = false;

        if (window.isPaidOrderInclusion && typeof editingPaidOrderId !== 'undefined') {
            payload.order_id = editingPaidOrderId;
            payload.save_account = true;
            isPaidLoop = true;
        } else if (modo === 'mesa' && fechandoConta) {
            endpoint = '/admin/loja/mesa/fechar';
            isMesaClose = true;
        } else if (modo === 'comanda' && fechandoConta) {
            endpoint = '/admin/loja/venda/fechar-comanda';
            payload.order_id = CheckoutState.closingOrderId;
        }

        // 6. Enviar via Service
        try {
            const data = await CheckoutService.sendSaleRequest(endpoint, payload);
            this._handleSuccess(data, isPaidLoop, isMesaClose);
        } catch (err) {
            alert(err.message);
        }
    },

    /**
     * 2. FORÇAR ENTREGA (Mesa/Comanda já paga)
     */
    forceDelivery: async function (orderId) {
        try {
            const data = await CheckoutService.closePaidTab(orderId);
            if (data.success) {
                alert('Entregue!');
                window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (err) {
            alert(err.message);
        }
    },

    /**
     * 3. SALVAR COMANDA (Botão Laranja)
     */
    saveClientOrder: async function () {
        const clientId = document.getElementById('current_client_id').value;
        const tableId = document.getElementById('current_table_id').value;
        const orderId = document.getElementById('current_order_id')?.value;

        // Validações
        if (!CheckoutValidator.validateCart(PDVCart.items)) return;
        if (!CheckoutValidator.validateClientOrTable(clientId, tableId)) return;

        // Atualiza Estado
        PDVState.set({
            modo: tableId ? 'mesa' : 'comanda',
            clienteId: clientId ? parseInt(clientId) : null,
            mesaId: tableId ? parseInt(tableId) : null
        });

        // Envia
        const payload = {
            cart: PDVCart.items,
            client_id: clientId || null,
            table_id: tableId || null,
            order_id: orderId,
            save_account: true,
            order_type: tableId ? 'mesa' : 'comanda'
        };

        try {
            const data = await CheckoutService.saveTabOrder(payload);
            this._handleSuccess(data);
        } catch (err) {
            alert(err.message);
        }
    },

    /**
     * 4. SALVAR PEDIDO (Retirada/Delivery) - Pagar Depois
     */
    savePickupOrder: async function () {
        const selectedOrderType = this._determineOrderType(false) === 'delivery' ? 'delivery' : 'pickup';

        // Validações
        if (!CheckoutValidator.validateDeliveryData(selectedOrderType)) return;

        const cartItems = this._getCartItems();
        if (!CheckoutValidator.validateCart(cartItems)) return;

        // Verificar se tem mesa selecionada
        const tableId = document.getElementById('current_table_id')?.value;
        const hasTable = tableId && tableId !== '' && tableId !== '0';

        // Montar Payload
        const payload = {
            cart: cartItems,
            table_id: hasTable ? parseInt(tableId) : null,
            client_id: document.getElementById('current_client_id')?.value || null,
            payments: [],
            discount: CheckoutState.discountValue || 0,
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0,
            keep_open: false,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: 0,
            payment_method_expected: CheckoutState.selectedMethod || 'dinheiro'
        };

        // Se é Entrega + Mesa, adiciona flag para vincular
        if (selectedOrderType === 'delivery' && hasTable) {
            payload.link_to_table = true;
            payload.table_id = parseInt(tableId); // [FIX] Envia o ID numérico da mesa
        }

        if (selectedOrderType === 'delivery') {
            const deliveryData = typeof CheckoutEntrega !== 'undefined' ? CheckoutEntrega.getData() : getDeliveryData();
            if (deliveryData) payload.delivery_data = deliveryData;
        }

        // Enviar
        try {
            const data = await CheckoutService.sendSaleRequest('/admin/loja/venda/finalizar', payload);
            this._handleSuccess(data);
        } catch (err) {
            alert(err.message);
        }
    },

    // --- Helpers Privados ---

    _getCartItems: function () {
        if (typeof cart !== 'undefined' && Array.isArray(cart)) return cart;
        if (typeof PDVCart !== 'undefined') return PDVCart.items;
        return [];
    },

    _determineOrderType: function (hasClientOrTable) {
        const cards = document.querySelectorAll('.order-type-card.active');
        let type = 'balcao';

        cards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('retirada')) type = 'pickup';
            else if (label.includes('entrega')) type = 'delivery';
            else if (label.includes('local') && hasClientOrTable) type = 'local';
        });
        return type;
    },

    _handleSuccess: function (data, isPaidLoop = false, isMesaClose = false) {
        if (data.success) {
            CheckoutUI.showSuccessModal();
            PDVCart.clear();
            if (typeof cart !== 'undefined') cart.length = 0;

            setTimeout(() => {
                if (isPaidLoop || isMesaClose) {
                    // Redireciona para página de mesas
                    window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                } else {
                    window.location.reload();
                }
            }, 1000);
        } else {
            alert('Erro: ' + data.message);
        }
    }
};

// Exports
window.CheckoutSubmit = CheckoutSubmit;
window.savePickupOrder = () => CheckoutSubmit.savePickupOrder();
