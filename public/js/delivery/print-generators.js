/**
 * PRINT-GENERATORS.JS - Geração de HTML das Fichas
 * Módulo: DeliveryPrint.Generators
 * 
 * Dependências: DeliveryPrint.Helpers
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    DeliveryPrint.Generators = {

        /**
         * Gera HTML da ficha UNIFICADA (Entrega ou Completa)
         */
        generateSlipHTML: function (order, items, title = '🛵 FICHA DE ENTREGA') {
            const orderItems = items || order.items || [];
            const data = DeliveryPrint.Helpers.extractOrderData(order);
            const itemsHTML = DeliveryPrint.Helpers.generateItemsHTML(orderItems, true);
            const changeHTML = DeliveryPrint.Helpers.generateChangeHTML(data.paymentMethod, data.changeFor);

            return `
                <div class="print-slip">
                    <div class="print-slip-header">
                        <h2>${title}</h2>
                        <div>Pedido #${data.orderId}</div>
                        <div style="font-size: 10px; color: #666;">${data.date}</div>
                    </div>

                    <div class="print-slip-section">
                        <h4>👤 Dados do Cliente:</h4>
                        <div><strong>Nome:</strong> ${data.clientName}</div>
                        <div><strong>Telefone:</strong> ${data.clientPhone}</div>
                    </div>

                    <div class="print-slip-section">
                        <h4>📍 Endereço de Entrega:</h4>
                        <div style="padding: 8px; background: #f5f5f5; border-radius: 4px;">
                            ${data.clientAddress}
                            ${data.neighborhood ? '<br><strong>Bairro:</strong> ' + data.neighborhood : ''}
                        </div>
                        ${data.observations ? '<div style="margin-top: 8px; font-weight: bold; color: #000;">📝 ' + data.observations + '</div>' : ''}
                    </div>

                    <div class="print-slip-section">
                        <h4>📦 Itens:</h4>
                        ${itemsHTML}
                    </div>

                    <div class="print-slip-section">
                        <h4>💳 Pagamento:</h4>
                        <div style="font-weight: bold; font-size: 14px;">${(data.paymentMethod || 'Não informado').toUpperCase()}</div>
                        ${changeHTML}
                    </div>

                    <div class="print-slip-total">
                        TOTAL: R$ ${data.total}
                    </div>
                </div>
            `;
        },

        /**
         * Gera HTML da ficha da COZINHA
         */
        generateKitchenSlipHTML: function (order, items) {
            const date = order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--';
            const orderType = order.order_type || 'local';

            const typeLabels = {
                'delivery': '🛵 ENTREGA',
                'pickup': '🏃 RETIRADA',
                'local': '🍽️ CONSUMO LOCAL'
            };
            const typeLabel = typeLabels[orderType] || '🍽️ CONSUMO LOCAL';

            const itemsHTML = DeliveryPrint.Helpers.generateItemsHTML(items, false);

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
        }
    };

    console.log('[DeliveryPrint.Generators] Módulo carregado');
})();
