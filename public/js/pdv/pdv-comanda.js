// ============================================
// PDV-COMANDA.JS - Comandas de Cliente
// ============================================

function initComanda() {
    console.log('[PDV] Inicializando modo COMANDA');

    // Exibe botão salvar comanda (se existir)
    const btnSave = document.getElementById('btn-save-command');
    if (btnSave) {
        btnSave.style.display = 'flex';
    }

    // Esconde finalizar padrão
    const btnFinalizar = document.getElementById('btn-finalizar');
    if (btnFinalizar) {
        btnFinalizar.style.display = 'none';
    }

    // Alerta sobre carrinho recuperado
    if (typeof recoveredCart !== 'undefined' && recoveredCart.length > 0) {
        console.log('[PDV] Pedido carregado para edição');
    }
}

function saveClientOrder() {
    if (cart.length === 0) {
        alert('O carrinho está vazio!');
        return;
    }

    const clientId = document.getElementById('current_client_id').value;
    const orderId = document.getElementById('current_order_id')?.value || null;

    if (!clientId) {
        alert('Nenhum cliente selecionado!');
        return;
    }

    fetch('venda/finalizar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cart: cart,
            client_id: clientId,
            order_id: orderId,
            save_account: true
        })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSuccessModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(err => alert('Erro de conexão.'));
}

function fecharComanda(orderId) {
    isClosingTable = false;
    isClosingCommand = true;
    closingOrderId = orderId;
    currentPayments = [];
    totalPaid = 0;

    const tableTotalStr = document.getElementById('table-initial-total')?.value || '0';
    const tableTotal = parseFloat(tableTotalStr);

    document.getElementById('checkout-total-display').innerText = formatCurrency(tableTotal);
    document.getElementById('checkoutModal').style.display = 'flex';
    setMethod('dinheiro');
    updateCheckoutUI();

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

console.log('[PDV] Comanda carregado ✓');
