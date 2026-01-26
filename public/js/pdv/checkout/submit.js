/**
 * PDV CHECKOUT - Submit (Refatorado)
 * Controlador de envio de pedidos (Orchestrator)
 * 
 * Dependências: CheckoutService, CheckoutValidator, CheckoutState, PDVState, PDVCart
 */

window.CheckoutSubmit = {

    /**
     * 1. FINALIZAR VENDA (Pagamento Realizado)
     */
    submitSale: async function () {
        // 1. Obter contexto via helper centralizado
        const ctx = CheckoutHelpers.getContextIds();
        const keepOpen = document.getElementById('keep_open_value')?.value === 'true';

        // 2. Obter Carrinho
        const cartItems = this._getCartItems();

        // IMPORTANTE: Guarda cópia dos itens do carrinho ANTES de enviar/limpar
        // para poder imprimir após finalizar
        const cartItemsCopy = JSON.parse(JSON.stringify(cartItems));

        // 3. Preparar Payload Base
        let endpoint = '/admin/loja/venda/finalizar';
        const hasClientOrTable = ctx.hasClient || ctx.hasTable;
        const selectedOrderType = this._determineOrderType(hasClientOrTable);

        // 3. Montar dados
        const payload = {
            cart: cartItems,
            table_id: ctx.tableId,
            client_id: ctx.clientId,
            order_id: ctx.orderId,
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
            if (ctx.hasTable) {
                payload.link_to_table = true;
                payload.table_id = ctx.tableId;
            } else if (ctx.hasClient) {
                payload.link_to_comanda = true;
                payload.table_id = null;
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
            // Passa itens e tipo para exibir modal de impressão
            this._handleSuccess(data, isPaidLoop, isMesaClose, cartItemsCopy, selectedOrderType);
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
                // Navega para mesas via SPA após sucesso
                setTimeout(() => {
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.navigateTo('mesas', true, true);
                    } else {
                        window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                    }
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
        // Obter contexto via helper centralizado
        const ctx = CheckoutHelpers.getContextIds();

        // Validações
        if (!CheckoutValidator.validateCart(PDVCart.items)) return;
        if (!CheckoutValidator.validateClientOrTable(ctx.clientId, ctx.tableId)) return;

        // Determina o tipo de pedido selecionado pelo usuário
        const selectedOrderType = this._determineOrderType(ctx.hasClient || ctx.hasTable);

        // IMPORTANTE: Guarda cópia dos itens do carrinho ANTES de enviar/limpar
        // Isso permite imprimir apenas os itens novos, não a comanda completa
        const cartItemsCopy = JSON.parse(JSON.stringify(PDVCart.items));

        // Atualiza Estado
        PDVState.set({
            modo: ctx.hasTable ? 'mesa' : 'comanda',
            clienteId: ctx.clientId,
            mesaId: ctx.tableId
        });

        // Envia
        const payload = {
            cart: PDVCart.items,
            client_id: ctx.clientId,
            table_id: ctx.tableId,
            order_id: ctx.orderId,
            save_account: true,
            order_type: selectedOrderType  // Usa o tipo selecionado (pickup, delivery, local, etc)
        };

        // Se for entrega, adiciona dados de entrega
        if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
            payload.delivery_data = getDeliveryData();
            payload.delivery_fee = (typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0;
        }

        try {
            const data = await CheckoutService.saveTabOrder(payload);
            // Após salvar, volta para a mesma mesa (não perde contexto)
            // Passa os itens do carrinho (cópia) para imprimir
            this._handleSaveSuccess(data, ctx.tableId, ctx.orderId, cartItemsCopy, selectedOrderType);
        } catch (err) {
            alert(err.message);
        }
    },

    /**
     * 4. SALVAR PEDIDO (Retirada/Delivery) - Pagar Depois
     */
    savePickupOrder: async function () {
        // Determina tipo baseado no card ATIVO
        const selectedOrderType = this._determineOrderType(false);

        // Validações
        if (!CheckoutValidator.validateDeliveryData(selectedOrderType)) return;

        const cartItems = this._getCartItems();
        if (!CheckoutValidator.validateCart(cartItems)) return;

        // Obter contexto via helper centralizado
        const ctx = CheckoutHelpers.getContextIds();

        // Montar Payload
        const payload = {
            cart: cartItems,
            table_id: ctx.tableId,
            client_id: ctx.clientId,
            order_id: ctx.orderId,
            payments: [],
            discount: CheckoutState.discountValue || 0,
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0,
            keep_open: false,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: 0,
            payment_method_expected: CheckoutState.selectedMethod || 'dinheiro'
        };

        // Vincular entrega à mesa ou comanda
        if (selectedOrderType === 'delivery') {
            if (ctx.hasTable) {
                payload.link_to_table = true;
                payload.table_id = ctx.tableId;
            } else if (ctx.hasClient) {
                payload.link_to_comanda = true;
                payload.table_id = null;
                payload.link_to_table = false;
            }

            // Adiciona dados de entrega
            const deliveryData = typeof CheckoutEntrega !== 'undefined' ? CheckoutEntrega.getData() : getDeliveryData();
            if (deliveryData) payload.delivery_data = deliveryData;
        }

        // Enviar
        try {
            const data = await CheckoutService.saveTabOrder(payload);

            // Se tem mesa ou cliente, manter no contexto
            if (ctx.hasTable || ctx.hasClient) {
                this._handleSaveSuccess(data, ctx.tableId, ctx.orderId);
            } else {
                // Sem mesa/cliente, comportamento padrão
                this._handleSuccess(data, false, true);
            }
        } catch (err) {
            alert(err.message);
        }
    },

    // --- Helpers Privados ---

    _getCartItems: function () {
        // IMPORTANTE: PDVCart.items tem prioridade porque window.cart pode ficar desatualizado
        // (window.cart é uma referência que fica stale quando this.items = [] substitui o array)
        if (typeof PDVCart !== 'undefined' && PDVCart.items) return PDVCart.items;
        if (typeof cart !== 'undefined' && Array.isArray(cart)) return cart;
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
        // 1. Verificar input hidden (prioridade ABSOLUTA para retirada/entrega)
        const selectedInput = document.getElementById('selected_order_type');
        const selectedVal = selectedInput?.value?.toLowerCase() || '';

        // [FIX] Retirada e Entrega SEMPRE respeitam a escolha do usuário, mesmo com cliente
        if (selectedVal === 'retirada') return 'pickup';
        if (selectedVal === 'entrega') return 'delivery';

        // 2. Se for "local" sem mesa/cliente, vira balcao
        if (selectedVal === 'local' && !hasClientOrTable) return 'balcao';

        // 3. Fallback: Só aplica mesa/comanda se NÃO foi retirada/entrega
        if (hasClientOrTable) {
            const ctx = CheckoutHelpers.getContextIds();
            if (ctx.tableId) return 'mesa';
            if (ctx.clientId) return 'comanda';
        }

        // 4. Fallback dos botões visuais
        const cards = document.querySelectorAll('.order-toggle-btn.active');
        let type = 'balcao';

        cards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('retirada')) type = 'pickup';
            else if (label.includes('entrega')) type = 'delivery';
            else if (label.includes('local')) type = 'balcao';
        });

        return type;
    },

    /**
     * Handler de sucesso para FINALIZAR venda
     * @param {object} data - Resposta do backend
     * @param {boolean} isPaidLoop - Se é inclusão em pedido pago
     * @param {boolean} isMesaClose - Se está fechando mesa
     * @param {array} cartItems - Itens do carrinho para impressão
     * @param {string} orderType - Tipo do pedido
     */
    _handleSuccess: function (data, isPaidLoop = false, isMesaClose = false, cartItems = [], orderType = 'local') {
        if (data.success) {
            CheckoutUI.showSuccessModal();
            PDVCart.clear();

            // [FIX] Invalidar cache do SPA para garantir que Balcão e Mesas recarreguem zerados
            if (typeof AdminSPA !== 'undefined') {
                AdminSPA.invalidateCache('mesas');
                AdminSPA.invalidateCache('balcao');
                AdminSPA.invalidateCache('pdv');
            }

            // Fecha modal de checkout
            document.getElementById('checkoutModal').style.display = 'none';

            // Abre modal de impressão após breve delay
            setTimeout(() => {
                const savedOrderId = data.order_id;

                if (savedOrderId && typeof PDVPrint !== 'undefined') {
                    // [MODIFICADO] Sempre busca do backend para garantir dados completos (pagamento, endereço, etc)
                    // Evita o problema de "Pagamento Não Informado" causado pelo openWithItems mockado
                    PDVPrint.open(savedOrderId);
                } else {
                    // Fallback se não tiver orderId ou PDVPrint não carregou
                    if (isPaidLoop || isMesaClose) {
                        if (typeof AdminSPA !== 'undefined') {
                            AdminSPA.navigateTo('mesas', true, true);
                        } else {
                            window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                        }
                    } else {
                        if (typeof AdminSPA !== 'undefined') {
                            AdminSPA.reloadCurrentSection();
                        } else {
                            window.location.reload();
                        }
                    }
                }
            }, 800);
        } else {
            alert('Erro: ' + data.message);
        }
    },

    /**
     * Handler de sucesso para SALVAR comanda (permanece no Balcão)
     * @param {object} data - Resposta do backend
     * @param {number} tableId - ID da mesa (se houver)
     * @param {number} orderId - ID do pedido existente
     * @param {array} cartItems - Itens do carrinho (para imprimir apenas o que foi adicionado)
     * @param {string} orderType - Tipo do pedido (local, delivery, etc)
     */
    _handleSaveSuccess: function (data, tableId, orderId, cartItems = [], orderType = 'local') {
        if (data.success) {
            CheckoutUI.showSuccessModal();
            PDVCart.clear();

            // Fecha checkout modal
            document.getElementById('checkoutModal').style.display = 'none';

            // Aguarda um pouco e abre o modal de impressão
            setTimeout(() => {
                // Usa o order_id retornado pelo backend (prioridade) ou o que já existia
                const savedOrderId = data.order_id || orderId;

                // Abre modal de impressão se tiver um pedido salvo
                if (savedOrderId && typeof PDVPrint !== 'undefined') {
                    // Se tem itens do carrinho, passa para imprimir apenas eles (não busca da API)
                    if (cartItems && cartItems.length > 0) {
                        PDVPrint.openWithItems(savedOrderId, cartItems, orderType);
                    } else {
                        // Fallback: busca do backend (comportamento original)
                        PDVPrint.open(savedOrderId);
                    }
                } else {
                    // Fallback: vai direto pro balcão se não tiver order_id
                    this._navigateToBalcao();
                }
            }, 800);
        } else {
            alert('Erro: ' + data.message);
        }
    },


    /**
     * Navega para o Balcão (utilizado pelo modal de impressão após fechar)
     */
    _navigateToBalcao: function () {
        if (typeof AdminSPA !== 'undefined') {
            AdminSPA.navigateTo('balcao', true, true);
        } else {
            window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/pdv';
        }
    }
};

// Exports
// window.CheckoutSubmit = CheckoutSubmit; // Já definido acima
// [FIX] Retorna promise para permitir lock (PDVEvents)
window.savePickupOrder = () => window.CheckoutSubmit.savePickupOrder();
window.saveClientOrder = () => window.CheckoutSubmit.saveClientOrder();
