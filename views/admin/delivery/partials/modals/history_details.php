<?php
/**
 * Modal de Detalhes do Pedido - HISTÓRICO
 * Versão simplificada com ficha única de impressão
 *
 * Refatorado: Usa classes CSS + Acessibilidade
 */
?>
<div id="historyDetailsModal" 
     class="delivery-modal" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="historyDetailsModalTitle"
     aria-hidden="true">
    <div class="delivery-modal__content delivery-modal__content--small">
        
        <!-- Header -->
        <div class="delivery-modal__header" style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 16px 16px 0 0;">
            <div>
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Pedido</div>
                <div id="historyDetailsModalTitle" style="font-size: 1.4rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    #<span id="history-modal-order-id">--</span>
                    <span id="history-modal-order-badge" class="delivery-badge">--</span>
                </div>
            </div>
            <button onclick="HistoryModal.close()" 
                    class="delivery-modal__close" 
                    style="background: rgba(100,116,139,0.1); color: #64748b;"
                    aria-label="Fechar detalhes do pedido">
                <span style="font-size: 1.2rem; font-weight: bold;">✕</span>
            </button>
        </div>

        <!-- Content -->
        <div class="delivery-modal__body">
            <!-- Cliente -->
            <div class="delivery-modal__section">
                <div class="delivery-modal__section-title">Cliente</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1e293b;" id="history-modal-client-name">--</div>
                <div style="font-size: 0.85rem; color: #64748b;" id="history-modal-client-phone">--</div>
            </div>

            <!-- Endereço -->
            <div class="delivery-modal__section">
                <div class="delivery-modal__section-content" style="border-left: 4px solid #3b82f6;">
                    <div class="delivery-modal__section-title" style="margin-bottom: 4px;">Endereço</div>
                    <div style="font-size: 0.95rem; color: #1e293b;" id="history-modal-address">--</div>
                </div>
            </div>

            <!-- Itens -->
            <div class="delivery-modal__section">
                <div class="delivery-modal__section-title">Itens</div>
                <div id="history-modal-items-list" style="font-size: 0.9rem; color: #334155;">
                    <!-- Preenchido via JS -->
                </div>
            </div>

            <!-- Total + Pagamento -->
            <div class="delivery-modal__info-row">
                <div class="delivery-modal__info-card delivery-modal__info-card--success">
                    <div class="delivery-modal__info-label">Total</div>
                    <div id="history-modal-total" class="delivery-modal__info-value">R$ --</div>
                </div>
                <div class="delivery-modal__info-card delivery-modal__info-card--neutral">
                    <div class="delivery-modal__info-label">Pagamento</div>
                    <div id="history-modal-payment" class="delivery-modal__info-value">--</div>
                </div>
            </div>

            <!-- Horário -->
            <div class="delivery-modal__timestamp">
                Pedido realizado em <span id="history-modal-time" style="font-weight: 600;">--</span>
            </div>
        </div>

        <!-- Footer: Impressão Única -->
        <div class="delivery-modal__footer" style="justify-content: center;">
            <button onclick="DeliveryPrint.openModal(HistoryModal.currentOrder?.id, 'complete')" 
                    class="delivery-modal__btn delivery-modal__btn--primary"
                    style="width: 100%;"
                    aria-label="Imprimir ficha completa do pedido">
                🖨️ Imprimir Ficha Completa
            </button>
        </div>
    </div>
</div>

<script>
/**
 * HistoryModal - Modal de detalhes para histórico
 * Usa DeliveryConstants para labels compartilhados
 */
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
            console.error('[History] Erro:', e);
            alert('Erro ao carregar pedido');
        }
    },

    showModal: function(order) {
        const modal = document.getElementById('historyDetailsModal');
        if (!modal) return;

        // Dados básicos
        document.getElementById('history-modal-order-id').textContent = order.id;
        document.getElementById('history-modal-client-name').textContent = order.client_name || 'Não identificado';
        document.getElementById('history-modal-client-phone').textContent = order.client_phone || '--';
        document.getElementById('history-modal-address').textContent = order.client_address || 'Endereço não informado';
        document.getElementById('history-modal-total').textContent = 'R$ ' + parseFloat(order.total || 0).toFixed(2).replace('.', ',');
        document.getElementById('history-modal-time').textContent = order.created_at || '--';
        document.getElementById('history-modal-payment').textContent = DeliveryConstants.getMethodLabel(order.payment_method);

        // Badge de status (usando DeliveryConstants)
        const badge = document.getElementById('history-modal-order-badge');
        badge.textContent = DeliveryConstants.getStatusLabel(order.status);
        badge.className = 'delivery-badge delivery-badge--' + order.status;

        // Lista de itens
        const itemsList = document.getElementById('history-modal-items-list');
        if (order.items && order.items.length > 0) {
            itemsList.innerHTML = order.items.map(item => `
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                    <span>${item.quantity}x ${item.name}</span>
                    <span style="font-weight: 600;">R$ ${parseFloat(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
                </div>
            `).join('');
        } else {
            itemsList.innerHTML = '<div style="color: #94a3b8; text-align: center; padding: 10px;">Itens não disponíveis</div>';
        }

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    close: function() {
        const modal = document.getElementById('historyDetailsModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        this.currentOrder = null;
    }
};
</script>
