// ============================================
// PDV-MESA.JS - Vendas para Mesas
// ============================================

function initMesa() {
    console.log('[PDV] Inicializando modo MESA');

    // Botão salvar (laranja)
    const btn = document.getElementById('btn-finalizar');
    if (btn) {
        btn.innerText = 'Salvar';
        btn.style.backgroundColor = '#d97706';
    }
}

function fecharContaMesa(mesaId) {
    // Prepara checkout para mesa
    isClosingTable = true;
    isClosingCommand = false;
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

console.log('[PDV] Mesa carregado ✓');
