/**
 * PDV CHECKOUT - Pickup
 * Salvar pedido para pagar depois (Retirada/Entrega)
 * 
 * Dependências: CheckoutState, CheckoutUI, PDVCart
 */

/**
 * Salva pedido de Retirada/Entrega sem pagamento (pagar depois)
 */
window.savePickupOrder = function () {
    // Detecta qual tipo de pedido está selecionado
    const orderTypeCards = document.querySelectorAll('.order-type-card.active');
    let selectedOrderType = 'pickup';

    orderTypeCards.forEach(card => {
        const label = card.innerText.toLowerCase().trim();
        if (label.includes('retirada')) selectedOrderType = 'pickup';
        else if (label.includes('entrega')) selectedOrderType = 'delivery';
    });

    // Para Entrega, verifica se dados foram preenchidos
    if (selectedOrderType === 'delivery') {
        if (typeof deliveryDataFilled === 'undefined' || !deliveryDataFilled) {
            alert('Preencha os dados de entrega primeiro!');
            openDeliveryPanel();
            return;
        }
    }

    // Pega o carrinho
    let cartItems = [];
    if (typeof cart !== 'undefined' && Array.isArray(cart)) {
        cartItems = cart;
    } else if (typeof PDVCart !== 'undefined') {
        cartItems = PDVCart.items;
    }

    if (cartItems.length === 0) {
        alert('Carrinho vazio!');
        return;
    }

    // Pega a forma de pagamento selecionada (para mostrar no Kanban)
    const selectedPaymentMethod = CheckoutState.selectedMethod || 'dinheiro';

    // Taxa de entrega (apenas para delivery)
    const deliveryFee = (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0;

    // Pega a mesa selecionada (se houver) - para vincular entrega à ficha da mesa
    const rawTableId = document.getElementById('current_table_id')?.value;
    const tableId = rawTableId && rawTableId !== '' ? parseInt(rawTableId) : null;

    const payload = {
        cart: cartItems,
        table_id: tableId,
        link_to_table: tableId ? true : false,
        client_id: document.getElementById('current_client_id')?.value || null,
        payments: [],
        discount: CheckoutState.discountValue || 0,
        delivery_fee: deliveryFee,
        keep_open: false,
        finalize_now: true,
        order_type: selectedOrderType,
        is_paid: 0,
        payment_method_expected: selectedPaymentMethod
    };

    // Se for Entrega, adiciona dados de entrega
    if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
        const deliveryData = getDeliveryData();
        if (deliveryData) {
            payload.delivery_data = deliveryData;
        }
    }

    const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/venda/finalizar';

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                CheckoutUI.showSuccessModal();
                PDVCart.clear();
                if (typeof cart !== 'undefined') cart.length = 0;

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(err => alert('Erro de conexão: ' + err.message));
};
