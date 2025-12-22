// ============================================
// PDV-CHECKOUT.JS - Modal de Pagamento
// ============================================
// Funções compartilhadas de pagamento (usadas por todos os modos)

function setMethod(method) {
    selectedMethod = method;

    // Visual dos botões
    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.style.borderColor = '#e2e8f0';
        btn.style.background = 'white';
        btn.style.color = '#475569';
    });

    const activeBtn = document.getElementById('btn-' + method);
    if (activeBtn) {
        activeBtn.style.borderColor = '#2563eb';
        activeBtn.style.background = '#eff6ff';
        activeBtn.style.color = '#2563eb';
    }

    // Auto-preenchimento
    const finalTotal = getFinalTotal();
    const remaining = finalTotal - totalPaid;

    const input = document.getElementById('pay-amount');
    if (remaining > 0) {
        let val = remaining.toFixed(2).replace('.', ',');
        input.value = val;
    } else {
        input.value = '';
    }
}

function getFinalTotal() {
    if (isClosingTable || isClosingCommand) {
        const tableTotalStr = document.getElementById('table-initial-total').value;
        return parseFloat(tableTotalStr) || 0;
    }

    let total = calculateTotal();

    // Se editando pedido PAGO, subtrai o que já foi pago
    if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder && typeof originalPaidTotal !== 'undefined') {
        const difference = total - originalPaidTotal;
        return difference > 0 ? difference : 0;
    }

    return total;
}

function addPayment() {
    const amountInput = document.getElementById('pay-amount');
    let rawValue = amountInput.value.replace(/\./g, '').replace(',', '.');
    let amount = parseFloat(rawValue);

    if (!amount || amount <= 0) return;

    const finalTotal = getFinalTotal();
    const remaining = finalTotal - totalPaid;

    // Troco só em dinheiro
    if (selectedMethod !== 'dinheiro' && amount > remaining) {
        amount = remaining;
        if (amount <= 0.01) {
            alert('Valor restante já foi pago!');
            return;
        }
    }

    currentPayments.push({
        method: selectedMethod,
        amount: amount
    });

    totalPaid += amount;
    updateCheckoutUI();
    amountInput.value = '';
}

function removePayment(index) {
    totalPaid -= currentPayments[index].amount;
    currentPayments.splice(index, 1);
    updateCheckoutUI();
}

function updateCheckoutUI() {
    const finalTotal = getFinalTotal();

    // Atualiza resumo de pagamentos
    const listEl = document.getElementById('payment-list');
    if (!listEl) return;

    if (currentPayments.length === 0) {
        listEl.style.display = 'none';
    } else {
        listEl.style.display = 'block';
        listEl.innerHTML = currentPayments.map((p, i) => `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                <span style="font-weight: 600; color: #334155;">${formatMethodLabel(p.method)}</span>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 700; color: #16a34a;">R$ ${p.amount.toFixed(2).replace('.', ',')}</span>
                    <button onclick="removePayment(${i})" style="background: #fee2e2; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; color: #991b1b; font-weight: bold;">×</button>
                </div>
            </div>
        `).join('');
    }

    // Calcula restante
    const remaining = finalTotal - totalPaid;

    const remainingEl = document.getElementById('checkout-remaining');
    const changeEl = document.getElementById('checkout-change');
    const changeBox = document.getElementById('change-box');
    const btnFinish = document.getElementById('btn-finish-sale');
    const remainingBox = document.getElementById('remaining-box');

    if (!remainingEl || !changeEl || !changeBox || !btnFinish || !remainingBox) return;

    if (remaining > 0.01) {
        // FALTA PAGAR
        remainingBox.style.display = 'block';
        changeBox.style.display = 'none';
        remainingEl.innerText = 'R$ ' + remaining.toFixed(2).replace('.', ',');
        remainingEl.style.color = '#ef4444';

        btnFinish.disabled = true;
        btnFinish.style.background = '#cbd5e1';
        btnFinish.style.cursor = 'not-allowed';
    } else {
        // PAGO OU TEM TROCO
        btnFinish.disabled = false;
        btnFinish.style.background = '#16a34a';
        btnFinish.style.cursor = 'pointer';

        if (remaining < -0.01) {
            // TEM TROCO
            remainingBox.style.display = 'none';
            changeBox.style.display = 'flex';
            changeEl.innerText = 'R$ ' + Math.abs(remaining).toFixed(2).replace('.', ',');
        } else {
            // CONTA EXATA
            remainingBox.style.display = 'block';
            remainingEl.innerText = 'R$ 0,00';
            remainingEl.style.color = '#166534';
            changeBox.style.display = 'none';
        }
    }
}

function formatMethodLabel(method) {
    const labels = {
        'dinheiro': '💵 Dinheiro',
        'pix': '📱 PIX',
        'credito': '💳 Crédito',
        'debito': '💳 Débito'
    };
    return labels[method] || method;
}

function closeCheckout() {
    document.getElementById('checkoutModal').style.display = 'none';
    currentPayments = [];
    totalPaid = 0;
}

function openCheckout(total) {
    isClosingTable = false;
    isClosingCommand = false;
    currentPayments = [];
    totalPaid = 0;

    document.getElementById('checkout-total-display').innerText = formatCurrency(total);
    document.getElementById('checkoutModal').style.display = 'flex';
    setMethod('dinheiro');
    updateCheckoutUI();
}

console.log('[PDV] Checkout carregado ✓');
