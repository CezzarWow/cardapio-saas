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

    window.DeliveryPrint.Generators = {

        /**
         * Gera HTML da ficha UNIFICADA (Entrega ou Completa)
         */
        generateSlipHTML: function (order, items, title = 'FICHA DE ENTREGA') {
            const orderItems = items || order.items || [];
            const data = window.DeliveryPrint.Helpers.extractOrderData(order);
            const itemsHTML = window.DeliveryPrint.Helpers.generateItemsHTML(orderItems, true);
            const changeHTML = window.DeliveryPrint.Helpers.generateChangeHTML(data.paymentMethod, data.changeFor);

            return `
                <div class="print-slip">
                    <div class="print-slip-header">
                        <h2>================================</h2>
                        <h2>${title}</h2>
                        <div>Pedido #${data.orderId}</div>
                        <div style="font-size: 10px;">${data.date}</div>
                        <h2>================================</h2>
                    </div>

                    <div class="print-slip-section">
                        <h4>CLIENTE:</h4>
                        <div><strong>Nome:</strong> ${data.clientName}</div>
                        <div><strong>Fone:</strong> ${data.clientPhone}</div>
                    </div>

                    <div class="print-slip-section">
                        <h4>ENDERECO:</h4>
                        <div>${data.clientAddress}</div>
                        ${data.neighborhood ? '<div><strong>Bairro:</strong> ' + data.neighborhood + '</div>' : ''}
                        ${data.observations ? '<div style="margin-top: 5px;"><strong>OBS:</strong> ' + data.observations + '</div>' : ''}
                    </div>

                    <div class="print-slip-section">
                        <h4>ITENS:</h4>
                        ${itemsHTML}
                    </div>

                    <div class="print-slip-section">
                        <h4>PAGAMENTO:</h4>
                        <div style="font-weight: bold;">${(data.paymentMethod || 'Nao informado').toUpperCase()}</div>
                        ${changeHTML}
                    </div>

                    <div class="print-slip-total">
                        ================================
                        <br>TOTAL: R$ ${data.total}
                        <br>================================
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
                'delivery': '*** ENTREGA ***',
                'pickup': '*** RETIRADA ***',
                'local': '*** CONSUMO LOCAL ***'
            };
            const typeLabel = typeLabels[orderType] || '*** CONSUMO LOCAL ***';

            const itemsHTML = window.DeliveryPrint.Helpers.generateItemsHTML(items, false);

            return `
                <div class="print-slip" style="font-size: 14px;">
                    <div class="print-slip-header" style="text-align: center; padding: 10px 0; border-bottom: 2px dashed #000;">
                        <h2 style="margin: 0; font-size: 20px;">** COZINHA **</h2>
                        <div style="font-size: 11px; margin-top: 5px;">${date}</div>
                    </div>

                    <div style="text-align: center; padding: 10px; margin: 8px 0; border: 1px dashed #000;">
                        <div style="font-size: 18px; font-weight: bold;">${typeLabel}</div>
                        <div style="margin-top: 5px;">Pedido #${order.id}</div>
                    </div>

                    <div style="padding: 8px 0;">
                        <h4 style="margin: 0 0 8px 0; font-size: 14px; text-transform: uppercase; border-bottom: 1px dashed #000; padding-bottom: 5px;">ITENS:</h4>
                        ${itemsHTML}
                    </div>
                    
                    <div style="text-align: center; padding-top: 10px; border-top: 2px dashed #000;">
                        --------------------------------
                    </div>
                </div>
            `;
        }
    };


})();
