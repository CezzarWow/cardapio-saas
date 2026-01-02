/**
 * ============================================
 * DELIVERY JS — Print (Impressão de Ficha)
 * ============================================
 */

const DeliveryPrint = {

    currentOrderId: null,
    currentOrderData: null,
    currentItemsData: null,
    slipType: 'delivery', // 'delivery' ou 'kitchen'

    /**
     * Abre modal de impressão (ficha do motoboy)
     */
    openModal: async function (orderId, type = 'delivery') {
        this.currentOrderId = orderId;
        this.slipType = type;

        const modal = document.getElementById('deliveryPrintModal');
        const content = document.getElementById('print-slip-content');

        if (!modal || !content) return;

        content.innerHTML = '<div style="padding: 40px; text-align: center; color: #64748b;">Carregando...</div>';
        modal.style.display = 'flex';

        try {
            const response = await fetch(BASE_URL + '/admin/loja/delivery/details?id=' + orderId);
            const data = await response.json();

            if (!data.success) {
                content.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Erro: ' + data.message + '</div>';
                return;
            }

            this.currentOrderData = data.order;
            this.currentItemsData = data.items;

            const html = type === 'kitchen'
                ? this.generateKitchenSlipHTML(data.order, data.items)
                : this.generateSlipHTML(data.order, data.items);
            content.innerHTML = html;

            if (typeof lucide !== 'undefined') lucide.createIcons();

        } catch (err) {
            content.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Erro de conexão</div>';
        }
    },

    /**
     * Alterna para ficha do motoboy
     */
    showDeliverySlip: function () {
        this.slipType = 'delivery';
        const content = document.getElementById('print-slip-content');
        if (content && this.currentOrderData) {
            content.innerHTML = this.generateSlipHTML(this.currentOrderData, this.currentItemsData);
        }
    },

    /**
     * Alterna para ficha da cozinha
     */
    showKitchenSlip: function () {
        this.slipType = 'kitchen';
        const content = document.getElementById('print-slip-content');
        if (content && this.currentOrderData) {
            content.innerHTML = this.generateKitchenSlipHTML(this.currentOrderData, this.currentItemsData);
        }
    },

    /**
     * Fecha modal
     */
    closeModal: function () {
        const modal = document.getElementById('deliveryPrintModal');
        if (modal) modal.style.display = 'none';
        this.currentOrderId = null;
    },

    /**
     * Gera HTML da ficha do MOTOBOY (entrega)
     */
    generateSlipHTML: function (order, items) {
        const clientName = order.client_name || 'Não identificado';
        const clientPhone = order.client_phone || '--';
        const clientAddress = order.client_address || 'Endereço não informado';
        const neighborhood = order.neighborhood || '';
        const observations = order.observations || '';
        const paymentMethod = order.payment_method || 'Não informado';
        const changeFor = order.change_for || '';
        const total = parseFloat(order.total || 0).toFixed(2).replace('.', ',');
        const date = order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--';

        let itemsHTML = '';
        if (items && items.length > 0) {
            items.forEach(item => {
                const subtotal = (item.quantity * item.price).toFixed(2).replace('.', ',');
                itemsHTML += `
                    <div class="print-slip-item">
                        <span>${item.quantity}x ${item.name}</span>
                        <span>R$ ${subtotal}</span>
                    </div>
                `;
            });
        } else {
            itemsHTML = '<div style="color: #999;">Sem itens</div>';
        }

        let changeHTML = '';
        if (paymentMethod.toLowerCase() === 'dinheiro' && changeFor) {
            changeHTML = `<div style="margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 4px; font-weight: bold;">💵 TROCO PARA: R$ ${parseFloat(changeFor).toFixed(2).replace('.', ',')}</div>`;
        }

        return `
            <div class="print-slip">
                <div class="print-slip-header">
                    <h2>🛵 FICHA DE ENTREGA</h2>
                    <div>Pedido #${order.id}</div>
                    <div style="font-size: 10px; color: #666;">${date}</div>
                </div>

                <div class="print-slip-section">
                    <h4>👤 Dados do Cliente:</h4>
                    <div><strong>Nome:</strong> ${clientName}</div>
                    <div><strong>Telefone:</strong> ${clientPhone}</div>
                </div>

                <div class="print-slip-section">
                    <h4>📍 Endereço de Entrega:</h4>
                    <div style="padding: 8px; background: #f5f5f5; border-radius: 4px;">
                        ${clientAddress}
                        ${neighborhood ? '<br><strong>Bairro:</strong> ' + neighborhood : ''}
                    </div>
                    ${observations ? '<div style="margin-top: 8px; font-style: italic; color: #666;">📝 ' + observations + '</div>' : ''}
                </div>

                <div class="print-slip-section">
                    <h4>📦 Itens:</h4>
                    ${itemsHTML}
                </div>

                <div class="print-slip-section">
                    <h4>💳 Pagamento:</h4>
                    <div style="font-weight: bold; font-size: 14px;">${paymentMethod.toUpperCase()}</div>
                    ${changeHTML}
                </div>

                <div class="print-slip-total">
                    TOTAL: R$ ${total}
                </div>
            </div>
        `;
    },

    /**
     * Gera HTML da ficha da COZINHA
     */
    generateKitchenSlipHTML: function (order, items) {
        const date = order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--';

        // Tipo do pedido
        const orderType = order.order_type || 'local';
        const typeLabels = {
            'delivery': '🛵 ENTREGA',
            'pickup': '🏃 RETIRADA',
            'local': '🍽️ CONSUMO LOCAL'
        };
        const typeLabel = typeLabels[orderType] || '🍽️ CONSUMO LOCAL';

        let itemsHTML = '';
        if (items && items.length > 0) {
            items.forEach(item => {
                itemsHTML += `
                    <div style="padding: 8px 0; border-bottom: 1px dashed #ccc; font-size: 14px;">
                        <strong style="font-size: 18px;">${item.quantity}x</strong> ${item.name}
                    </div>
                `;
            });
        } else {
            itemsHTML = '<div style="color: #999;">Sem itens</div>';
        }

        return `
            <div class="print-slip" style="font-size: 14px;">
                <div class="print-slip-header" style="text-align: center; padding: 15px 0; border-bottom: 3px solid #333;">
                    <h2 style="margin: 0; font-size: 24px;">🍳 COZINHA</h2>
                    <div style="font-size: 12px; margin-top: 5px;">${date}</div>
                </div>

                <div style="text-align: center; padding: 15px; background: #f0f0f0; margin: 10px 0; border-radius: 8px;">
                    <div style="font-size: 22px; font-weight: bold;">${typeLabel}</div>
                    <div style="margin-top: 5px;">Pedido #${order.id}</div>
                </div>

                <div style="padding: 10px 0;">
                    <h4 style="margin: 0 0 10px 0; font-size: 16px; text-transform: uppercase; border-bottom: 2px solid #333; padding-bottom: 5px;">Itens do Pedido:</h4>
                    ${itemsHTML}
                </div>
            </div>
        `;
    },

    /**
     * Imprime a ficha
     */
    print: function () {
        const content = document.getElementById('print-slip-content');
        const printArea = document.getElementById('print-area');

        if (!content || !printArea) return;

        printArea.innerHTML = content.innerHTML;
        window.print();

        this.closeModal();
    },

    /**
     * Imprime ficha completa diretamente (sem modal)
     */
    printComplete: function (orderData) {
        if (!orderData) {
            alert('Dados do pedido não disponíveis');
            return;
        }

        const printArea = document.getElementById('print-area');
        if (!printArea) {
            alert('Área de impressão não encontrada');
            return;
        }

        // Gera HTML da ficha completa
        const html = this.generateCompleteSlipHTML(orderData);
        printArea.innerHTML = html;
        window.print();
    },

    /**
     * Gera HTML da ficha COMPLETA (todas as informações)
     */
    generateCompleteSlipHTML: function (order) {
        const restaurantName = order.restaurant_name || 'Restaurante';
        const restaurantPhone = order.restaurant_phone || '';
        const clientName = order.client_name || 'Não identificado';
        const clientPhone = order.client_phone || '--';
        const clientAddress = order.client_address || 'Endereço não informado';
        const neighborhood = order.neighborhood || '';
        const observations = order.observations || '';
        const paymentMethod = order.payment_method || 'Não informado';
        const changeFor = order.change_for || '';
        const total = parseFloat(order.total || 0).toFixed(2).replace('.', ',');
        const date = order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--';
        const items = order.items || [];

        let itemsHTML = '';
        if (items.length > 0) {
            items.forEach(item => {
                const subtotal = (item.quantity * item.price).toFixed(2).replace('.', ',');
                itemsHTML += `
                    <tr>
                        <td style="padding: 6px 0; border-bottom: 1px dashed #ccc;">${item.quantity}x ${item.name}</td>
                        <td style="padding: 6px 0; border-bottom: 1px dashed #ccc; text-align: right;">R$ ${subtotal}</td>
                    </tr>
                `;
            });
        } else {
            itemsHTML = '<tr><td colspan="2" style="color: #999;">Sem itens</td></tr>';
        }

        let changeHTML = '';
        if (paymentMethod.toLowerCase() === 'dinheiro' && changeFor) {
            changeHTML = `
                <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; font-weight: bold; text-align: center;">
                    💵 TROCO PARA: R$ ${parseFloat(changeFor).toFixed(2).replace('.', ',')}
                </div>
            `;
        }

        let obsHTML = '';
        if (observations) {
            obsHTML = `
                <div style="margin-top: 10px; padding: 10px; background: #f8f8f8; border-radius: 4px;">
                    <strong>📝 Obs:</strong> ${observations}
                </div>
            `;
        }

        return `
            <div style="font-family: 'Courier New', monospace; width: 280px; padding: 10px; font-size: 12px;">
                <!-- Cabeçalho -->
                <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
                    <h2 style="margin: 0; font-size: 18px;">${restaurantName}</h2>
                    ${restaurantPhone ? `<div style="font-size: 11px; color: #666;">${restaurantPhone}</div>` : ''}
                    <div style="margin-top: 8px; font-size: 16px; font-weight: bold;">PEDIDO #${order.id}</div>
                    <div style="font-size: 10px; color: #666;">${date}</div>
                </div>

                <!-- Cliente e Endereço -->
                <div style="border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                    <div style="font-weight: bold; margin-bottom: 5px;">📞 CLIENTE</div>
                    <div>${clientName}</div>
                    <div style="color: #666;">${clientPhone}</div>
                    <div style="margin-top: 8px; font-weight: bold;">📍 ENDEREÇO</div>
                    <div>${clientAddress}</div>
                    ${neighborhood ? `<div style="color: #666;">${neighborhood}</div>` : ''}
                </div>

                <!-- Itens -->
                <div style="border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                    <div style="font-weight: bold; margin-bottom: 8px;">🛒 ITENS</div>
                    <table style="width: 100%; border-collapse: collapse;">
                        ${itemsHTML}
                    </table>
                </div>

                <!-- Total e Pagamento -->
                <div style="border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: bold;">
                        <span>TOTAL:</span>
                        <span>R$ ${total}</span>
                    </div>
                    <div style="margin-top: 8px; font-weight: bold;">💳 PAGAMENTO</div>
                    <div>${paymentMethod}</div>
                    ${changeHTML}
                </div>

                ${obsHTML}

                <!-- Rodapé -->
                <div style="text-align: center; margin-top: 15px; font-size: 10px; color: #666;">
                    Obrigado pela preferência!
                </div>
            </div>
        `;
    }
};

// Expõe globalmente
window.DeliveryPrint = DeliveryPrint;

console.log('[Delivery] Print carregado ✓');
