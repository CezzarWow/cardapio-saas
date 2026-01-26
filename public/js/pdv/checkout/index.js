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
 * - CheckoutFlow
 * - CheckoutEntrega
 */

window.PDVCheckout = {

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

    formatMoneyInput: (input) => CheckoutHelpers.formatMoneyInput(input),
    formatCurrency: (val) => CheckoutHelpers.formatCurrency(val),
    formatMethodLabel: (method) => CheckoutHelpers.formatMethodLabel(method),

    // ==========================================
    // DESCONTO / TOTAIS (delegados)
    // ==========================================

    applyDiscount: (valStr) => CheckoutTotals.applyDiscount(valStr),
    getFinalTotal: () => CheckoutTotals.getFinalTotal(),

    // ==========================================
    // UI (delegados)
    // ==========================================

    updatePaymentList: () => CheckoutUI.updatePaymentList(),
    updateCheckoutUI: () => CheckoutUI.updateCheckoutUI(),
    showSuccessModal: () => CheckoutUI.showSuccessModal(),

    // ==========================================
    // PAGAMENTOS (delegados)
    // ==========================================

    setMethod: (method) => CheckoutPayments.setMethod(method),
    addPayment: (m, a) => CheckoutPayments.addPayment(m, a),
    addCrediarioPayment: () => CheckoutPayments.addCrediarioPayment(),
    removePayment: (index) => CheckoutPayments.removePayment(index),

    // ==========================================
    // TIPO DE PEDIDO (delegado)
    // ==========================================

    selectOrderType: (type, element) => CheckoutOrderType.selectOrderType(type, element),

    // ==========================================
    // SUBMIT (delegados)
    // ==========================================

    submitSale: () => CheckoutSubmit.submitSale(),
    saveClientOrder: () => CheckoutSubmit.saveClientOrder(),
    savePickupOrder: () => CheckoutSubmit.savePickupOrder(),
    forceDelivery: (orderId) => CheckoutSubmit.forceDelivery(orderId),

    // ==========================================
    // FLUXO (delegado para CheckoutFlow)
    // ==========================================

    finalizeSale: () => CheckoutFlow.finalizeSale(),
    fecharContaMesa: (mesaId) => CheckoutFlow.fecharContaMesa(mesaId),
    fecharComanda: (orderId) => CheckoutFlow.fecharComanda(orderId),
    openCheckoutModal: () => CheckoutFlow.openCheckoutModal(),
    closeCheckout: () => CheckoutFlow.closeCheckout(),
    handlePaidOrderInclusion: () => CheckoutFlow.handlePaidOrderInclusion()

};

// ==========================================
// EXPÕE GLOBALMENTE
// ==========================================

window.PDVCheckout = PDVCheckout;

// ==========================================
// ALIASES DE COMPATIBILIDADE (HTML usa esses)
// ==========================================

window.finalizeSale = () => {
    window.PDVCheckout.finalizeSale();
};
window.fecharContaMesa = (id) => window.PDVCheckout.fecharContaMesa(id);
window.fecharComanda = (mid) => window.PDVCheckout.fecharComanda(mid);
window.includePaidOrderItems = () => window.PDVCheckout.finalizeSale();
window.saveClientOrder = () => window.PDVCheckout.saveClientOrder();
window.submitSale = () => window.PDVCheckout.submitSale();
window.setMethod = (m) => window.PDVCheckout.setMethod(m);
window.addPayment = (m, a) => window.PDVCheckout.addPayment(m, a);
window.addCrediarioPayment = () => window.PDVCheckout.addCrediarioPayment();
window.removePayment = (i) => window.PDVCheckout.removePayment(i);
window.closeCheckout = () => window.PDVCheckout.closeCheckout();
window.selectOrderType = (t, e) => window.PDVCheckout.selectOrderType(t, e);
