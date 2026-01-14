/**
 * PRINT-ACTIONS.JS - Ações de Impressão
 * Módulo: DeliveryPrint.Actions
 * 
 * Dependências: DeliveryPrint.Modal, DeliveryPrint.Generators
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    DeliveryPrint.Actions = {

        /**
         * Imprime a ficha atual
         */
        print: function () {
            const content = document.getElementById('print-slip-content');
            const printArea = document.getElementById('print-area');

            if (!content || !printArea) return;

            printArea.innerHTML = content.innerHTML;
            window.print();

            DeliveryPrint.Modal.close();
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

            const html = DeliveryPrint.Generators.generateSlipHTML(orderData, orderData.items, '📋 FICHA DO PEDIDO');
            printArea.innerHTML = html;
            window.print();
        }
    };


})();
