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
        const orderId = document.getElementById('current_order_id')?.value; // Captura ID da comanda
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
            order_id: orderId ? parseInt(orderId) : null, // Envia para o backend
            payments: CheckoutState.currentPayments,
            discount: CheckoutState.discountValue,
            keep_open: keepOpen,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: this._calculateIsPaidStatus(CheckoutState.currentPayments),
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0
        };

        // 4. Dados de Entrega
        if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
            payload.delivery_data = getDeliveryData();
        }

        // 5. Vincular entrega à mesa ou comanda (independente de pagar agora ou depois)
        if (selectedOrderType === 'delivery') {
            const tId = parseInt(tableId);
            const cId = parseInt(clientId);

            const hasTable = !isNaN(tId) && tId > 0;
            const hasClient = !isNaN(cId) && cId > 0;

            if (hasTable) {
                payload.link_to_table = true;
                payload.table_id = tId;
            } else if (hasClient) {
                payload.link_to_comanda = true;
                payload.table_id = null; // Garante que não vá lixo
                payload.link_to_table = false;
            }
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
     * 2. FORÇAR ENTREGA (Pedido já pago)
     */
    forceDelivery: async function (orderId) {
        if (!orderId) return;

        try {
            const data = await CheckoutService.sendSaleRequest('/admin/loja/pedidos/entregar', {
                order_id: parseInt(orderId)
            });

            if (data.success) {
                CheckoutUI.showSuccessModal();
                PDVCart.clear();
                // Redireciona para mesas após sucesso
                setTimeout(() => {
                    window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                }, 1000);
            } else {
                alert('Erro: ' + (data.message || 'Falha ao entregar pedido.'));
            }
        } catch (err) {
            alert('Erro: ' + err.message);
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

        // Validação correta de IDs (ignora "0" ou string vazia)
        const hasTable = tableId && tableId !== '' && tableId !== '0';

        // Atualiza Estado
        PDVState.set({
            modo: hasTable ? 'mesa' : 'comanda',
            clienteId: clientId ? parseInt(clientId) : null,
            mesaId: hasTable ? parseInt(tableId) : null
        });

        // Envia
        const payload = {
            cart: PDVCart.items,
            client_id: clientId ? parseInt(clientId) : null,
            table_id: hasTable ? parseInt(tableId) : null,
            order_id: orderId ? parseInt(orderId) : null,
            save_account: true,
            order_type: hasTable ? 'mesa' : 'comanda'
        };

        try {
            const data = await CheckoutService.saveTabOrder(payload);
            // Após salvar, volta para a mesma mesa (não perde contexto)
            this._handleSaveSuccess(data, hasTable ? parseInt(tableId) : null, orderId);
        } catch (err) {
            alert(err.message);
        }
    },

    /**
     * 4. SALVAR PEDIDO (Retirada/Delivery) - Pagar Depois
     */
    savePickupOrder: async function () {
        // Determina tipo baseado no card ATIVO (não no deliveryDataFilled)
        const rawType = this._determineOrderType(false);
        const selectedOrderType = rawType === 'delivery' ? 'delivery' : 'pickup';



        // Validações
        if (!CheckoutValidator.validateDeliveryData(selectedOrderType)) return;

        const cartItems = this._getCartItems();
        if (!CheckoutValidator.validateCart(cartItems)) return;

        // Verificar se tem mesa ou cliente selecionado
        const tableId = document.getElementById('current_table_id')?.value;
        const clientId = document.getElementById('current_client_id')?.value;
        const orderId = document.getElementById('current_order_id')?.value; // Captura ID da comanda
        const hasTable = tableId && tableId !== '' && tableId !== '0';
        const hasClient = clientId && clientId !== '' && clientId !== '0';



        // Montar Payload
        const payload = {
            cart: cartItems,
            table_id: hasTable ? parseInt(tableId) : null,
            client_id: hasClient ? parseInt(clientId) : null,
            order_id: orderId ? parseInt(orderId) : null, // Envia para o backend
            payments: [],
            discount: CheckoutState.discountValue || 0,
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0,
            keep_open: false,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: 0,
            payment_method_expected: CheckoutState.selectedMethod || 'dinheiro'
        };



        // 5. Vincular entrega à mesa ou comanda (lógica robusta)
        if (selectedOrderType === 'delivery') {
            const tId = parseInt(tableId);
            const cId = parseInt(clientId);

            const hasTable = !isNaN(tId) && tId > 0;
            const hasClient = !isNaN(cId) && cId > 0;



            if (hasTable) {
                payload.link_to_table = true;
                payload.table_id = tId;
            } else if (hasClient) {
                payload.link_to_comanda = true;
                payload.table_id = null;
                payload.link_to_table = false;
            }
        }

        if (selectedOrderType === 'delivery') {
            const deliveryData = typeof CheckoutEntrega !== 'undefined' ? CheckoutEntrega.getData() : getDeliveryData();
            if (deliveryData) payload.delivery_data = deliveryData;
        }

        // Enviar
        try {
            const data = await CheckoutService.saveTabOrder(payload);

            // Se tem mesa ou cliente, manter no contexto (igual saveClientOrder)
            if (hasTable || hasClient) {
                this._handleSaveSuccess(data, hasTable ? parseInt(tableId) : null, orderId);
            } else {
                // Sem mesa/cliente, comportamento padrão
                const isFinalize = payload.finalize_now === true;
                this._handleSuccess(data, false, isFinalize);
            }
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

    /**
     * Calcula is_paid baseado nos métodos de pagamento.
     * - Se tem qualquer pagamento "real" (dinheiro, pix, cartão) = is_paid 1
     * - Se é APENAS crediário = is_paid 0
     * - A dívida do crediário é calculada separadamente pelo backend (order_payments)
     */
    _calculateIsPaidStatus: function (payments) {
        if (!payments || payments.length === 0) return 0;

        // Verifica se tem algum pagamento "real" (não crediário)
        const hasRealPayment = payments.some(p => p.method !== 'crediario');

        // Se tem pagamento real, marca como pago
        // A parte do crediário será contabilizada como dívida pelo backend
        return hasRealPayment ? 1 : 0;
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
    },

    /**
     * Handler de sucesso para SALVAR comanda (mantém na mesa)
     */
    _handleSaveSuccess: function (data, tableId, orderId) {
        if (data.success) {
            CheckoutUI.showSuccessModal();
            PDVCart.clear();
            if (typeof cart !== 'undefined') cart.length = 0;

            setTimeout(() => {
                // Se foi salvo com mesa, redireciona de volta para a mesa
                // Se foi novo pedido, usa o order_id retornado
                const newOrderId = data.order_id || orderId;

                if (tableId) {
                    window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') +
                        '/admin/loja/pdv?mesa_id=' + tableId;
                } else if (newOrderId) {
                    window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') +
                        '/admin/loja/pdv?order_id=' + newOrderId;
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
