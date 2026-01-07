// ============================================
// PDV-RETIRADA.JS - Pedido Pago para Retirada
// ============================================

function initRetirada() {
    console.log('[PDV] Inicializando modo RETIRADA (pedido pago)');

    // Esconde botões padrão
    const btnSave = document.getElementById('btn-save-command');
    if (btnSave) btnSave.style.display = 'none';

    const btnFinalizar = document.getElementById('btn-finalizar');
    if (btnFinalizar) btnFinalizar.style.display = 'none';

    // Exibe botão "Incluir" (se existir)
    const btnInclude = document.getElementById('btn-include-paid');
    if (btnInclude) btnInclude.style.display = 'flex';
}

function includePaidOrderItems() {
    if (cart.length === 0) {
        alert('Carrinho vazio! Adicione itens para incluir.');
        return;
    }

    const cartTotal = calculateTotal();

    if (cartTotal <= 0.01) {
        alert('Nenhum valor a cobrar.');
        return;
    }

    // Abre modal de pagamento para cobrar novos itens
    isClosingTable = false;
    isClosingCommand = false;
    currentPayments = [];
    totalPaid = 0;

    document.getElementById('checkout-total-display').innerText = formatCurrency(cartTotal);
    document.getElementById('checkoutModal').style.display = 'flex';
    setMethod('dinheiro');
    updateCheckoutUI();

    // Marca para salvar como inclusão
    window.isPaidOrderInclusion = true;
}

function cancelPaidOrder(orderId) {
    if (!confirm('⚠️ ATENÇÃO!\n\nIsso irá CANCELAR o pedido e DEVOLVER o valor ao cliente.\n\nDeseja continuar?')) {
        return;
    }

    fetch(BASE_URL + '/admin/loja/pedidos/cancelar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Pedido cancelado com sucesso!');
                window.location.href = BASE_URL + '/admin/loja/mesas';
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(err => alert('Erro ao cancelar pedido.'));
}

console.log('[PDV] Retirada carregado ✓');
