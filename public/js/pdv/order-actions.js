/**
 * PDV ORDER ACTIONS - Ações de Pedido/Mesa/Comanda
 * 
 * Funções para deletar itens e cancelar pedidos.
 * Dependências: BASE_URL (global)
 */

window.PDVOrderActions = {

    /**
     * Deleta item já salvo da mesa/comanda
     * @param {number} itemId - ID do order_item
     * @param {number} orderId - ID do pedido
     */
    deleteSavedItem: function (itemId, orderId) {
        if (!confirm('Remover este item do pedido?')) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(BASE_URL + '/admin/loja/venda/remover-item', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ item_id: itemId, order_id: orderId })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Recarrega seção atual via SPA
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.reloadCurrentSection();
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert('Erro: ' + (data.message || 'Não foi possível remover o item'));
                }
            })
            .catch(err => {
                alert('Erro de conexão: ' + err.message);
            });
    },

    /**
     * Cancela todo o pedido da mesa
     * @param {number} tableId - ID da mesa
     * @param {number} orderId - ID do pedido
     */
    cancelTableOrder: function (tableId, orderId) {
        if (!confirm('ATENÇÃO: Isso cancelará TODO o pedido desta mesa.\n\nOs itens voltarão ao estoque.\n\nDeseja continuar?')) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(BASE_URL + '/admin/loja/mesa/cancelar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ table_id: tableId, order_id: orderId })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Pedido cancelado com sucesso!');
                    // Navega para mesas via SPA
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.navigateTo('mesas', true, true);
                    } else {
                        window.location.href = BASE_URL + '/admin/loja/mesas';
                    }
                } else {
                    alert('Erro: ' + (data.message || 'Não foi possível cancelar o pedido'));
                }
            })
            .catch(err => {
                alert('Erro de conexão: ' + err.message);
            });
    }
};

// Expõe globalmente
// window.PDVOrderActions = PDVOrderActions; // Já definido acima

// Aliases globais para compatibilidade com onclick no HTML
window.deleteSavedItem = (itemId, orderId) => PDVOrderActions.deleteSavedItem(itemId, orderId);
window.cancelTableOrder = (tableId, orderId) => PDVOrderActions.cancelTableOrder(tableId, orderId);
