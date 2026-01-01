/**
 * ============================================
 * DELIVERY JS — Actions
 * FASE 3: Ações de status (aceitar, avançar, cancelar)
 * ============================================
 */

const DeliveryActions = {

    // Labels para exibição
    statusLabels: {
        'novo': 'Novo',
        'aceito': 'Aceito',
        'preparo': 'Em Preparo',
        'rota': 'Em Rota',
        'entregue': 'Entregue',
        'cancelado': 'Cancelado'
    },

    // Próximo status na cadeia (sem aceito)
    nextStatus: {
        'novo': 'preparo',
        'preparo': 'rota',
        'rota': 'entregue'
    },

    /**
     * Avança para o próximo status
     */
    advance: async function (orderId, currentStatus) {
        const next = this.nextStatus[currentStatus];
        if (!next) {
            alert('Este pedido já está no status final.');
            return;
        }
        await this.updateStatus(orderId, next);
    },

    /**
     * Aceita pedido novo
     */
    accept: async function (orderId) {
        await this.updateStatus(orderId, 'aceito');
    },

    /**
     * Cancela pedido
     */
    cancel: async function (orderId) {
        if (!confirm('Tem certeza que deseja CANCELAR este pedido?')) return;
        await this.updateStatus(orderId, 'cancelado');
    },

    /**
     * Envia requisição de update
     */
    updateStatus: async function (orderId, newStatus) {
        const btn = event?.target?.closest('button');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:16px;height:16px;"></i>';
        }

        try {
            const response = await fetch(BASE_URL + '/admin/loja/delivery/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, new_status: newStatus })
            });

            const data = await response.json();

            if (data.success) {
                // Recarrega a página para mostrar novo estado
                location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Falha ao atualizar'));
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Tentar novamente';
                }
            }
        } catch (err) {
            alert('Erro de conexão: ' + err.message);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Tentar novamente';
            }
        }
    }
};

// Expõe globalmente
window.DeliveryActions = DeliveryActions;

console.log('[Delivery] Actions carregado ✓');
