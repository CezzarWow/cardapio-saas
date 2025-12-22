// ============================================
// PDV-SUBMIT.JS - Finalização de Vendas
// ============================================

function finalizeSale() {
    const tableId = document.getElementById('current_table_id').value;

    // VERIFICAÇÃO: Pedido PAGO com novos itens
    if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder) {
        const cartTotal = calculateTotal();

        if (cart.length > 0 && cartTotal > 0.01) {
            // Abre pagamento para cobrar novos itens
            isClosingTable = false;
            isClosingCommand = false;
            currentPayments = [];
            totalPaid = 0;

            document.getElementById('checkout-total-display').innerText = formatCurrency(cartTotal);
            document.getElementById('checkoutModal').style.display = 'flex';
            setMethod('dinheiro');
            updateCheckoutUI();
            return;
        } else {
            alert('Carrinho vazio! Adicione novos itens para cobrar.');
            return;
        }
    }

    // MESA: Salva direto
    if (tableId) {
        if (cart.length === 0) { alert('Carrinho vazio!'); return; }
        isClosingTable = false;
        submitSale();
        return;
    }

    // BALCÃO: Abre pagamento
    if (cart.length === 0) { alert('Carrinho vazio!'); return; }

    isClosingTable = false;
    currentPayments = [];
    totalPaid = 0;

    const total = calculateTotal();
    document.getElementById('checkout-total-display').innerText = formatCurrency(total);
    document.getElementById('checkoutModal').style.display = 'flex';
    setMethod('dinheiro');
    updateCheckoutUI();
}

function submitSale() {
    const tableId = document.getElementById('current_table_id').value;
    const clientId = document.getElementById('current_client_id').value;
    const searchInput = document.getElementById('client-search');

    // Validação do input de busca
    if (searchInput) {
        const searchVal = searchInput.value.trim();
        if (searchVal !== '' && !tableId && !clientId) {
            alert('⚠️ ATENÇÃO: Você digitou algo no campo de busca mas não selecionou nenhum resultado.');
            searchInput.focus();
            return;
        }
    }

    let endpoint = '/admin/loja/venda/finalizar';

    const keepOpenStr = document.getElementById('keep_open_value')?.value || 'false';
    const keepOpen = keepOpenStr === 'true';

    const payload = {
        cart: cart,
        table_id: tableId ? parseInt(tableId) : null,
        client_id: clientId ? parseInt(clientId) : null,
        payments: currentPayments,
        keep_open: keepOpen
    };

    // Inclusão em pedido PAGO
    let wasPaidOrderInclusion = false;
    if (window.isPaidOrderInclusion && typeof editingPaidOrderId !== 'undefined' && editingPaidOrderId) {
        endpoint = '/admin/loja/venda/finalizar';
        payload.order_id = editingPaidOrderId;
        payload.save_account = true;
        wasPaidOrderInclusion = true;
        window.isPaidOrderInclusion = false;
    } else if (isClosingTable) {
        endpoint = '/admin/loja/mesa/fechar';
    } else if (isClosingCommand) {
        endpoint = '/admin/loja/venda/fechar-comanda';
        payload.order_id = closingOrderId;
    }

    const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + endpoint;

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(r => r.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Erro ao parsear resposta:', text);
                throw new Error('Resposta inválida do servidor');
            }
        })
        .then(data => {
            if (data.success) {
                showSuccessModal();

                cart = [];
                currentPayments = [];
                updateCartUI();
                closeCheckout();

                setTimeout(() => {
                    if (wasPaidOrderInclusion) {
                        window.location.reload();
                    } else if (tableId || isClosingCommand) {
                        window.location.href = BASE_URL + '/admin/loja/mesas';
                    } else {
                        if (typeof clearClient === 'function') clearClient();
                    }
                }, 1500);
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro ao processar venda: ' + error.message);
        });
}

console.log('[PDV] Submit carregado ✓');
