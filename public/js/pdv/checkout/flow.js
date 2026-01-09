/**
 * PDV CHECKOUT - Flow
 * Orquestração de fluxos de venda (Mesa, Comanda, Balcão)
 * 
 * Dependências: CheckoutState, CheckoutHelpers, CheckoutUI, CheckoutPayments, CheckoutOrderType, PDVCart, PDVState
 */

const CheckoutFlow = {

    /**
     * Ponto de entrada principal para finalizar venda
     * Detecta contexto (Mesa, Comanda, Balcão, Edição de Pago) e direciona
     */
    finalizeSale: function () {
        const tableId = document.getElementById('current_table_id').value;

        // VERIFICAÇÃO ESPECIAL: Edição de Pedido Pago
        if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder) {
            this.handlePaidOrderInclusion();
            return;
        }

        // MESA
        if (tableId) {
            if (PDVCart.items.length === 0) { alert('Carrinho vazio!'); return; }
            this.openCheckoutModal();
            return;
        }

        // BALCÃO
        const stateBalcao = PDVState.getState();
        if (!tableId && stateBalcao.modo !== 'retirada') {
            PDVState.set({ modo: 'balcao' });
        }

        if (PDVCart.items.length === 0) { alert('Carrinho vazio!'); return; }

        this.openCheckoutModal();
    },

    /**
     * Fluxo de inclusão em pedido já pago
     */
    handlePaidOrderInclusion: function () {
        const cartTotal = PDVCart.calculateTotal();

        if (PDVCart.items.length > 0 && cartTotal > 0.01) {
            CheckoutState.resetPayments();
            document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(cartTotal);
            document.getElementById('checkoutModal').style.display = 'flex';
            CheckoutPayments.setMethod('dinheiro');
            CheckoutUI.updateCheckoutUI();
            window.isPaidOrderInclusion = true;
        } else {
            alert('Carrinho vazio! Adicione novos itens para cobrar.');
        }
    },

    /**
     * Fechar conta de Mesa
     */
    fecharContaMesa: function (mesaId) {
        PDVState.set({ modo: 'mesa', mesaId: mesaId, fechandoConta: true });
        const state = PDVState.getState();

        if (state.status === 'editando_pago') {
            alert('Mesa não permite editar pedido pago.');
            return;
        }

        CheckoutState.resetPayments();

        const tableTotalStr = document.getElementById('table-initial-total').value;
        const tableTotal = parseFloat(tableTotalStr) || 0;

        // CORREÇÃO: Atualiza cachedTotal para que getFinalTotal() retorne o valor correto
        CheckoutState.cachedTotal = tableTotal;

        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(tableTotal);
        document.getElementById('checkoutModal').style.display = 'flex';
        CheckoutPayments.setMethod('dinheiro');
        CheckoutUI.updateCheckoutUI();

        // Preenche o input com o valor a pagar
        const payInput = document.getElementById('pay-amount');
        if (payInput) {
            payInput.value = tableTotal.toFixed(2).replace('.', ',');
            CheckoutHelpers.formatMoneyInput(payInput);
        }
    },

    /**
     * Fechar Comanda (Cliente)
     */
    fecharComanda: function (orderId) {
        const isPaid = document.getElementById('current_order_is_paid') ? document.getElementById('current_order_is_paid').value == '1' : false;

        PDVState.set({
            modo: 'comanda',
            pedidoId: orderId ? parseInt(orderId) : null,
            fechandoConta: true
        });

        if (isPaid) {
            if (!confirm('Este pedido já está PAGO. Deseja entregá-lo e finalizar?')) return;
            CheckoutSubmit.forceDelivery(orderId);
            return;
        }

        CheckoutState.closingOrderId = orderId;
        CheckoutState.resetPayments();

        const totalStr = document.getElementById('table-initial-total').value;
        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(parseFloat(totalStr));

        const cards = document.querySelectorAll('.order-type-card');
        if (cards.length > 0) CheckoutOrderType.selectOrderType('local', cards[0]);

        document.getElementById('checkoutModal').style.display = 'flex';
        CheckoutPayments.setMethod('dinheiro');
        CheckoutUI.updateCheckoutUI();
    },

    /**
     * Abre modal de checkout (fluxo padrão)
     */
    openCheckoutModal: function () {
        CheckoutState.reset();

        // Calcula o total
        let cartTotal = 0;
        if (typeof calculateTotal === 'function') {
            cartTotal = calculateTotal();
        } else if (typeof PDVCart !== 'undefined') {
            cartTotal = PDVCart.calculateTotal();
        }
        let tableInitialTotal = parseFloat(document.getElementById('table-initial-total')?.value || 0);
        CheckoutState.cachedTotal = cartTotal + tableInitialTotal;

        // Reset Inputs
        const discInput = document.getElementById('discount-amount');
        if (discInput) discInput.value = '';

        CheckoutUI.updatePaymentList();

        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(CheckoutState.cachedTotal);
        document.getElementById('checkoutModal').style.display = 'flex';
        CheckoutPayments.setMethod('dinheiro');

        // SEMPRE abre com "Local" selecionado
        const localCard = document.querySelector('.order-type-card:first-child');
        if (localCard) {
            CheckoutOrderType.selectOrderType('local', localCard);
        } else {
            document.getElementById('keep_open_value').value = 'false';
            const alertBox = document.getElementById('retirada-client-alert');
            if (alertBox) alertBox.style.display = 'none';
        }

        CheckoutUI.updateCheckoutUI();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    /**
     * Fecha modal de checkout e limpa estado
     */
    closeCheckout: function () {
        document.getElementById('checkoutModal').style.display = 'none';
        CheckoutState.resetPayments();

        // Limpa visual
        const alertBox = document.getElementById('retirada-client-alert');
        if (alertBox) alertBox.style.display = 'none';

        // Reset dados de entrega
        if (typeof CheckoutEntrega !== 'undefined') {
            CheckoutEntrega.resetOnClose();
        } else if (typeof _resetDeliveryOnClose === 'function') {
            _resetDeliveryOnClose();
        }
    }

};

// Expõe globalmente
window.CheckoutFlow = CheckoutFlow;
