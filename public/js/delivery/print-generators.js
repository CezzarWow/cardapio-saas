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
                        <div><strong>Nome:</strong> ${data.clientName || 'Não identificado'}</div>
                        <div><strong>Fone:</strong> ${data.clientPhone || '--'}</div>
                    </div>

                    <div class="print-slip-section">
                        <h4>ENDERECO:</h4>
                        <div>${data.clientAddress || 'Endereço não informado'}</div>
                        ${data.neighborhood ? '<div><strong>Bairro:</strong> ' + data.neighborhood + '</div>' : ''}
                        ${data.observations ? '<div style="margin-top: 5px;"><strong>OBS:</strong> ' + data.observations + '</div>' : ''}
                    </div>

                    <div class="print-slip-section">
                        <h4>ITENS:</h4>
                        ${itemsHTML}
                    </div>

                    <div class="print-slip-section">
                        <h4>PAGAMENTO:</h4>
                        <div style="font-weight: bold;">${(data.paymentMethod || 'Não informado').toUpperCase()}</div>
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

            // [FIX] Filtra Taxa de Entrega da COZINHA
            const kitchenItems = (items || []).filter(item => item.name !== 'Taxa de Entrega');

            const itemsHTML = window.DeliveryPrint.Helpers.generateItemsHTML(kitchenItems, false);

            return `
                <div class="print-slip" style="font-size: 14px;">
                    <!-- Espaco para grampear -->
                    <div style="height: 15mm;"></div>
                    
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
        },

        /**
         * Gera HTML de RECIBO SIMPLES (Estilo Nota de Mercado)
         * Sem dados de entrega, focado em itens e totais
         */
        generateReceiptHTML: function (order, items) {
            const orderItems = items || order.items || [];
            const data = window.DeliveryPrint.Helpers.extractOrderData(order);

            // [FIX] Separa Taxa de Entrega dos itens normais
            const deliveryFeeItem = orderItems.find(item => item.name === 'Taxa de Entrega');
            const normalItems = orderItems.filter(item => item.name !== 'Taxa de Entrega');

            // Itens simplificados (sem observações longas se possível)
            const itemsHTML = normalItems.map(item => {
                const qtd = item.quantity || 1;
                const price = parseFloat(item.price || 0);
                const total = qtd * price;

                // Formatação
                const priceFmt = price.toFixed(2).replace('.', ',');
                const totalFmt = total.toFixed(2).replace('.', ',');

                // Adicionais
                let extrasHTML = '';
                if (item.additionals && item.additionals.length > 0) {
                    const extrasNames = item.additionals.map(a => `+ ${a.name}`).join(', ');
                    extrasHTML = `<div style="font-size: 10px; color: #444; margin-left: 10px;">${extrasNames}</div>`;
                }

                return `
                    <div style="margin-bottom: 5px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>${qtd}x ${item.name}</span>
                            <span>R$ ${totalFmt}</span>
                        </div>
                        ${extrasHTML}
                    </div>
                `;
            }).join('');

            // HTML da Taxa Separada
            let feeHTML = '';
            if (deliveryFeeItem) {
                const feeVal = parseFloat(deliveryFeeItem.price || 0).toFixed(2).replace('.', ',');
                feeHTML = `
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; border-top: 1px dotted #999; padding-top: 5px;">
                        <span>Taxa de Entrega</span>
                        <span>R$ ${feeVal}</span>
                    </div>
                `;
            }

            return `
                <div class="print-slip" style="font-size: 12px; font-family: 'Courier New', monospace;">
                    
                    <div style="text-align: center; margin-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: bold;">EXTRATO DE VENDA</h3>
                        <div style="margin-top: 5px;">Pedido #${data.orderId}</div>
                        <div style="font-size: 10px;">${data.date}</div>
                    </div>

                    <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin-bottom: 10px;">
                        <div style="font-weight: bold; margin-bottom: 5px;">ITEM &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; VALOR</div>
                        ${itemsHTML}
                        ${feeHTML}
                    </div>

                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-bottom: 5px;">
                        <span>TOTAL</span>
                        <span>R$ ${data.total}</span>
                    </div>

                    <div style="border-top: 1px dashed #000; padding-top: 5px; margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Pagamento:</span>
                            <span>${(data.paymentMethod || 'A Receber').toUpperCase()}</span>
                        </div>
                        ${data.changeFor ? `
                        <div style="display: flex; justify-content: space-between;">
                            <span>Troco:</span>
                            <span>R$ ${data.changeFor}</span>
                        </div>` : ''}
                    </div>

                    ${data.clientName ? `
                    <div style="margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px; text-align: center;">
                        Consumidor: ${data.clientName}
                    </div>` : ''}

                    <div style="text-align: center; margin-top: 20px; font-size: 10px;">
                        OBRIGADO PELA PREFERENCIA
                    </div>
                </div>
            `;
        }
    };

})();
