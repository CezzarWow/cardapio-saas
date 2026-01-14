/**
 * PDV CHECKOUT - Payments
 * Lógica de adição/remoção de pagamentos
 * 
 * Dependências: CheckoutState, CheckoutTotals, CheckoutUI, CheckoutHelpers
 */

const CheckoutPayments = {

    /**
     * Seleciona método de pagamento e atualiza visual
     * @param {string} method - 'dinheiro' | 'pix' | 'credito' | 'debito'
     */
    setMethod: function (method) {
        CheckoutState.selectedMethod = method;

        // Visual
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderColor = '#cbd5e1';
            btn.style.background = 'white';
            const icon = btn.querySelector('svg');
            if (icon) icon.style.color = 'currentColor';
        });

        const activeBtn = document.getElementById('btn-method-' + method);
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.borderColor = '#2563eb';
            activeBtn.style.background = '#eff6ff';
            const icon = activeBtn.querySelector('svg');
            if (icon) icon.style.color = '#2563eb';
        }

        // Auto-preenchimento
        const finalTotal = CheckoutTotals.getFinalTotal();
        const remaining = finalTotal - CheckoutState.totalPaid;
        const input = document.getElementById('pay-amount');
        if (remaining > 0) {
            input.value = remaining.toFixed(2).replace('.', ',');
        } else {
            input.value = '';
        }
        setTimeout(() => input.focus(), 100);
    },

    /**
     * Adiciona um pagamento à lista
     */
    addPayment: function (forceMethod, forceAmount) {
        let amount = 0;
        let isManual = false;

        if (typeof forceAmount !== 'undefined') {
            amount = parseFloat(forceAmount);
        } else {
            const amountInput = document.getElementById('pay-amount');
            let valStr = amountInput.value.trim();
            if (valStr.includes(',')) valStr = valStr.replace(/\./g, '').replace(',', '.');
            amount = parseFloat(valStr);
            isManual = true;
        }

        const method = forceMethod || CheckoutState.selectedMethod;

        if (!amount || amount <= 0 || isNaN(amount)) {
            alert('Digite um valor válido.');
            return;
        }

        const finalTotal = CheckoutTotals.getFinalTotal();
        const remaining = finalTotal - CheckoutState.totalPaid;

        // Regra de troco: se não for dinheiro, trava no restante
        // Permite 1 centavo de tolerância
        if (method !== 'dinheiro' && amount > remaining + 0.01) {
            amount = remaining;
            if (amount <= 0.01) { alert('Valor restante já pago!'); return; }
        }

        CheckoutState.currentPayments.push({
            method: method,
            amount: amount,
            label: (method === 'crediario' ? 'Crediário' : CheckoutHelpers.formatMethodLabel(method))
        });
        CheckoutState.totalPaid += amount;

        if (isManual) {
            document.getElementById('pay-amount').value = '';
        }

        CheckoutUI.updatePaymentList();
        CheckoutUI.updateCheckoutUI();

        // Foca no botão se terminou
        const newRemaining = finalTotal - CheckoutState.totalPaid;
        if (newRemaining <= 0.01) {
            document.getElementById('btn-finish-sale').focus();
        } else {
            let rest = newRemaining.toFixed(2).replace('.', ',');
            if (isManual) document.getElementById('pay-amount').value = rest;
            if (isManual) document.getElementById('pay-amount').focus();
        }
    },

    addCrediarioPayment: function () {
        const input = document.getElementById('crediario-amount');
        if (!input) return;

        let valStr = input.value.trim();
        if (valStr.includes(',')) valStr = valStr.replace(/\./g, '').replace(',', '.');
        let amount = parseFloat(valStr);

        if (!amount || amount <= 0) {
            alert('Digite um valor para o Crediário.');
            return;
        }

        this.addPayment('crediario', amount);
        input.value = '';
    },

    /**
     * Remove um pagamento da lista
     * @param {number} index 
     */
    removePayment: function (index) {
        const removed = CheckoutState.currentPayments.splice(index, 1)[0];
        CheckoutState.totalPaid -= removed.amount;

        CheckoutUI.updatePaymentList();
        CheckoutUI.updateCheckoutUI();

        // Restaura o valor restante no campo
        const finalTotal = CheckoutTotals.getFinalTotal();
        const remaining = Math.max(0, finalTotal - CheckoutState.totalPaid);
        const input = document.getElementById('pay-amount');
        if (input && remaining > 0) {
            input.value = remaining.toFixed(2).replace('.', ',');
            CheckoutHelpers.formatMoneyInput(input);
            input.focus();
        }
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutPayments = CheckoutPayments;
