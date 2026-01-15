/**
 * PDV MAIN - Ponto de Entrada
 * Orquestra os módulos: State, Cart, Tables, Checkout.
 */

// Limpa URL após carregar (F5 volta ao balcão limpo)
// Só limpa se tiver order_id ou mesa_id na URL
(function () {
    const url = new URL(window.location.href);
    if (url.searchParams.has('order_id') || url.searchParams.has('mesa_id')) {
        const cleanUrl = url.origin + url.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    // 1. INICIALIZA ESTADO (PDVState)
    const tableIdInput = document.getElementById('current_table_id');
    const clientIdInput = document.getElementById('current_client_id');
    const orderIdInput = document.getElementById('current_order_id');

    const tableId = tableIdInput ? tableIdInput.value : null;
    const clientId = clientIdInput ? clientIdInput.value : null;
    const orderId = orderIdInput ? orderIdInput.value : null;

    // Detecta modo baseado em variáveis PHP
    let modo = 'balcao';
    let status = 'aberto';

    // Variáveis globais injetadas pelo PHP (dashboard.php)
    if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder) {
        modo = 'retirada';
        status = 'editando_pago';
    } else if (tableId) {
        modo = 'mesa';
    } else if (orderId) {
        modo = 'comanda';
    }

    PDVState.set({
        modo: modo,
        mesaId: tableId ? parseInt(tableId) : null,
        clienteId: clientId ? parseInt(clientId) : null,
        pedidoId: orderId ? parseInt(orderId) : null
    });

    PDVState.initStatus(status);
    // 2. INICIALIZA CARRINHO (PDVCart)
    // Recupera carrinho do PHP (Recovered Cart)
    if (typeof recoveredCart !== 'undefined' && recoveredCart.length > 0) {
        // Mapeia formato do PHP para formato do JS (se necessário)
        const items = recoveredCart.map(item => ({
            id: parseInt(item.id),
            name: item.name,
            price: parseFloat(item.price),
            quantity: parseInt(item.quantity)
        }));
        PDVCart.setItems(items);
        // alert('Pedido carregado para edição! ✏️'); // Opcional
    }

    // [MIGRATION] Recupera itens do balcão se houver migração pendente
    if (typeof PDVCart.recoverFromMigration === 'function') {
        PDVCart.recoverFromMigration();
    }


    // 3. INICIALIZA MÓDULOS DE UI
    if (window.PDVTables) PDVTables.init();
    if (window.PDVCheckout) PDVCheckout.init();

    // 4. VISUAL INICIAL
    const btn = document.getElementById('btn-finalizar');
    // FIX: String "0" é truthy em JS, precisamos verificar se é um ID válido (> 0)
    if (parseInt(tableId) > 0 && btn) {
        btn.innerText = "Salvar";
        btn.style.backgroundColor = "#d97706";
        btn.disabled = false;
    }

    // Atualiza a UI do carrinho inicialmente
    PDVCart.updateUI();

    // 5. FILTRO DE CATEGORIAS E BUSCA
    if (window.PDVSearch) {
        PDVSearch.init();
    } else {
        console.warn('PDVSearch module not found');
    }

    // 6. ÍCONES (Lucide)
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

// ============================================
// HELPERS GLOBAIS (Compatibilidade)
// ============================================
function formatCurrency(value) {
    return parseFloat(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
window.formatCurrency = formatCurrency;
