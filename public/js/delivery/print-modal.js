/**
 * PRINT-MODAL.JS - Controle do Modal de Impressão
 * Módulo: DeliveryPrint.Modal
 * 
 * Dependências: DeliveryPrint.Generators
 */

(function () {
    'use strict';

    // Garante namespace
    window.DeliveryPrint = window.DeliveryPrint || {};

    // Estado privado
    let currentOrderId = null;
    let currentOrderData = null;
    let currentItemsData = null;
    let slipType = 'delivery';

    window.DeliveryPrint.Modal = {

        // Getters para estado
        getCurrentOrderId: () => currentOrderId,
        getCurrentOrderData: () => currentOrderData,
        getSlipType: () => slipType,

        /**
         * Abre modal de impressão
         */
        open: async function (orderId, type = 'delivery') {
            currentOrderId = orderId;
            slipType = type;

            const modal = document.getElementById('deliveryPrintModal');
            const content = document.getElementById('print-slip-content');
            const tabsContainer = document.getElementById('print-tabs-container');

            if (!modal || !content) return;

            // Salva elemento com foco para restaurar depois
            this._previouslyFocused = document.activeElement;

            if (tabsContainer) {
                tabsContainer.style.display = 'none';
            }

            content.innerHTML = '<div style="padding: 40px; text-align: center; color: #64748b;">Carregando...</div>';
            content.innerHTML = '<div style="padding: 40px; text-align: center; color: #64748b;">Carregando...</div>';

            // ✅ ABRE MODAL (inert + hidden)
            modal.removeAttribute('hidden');
            modal.removeAttribute('inert');
            modal.style.display = 'flex';

            // Move foco para botão de fechar
            const closeBtn = modal.querySelector('.delivery-modal__close');
            if (closeBtn) setTimeout(() => closeBtn.focus(), 50);

            try {
                const baseUrl = DeliveryHelpers.getBaseUrl();
                const response = await fetch(baseUrl + '/admin/loja/delivery/details?id=' + orderId);
                const data = await response.json();

                if (!data.success) {
                    content.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Erro: ' + data.message + '</div>';
                    return;
                }

                currentOrderData = data.order;
                currentItemsData = data.items;

                const html = this._renderSlip(type, data.order, data.items);
                content.innerHTML = html;

                if (typeof lucide !== 'undefined') lucide.createIcons();

            } catch (err) {
                content.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Erro de conexão</div>';
            }
        },

        /**
         * Renderiza o slip baseado no tipo
         */
        _renderSlip: function (type, order, items) {
            switch (type) {
                case 'kitchen':
                    return window.DeliveryPrint.Generators.generateKitchenSlipHTML(order, items);
                case 'complete':
                    return window.DeliveryPrint.Generators.generateSlipHTML(order, items, 'FICHA DO PEDIDO');
                default:
                    return window.DeliveryPrint.Generators.generateSlipHTML(order, items, 'FICHA DE ENTREGA');
            }
        },

        /**
         * Alterna para ficha do motoboy
         */
        showDeliverySlip: function () {
            slipType = 'delivery';
            const content = document.getElementById('print-slip-content');
            if (content && currentOrderData) {
                content.innerHTML = window.DeliveryPrint.Generators.generateSlipHTML(currentOrderData, currentItemsData, 'FICHA DE ENTREGA');
            }
        },

        /**
         * Alterna para ficha da cozinha
         */
        showKitchenSlip: function () {
            slipType = 'kitchen';
            const content = document.getElementById('print-slip-content');
            if (content && currentOrderData) {
                content.innerHTML = window.DeliveryPrint.Generators.generateKitchenSlipHTML(currentOrderData, currentItemsData);
            }
        },

        /**
         * Fecha modal
         */
        close: function () {
            const modal = document.getElementById('deliveryPrintModal');
            if (!modal) return;

            // ✅ PASSO 1: INERT IMEDIATO
            modal.setAttribute('inert', '');

            // ✅ PASSO 2: Move foco para FORA
            if (this._previouslyFocused && typeof this._previouslyFocused.focus === 'function') {
                try { this._previouslyFocused.focus(); } catch (e) { }
            } else {
                document.body.focus();
            }
            this._previouslyFocused = null;

            // ✅ PASSO 3: Oculta
            modal.style.display = 'none';
            modal.setAttribute('hidden', '');
            currentOrderId = null;
        }
    };


})();
