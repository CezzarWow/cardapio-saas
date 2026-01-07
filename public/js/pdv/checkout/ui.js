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
            row.style.cssText = "display: flex; justify-content: space-between; padding: 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; align-items: center; margin-bottom: 5px; border-radius: 6px;";
            row.innerHTML = `
                <span style="font-weight:600; color:#334155;">${pay.label}</span>
                <div style="display:flex; align-items:center; gap:10px;">
                    <strong>${CheckoutHelpers.formatCurrency(pay.amount)}</strong>
                    <button onclick="PDVCheckout.removePayment(${index})" style="color:#ef4444; border:none; background:#fee2e2; width:24px; height:24px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
                </div>
            `;
            listEl.appendChild(row);
        });

        // Auto-scroll para o final
        setTimeout(() => {
            listEl.scrollTop = listEl.scrollHeight;
        }, 50);

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

        const remaining = finalTotal - CheckoutState.totalPaid;
        const btnFinish = document.getElementById('btn-finish-sale');

        document.getElementById('display-remaining').innerText = CheckoutHelpers.formatCurrency(Math.max(0, remaining));

        const changeBox = document.getElementById('change-box');

        if (!btnFinish) return;

        // Lógica: Se falta <= 1 centavo, libera
        if (remaining <= 0.01) {
            if (remaining < -0.01) {
                // Tem troco
                changeBox.style.display = 'block';
                document.getElementById('checkout-change').innerText = CheckoutHelpers.formatCurrency(Math.abs(remaining));
            } else {
                changeBox.style.display = 'none';
            }
            btnFinish.disabled = false;
            btnFinish.style.background = '#22c55e';
            btnFinish.style.cursor = 'pointer';
        } else {
            // Falta pagar
            changeBox.style.display = 'none';
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
