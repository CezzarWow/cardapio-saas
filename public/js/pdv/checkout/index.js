/**
 * PDV CHECKOUT - Index (Fachada)
 * Orquestrador principal que monta window.PDVCheckout
 * 
 * Dependências: Todos os módulos de checkout devem estar carregados antes
 * - CheckoutHelpers
 * - CheckoutState
 * - CheckoutTotals
 * - CheckoutUI
 * - CheckoutPayments
 * - CheckoutSubmit
 * - CheckoutOrderType
 */

const PDVCheckout = {

    // ==========================================
    // ESTADO (delegado para CheckoutState)
    // ==========================================

    get currentPayments() { return CheckoutState.currentPayments; },
    set currentPayments(val) { CheckoutState.currentPayments = val; },

    get totalPaid() { return CheckoutState.totalPaid; },
    set totalPaid(val) { CheckoutState.totalPaid = val; },

    get discountValue() { return CheckoutState.discountValue; },
    set discountValue(val) { CheckoutState.discountValue = val; },

    get cachedTotal() { return CheckoutState.cachedTotal; },
    set cachedTotal(val) { CheckoutState.cachedTotal = val; },

    get selectedMethod() { return CheckoutState.selectedMethod; },
    set selectedMethod(val) { CheckoutState.selectedMethod = val; },

    get closingOrderId() { return CheckoutState.closingOrderId; },
    set closingOrderId(val) { CheckoutState.closingOrderId = val; },

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================

    init: function () {
        this.bindEvents();
    },

    bindEvents: function () {
        // Input de pagamento
        const payInput = document.getElementById('pay-amount');
        if (payInput) {
            payInput.addEventListener('input', function () { CheckoutHelpers.formatMoneyInput(this); });
            payInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    CheckoutPayments.addPayment();
                }
            });
        }

        // Input Desconto
        const discInput = document.getElementById('discount-amount');
        if (discInput) {
            discInput.addEventListener('input', function () {
                CheckoutHelpers.formatMoneyInput(this);
                CheckoutTotals.applyDiscount(this.value);
            });
        }
    },

    // ==========================================
    // HELPERS (delegados)
    // ==========================================

    formatMoneyInput: function (input) {
        return CheckoutHelpers.formatMoneyInput(input);
    },

    formatCurrency: function (val) {
        return CheckoutHelpers.formatCurrency(val);
    },

    formatMethodLabel: function (method) {
        return CheckoutHelpers.formatMethodLabel(method);
    },

    // ==========================================
    // DESCONTO / TOTAIS (delegados)
    // ==========================================

    applyDiscount: function (valStr) {
        return CheckoutTotals.applyDiscount(valStr);
    },

    getFinalTotal: function () {
        return CheckoutTotals.getFinalTotal();
    },

    // ==========================================
    // UI (delegados)
    // ==========================================

    updatePaymentList: function () {
        return CheckoutUI.updatePaymentList();
    },

    updateCheckoutUI: function () {
        return CheckoutUI.updateCheckoutUI();
    },

    showSuccessModal: function () {
        return CheckoutUI.showSuccessModal();
    },

    // ==========================================
    // PAGAMENTOS (delegados)
    // ==========================================

    setMethod: function (method) {
        return CheckoutPayments.setMethod(method);
    },

    addPayment: function () {
        return CheckoutPayments.addPayment();
    },

    removePayment: function (index) {
        return CheckoutPayments.removePayment(index);
    },

    // ==========================================
    // TIPO DE PEDIDO (delegado)
    // ==========================================

    selectOrderType: function (type, element) {
        return CheckoutOrderType.selectOrderType(type, element);
    },

    // ==========================================
    // SUBMIT (delegados)
    // ==========================================

    submitSale: function () {
        return CheckoutSubmit.submitSale();
    },

    saveClientOrder: function () {
        return CheckoutSubmit.saveClientOrder();
    },

    forceDelivery: function (orderId) {
        return CheckoutSubmit.forceDelivery(orderId);
    },

    // ==========================================
    // LÓGICA DE ABERTURA DO CHECKOUT
    // ==========================================

    finalizeSale: function () {
        const tableId = document.getElementById('current_table_id').value;

        // VERIFICAÇÃO ESPECIAL: Edição de Pedido Pago
        if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder) {
            this.handlePaidOrderInclusion();
            return;
        }

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

    handlePaidOrderInclusion: function () {
        const cartTotal = PDVCart.calculateTotal();

        if (PDVCart.items.length > 0 && cartTotal > 0.01) {
            CheckoutState.resetPayments();
            document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(cartTotal);
            document.getElementById('checkoutModal').style.display = 'flex';
            this.setMethod('dinheiro');
            this.updateCheckoutUI();
            window.isPaidOrderInclusion = true;
        } else {
            alert('Carrinho vazio! Adicione novos itens para cobrar.');
        }
    },

    fecharContaMesa: function (mesaId) {
        PDVState.set({ modo: 'mesa', mesaId: mesaId, fechandoConta: true });
        const state = PDVState.getState();

        if (state.status === 'editando_pago') {
            alert('Mesa não permite editar pedido pago.');
            return;
        }

        CheckoutState.resetPayments();

        const tableTotalStr = document.getElementById('table-initial-total').value;
        const tableTotal = parseFloat(tableTotalStr);

        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(tableTotal);
        document.getElementById('checkoutModal').style.display = 'flex';
        this.setMethod('dinheiro');
        this.updateCheckoutUI();
    },

    fecharComanda: function (orderId) {
        const isPaid = document.getElementById('current_order_is_paid') ? document.getElementById('current_order_is_paid').value == '1' : false;

        PDVState.set({
            modo: 'comanda',
            pedidoId: orderId ? parseInt(orderId) : null,
            fechandoConta: true
        });

        if (isPaid) {
            if (!confirm('Este pedido já está PAGO. Deseja entregá-lo e finalizar?')) return;
            this.forceDelivery(orderId);
            return;
        }

        CheckoutState.closingOrderId = orderId;
        CheckoutState.resetPayments();

        const totalStr = document.getElementById('table-initial-total').value;
        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(parseFloat(totalStr));

        const cards = document.querySelectorAll('.order-type-card');
        if (cards.length > 0) this.selectOrderType('local', cards[0]);

        document.getElementById('checkoutModal').style.display = 'flex';
        this.setMethod('dinheiro');
        this.updateCheckoutUI();
    },

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

        this.updatePaymentList();

        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(CheckoutState.cachedTotal);
        document.getElementById('checkoutModal').style.display = 'flex';
        this.setMethod('dinheiro');

        // SEMPRE abre com "Local" selecionado
        const localCard = document.querySelector('.order-type-card:first-child');
        if (localCard) {
            this.selectOrderType('local', localCard);
        } else {
            document.getElementById('keep_open_value').value = 'false';
            const alertBox = document.getElementById('retirada-client-alert');
            if (alertBox) alertBox.style.display = 'none';
        }

        this.updateCheckoutUI();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    closeCheckout: function () {
        document.getElementById('checkoutModal').style.display = 'none';
        CheckoutState.resetPayments();

        // Limpa visual
        const alertBox = document.getElementById('retirada-client-alert');
        if (alertBox) alertBox.style.display = 'none';

        // Reset dados de entrega
        if (typeof _resetDeliveryOnClose === 'function') {
            _resetDeliveryOnClose();
        }
    }

};

// ==========================================
// EXPÕE GLOBALMENTE
// ==========================================

window.PDVCheckout = PDVCheckout;

// ==========================================
// ALIASES DE COMPATIBILIDADE (HTML usa esses)
// ==========================================

window.finalizeSale = () => PDVCheckout.finalizeSale();
window.fecharContaMesa = (id) => PDVCheckout.fecharContaMesa(id);
window.fecharComanda = (mid) => PDVCheckout.fecharComanda(mid);
window.includePaidOrderItems = () => PDVCheckout.finalizeSale();
window.saveClientOrder = () => PDVCheckout.saveClientOrder();
window.submitSale = () => PDVCheckout.submitSale();
window.setMethod = (m) => PDVCheckout.setMethod(m);
window.addPayment = () => PDVCheckout.addPayment();
window.removePayment = (i) => PDVCheckout.removePayment(i);
window.closeCheckout = () => PDVCheckout.closeCheckout();
window.selectOrderType = (t, e) => PDVCheckout.selectOrderType(t, e);
