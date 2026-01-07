/**
 * PDV CHECKOUT - Totals
 * Cálculos de total, desconto e taxas
 * 
 * Dependências: CheckoutState, CheckoutHelpers, CheckoutUI
 */

const CheckoutTotals = {

    /**
     * Aplica desconto e atualiza UI
     * @param {string} valStr - Valor formatado (ex: "10,00")
     */
    applyDiscount: function (valStr) {
        if (!valStr) {
            CheckoutState.discountValue = 0;
        } else {
            let val = valStr.replace(/\./g, '').replace(',', '.');
            CheckoutState.discountValue = parseFloat(val) || 0;
        }

        // Atualiza UI
        if (typeof CheckoutUI !== 'undefined') {
            CheckoutUI.updateCheckoutUI();
        }

        // Atualiza valor sugerido no input de pagamento
        const finalTotal = this.getFinalTotal();
        const remaining = Math.max(0, finalTotal - CheckoutState.totalPaid);
        const input = document.getElementById('pay-amount');
        if (input) {
            input.value = remaining.toFixed(2).replace('.', ',');
            CheckoutHelpers.formatMoneyInput(input);
        }
    },

    /**
     * Calcula o total final (cache + desconto + taxa entrega)
     * @returns {number}
     */
    getFinalTotal: function () {
        // Usa cachedTotal que já inclui table-initial-total + cart
        let total = CheckoutState.cachedTotal || 0;

        // Se editando pago, desconta o original
        if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder && typeof originalPaidTotal !== 'undefined') {
            const diff = total - originalPaidTotal;
            total = diff > 0 ? diff : 0;
        }

        // Aplica desconto
        total = total - CheckoutState.discountValue;

        // Adiciona taxa de entrega se for Entrega com dados preenchidos
        const orderTypeCards = document.querySelectorAll('.order-type-card.active');
        let isDelivery = false;
        orderTypeCards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('entrega')) isDelivery = true;
        });

        if (isDelivery && typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled) {
            if (typeof PDV_DELIVERY_FEE !== 'undefined') {
                total += PDV_DELIVERY_FEE;
            }
        }

        return total > 0 ? total : 0;
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutTotals = CheckoutTotals;
