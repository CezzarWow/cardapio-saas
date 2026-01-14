/**
 * PDV CHECKOUT - UI
 * Atualização de interface (DOM updates)
 * 
 * Dependências: CheckoutState, CheckoutTotals, CheckoutHelpers
 */

const CheckoutUI = {

    /**
     * Atualiza a lista visual de pagamentos
     */
    updatePaymentList: function () {
        const listEl = document.getElementById('payment-list');
        listEl.innerHTML = '';

        if (CheckoutState.currentPayments.length === 0) {
            listEl.style.display = 'block';
            listEl.innerHTML = '<div style="text-align: center; color: #94a3b8; font-size: 0.9rem; padding: 20px 0;">Nenhum pagamento lançado</div>';
            return;
        }

        listEl.style.display = 'block';
        CheckoutState.currentPayments.forEach((pay, index) => {
            const row = document.createElement('div');
            row.style.cssText = "display: flex; justify-content: space-between; padding: 8px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; align-items: center; margin-bottom: 4px; border-radius: 6px;";
            row.innerHTML = `
                <span style="font-weight:600; color:#334155;">${pay.label}</span>
                <div style="display:flex; align-items:center; gap:10px;">
                    <strong>${CheckoutHelpers.formatCurrency(pay.amount)}</strong>
                    <button onclick="PDVCheckout.removePayment(${index})" style="color:#ef4444; border:none; background:#fee2e2; width:24px; height:24px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
                </div>
            `;
            listEl.appendChild(row);
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    /**
     * Atualiza todos os displays do checkout (total, restante, troco, botão)
     */
    updateCheckoutUI: function () {
        const finalTotal = CheckoutTotals.getFinalTotal();
        const discount = CheckoutState.discountValue || 0;
        const subtotal = finalTotal + discount;

        document.getElementById('display-discount').innerText = '- ' + CheckoutHelpers.formatCurrency(discount);
        document.getElementById('display-paid').innerText = CheckoutHelpers.formatCurrency(CheckoutState.totalPaid);
        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(finalTotal);

        // Atualiza input de Edição de Total (apenas se não estiver editando)
        const totalInput = document.getElementById('display-total-edit');
        if (totalInput && totalInput.readOnly) {
            totalInput.value = finalTotal.toFixed(2).replace('.', ',');
            if (CheckoutHelpers.formatMoneyInput) CheckoutHelpers.formatMoneyInput(totalInput);
        }

        const remaining = finalTotal - CheckoutState.totalPaid;
        const btnFinish = document.getElementById('btn-finish-sale');

        // Feature: Atualiza valor a lançar com o restante atualizado
        const payInput = document.getElementById('pay-amount');
        if (payInput) {
            payInput.value = Math.max(0, remaining).toFixed(2).replace('.', ',');
            if (CheckoutHelpers.formatMoneyInput) CheckoutHelpers.formatMoneyInput(payInput);
        }

        document.getElementById('display-remaining').innerText = CheckoutHelpers.formatCurrency(Math.max(0, remaining));

        const changeBox = document.getElementById('change-display-box');
        const changeBoxOld = document.getElementById('change-box'); // Footer antigo

        if (!btnFinish) return;

        // Sempre atualiza o troco (fixo)
        const changeValue = remaining < 0 ? Math.abs(remaining) : 0;
        if (changeBox) {
            document.getElementById('display-change').innerText = CheckoutHelpers.formatCurrency(changeValue);
        }
        // Esconde o antigo
        if (changeBoxOld) changeBoxOld.style.display = 'none';

        // Lógica: Se falta <= 1 centavo, libera
        if (remaining <= 0.01) {
            btnFinish.disabled = false;
            btnFinish.style.background = '#22c55e';
            btnFinish.style.cursor = 'pointer';
        } else {
            // Falta pagar
            btnFinish.disabled = true;
            btnFinish.style.background = '#cbd5e1';
            btnFinish.style.cursor = 'not-allowed';
        }

        // Validação Extra: Retirada sem Cliente
        const keepOpenInput = document.getElementById('keep_open_value');
        const clientId = document.getElementById('current_client_id')?.value;
        const tableId = document.getElementById('current_table_id')?.value;

        if (keepOpenInput && keepOpenInput.value === 'true' && !clientId && !tableId) {
            btnFinish.disabled = true;
            btnFinish.style.background = '#cbd5e1';
            btnFinish.style.cursor = 'not-allowed';
        }

        // Verificação Crediário
        const credContainer = document.getElementById('container-crediario-slot');
        const credInput = document.getElementById('crediario-amount');
        const credBtn = document.getElementById('btn-add-crediario');
        const credLimitInput = document.getElementById('current_client_credit_limit');
        let creditLimit = 0;

        if (credLimitInput && credLimitInput.value) {
            creditLimit = parseFloat(credLimitInput.value);
        }

        // Ler Dívida (Fetch Async preenche isso)
        const credDebtInput = document.getElementById('current_client_debt');
        let creditDebt = 0;
        if (credDebtInput && credDebtInput.value) {
            creditDebt = parseFloat(credDebtInput.value);
        }

        const lblTotal = document.getElementById('cred-limit-total');
        const lblAvail = document.getElementById('cred-limit-available');

        if (credInput && credBtn) {
            if (creditLimit > 0) {
                credInput.disabled = false;
                credBtn.disabled = false;
                if (credContainer) credContainer.style.opacity = '1';
                credBtn.style.opacity = '1';
                credBtn.style.cursor = 'pointer';
                if (credInput.placeholder === "Sem Limite") credInput.placeholder = "0,00";

                // Atualiza Textos
                if (lblTotal) lblTotal.innerText = CheckoutHelpers.formatCurrency(creditLimit);
                if (lblAvail) {
                    const available = creditLimit - creditDebt;
                    lblAvail.innerText = CheckoutHelpers.formatCurrency(available);
                    // Destaque visual
                    lblAvail.style.color = available >= 0 ? '#15803d' : '#dc2626';
                }
            } else {
                credInput.disabled = true;
                credBtn.disabled = true;
                if (credContainer) credContainer.style.opacity = '0.5';
                credBtn.style.opacity = '0.5';
                credBtn.style.cursor = 'not-allowed';
                credInput.value = '';
                credInput.placeholder = "Sem Limite";

                if (lblTotal) lblTotal.innerText = 'R$ 0,00';
                if (lblAvail) {
                    lblAvail.innerText = 'R$ 0,00';
                    lblAvail.style.color = '#9a3412';
                }
            }
        }
    },

    /**
     * Exibe modal de sucesso temporário
     */
    showSuccessModal: function () {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => modal.style.display = 'none', 1500);
        }
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutUI = CheckoutUI;
