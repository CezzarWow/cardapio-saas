// ============================================
// PDV-BALCAO.JS - Venda Rápida Balcão
// ============================================

function initBalcao() {
    console.log('[PDV] Inicializando modo BALCÃO');

    // Botão finalizar padrão
    const btn = document.getElementById('btn-finalizar');
    if (btn) {
        btn.innerText = 'Finalizar';
        btn.style.backgroundColor = '#2563eb';
    }
}

function finalizeSaleBalcao() {
    if (cart.length === 0) {
        alert('Carrinho vazio!');
        return;
    }

    // Abre checkout para pagamento
    const total = calculateTotal();
    openCheckout(total);
}

console.log('[PDV] Balcão carregado ✓');
