/**
 * PDV MAIN - Ponto de Entrada
 * Orquestra os módulos: State, Cart, Tables, Checkout.
 */
(function () {
    'use strict';

    window.PDV = {
        init: function () {
            console.log('[PDV] Initializing...');

            // 1. LER CONFIGURAÇÃO (SPA)
            const configEl = document.getElementById('pdv-config');
            let config = {};
            if (configEl) {
                try {
                    config = JSON.parse(configEl.dataset.config);
                    // Define globais legado se necessário para compatibilidade
                    if (config.baseUrl) window.BASE_URL = config.baseUrl;
                    if (config.deliveryFee) window.PDV_DELIVERY_FEE = config.deliveryFee;
                    if (config.tableId) window.PDV_TABLE_ID = config.tableId;
                } catch (e) {
                    console.error('[PDV] Invalid config JSON', e);
                }
            }

            // Limpa URL
            const url = new URL(window.location.href);
            if (url.searchParams.has('order_id') || url.searchParams.has('mesa_id')) {
                const cleanUrl = url.origin + url.pathname + '#pdv'; // Mantém hash se necessário
                // window.history.replaceState({}, document.title, cleanUrl); // AdminSPA cuida da URL
            }

            // 2. INICIALIZA ESTADO (PDVState)
            const tableIdInput = document.getElementById('current_table_id');
            const clientIdInput = document.getElementById('current_client_id');
            const orderIdInput = document.getElementById('current_order_id');

            const tableId = tableIdInput ? tableIdInput.value : null;
            const clientId = clientIdInput ? clientIdInput.value : null;
            const orderId = orderIdInput ? orderIdInput.value : null;

            // Detecta modo baseado config ou inputs
            let modo = 'balcao';
            let status = 'aberto';

            if (config.isEditingPaidOrder) {
                modo = 'retirada';
                status = 'editando_pago';
            } else if (tableId) {
                modo = 'mesa';
            } else if (orderId) {
                modo = 'comanda';
            }

            if (window.PDVState) {
                PDVState.set({
                    modo: modo,
                    mesaId: tableId ? parseInt(tableId) : null,
                    clienteId: clientId ? parseInt(clientId) : null,
                    pedidoId: orderId ? parseInt(orderId) : null
                });
                PDVState.initStatus(status);
            }

            // 3. INICIALIZA CARRINHO (PDVCart)
            if (window.PDVCart) {
                // Recupera carrinho da config
                const recoveredCart = config.recoveredCart || [];

                // Mapeia formato do PHP para formato do JS
                const items = recoveredCart.map(item => ({
                    id: parseInt(item.id),
                    name: item.name,
                    price: parseFloat(item.price),
                    quantity: parseInt(item.quantity),
                    extras: item.extras || []
                }));

                PDVCart.items = []; // Reseta antes de setar
                PDVCart.setItems(items);

                // [MIGRATION] Recupera itens do balcão se houver migração pendente
                if (typeof PDVCart.recoverFromMigration === 'function') {
                    PDVCart.recoverFromMigration();
                }

                PDVCart.updateUI();
            }

            // 4. INICIALIZA MÓDULOS DE UI
            if (window.PDVTables && typeof PDVTables.init === 'function') PDVTables.init();
            if (window.PDVCheckout && typeof PDVCheckout.init === 'function') PDVCheckout.init();

            // Inicializa Eventos (agora com proteção contra duplicação)
            if (window.PDVEvents && typeof PDVEvents.init === 'function') PDVEvents.init();

            // 5. VISUAL INICIAL
            const btn = document.getElementById('btn-finalizar');
            if (parseInt(tableId) > 0 && btn) {
                btn.innerText = "Salvar";
                btn.style.backgroundColor = "#d97706";
                btn.disabled = false;
            }

            // 6. FILTRO DE CATEGORIAS E BUSCA
            if (window.PDVSearch && typeof PDVSearch.init === 'function') {
                PDVSearch.init();
            }

            // 7. ÍCONES (Lucide)
            if (typeof lucide !== 'undefined') lucide.createIcons();

            console.log('[PDV] Ready');
        }
    };

    // ============================================
    // HELPERS GLOBAIS (Compatibilidade)
    // ============================================
    window.formatCurrency = function (value) {
        return parseFloat(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    };

    // Auto-init apenas se não estiver no SPA (fallback legado)
    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('spa-content')) {
            PDV.init();
        }
    });

})();
