/**
 * CHECKOUT.JS - Orquestrador do Fluxo de Checkout
 * 
 * Módulos externos:
 * - checkout-order.js  → CheckoutOrder (envio de pedidos)
 * - checkout-fields.js → CheckoutFields (toggles e campos)
 * - checkout-modals.js → CheckoutModals (modais UI)
 */

const CardapioCheckout = {
    // Estado
    selectedOrderType: 'entrega',
    selectedPaymentMethod: null,
    hasNoNumber: false,
    hasNoChange: false,

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================
    init: function () {
        // Máscara de telefone
        const phoneInput = document.getElementById('customerPhone');
        if (phoneInput) {
            phoneInput.oninput = function () {
                Utils.formatPhone(this);
            };
        }

        // Listener para fechar modal de pagamento ao clicar fora
        const modal = document.getElementById('paymentModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closePayment();
            });
        }

        // Valida tipo de pedido inicial
        setTimeout(() => {
            const currentRadio = document.querySelector(`input[name="orderType"][value="${this.selectedOrderType}"]`);
            if (!currentRadio || currentRadio.disabled) {
                const enabledRadio = document.querySelector('input[name="orderType"]:not([disabled])');
                if (enabledRadio) {
                    this.setOrderType(enabledRadio.value);
                }
            }
        }, 100);
    },

    // ==========================================
    // TAXAS E TOTAIS
    // ==========================================
    getDeliveryFee: function () {
        if (this.selectedOrderType !== 'entrega') return 0;
        const config = window.cardapioConfig || {};
        return parseFloat(config.delivery_fee || 0);
    },

    getFinalTotal: function () {
        const cartTotal = CardapioCart.getTotals().value;
        return cartTotal + this.getDeliveryFee();
    },

    updateTotals: function () {
        const fee = this.getDeliveryFee();
        const total = this.getFinalTotal();
        const feeRow = document.getElementById('deliveryFeeRow');

        if (feeRow) {
            if (this.selectedOrderType === 'entrega' && fee > 0) {
                feeRow.style.display = 'flex';
                const elDiv = document.getElementById('deliveryFeeValue');
                if (elDiv) elDiv.innerText = Utils.formatCurrency(fee);
            } else {
                feeRow.style.display = 'none';
            }
        }

        const totalFormatted = Utils.formatCurrency(total);
        const reviewTotal = document.getElementById('orderReviewTotal');
        if (reviewTotal) reviewTotal.innerText = totalFormatted;

        const paymentTotal = document.getElementById('paymentTotalValue');
        if (paymentTotal) paymentTotal.innerText = totalFormatted;
    },

    // ==========================================
    // TIPO DE PEDIDO
    // ==========================================
    setOrderType: function (type) {
        this.selectedOrderType = type;

        document.querySelectorAll('input[name="orderType"]').forEach(radio => {
            radio.checked = (radio.value === type);
        });

        this.updateFieldsVisibility();
        this.updateTotals();
    },

    // ==========================================
    // DELEGAÇÕES PARA MÓDULOS EXTERNOS
    // ==========================================

    // Fields (checkout-fields.js)
    updateFieldsVisibility: function () {
        CheckoutFields.updateFieldsVisibility(this.selectedOrderType);
    },
    toggleNoNumber: function () {
        CheckoutFields.toggleNoNumber(this);
    },
    toggleNoChange: function () {
        CheckoutFields.toggleNoChange(this);
    },
    confirmChange: function () {
        CheckoutFields.confirmChange();
    },
    editChange: function () {
        CheckoutFields.editChange(this);
    },
    scrollToChange: function () {
        CheckoutFields.scrollToChange();
    },

    // Modals (checkout-modals.js)
    openOrderReview: function () {
        CheckoutModals.openOrderReview(this);
    },
    closeOrderReview: function () {
        CheckoutModals.closeOrderReview();
    },
    renderReviewItems: function () {
        CheckoutModals.renderReviewItems();
        this.updateTotals();
    },
    goToPayment: function () {
        CheckoutModals.goToPayment(this);
    },
    closePayment: function () {
        CheckoutModals.closePayment();
    },
    backToReview: function () {
        CheckoutModals.backToReview(this);
    },
    selectPaymentMethod: function (method) {
        CheckoutModals.selectPaymentMethod(this, method);
    },

    // Order (checkout-order.js)
    sendOrder: async function () {
        await CheckoutOrder.send(this);
    },

    // ==========================================
    // RESET
    // ==========================================
    reset: function () {
        CardapioCart.clear();
        this.selectedPaymentMethod = null;
        this.selectedOrderType = 'entrega';
        this.hasNoNumber = false;

        document.getElementById('paymentModal').classList.remove('show');
        document.querySelectorAll('input').forEach(i => i.value = '');
        document.getElementById('changeContainer').style.display = 'none';
        document.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);

        this.setOrderType('entrega');
    }
};

// ==========================================
// EXPOR PARA COMPATIBILIDADE
// ==========================================
window.CardapioCheckout = CardapioCheckout;

// Getters globais
try {
    Object.defineProperty(window, 'selectedOrderType', {
        get: () => CardapioCheckout.selectedOrderType,
        set: (val) => CardapioCheckout.selectedOrderType = val,
        configurable: true
    });
    Object.defineProperty(window, 'selectedPaymentMethod', {
        get: () => CardapioCheckout.selectedPaymentMethod,
        set: (val) => CardapioCheckout.selectedPaymentMethod = val,
        configurable: true
    });
    Object.defineProperty(window, 'hasNoNumber', {
        get: () => CardapioCheckout.hasNoNumber,
        set: (val) => CardapioCheckout.hasNoNumber = val,
        configurable: true
    });
} catch (e) {
    console.warn('[CardapioCheckout] Falha ao definir getters globais:', e);
}

// Funções legado


// Evento Global de Pagamento
document.addEventListener('change', function (e) {
    if (e.target.name === 'paymentMethod') {
        CardapioCheckout.selectPaymentMethod(e.target.value);
    }
});
