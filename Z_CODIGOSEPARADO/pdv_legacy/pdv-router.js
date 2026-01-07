// ============================================
// PDV-ROUTER.JS - Detecta modo de venda e inicializa
// ============================================

function getModoVenda() {
    const url = new URL(window.location.href);

    if (url.searchParams.has('table_id')) return 'mesa';
    if (url.searchParams.has('order_id') && url.searchParams.has('edit_paid')) return 'retirada';
    if (url.searchParams.has('order_id')) return 'comanda';

    return 'balcao';
}

// Inicialização quando DOM carrega
document.addEventListener('DOMContentLoaded', () => {
    const modo = getModoVenda();
    console.log('[PDV] Modo detectado:', modo);

    // Carrega carrinho recuperado (se houver)
    loadRecoveredCart();

    // Inicializa modo específico
    switch (modo) {
        case 'mesa':
            if (typeof initMesa === 'function') initMesa();
            break;
        case 'comanda':
            if (typeof initComanda === 'function') initComanda();
            break;
        case 'retirada':
            if (typeof initRetirada === 'function') initRetirada();
            break;
        default:
            if (typeof initBalcao === 'function') initBalcao();
    }

    // Atualiza UI do carrinho
    updateCartUI();

    // Configura input de pagamento
    const payInput = document.getElementById('pay-amount');
    if (payInput) {
        payInput.type = 'text';
        payInput.addEventListener('input', function () { formatMoneyInput(this); });
        payInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addPayment();
            }
        });
    }
});

console.log('[PDV] Router carregado ✓');
