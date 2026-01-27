/**
 * Delivery Details Modal - Módulo compartilhado
 * 
 * Permite abrir modal de detalhes de pedido de delivery
 * em qualquer aba do SPA (Mesas, Delivery, etc).
 * 
 * Uso: openDeliveryDetailsModal(orderId)
 */
(function () {
    'use strict';

    // Guard contra re-execução
    if (window._deliveryDetailsLoaded) return;
    window._deliveryDetailsLoaded = true;

    // Constantes compartilhadas
    window.DeliveryConstants = window.DeliveryConstants || {
        statusLabels: {
            'novo': 'Novo',
            'preparo': 'Em Preparo',
            'rota': 'Em Rota',
            'entregue': 'Entregue',
            'cancelado': 'Cancelado'
        },
        methodLabels: {
            'dinheiro': '💵 Dinheiro',
            'pix': '📱 Pix',
            'credito': '💳 Crédito',
            'debito': '💳 Débito',
            'multiplo': '💰 Múltiplo'
        },
        getStatusLabel: function (s) { return this.statusLabels[s] || s; },
        getMethodLabel: function (m) { return this.methodLabels[m] || m || 'A pagar'; }
    };

    // Se nosso modal completo já foi carregado, não reexecuta
    if (window.DeliveryUI && window.DeliveryUI.printSlip) return;

    // Inicializa DeliveryUI (se já existe do delivery-bundle, vamos sobrescrever métodos)
    window.DeliveryUI = window.DeliveryUI || {};

    window.DeliveryUI.currentOrder = null;
    window.DeliveryUI.previouslyFocused = null;

    // Função para verificar/criar modal com botões de impressão
    function ensureModalExistsLocal() {
        const existingModal = document.getElementById('deliveryDetailsModal');

        // Se existe mas NÃO tem seção de impressão, remove para recriar
        if (existingModal && !existingModal.querySelector('.delivery-modal__print-section')) {
            existingModal.parentElement?.remove() || existingModal.remove();
        }

        // Se ainda existe (com botões corretos), não faz nada
        if (document.getElementById('deliveryDetailsModal')) return;

        // Carrega CSS se necessário
        if (!document.querySelector('link[href*="delivery/modals.css"]')) {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = BASE_URL + '/css/delivery/modals.css?v=' + Date.now();
            document.head.appendChild(css);
        }

        // Cria container e modal
        const container = document.createElement('div');
        container.id = 'delivery-modal-container-shared';
        container.innerHTML = getModalHTML();
        document.body.appendChild(container);

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // SOBRESCREVE openDetailsModal para usar nosso modal com botões
    window.DeliveryUI.openDetailsModal = async function (id) {
        // Garante que o modal NOSSO existe no DOM (com botões de impressão)
        ensureModalExistsLocal();
        try {
            const res = await fetch(BASE_URL + '/admin/loja/delivery/details?id=' + id);
            const data = await res.json();
            if (data.success) {
                this.showDetailsModal({ ...data.order, items: data.items || [] });
            } else {
                alert('Erro: ' + (data.message || 'Pedido não encontrado'));
            }
        } catch (e) {
            console.error('[DeliveryDetails]', e);
            alert('Erro ao carregar detalhes');
        }
    };

    window.DeliveryUI.showDetailsModal = function (order) {
        this.currentOrder = order;
        const m = document.getElementById('deliveryDetailsModal');
        if (!m) return;

        this.previouslyFocused = document.activeElement;

        document.getElementById('modal-order-id').textContent = order.id;
        document.getElementById('modal-client-name').textContent = order.client_name || 'Não identificado';
        document.getElementById('modal-client-phone').textContent = order.client_phone || '--';

        let addr = order.client_address || 'Não informado';
        if (order.client_number) addr += ', ' + order.client_number;
        if (order.client_neighborhood) addr += ' - ' + order.client_neighborhood;
        document.getElementById('modal-address').textContent = addr;

        document.getElementById('modal-total').textContent = 'R$ ' + parseFloat(order.total || 0).toFixed(2).replace('.', ',');
        document.getElementById('modal-time').textContent = order.created_at || '--';

        const pay = document.getElementById('modal-payment');
        const isPaid = parseInt(order.is_paid) || 0;
        pay.textContent = isPaid ? '✅ PAGO' : DeliveryConstants.getMethodLabel(order.payment_method);
        pay.parentElement.style.background = isPaid ? '#dcfce7' : '#fee2e2';
        pay.style.color = isPaid ? '#166534' : '#dc2626';

        const badge = document.getElementById('modal-order-badge');
        badge.textContent = DeliveryConstants.getStatusLabel(order.status);
        badge.className = 'delivery-badge delivery-badge--' + order.status;

        const list = document.getElementById('modal-items-list');
        list.innerHTML = (order.items || []).map(i =>
            `<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0">
                <span>${i.quantity}x ${i.name}</span>
                <span style="font-weight:600">R$ ${(i.price * i.quantity).toFixed(2).replace('.', ',')}</span>
            </div>`
        ).join('') || '<div style="color:#94a3b8;text-align:center;padding:10px">Sem itens</div>';

        m.removeAttribute('hidden');
        m.removeAttribute('inert');
        m.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    window.DeliveryUI.closeDetailsModal = function () {
        const m = document.getElementById('deliveryDetailsModal');
        if (!m) return;
        m.setAttribute('inert', '');
        m.style.display = 'none';
        m.setAttribute('hidden', '');
        if (this.previouslyFocused) try { this.previouslyFocused.focus(); } catch (e) { }
        this.currentOrder = null;
    };

    window.DeliveryUI.printSlip = function (type) {
        if (!this.currentOrder) {
            alert('Nenhum pedido selecionado');
            return;
        }

        // Se DeliveryPrint disponível (QZ Tray), usa
        if (window.DeliveryPrint && window.DeliveryPrint.openModal) {
            DeliveryPrint.openModal(this.currentOrder.id, type);
            return;
        }

        // Fallback: impressão via browser
        const order = this.currentOrder;
        const items = order.items || [];

        let html = `<div style="font-family:monospace;max-width:300px;margin:0 auto;padding:20px">
            <h2 style="text-align:center;margin:0">${type === 'kitchen' ? '** COZINHA **' : '🛵 ENTREGA'}</h2>
            <p style="text-align:center">Pedido #${order.id}</p>
            <hr>
            <p><strong>Cliente:</strong> ${order.client_name || 'N/I'}</p>
            ${type !== 'kitchen' ? `<p><strong>Endereço:</strong> ${order.client_address || 'N/I'}</p>` : ''}
            <hr>
            <p><strong>Itens:</strong></p>
            ${items.map(i => `<p>${i.quantity}x ${i.name}</p>`).join('')}
            <hr>
            <p style="font-size:1.2em"><strong>Total: R$ ${parseFloat(order.total || 0).toFixed(2).replace('.', ',')}</strong></p>
        </div>`;

        const printWindow = window.open('', '_blank', 'width=400,height=600');
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.print();
    };

    // Função global de atalho
    window.openDeliveryDetailsModal = function (orderId) {
        DeliveryUI.openDetailsModal(orderId);
    };

    function getModalHTML() {
        return `
<div id="deliveryDetailsModal" class="delivery-modal" role="dialog" aria-modal="true" inert hidden>
    <div class="delivery-modal__content delivery-modal__content--medium">
        <div class="delivery-modal__header delivery-modal__header--dark">
            <div>
                <h2 class="delivery-modal__title">Pedido #<span id="modal-order-id">--</span></h2>
                <span id="modal-order-badge" class="delivery-badge"></span>
            </div>
            <button onclick="DeliveryUI.closeDetailsModal()" class="delivery-modal__close" aria-label="Fechar">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="delivery-modal__body">
            <div class="delivery-modal__section">
                <h4 class="delivery-modal__section-title">Cliente</h4>
                <div class="delivery-modal__section-content">
                    <div style="font-weight:600;color:#1e293b;margin-bottom:2px" id="modal-client-name">--</div>
                    <div style="font-size:0.85rem;color:#64748b" id="modal-client-phone">--</div>
                </div>
            </div>
            <div class="delivery-modal__section">
                <h4 class="delivery-modal__section-title">Endereço</h4>
                <div class="delivery-modal__section-content" style="display:flex;align-items:flex-start;gap:8px">
                    <i data-lucide="map-pin" style="width:16px;height:16px;color:#f59e0b;flex-shrink:0;margin-top:2px"></i>
                    <span id="modal-address" style="color:#334155;font-size:0.9rem">--</span>
                </div>
            </div>
            <div class="delivery-modal__section">
                <h4 class="delivery-modal__section-title">Itens</h4>
                <div id="modal-items-list" class="delivery-modal__section-content"></div>
            </div>
            <div class="delivery-modal__info-row">
                <div class="delivery-modal__info-card delivery-modal__info-card--success">
                    <div class="delivery-modal__info-label">Total</div>
                    <div id="modal-total" class="delivery-modal__info-value">R$ --</div>
                </div>
                <div class="delivery-modal__info-card delivery-modal__info-card--neutral">
                    <div class="delivery-modal__info-label">Pagamento</div>
                    <div id="modal-payment" class="delivery-modal__info-value">--</div>
                </div>
            </div>
            <div class="delivery-modal__timestamp">Pedido realizado em <span id="modal-time" style="font-weight:600">--</span></div>
        </div>
        <div class="delivery-modal__print-section">
            <div class="delivery-modal__print-title">🖨️ Imprimir Ficha</div>
            <div class="delivery-modal__info-row">
                <button onclick="DeliveryUI.printSlip('delivery')" class="delivery-modal__btn delivery-modal__btn--primary" aria-label="Imprimir ficha do motoboy">
                    🛵 Motoboy
                </button>
                <button onclick="DeliveryUI.printSlip('kitchen')" class="delivery-modal__btn delivery-modal__btn--purple" aria-label="Imprimir ficha da cozinha">
                    🍳 Cozinha
                </button>
            </div>
        </div>
    </div>
</div>`;
    }
})();
