/**
 * ============================================
 * DELIVERY JS — Actions
 * Ações de status (avançar, cancelar)
 * 
 * Dependências: constants.js, helpers.js (carregar antes)
 * ============================================
 */

const DeliveryActions = {

    /**
     * Avança para o próximo status
     * @param orderType - 'delivery', 'pickup' ou 'local'
     */
    advance: async function (orderId, currentStatus, orderType = 'delivery') {
        // Pedidos "local" vão para a aba Mesas em vez de avançar normalmente
        if (orderType === 'local') {
            await this.sendToTable(orderId);
            return;
        }

        const transitions = (orderType === 'pickup')
            ? DeliveryConstants.nextStatusPickup
            : DeliveryConstants.nextStatusDelivery;
        const next = transitions[currentStatus];
        if (!next) {
            alert('Este pedido já está no status final.');
            return;
        }
        await this.updateStatus(orderId, next);
    },

    /**
     * Envia pedido Local para a aba Mesas (Clientes/Comanda)
     */
    sendToTable: async function (orderId) {
        const btn = event?.target?.closest('button');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:16px;height:16px;"></i>';
        }

        try {
            const response = await fetch(BASE_URL + '/admin/loja/delivery/send-to-table', {
                method: 'POST',
                headers: DeliveryHelpers.getJsonHeaders(),
                body: JSON.stringify({ order_id: orderId })
            });

            const data = await response.json();

            if (data.success) {
                // SPA Update
                if (window.DeliveryPolling) window.DeliveryPolling.poll();
                else location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Falha ao enviar para mesa'));
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
                headers: DeliveryHelpers.getJsonHeaders(),
                body: JSON.stringify({ order_id: orderId, new_status: newStatus })
            });

            const data = await response.json();

            if (data.success) {
                // SPA Update
                if (window.DeliveryPolling) window.DeliveryPolling.poll();
                else location.reload();
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
