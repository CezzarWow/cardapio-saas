/**
 * ============================================
 * DELIVERY JS — UI (Modais)
 * Abrir/fechar modais, sem lógica de negócio
 * 
 * Dependência: constants.js (carregar antes)
 * ============================================
 */

const DeliveryUI = {

    // Dados do pedido atual (para modais)
    currentOrder: null,

    /**
     * Abre modal de detalhes (aceita objeto ou ID)
     */
    openDetailsModal: async function (orderDataOrId) {
        // Se recebeu apenas ID, busca dados da API
        if (typeof orderDataOrId === 'number' || typeof orderDataOrId === 'string') {
            try {
                const response = await fetch(BASE_URL + '/admin/loja/delivery/details?id=' + orderDataOrId);
                if (!response.ok) throw new Error('Erro ao buscar pedido');
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Erro ao buscar pedido');
                }

                // Monta objeto com dados do pedido + itens
                const orderData = {
                    ...result.order,
                    items: result.items || []
                };

                this.showDetailsModal(orderData);
            } catch (e) {
                console.error('[Delivery] Erro ao buscar detalhes:', e);
                alert('Erro ao carregar detalhes do pedido');
            }
        } else {
            // Recebeu objeto completo
            this.showDetailsModal(orderDataOrId);
        }
    },

    // [FIX] Acessibilidade: Guarda elemento que tinha foco antes de abrir modal
    previouslyFocused: null,

    /**
     * Exibe modal com dados do pedido
     */
    showDetailsModal: function (orderData) {
        this.currentOrder = orderData;

        const modal = document.getElementById('deliveryDetailsModal');
        if (!modal) return;

        // [FIX] Guarda elemento focado para restaurar depois
        this.previouslyFocused = document.activeElement;

        // Preenche dados
        document.getElementById('modal-order-id').textContent = orderData.id;
        document.getElementById('modal-client-name').textContent = orderData.client_name || 'Não identificado';
        document.getElementById('modal-client-phone').textContent = orderData.client_phone || '--';

        // Formata endereço completo
        let fullAddress = orderData.client_address || 'Endereço não informado';
        if (orderData.client_number) fullAddress += ', ' + orderData.client_number;
        if (orderData.client_neighborhood) fullAddress += ' - ' + orderData.client_neighborhood;

        document.getElementById('modal-address').textContent = fullAddress;

        // Observação do Pedido (Adicionar elemento se não existir no HTML, mas vamos injetar via JS)
        const addressContainer = document.getElementById('modal-address').parentElement.parentElement;
        let obsContainer = document.getElementById('modal-observation-container');

        if (!obsContainer) {
            obsContainer = document.createElement('div');
            obsContainer.id = 'modal-observation-container';
            obsContainer.style.marginBottom = '16px';
            addressContainer.after(obsContainer);
        }

        if (orderData.observation) {
            obsContainer.innerHTML = `
                <h4 style="font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase;">Observação</h4>
                <div style="background: #fff7ed; padding: 12px; border-radius: 8px; border: 1px solid #ffedd5; color: #c2410c; font-size: 0.9rem;">
                    ${orderData.observation}
                </div>
            `;
            obsContainer.style.display = 'block';
        } else {
            obsContainer.style.display = 'none';
        }

        document.getElementById('modal-total').textContent = 'R$ ' + parseFloat(orderData.total || 0).toFixed(2).replace('.', ',');
        document.getElementById('modal-time').textContent = orderData.created_at || '--';

        // [NOVO] Exibe status de pagamento
        const paymentEl = document.getElementById('modal-payment');
        const paymentContainer = paymentEl.parentElement;

        // [DEBUG] Forçar conversão para número
        const isPaidValue = parseInt(orderData.is_paid) || 0;
        if (isPaidValue === 1) {
            paymentEl.textContent = '✅ PAGO';
            paymentContainer.style.background = '#dcfce7';
            paymentEl.style.color = '#166534';
        } else {
            paymentEl.textContent = DeliveryConstants.getMethodLabel(orderData.payment_method);
            paymentContainer.style.background = '#fee2e2';
            paymentEl.style.color = '#dc2626';
        }

        // Badge de status
        const badge = document.getElementById('modal-order-badge');
        badge.textContent = DeliveryConstants.getStatusLabel(orderData.status);
        badge.className = 'delivery-badge delivery-badge--' + orderData.status;

        // Lista de itens (se disponível)
        const itemsList = document.getElementById('modal-items-list');
        if (orderData.items && orderData.items.length > 0) {
            let html = '';
            orderData.items.forEach(item => {
                html += `<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                    <span>${item.quantity}x ${item.name}</span>
                    <span style="font-weight: 600;">R$ ${parseFloat(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
                </div>`;
            });
            itemsList.innerHTML = html;
        } else {
            itemsList.innerHTML = '<div style="color: #94a3b8; text-align: center; padding: 10px;">Itens não disponíveis</div>';
        }

        // ✅ ABRE MODAL (padrão inert + hidden + inline visual force)
        modal.removeAttribute('hidden');
        modal.removeAttribute('inert');
        modal.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Move foco para o botão de fechar
        const closeBtn = modal.querySelector('.delivery-modal__close');
        if (closeBtn) {
            setTimeout(() => closeBtn.focus(), 50);
        }
    },

    /**
     * Fecha modal de detalhes
     */
    closeDetailsModal: function () {
        const modal = document.getElementById('deliveryDetailsModal');
        if (!modal) return;

        // ✅ PASSO 1: INERT IMEDIATO (Mata eventos de foco do Chrome)
        modal.setAttribute('inert', '');

        // ✅ PASSO 2: Move foco para FORA
        if (this.previouslyFocused && typeof this.previouslyFocused.focus === 'function') {
            try { this.previouslyFocused.focus(); } catch (e) { }
        } else {
            document.body.focus();
        }
        this.previouslyFocused = null;

        // ✅ PASSO 3: Oculta visualmente
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
        // modal.removeAttribute('style'); // Removido para manter display: none

        this.currentOrder = null;
    },

    /**
     * Abre modal de cancelamento
     */
    openCancelModal: function (orderId) {
        const modal = document.getElementById('deliveryCancelModal');
        if (!modal) return;

        // Salva elemento com foco para restaurar depois
        this.previouslyFocusedCancel = document.activeElement;

        document.getElementById('cancel-order-id').textContent = orderId;
        document.getElementById('cancel-order-id-value').value = orderId;
        document.getElementById('cancel-reason').value = '';

        // ✅ ABRE MODAL (inert + hidden)
        modal.removeAttribute('hidden');
        modal.removeAttribute('inert');
        modal.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Move foco para dentro do modal
        const closeBtn = modal.querySelector('.delivery-modal__close');
        if (closeBtn) setTimeout(() => closeBtn.focus(), 50);
    },

    /**
     * Fecha modal de cancelamento
     */
    closeCancelModal: function () {
        const modal = document.getElementById('deliveryCancelModal');
        if (!modal) return;

        // ✅ PASSO 1: INERT IMEDIATO
        modal.setAttribute('inert', '');

        // ✅ PASSO 2: Move foco para FORA
        if (this.previouslyFocusedCancel && typeof this.previouslyFocusedCancel.focus === 'function') {
            try { this.previouslyFocusedCancel.focus(); } catch (e) { }
        } else {
            document.body.focus();
        }
        this.previouslyFocusedCancel = null;

        // ✅ PASSO 3: Oculta
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
    },

    /**
     * Confirma cancelamento (usa ação existente)
     */
    confirmCancel: function () {
        const orderId = document.getElementById('cancel-order-id-value').value;
        const reason = document.getElementById('cancel-reason').value;

        // TODO: Salvar motivo em coluna separada (futura fase)
        // Por agora, apenas cancela
        if (orderId) {
            this.closeCancelModal();
            DeliveryActions.updateStatus(orderId, 'cancelado');
        }
    }
};

// Expõe globalmente
window.DeliveryUI = DeliveryUI;
