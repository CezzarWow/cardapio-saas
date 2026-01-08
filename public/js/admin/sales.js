/**
 * Sales (Histórico de Vendas)
 * Funções para gerenciar modal de detalhes, cancelamento e reabertura de mesa
 */

const SalesAdmin = {
    /**
     * Abre modal e busca itens do pedido
     */
    openDetails(orderId) {
        const modal = document.getElementById('orderModal');
        const list = document.getElementById('modalItemsList');

        modal.style.display = 'flex';
        list.innerHTML = '<p style="text-align:center; color:#666;">Buscando itens...</p>';

        fetch('vendas/itens?id=' + orderId)
            .then(response => response.json())
            .then(data => {
                if (!data || data.length === 0) {
                    list.innerHTML = '<p>Nenhum item encontrado.</p>';
                    return;
                }

                let html = '<ul style="list-style: none; padding: 0;">';
                data.forEach(item => {
                    const price = parseFloat(item.price).toFixed(2).replace('.', ',');
                    html += `
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                            <div>
                                <span style="font-weight: 600; color: #374151;">${item.quantity}x</span> 
                                ${item.name}
                            </div>
                            <div style="font-weight: 600; color: #1f2937;">
                                R$ ${price}
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                list.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                list.innerHTML = '<p style="color:red;">Erro ao carregar itens.</p>';
            });
    },

    /**
     * Cancela uma venda (Estorna estoque/caixa)
     */
    cancelSale(id) {
        if (!confirm('ATENÇÃO: Isso vai estornar o dinheiro do caixa e devolver os produtos ao estoque.\n\nDeseja realmente cancelar?')) return;

        fetch('vendas/cancelar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    alert('Venda cancelada com sucesso! ✅');
                    location.reload();
                } else {
                    alert('Erro: ' + (d.message || 'Erro desconhecido'));
                }
            })
            .catch(err => alert('Erro de conexão: ' + err));
    },

    /**
     * Reabre mesa (Estorna dinheiro e volta mesa para ocupada)
     */
    reopenTable(id) {
        if (!confirm('Deseja reabrir esta mesa?\nO dinheiro sairá do caixa e a mesa voltará a ficar OCUPADA.')) return;

        fetch('vendas/reabrir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    alert('Mesa reaberta! Volte para o mapa de mesas para editar. 🔄');
                    window.location.href = 'mesas';
                } else {
                    alert('Erro: ' + (d.message || 'Erro desconhecido'));
                }
            })
            .catch(err => alert('Erro de conexão: ' + err));
    },

    /**
     * Fecha modal
     */
    closeModal() {
        document.getElementById('orderModal').style.display = 'none';
    }
};

// Expor globalmente para onclicks (idealmente refatorar para listeners)
window.openOrderDetails = SalesAdmin.openDetails;
window.cancelarVenda = SalesAdmin.cancelSale;
window.reabrirMesa = SalesAdmin.reopenTable;
window.SalesAdmin = SalesAdmin;
