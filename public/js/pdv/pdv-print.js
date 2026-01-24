/**
 * PDV Print - Controle do Modal de Impressão de Comanda
 * Namespace: PDVPrint
 * 
 * Dependências: 
 *   - DeliveryPrint.Generators (para gerar HTML da ficha)
 *   - DeliveryPrint.Helpers (para formatação de itens)
 *   - DeliveryPrint.QZ (para impressão térmica)
 */

(function () {
    'use strict';

    // Estado privado
    let currentOrderId = null;
    let currentOrderData = null;
    let currentItemsData = null;
    let currentSlipType = 'kitchen'; // 'kitchen' ou 'receipt'

    window.PDVPrint = {

        // ==========================================
        // ABRIR MODAL
        // ==========================================

        /**
         * Abre modal de impressão buscando dados do pedido via API
         * @param {number} orderId - ID do pedido salvo
         */
        open: async function (orderId) {
            if (!orderId) {
                console.warn('[PDVPrint] orderId não fornecido');
                return;
            }

            currentOrderId = orderId;
            currentSlipType = 'kitchen';

            const modal = document.getElementById('pdvPrintModal');
            const content = document.getElementById('pdv-print-slip-content');
            const tabsContainer = document.getElementById('pdv-print-tabs');

            if (!modal || !content) {
                console.warn('[PDVPrint] Modal ou container não encontrado');
                return;
            }

            // Mostra abas para poder escolher
            if (tabsContainer) tabsContainer.style.display = 'flex';

            // Loading state
            content.innerHTML = '<div style="padding: 40px; text-align: center; color: #64748b;">Carregando...</div>';
            modal.style.display = 'flex';

            try {
                // Buscar dados do pedido
                const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '';
                const response = await fetch(baseUrl + '/admin/loja/delivery/details?id=' + orderId);
                const data = await response.json();

                if (!data.success) {
                    content.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Erro: ' + (data.message || 'Não foi possível carregar') + '</div>';
                    return;
                }

                currentOrderData = data.order;
                currentItemsData = data.items;

                // Mostra ficha da cozinha por padrão
                this._renderCurrentSlip();
                this._updateTabsUI();

            } catch (err) {
                console.error('[PDVPrint] Erro ao carregar dados:', err);
                content.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Erro de conexão</div>';
            }
        },

        /**
         * Abre modal de impressão com itens do CARRINHO (não busca da API)
         * Usado quando se adiciona itens a uma mesa/cliente já ocupado
         * Imprime APENAS os itens novos, não toda a comanda
         * 
         * @param {number} orderId - ID do pedido
         * @param {array} cartItems - Itens do carrinho (formato PDVCart)
         * @param {string} orderType - Tipo do pedido (local, delivery, etc)
         * @param {boolean} showTabs - Se deve mostrar as abas (default: false para apenas salvar)
         */
        openWithItems: function (orderId, cartItems, orderType = 'local', showTabs = false) {
            if (!orderId || !cartItems || cartItems.length === 0) {
                console.warn('[PDVPrint] orderId ou cartItems não fornecido');
                return;
            }

            currentOrderId = orderId;
            currentSlipType = 'kitchen';

            const modal = document.getElementById('pdvPrintModal');
            const content = document.getElementById('pdv-print-slip-content');
            const tabsContainer = document.getElementById('pdv-print-tabs');

            if (!modal || !content) {
                console.warn('[PDVPrint] Modal ou container não encontrado');
                return;
            }

            // Mostra ou oculta abas baseado no parâmetro
            if (tabsContainer) {
                tabsContainer.style.display = showTabs ? 'flex' : 'none';
            }

            modal.style.display = 'flex';

            // Converte itens do carrinho para formato esperado pelo gerador
            const items = cartItems.map(item => ({
                quantity: item.quantity || 1,
                name: item.name || item.productName || 'Produto',
                price: item.price || 0,
                observation: item.observation || item.obs || '',
                additionals: item.extras || item.additionals || []
            }));

            // Calcula total dos itens
            const total = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            // Cria objeto order para o gerador
            const order = {
                id: orderId,
                created_at: new Date().toISOString(),
                order_type: orderType,
                total: total
            };

            currentOrderData = order;
            currentItemsData = items;

            // Mostra ficha da cozinha por padrão
            this._renderCurrentSlip();
            this._updateTabsUI();
        },

        // ==========================================
        // ALTERNÂNCIA DE ABAS
        // ==========================================

        /**
         * Mostra ficha da cozinha
         */
        showKitchenSlip: function () {
            currentSlipType = 'kitchen';
            this._renderCurrentSlip();
            this._updateTabsUI();
        },

        /**
         * Mostra cupom/recibo
         */
        showReceiptSlip: function () {
            currentSlipType = 'receipt';
            this._renderCurrentSlip();
            this._updateTabsUI();
        },

        /**
         * Renderiza o slip atual baseado no tipo selecionado
         */
        _renderCurrentSlip: function () {
            const content = document.getElementById('pdv-print-slip-content');
            if (!content || !currentOrderData) return;

            let html = '';

            if (currentSlipType === 'kitchen') {
                // Ficha de Cozinha
                if (window.DeliveryPrint && window.DeliveryPrint.Generators) {
                    html = window.DeliveryPrint.Generators.generateKitchenSlipHTML(currentOrderData, currentItemsData);
                } else {
                    html = this._fallbackKitchenSlipHTML(currentOrderData, currentItemsData);
                }
            } else {
                // Cupom/Recibo
                if (window.DeliveryPrint && window.DeliveryPrint.Generators) {
                    if (typeof window.DeliveryPrint.Generators.generateReceiptHTML === 'function') {
                        html = window.DeliveryPrint.Generators.generateReceiptHTML(currentOrderData, currentItemsData);
                    } else {
                        html = window.DeliveryPrint.Generators.generateSlipHTML(currentOrderData, currentItemsData, 'CUPOM / RECIBO');
                    }
                } else {
                    html = this._fallbackReceiptSlipHTML(currentOrderData, currentItemsData);
                }
            }

            content.innerHTML = html;

            // Atualiza ícones Lucide se disponível
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        /**
         * Atualiza a UI das abas (ativo/inativo)
         */
        _updateTabsUI: function () {
            const tabKitchen = document.getElementById('pdv-tab-kitchen');
            const tabReceipt = document.getElementById('pdv-tab-receipt');

            if (tabKitchen && tabReceipt) {
                // Remove classes ativas de ambos
                tabKitchen.classList.remove('print-tabs__btn--active');
                tabReceipt.classList.remove('print-tabs__btn--active', 'print-tabs__btn--receipt');

                // Atualiza ARIA
                tabKitchen.setAttribute('aria-selected', 'false');
                tabReceipt.setAttribute('aria-selected', 'false');

                // Adiciona classe ao ativo
                if (currentSlipType === 'kitchen') {
                    tabKitchen.classList.add('print-tabs__btn--active');
                    tabKitchen.setAttribute('aria-selected', 'true');
                } else {
                    tabReceipt.classList.add('print-tabs__btn--active', 'print-tabs__btn--receipt');
                    tabReceipt.setAttribute('aria-selected', 'true');
                }
            }
        },

        // ==========================================
        // FECHAR E IMPRIMIR
        // ==========================================

        /**
         * Fecha o modal e navega para o balcão
         */
        close: function () {
            const modal = document.getElementById('pdvPrintModal');
            if (modal) {
                modal.style.display = 'none';
            }
            currentOrderId = null;
            currentOrderData = null;
            currentItemsData = null;
            currentSlipType = 'kitchen';

            // Navega para o balcão após fechar
            setTimeout(() => {
                if (typeof AdminSPA !== 'undefined') {
                    AdminSPA.navigateTo('balcao', true, true);
                } else {
                    window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/pdv';
                }
            }, 100);
        },

        /**
         * Imprime a ficha atual usando QZ Tray (impressora térmica)
         */
        print: async function () {
            const content = document.getElementById('pdv-print-slip-content');
            if (!content) {
                alert('Conteúdo de impressão não encontrado');
                return;
            }

            // Mostra Animação
            const overlay = document.getElementById('printing-overlay');
            if (overlay) overlay.style.display = 'flex';

            const html = content.innerHTML;

            // Espera mínima (visual)
            const minWait = new Promise(resolve => setTimeout(resolve, 1500));

            try {
                // Usa QZ Tray se disponível (via DeliveryPrint)
                if (window.DeliveryPrint && window.DeliveryPrint.QZ) {
                    await window.DeliveryPrint.QZ.printHTML(html);
                } else {
                    // Fallback: impressão pelo navegador
                    const printArea = document.getElementById('pdv-print-area');
                    if (printArea) {
                        printArea.innerHTML = html;
                        // Pequeno delay para renderizar DOM
                        await new Promise(r => setTimeout(r, 100));
                        window.print();
                    }
                }
            } catch (error) {
                console.error('Erro impressão:', error);
                alert('Erro ao imprimir: ' + error.message);
            }

            // Aguarda tempo mínimo da animação
            await minWait;

            // Oculta Animação
            if (overlay) overlay.style.display = 'none';

            // Fecha modal após impressão
            this.close();
        },

        // ==========================================
        // FALLBACKS (caso geradores do Delivery não estejam carregados)
        // ==========================================

        /**
         * Fallback para ficha de cozinha
         */
        _fallbackKitchenSlipHTML: function (order, items) {
            const date = order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--';
            const itemsHTML = (items || []).map(i => `<div style="padding: 4px 0;">${i.quantity}x ${i.name}</div>`).join('');

            return `
                <div class="print-slip" style="font-size: 14px;">
                    <div style="text-align: center; padding: 10px 0; border-bottom: 2px dashed #000;">
                        <h2 style="margin: 0; font-size: 20px;">** COZINHA **</h2>
                        <div style="font-size: 11px; margin-top: 5px;">${date}</div>
                    </div>
                    <div style="text-align: center; padding: 10px; margin: 8px 0; border: 1px dashed #000;">
                        <div>Pedido #${order.id}</div>
                    </div>
                    <div style="padding: 8px 0;">
                        <h4 style="margin: 0 0 8px 0;">ITENS:</h4>
                        ${itemsHTML}
                    </div>
                </div>
            `;
        },

        /**
         * Fallback para cupom/recibo
         */
        _fallbackReceiptSlipHTML: function (order, items) {
            const date = order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '--';
            const total = order.total ? parseFloat(order.total).toFixed(2).replace('.', ',') : '0,00';
            const itemsHTML = (items || []).map(i => {
                const subtotal = ((i.price || 0) * (i.quantity || 1)).toFixed(2).replace('.', ',');
                return `<div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span>${i.quantity}x ${i.name}</span>
                    <span>R$ ${subtotal}</span>
                </div>`;
            }).join('');

            return `
                <div class="print-slip" style="font-size: 14px;">
                    <div style="text-align: center; padding: 10px 0; border-bottom: 2px dashed #000;">
                        <h2 style="margin: 0; font-size: 20px;">CUPOM / RECIBO</h2>
                        <div style="font-size: 11px; margin-top: 5px;">${date}</div>
                        <div>Pedido #${order.id}</div>
                    </div>
                    <div style="padding: 8px 0; border-bottom: 1px dashed #ccc;">
                        <h4 style="margin: 0 0 8px 0;">ITENS:</h4>
                        ${itemsHTML}
                    </div>
                    <div style="text-align: right; padding-top: 10px; font-size: 18px; font-weight: bold;">
                        TOTAL: R$ ${total}
                    </div>
                </div>
            `;
        }
    };

})();
