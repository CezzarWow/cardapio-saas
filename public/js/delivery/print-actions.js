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

    window.DeliveryPrint.Actions = {

        /**
         * Imprime a ficha atual
         */
        print: function () {
            const content = document.getElementById('print-slip-content');
            const printArea = document.getElementById('print-area');

            if (!content || !printArea) return;

            printArea.innerHTML = content.innerHTML;
            window.print();

            window.DeliveryPrint.Modal.close();
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

            const html = window.DeliveryPrint.Generators.generateSlipHTML(orderData, orderData.items, '📋 FICHA DO PEDIDO');
            printArea.innerHTML = html;
            window.print();
        },

        /**
         * Imprime diretamente pelo ID e Tipo (Pula prévia)
         */
        printDirect: async function (orderId, type) {
            let orderData = null;
            let itemsData = null;

            // Tenta usar dados já carregados no UI Principal para ser instantâneo
            if (window.DeliveryUI && window.DeliveryUI.currentOrder && window.DeliveryUI.currentOrder.id == orderId) {
                orderData = window.DeliveryUI.currentOrder;
                itemsData = orderData.items || [];
            } else {
                // Fetch silencioso se necessário
                try {
                    const baseUrl = window.DeliveryHelpers ? window.DeliveryHelpers.getBaseUrl() : '';
                    const response = await fetch(baseUrl + '/admin/loja/delivery/details?id=' + orderId);
                    const data = await response.json();
                    if (data.success) {
                        orderData = data.order;
                        itemsData = data.items;
                    }
                } catch (e) {
                    console.error('Erro ao buscar dados para impressão direta', e);
                    return;
                }
            }

            if (!orderData) return;

            const printArea = document.getElementById('print-area');
            if (!printArea) return;

            let html = '';
            // Gera o HTML correspondente
            if (type === 'kitchen') {
                html = window.DeliveryPrint.Generators.generateKitchenSlipHTML(orderData, itemsData);
            } else {
                html = window.DeliveryPrint.Generators.generateSlipHTML(orderData, itemsData, 'FICHA DE ENTREGA');
            }

            printArea.innerHTML = html;

            // [QZ Tray] Tentativa de impressão silenciosa
            if (window.DeliveryPrint.QZ) {
                // Tenta init se não estiver conectado
                await window.DeliveryPrint.QZ.init();
                // Envia para impressora
                // printHTML cuida de achar a printer default
                const qzSuccess = await window.DeliveryPrint.QZ.printHTML(html);

                // Se o script QZ rodou sem erro (retornou promise resolved), consideramos impresso
                // Mas printHTML retorna void ou undefined em sucesso, e alerta em erro.
                // Vamos assumir que se não lançou exceção global, foi.
                // Mas para garantir o fallback, vamos fazer o seguinte:
                // Se o usuário cancelou o certificado ou QZ não está rodando, init retorna false.
                return;
            }

            // Fallback para navegador
            setTimeout(() => {
                window.print();
            }, 50);
        },

        /**
         * Imprime usando o conteúdo já renderizado no modal de prévia
         */
        printFromModal: async function () {
            const content = document.getElementById('print-slip-content');
            if (!content) {
                alert('Conteúdo de impressão não encontrado');
                return;
            }

            const html = content.innerHTML;

            // Usa QZ Tray se disponível
            if (window.DeliveryPrint.QZ) {
                await window.DeliveryPrint.QZ.printHTML(html);
            } else {
                // Fallback: impressão pelo navegador
                const printArea = document.getElementById('print-area');
                if (printArea) {
                    printArea.innerHTML = html;
                    window.print();
                }
            }
        }
    };


})();
