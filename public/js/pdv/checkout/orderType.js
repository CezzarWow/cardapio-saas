/**
 * PDV CHECKOUT - Order Type
 * Seleção de tipo de pedido (Local/Retirada/Entrega)
 * 
 * Dependências: CheckoutUI
 */

const CheckoutOrderType = {

    /**
     * Seleciona tipo de pedido e atualiza visual/alertas
     * @param {string} type - 'local' | 'retirada' | 'entrega'
     * @param {HTMLElement} element - Card clicado (pode ser null)
     */
    selectOrderType: function (type, element) {
        // Reset todos os cards
        document.querySelectorAll('.order-type-card').forEach(el => {
            if (!el.classList.contains('disabled')) {
                el.classList.remove('active');
                el.style.border = '1px solid #cbd5e1';
                el.style.background = 'white';
            }
        });

        // Se element não foi passado, busca pelo tipo
        if (!element) {
            document.querySelectorAll('.order-type-card').forEach(card => {
                const label = card.innerText.toLowerCase().trim();
                if (type === 'local' && label.includes('local')) element = card;
                else if (type === 'retirada' && label.includes('retirada')) element = card;
                else if (type === 'entrega' && label.includes('entrega')) element = card;
            });
        }

        // Ativa o selecionado
        if (element && !element.classList.contains('disabled')) {
            element.classList.add('active');
            element.style.border = '2px solid #2563eb';
            element.style.background = '#eff6ff';
        }

        // Logica de keep open / Retirada
        const keepOpenInput = document.getElementById('keep_open_value');
        const alertBoxRetirada = document.getElementById('retirada-client-alert');
        const clientSelectedBox = document.getElementById('retirada-client-selected');
        const noClientBox = document.getElementById('retirada-no-client');

        // Elementos de Entrega
        const alertBoxEntrega = document.getElementById('entrega-alert');
        const entregaDadosOk = document.getElementById('entrega-dados-ok');
        const entregaDadosPendente = document.getElementById('entrega-dados-pendente');

        // Esconde todos os alertas primeiro
        if (alertBoxRetirada) alertBoxRetirada.style.display = 'none';
        if (alertBoxEntrega) alertBoxEntrega.style.display = 'none';

        if (type === 'retirada') {
            if (keepOpenInput) keepOpenInput.value = 'true';

            const clientId = document.getElementById('current_client_id')?.value;
            const tableId = document.getElementById('current_table_id')?.value;

            // Tenta pegar o nome de várias fontes (cliente OU mesa)
            let displayName = document.getElementById('current_client_name')?.value;
            if (!displayName) {
                displayName = document.getElementById('current_table_name')?.value;
            }
            if (!displayName) {
                displayName = document.getElementById('selected-client-name')?.innerText;
            }

            if (alertBoxRetirada) alertBoxRetirada.style.display = 'block';

            // Aceita cliente OU mesa para liberar Retirada
            if ((clientId || tableId) && displayName) {
                if (clientSelectedBox) {
                    clientSelectedBox.style.display = 'block';
                    document.getElementById('retirada-client-name').innerText = displayName;
                }
                if (noClientBox) noClientBox.style.display = 'none';
            } else {
                if (clientSelectedBox) clientSelectedBox.style.display = 'none';
                if (noClientBox) noClientBox.style.display = 'block';
            }
        } else if (type === 'entrega') {
            if (keepOpenInput) keepOpenInput.value = 'false';

            // Mostra alerta de entrega
            if (alertBoxEntrega) alertBoxEntrega.style.display = 'block';

            // Verifica se dados já foram preenchidos
            if (typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled) {
                if (entregaDadosOk) entregaDadosOk.style.display = 'block';
                if (entregaDadosPendente) entregaDadosPendente.style.display = 'none';
            } else {
                if (entregaDadosOk) entregaDadosOk.style.display = 'none';
                if (entregaDadosPendente) entregaDadosPendente.style.display = 'block';
            }
        } else {
            // Local
            if (keepOpenInput) keepOpenInput.value = 'false';
        }

        // Mostra/esconde botão "Pagar Depois" para Retirada/Entrega
        const btnSavePickup = document.getElementById('btn-save-pickup');
        if (btnSavePickup) {
            if (type === 'retirada' || type === 'entrega') {
                btnSavePickup.style.display = 'flex';

                const clientId = document.getElementById('current_client_id')?.value;
                const tableId = document.getElementById('current_table_id')?.value;

                let canEnable = false;

                if (type === 'retirada') {
                    canEnable = !!(clientId || tableId);
                } else if (type === 'entrega') {
                    canEnable = !!(clientId || tableId || (typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled));
                }

                if (canEnable) {
                    btnSavePickup.disabled = false;
                    btnSavePickup.style.opacity = '1';
                    btnSavePickup.style.cursor = 'pointer';
                } else {
                    btnSavePickup.disabled = true;
                    btnSavePickup.style.opacity = '0.5';
                    btnSavePickup.style.cursor = 'not-allowed';
                }
            } else {
                btnSavePickup.style.display = 'none';
            }
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
        CheckoutUI.updateCheckoutUI();
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutOrderType = CheckoutOrderType;
