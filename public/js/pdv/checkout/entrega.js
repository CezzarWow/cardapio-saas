/**
 * PDV CHECKOUT - Entrega
 * Funções do painel de dados de entrega
 * 
 * Dependências: CheckoutUI, CheckoutOrderType, CheckoutTotals
 */

// Flag global para saber se dados de entrega foram preenchidos
let deliveryDataFilled = false;

/**
 * Abre o painel de entrega
 */
window.openDeliveryPanel = function () {
    const panel = document.getElementById('delivery-panel');
    if (!panel) return;

    // Só auto-preenche nome se realmente tiver cliente/mesa selecionado
    const clientId = document.getElementById('current_client_id')?.value;
    const tableId = document.getElementById('current_table_id')?.value;

    if (clientId || tableId) {
        const clientName = document.getElementById('current_client_name')?.value ||
            document.getElementById('current_table_name')?.value || '';

        if (clientName && clientName.trim()) {
            document.getElementById('delivery_name').value = clientName.replace('🔹 ', '').split(' (')[0].trim();
        }
    }

    // Mostra o painel
    panel.style.display = 'flex';

    // Foca no primeiro campo vazio
    const nameInput = document.getElementById('delivery_name');
    const addressInput = document.getElementById('delivery_address');
    if (nameInput && !nameInput.value) {
        nameInput.focus();
    } else if (addressInput) {
        addressInput.focus();
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
};

/**
 * Fecha o painel de entrega
 */
window.closeDeliveryPanel = function () {
    const panel = document.getElementById('delivery-panel');
    if (panel) panel.style.display = 'none';
};

/**
 * Confirma dados de entrega e atualiza total com taxa
 */
window.confirmDeliveryData = function () {
    // Valida campos obrigatórios
    const name = document.getElementById('delivery_name').value.trim();
    const address = document.getElementById('delivery_address').value.trim();
    const neighborhood = document.getElementById('delivery_neighborhood').value.trim();

    if (!name) {
        alert('Digite o nome do cliente!');
        document.getElementById('delivery_name').focus();
        return;
    }
    if (!address) {
        alert('Digite o endereço!');
        document.getElementById('delivery_address').focus();
        return;
    }
    if (!neighborhood) {
        alert('Digite o bairro!');
        document.getElementById('delivery_neighborhood').focus();
        return;
    }

    // Marca como preenchido
    deliveryDataFilled = true;

    // Fecha o painel
    closeDeliveryPanel();

    // Atualiza o alerta para mostrar que dados estão OK
    const alertEntrega = document.getElementById('entrega-alert');
    const dadosOk = document.getElementById('entrega-dados-ok');
    const dadosPendente = document.getElementById('entrega-dados-pendente');

    if (alertEntrega) alertEntrega.style.display = 'block';
    if (dadosOk) dadosOk.style.display = 'block';
    if (dadosPendente) dadosPendente.style.display = 'none';

    if (typeof lucide !== 'undefined') lucide.createIcons();

    // Re-executa selectOrderType para atualizar botões
    CheckoutOrderType.selectOrderType('entrega');

    // Atualiza o TOTAL exibido com a taxa de entrega
    let newTotal = CheckoutTotals.getFinalTotal();

    const totalDisplay = document.getElementById('checkout-total-display');
    if (totalDisplay) {
        totalDisplay.innerText = 'R$ ' + newTotal.toFixed(2).replace('.', ',');
    }

    // Atualiza o Input "Valor a Lançar"
    const payInput = document.getElementById('pay-amount');
    const paidDisplay = document.getElementById('display-paid');

    if (payInput) {
        let paidValue = 0;
        if (paidDisplay) {
            const raw = paidDisplay.innerText.replace(/[^\d,]/g, '').replace(',', '.');
            paidValue = parseFloat(raw) || 0;
        }

        if (paidValue < 0.01) {
            payInput.value = newTotal.toFixed(2).replace('.', ',');
            payInput.dispatchEvent(new Event('input'));
        }
    }

    // Atualiza UI do checkout
    CheckoutUI.updateCheckoutUI();
};

/**
 * Retorna objeto com dados de entrega preenchidos
 * @returns {Object|null}
 */
window.getDeliveryData = function () {
    if (!deliveryDataFilled) return null;

    return {
        name: document.getElementById('delivery_name')?.value || '',
        address: document.getElementById('delivery_address')?.value || '',
        number: document.getElementById('delivery_number')?.value || '',
        neighborhood: document.getElementById('delivery_neighborhood')?.value || '',
        phone: document.getElementById('delivery_phone')?.value || '',
        complement: document.getElementById('delivery_complement')?.value || ''
    };
};

/**
 * Limpa dados de entrega
 */
window.clearDeliveryData = function () {
    deliveryDataFilled = false;

    // Limpa campos
    ['delivery_name', 'delivery_address', 'delivery_number', 'delivery_neighborhood', 'delivery_phone', 'delivery_complement'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    // Atualiza alertas
    const dadosOk = document.getElementById('entrega-dados-ok');
    const dadosPendente = document.getElementById('entrega-dados-pendente');

    if (dadosOk) dadosOk.style.display = 'none';
    if (dadosPendente) dadosPendente.style.display = 'block';

    if (typeof lucide !== 'undefined') lucide.createIcons();
    CheckoutUI.updateCheckoutUI();
};

/**
 * Função para resetar dados de entrega ao fechar checkout
 * Será chamada pelo PDVCheckout.closeCheckout
 */
window._resetDeliveryOnClose = function () {
    deliveryDataFilled = false;
    closeDeliveryPanel();

    // Limpa campos
    ['delivery_name', 'delivery_address', 'delivery_number', 'delivery_neighborhood', 'delivery_phone', 'delivery_complement'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
};
