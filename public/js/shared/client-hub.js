/**
 * Client Hub - Gestor Unified de Comandas do Cliente
 * Centraliza Mesa, Retirada e Entrega em uma única visão.
 */
window.ClientHub = {
    modalId: 'client-hub-modal',
    overlayId: 'client-hub-overlay',

    /**
     * Ponto de entrada principal
     * @param {number} orderId - ID de qualquer pedido do cliente (gatilho)
     */
    open: async function (orderId) {
        this.injectStyles();
        this.showLoading();

        try {
            // Busca dados agrupados do backend
            const response = await fetch(`${this.getBaseUrl()}/admin/loja/delivery/hub?id=${orderId}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Erro ao carregar Hub do Cliente');
                this.close();
                return;
            }

            this.render(data);

        } catch (error) {
            console.error('ClientHub Error:', error);
            alert('Erro de conexão ao carregar Hub');
            this.close();
        }
    },

    /**
     * Renderiza o HTML completo baseados nos dados
     */
    render: function (data) {
        const client = data.client || { name: 'Cliente Não Identificado' };
        const orders = data.orders || [];

        // Calcula total geral
        const grandTotal = orders.reduce((sum, order) => sum + parseFloat(order.total || 0), 0);

        // Gera seções
        const sectionsHtml = orders.map(order => this.renderOrderSection(order)).join('');

        // Tenta achar um pedido de mesa para o rodapé ou usa o mais recente
        const primaryOrder = orders.find(o => o.order_type === 'local') ||
            orders.find(o => o.table_number) ||
            orders[0]; // Fallback

        // Configuração do Botão de Rodapé
        let footerConfig = { label: 'PEDIDO', icon: 'clipboard-list', cssClass: 'hub-btn-default', status: '' };

        if (primaryOrder) {
            footerConfig.status = primaryOrder.status;
            if (primaryOrder.order_type === 'local' || primaryOrder.table_number) {
                footerConfig.label = `MESA ${primaryOrder.table_number || '?'}`;
                footerConfig.icon = 'armchair';
                footerConfig.cssClass = 'hub-btn-orange';
            } else if (primaryOrder.order_type === 'delivery') {
                footerConfig.label = 'ENTREGA';
                footerConfig.icon = 'bike';
                footerConfig.cssClass = 'hub-btn-orange';
            } else if (['pickup', 'retirada'].includes(primaryOrder.order_type)) {
                footerConfig.label = 'RETIRADA';
                footerConfig.icon = 'shopping-bag';
                footerConfig.cssClass = 'hub-btn-sky';
            }
        }

        const html = `
        <div class="hub-modal">
            <!-- HEADER -->
            <div class="hub-header">
                <div class="hub-client-info">
                    <h1 class="hub-client-name">${client.name}</h1>
                </div>
                <!-- Total -->
                <div class="hub-total-wrapper">
                    <div class="hub-total-value">R$ ${this.formatMoney(grandTotal)}</div>
                </div>
            </div>

            <!-- BODY -->
            <div class="hub-body">
                <div class="hub-content-wrapper">
                    
                    <!-- LISTA DE PEDIDOS -->
                    <div class="hub-list">
                        ${sectionsHtml}
                    </div>

                    <!-- FOOTER BUTTONS -->
                    <div class="hub-footer">
                        <button onclick="ClientHub.close()" 
                            class="hub-btn-cancel">
                            <i data-lucide="x-circle" size="24"></i>
                            CANCELAR
                        </button>
                        
                        <button class="hub-btn-main ${footerConfig.cssClass}">
                            <i data-lucide="${footerConfig.icon}" size="32"></i>
                            <div class="hub-btn-text">
                                <div class="hub-btn-title">${footerConfig.label}</div>
                                <div class="hub-btn-subtitle">${footerConfig.status}</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;

        this.getOverlay().innerHTML = html;

        // Re-render icons
        if (window.lucide) window.lucide.createIcons();
    },

    /**
     * Gera HTML de uma seção de pedido (Card)
     */
    renderOrderSection: function (order) {
        // Configuração visual por tipo
        let typeConfig = {
            icon: 'shopping-bag', colorClass: 'hub-icon-sky', label: 'Pedido', borderClass: ''
        };

        if (order.order_type === 'local' || order.table_number) {
            typeConfig = { icon: 'armchair', colorClass: 'hub-icon-indigo', label: `Mesa ${order.table_number || '?'}` };
        } else if (order.order_type === 'delivery') {
            typeConfig = { icon: 'bike', colorClass: 'hub-icon-orange', label: 'Entrega', borderClass: 'hub-border-orange' };
        } else if (order.order_type === 'pickup' || order.order_type === 'retirada') {
            typeConfig = { icon: 'shopping-bag', colorClass: 'hub-icon-sky', label: 'Retirada no Balcão' };
        }

        // Subtotal do pedido
        const total = parseFloat(order.total || 0);

        // Verifica estado (Em rota, etc)
        let statusBadge = '';
        if (order.status === 'rota') {
            statusBadge = `<span class="hub-badge-rota">Em Rota 🛵</span>`;
        }

        // Endereço (se delivery)
        let addressHtml = '';
        if (order.order_type === 'delivery') {
            let fullAddress = order.client_address || order.address || '';
            if (order.client_number) fullAddress += `, ${order.client_number}`;
            if (order.client_neighborhood) fullAddress += ` - ${order.client_neighborhood}`;

            if (!fullAddress || fullAddress.trim() === '') fullAddress = 'Endereço não informado';

            addressHtml = `
            <div class="hub-address-row">
                📍 ${fullAddress}
            </div>`;
        }

        // Items
        const itemsHtml = (order.items || []).map(item => `
            <div class="hub-item-row">
                <div><span class="hub-qty">${parseInt(item.quantity)}x</span> ${item.name}</div>
                <div class="hub-price">R$ ${this.formatMoney(item.price * item.quantity)}</div>
            </div>
        `).join('');

        return `
        <div class="hub-card ${typeConfig.borderClass}">
            <div class="hub-card-header">
                <div class="hub-card-title">
                    <span class="hub-icon-box ${typeConfig.colorClass}">
                        <i data-lucide="${typeConfig.icon}" size="20"></i>
                    </span>
                    <div>
                        <div class="hub-card-label">${typeConfig.label}</div>
                    </div>
                </div>
                <div class="hub-card-actions">
                    ${statusBadge}
                    <button onclick="DeliveryUI.printSlip(${order.id}, '${order.order_type}')" 
                        class="hub-btn-icon">
                        <i data-lucide="printer" size="16"></i>
                    </button>
                </div>
            </div>
            ${addressHtml}
            <div>
                ${itemsHtml}
                <div class="hub-item-row hub-subtotal-row">
                    Subtotal: R$ ${this.formatMoney(total)}
                </div>
            </div>
        </div>
        `;
    },

    // --- Helpers ---

    showLoading: function () {
        const overlay = this.getOverlay();
        overlay.innerHTML = '<div style="color:white; font-size:1.2rem; font-weight:bold;">Carregando Hub...</div>';
        overlay.style.display = 'flex';
    },

    close: function () {
        const overlay = document.getElementById(this.overlayId);
        if (overlay) overlay.style.display = 'none';
    },

    getOverlay: function () {
        let overlay = document.getElementById(this.overlayId);
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = this.overlayId;
            overlay.className = 'hub-modal-overlay';
            document.body.appendChild(overlay);
        }
        return overlay;
    },

    formatMoney: function (val) {
        return parseFloat(val).toFixed(2).replace('.', ',');
    },

    getBaseUrl: function () {
        return typeof BASE_URL !== 'undefined' ? BASE_URL : '';
    },

    injectStyles: function () {
        if (document.getElementById('hub-styles')) return;
        const css = `
         /* RESET & LAYOUT */
         .hub-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            z-index: 9999;
            font-family: 'Inter', sans-serif;
        }

        .hub-modal {
            background: white;
            width: 100%;
            max-width: 900px;
            height: 80vh;
            max-height: 800px;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.3s ease-out;
            color: #334155;
        }

        /* HEADER */
        .hub-header {
            background: #1e293b;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hub-client-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .hub-client-name { font-size: 1.25rem; font-weight: 700; margin: 0; }
        .hub-total-wrapper { text-align: right; }
        .hub-total-value { font-size: 1.875rem; font-weight: 700; color: #4ade80; }

        /* BODY */
        .hub-body { flex: 1; display: flex; overflow: hidden; background: #f8fafc; }
        .hub-content-wrapper { flex: 1; display: flex; flex-direction: column; height: 100%; padding: 30px; box-sizing: border-box; }
        .hub-list { flex: 1; overflow-y: auto; padding-bottom: 20px; }

        /* CARDS */
        .hub-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .hub-border-orange { border-left: 4px solid #fb923c; }

        .hub-card-header {
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hub-card-title { display: flex; align-items: center; gap: 8px; }
        .hub-card-label { font-size: 1.125rem; font-weight: 500; color: #334155; }
        
        .hub-icon-box { padding: 8px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .hub-icon-indigo { background: #e0e7ff; color: #4338ca; }
        .hub-icon-sky { background: #e0f2fe; color: #0369a1; }
        .hub-icon-orange { background: #ffedd5; color: #c2410c; }

        .hub-card-actions { display: flex; align-items: center; gap: 8px; }
        .hub-badge-rota { font-size: 0.75rem; font-weight: 700; color: #ea580c; background: #fff7ed; padding: 4px 8px; border-radius: 4px; }
        
        .hub-btn-icon { padding: 8px; border-radius: 6px; color: #64748b; background: transparent; border: none; cursor: pointer; }
        .hub-btn-icon:hover { background: #e2e8f0; }

        .hub-address-row { background: #f8fafc; padding: 12px; font-size: 0.85rem; color: #64748b; border-bottom: 1px dashed #e2e8f0; }

        /* ITEMS */
        .hub-item-row { display: flex; justify-content: space-between; padding: 12px 20px; border-bottom: 1px dashed #e2e8f0; font-size: 0.95rem; color: #475569; }
        .hub-item-row:last-child { border-bottom: none; }
        .hub-subtotal-row { background: #f8fafc; font-weight: 700; justify-content: flex-end; color: #334155; }
        .hub-qty { font-weight: 700; margin-right: 4px; }
        .hub-price { font-weight: 500; }

        /* FOOTER */
        .hub-footer { border-top: 1px solid #e2e8f0; padding-top: 20px; display: flex; gap: 15px; }
        
        .hub-btn-cancel {
            width: 33%;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 16px;
            border-radius: 8px;
            border: 2px solid #cbd5e1;
            background: white;
            color: #dc2626;
            font-weight: 700;
            font-size: 1.125rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .hub-btn-cancel:hover { border-color: #fca5a5; color: #b91c1c; }

        .hub-btn-main {
            width: 67%;
            height: 64px;
            display: flex; align-items: center; justify-content: center; gap: 12px;
            border-radius: 12px;
            border: none;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            text-align: left;
            padding: 0 20px;
            transition: background-color 0.2s;
        }
        .hub-btn-orange { background: #f97316; }
        .hub-btn-orange:hover { background: #ea580c; }
        .hub-btn-sky { background: #0ea5e9; }
        .hub-btn-sky:hover { background: #0284c7; }
        .hub-btn-default { background: #64748b; }
        .hub-btn-default:hover { background: #475569; }

        .hub-btn-text { line-height: 1; }
        .hub-btn-title { font-size: 1.25rem; font-weight: 700; }
        .hub-btn-subtitle { font-size: 0.75rem; opacity: 0.9; text-transform: uppercase; margin-top: 2px;}
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        `;
        const style = document.createElement('style');
        style.id = 'hub-styles';
        style.textContent = css;
        document.head.appendChild(style);
    }
};
