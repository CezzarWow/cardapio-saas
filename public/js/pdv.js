/**
 * PDV MAIN - Ponto de Entrada
 * Orquestra os módulos: State, Cart, Tables, Checkout.
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('[PDV Main] Inicializando...');

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
    console.log('[PDV Main] Estado Inicial:', PDVState.getState());

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
        console.log('[PDV Main] Carrinho recuperado:', items.length, 'itens');
        // alert('Pedido carregado para edição! ✏️'); // Opcional
    }

    // 3. INICIALIZA MÓDULOS DE UI
    if (window.PDVTables) PDVTables.init();
    if (window.PDVCheckout) PDVCheckout.init();

    // 4. VISUAL INICIAL
    const btn = document.getElementById('btn-finalizar');
    if (tableId && btn) {
        btn.innerText = "Salvar";
        btn.style.backgroundColor = "#d97706";
        btn.disabled = false;
    }

    // Atualiza a UI do carrinho inicialmente
    PDVCart.updateUI();

    // 5. FILTRO DE CATEGORIAS (Chips)
    let selectedCategory = '';

    function filterPdvProducts() {
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const cat = card.dataset.category;
            card.style.display = (!selectedCategory || cat === selectedCategory) ? '' : 'none';
        });
    }

    document.querySelectorAll('.pdv-category-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.pdv-category-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            selectedCategory = this.dataset.category;
            filterPdvProducts();
        });
    });

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
