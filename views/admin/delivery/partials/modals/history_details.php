<?php
/**
 * Modal de Detalhes do Pedido - HISTÓRICO
 * Versão simplificada com ficha única de impressão
 */
?>
<div id="historyDetailsModal" class="delivery-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div class="delivery-modal-content" style="background: white; border-radius: 16px; width: 100%; max-width: 420px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        
        <!-- Header -->
        <div style="padding: 16px 20px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 16px 16px 0 0;">
            <div>
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Pedido</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    #<span id="history-modal-order-id">--</span>
                    <span id="history-modal-order-badge" class="delivery-badge">--</span>
                </div>
            </div>
            <button onclick="HistoryModal.close()" 
                    style="background: rgba(100,116,139,0.1); border: none; cursor: pointer; padding: 8px 12px; border-radius: 8px; color: #64748b; font-size: 1.2rem; font-weight: bold;">
                ✕
            </button>
        </div>

        <!-- Content -->
        <div style="padding: 20px;">
            <!-- Cliente -->
            <div style="margin-bottom: 16px;">
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Cliente</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1e293b;" id="history-modal-client-name">--</div>
                <div style="font-size: 0.85rem; color: #64748b;" id="history-modal-client-phone">--</div>
            </div>

            <!-- Endereço -->
            <div style="margin-bottom: 16px; padding: 12px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6;">
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Endereço</div>
                <div style="font-size: 0.95rem; color: #1e293b;" id="history-modal-address">--</div>
            </div>

            <!-- Itens -->
            <div style="margin-bottom: 16px;">
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Itens</div>
                <div id="history-modal-items-list" style="font-size: 0.9rem; color: #334155;">
                    <!-- Preenchido via JS -->
                </div>
            </div>

            <!-- Total + Pagamento -->
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1; background: #dcfce7; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 0.75rem; color: #166534; text-transform: uppercase;">Total</div>
                    <div id="history-modal-total" style="font-size: 1.2rem; font-weight: 800; color: #166534;">R$ --</div>
                </div>
                <div style="flex: 1; background: #f1f5f9; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Pagamento</div>
                    <div id="history-modal-payment" style="font-size: 1rem; font-weight: 700; color: #334155;">--</div>
                </div>
            </div>

            <!-- Horário -->
            <div style="margin-top: 12px; text-align: center; font-size: 0.8rem; color: #94a3b8;">
                Pedido realizado em <span id="history-modal-time" style="font-weight: 600;">--</span>
            </div>
        </div>

        <!-- Footer: Impressão Única -->
        <div style="padding: 16px 20px; border-top: 2px solid #e2e8f0; background: #f8fafc;">
            <button onclick="DeliveryPrint.openModal(HistoryModal.currentOrder?.id, 'complete')" 
                    style="width: 100%; padding: 14px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 1rem; box-shadow: 0 2px 8px rgba(59,130,246,0.3);">
                🖨️ Imprimir Ficha Completa
            </button>
        </div>
    </div>
</div>

<script>
const HistoryModal = {
    currentOrder: null,

    open: async function(orderId) {
        try {
            const response = await fetch(BASE_URL + '/admin/loja/delivery/details?id=' + orderId);
            const result = await response.json();
            
            if (!result.success) {
                alert('Erro ao carregar pedido');
                return;
            }
            
            const order = { ...result.order, items: result.items || [] };
            this.currentOrder = order;
            this.showModal(order);
        } catch (e) {
            console.error('Erro:', e);
            alert('Erro ao carregar pedido');
        }
    },

    showModal: function(order) {
        const modal = document.getElementById('historyDetailsModal');
        if (!modal) return;

        document.getElementById('history-modal-order-id').textContent = order.id;
        document.getElementById('history-modal-client-name').textContent = order.client_name || 'Não identificado';
        document.getElementById('history-modal-client-phone').textContent = order.client_phone || '--';
        document.getElementById('history-modal-address').textContent = order.client_address || 'Endereço não informado';
        document.getElementById('history-modal-total').textContent = 'R$ ' + parseFloat(order.total || 0).toFixed(2).replace('.', ',');
        document.getElementById('history-modal-time').textContent = order.created_at || '--';
        document.getElementById('history-modal-payment').textContent = order.payment_method || 'Não informado';

        // Badge de status
        const badge = document.getElementById('history-modal-order-badge');
        const statusLabels = { 'novo': 'Novo', 'preparo': 'Em Preparo', 'rota': 'Em Rota', 'entregue': 'Entregue', 'cancelado': 'Cancelado' };
        badge.textContent = statusLabels[order.status] || order.status;
        badge.className = 'delivery-badge delivery-badge--' + order.status;

        // Lista de itens
        const itemsList = document.getElementById('history-modal-items-list');
        if (order.items && order.items.length > 0) {
            let html = '';
            order.items.forEach(item => {
                html += `<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                    <span>${item.quantity}x ${item.name}</span>
                    <span style="font-weight: 600;">R$ ${parseFloat(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
                </div>`;
            });
            itemsList.innerHTML = html;
        } else {
            itemsList.innerHTML = '<div style="color: #94a3b8; text-align: center; padding: 10px;">Itens não disponíveis</div>';
        }

        modal.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    close: function() {
        const modal = document.getElementById('historyDetailsModal');
        if (modal) modal.style.display = 'none';
        this.currentOrder = null;
    }
};
</script>
