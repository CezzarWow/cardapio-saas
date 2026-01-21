/**
 * PRINT-HELPERS.JS - Funções Auxiliares de Impressão
 * Módulo: DeliveryPrint.Helpers
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    window.DeliveryPrint.Helpers = {

        /**
         * Extrai e normaliza dados do pedido
         */
        extractOrderData: function (order) {
            let clientAddress = order.client_address || 'Endereço não informado';
            if (order.client_number) clientAddress += ', ' + order.client_number;

            return {
                clientName: order.client_name || 'Não identificado',
                clientPhone: order.client_phone || '--',
                clientAddress: clientAddress,
                neighborhood: order.client_neighborhood || order.neighborhood || '',
                observations: order.observation || order.observations || '',
                paymentMethod: order.payment_method || 'Não informado',
                changeFor: order.change_for || '',
                total: parseFloat(order.total || 0).toFixed(2).replace('.', ','),
                date: order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--',
                orderId: order.id
            };
        },

        /**
         * Gera HTML da lista de itens
         */
        generateItemsHTML: function (items, showPrice = true) {
            if (!items || items.length === 0) {
                return '<div style="color: #999;">Sem itens</div>';
            }

            return items.map(item => {
                if (showPrice) {
                    const subtotal = (item.quantity * item.price).toFixed(2).replace('.', ',');
                    return `
                        <div class="print-slip-item">
                            <span>${item.quantity}x ${item.name}</span>
                            <span>R$ ${subtotal}</span>
                        </div>
                    `;
                } else {
                    return `
                        <div style="padding: 8px 0; border-bottom: 1px dashed #ccc; font-size: 14px;">
                            <strong style="font-size: 18px;">${item.quantity}x</strong> ${item.name}
                        </div>
                    `;
                }
            }).join('');
        },

        /**
         * Gera HTML do troco (se aplicável)
         */
        generateChangeHTML: function (paymentMethod, changeFor) {
            if (!paymentMethod) return '';
            if (paymentMethod.toLowerCase() === 'dinheiro' && changeFor) {
                const changeValue = parseFloat(changeFor).toFixed(2).replace('.', ',');
                return `<div style="margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 4px; font-weight: bold;">💵 TROCO PARA: R$ ${changeValue}</div>`;
            }
            return '';
        }
    };


})();
