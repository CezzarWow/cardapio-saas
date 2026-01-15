/**
 * PDV CHECKOUT - Order Type
 * Seleção de tipo de pedido (Local/Retirada/Entrega)
 * 
 * Dependências: CheckoutUI, CheckoutHelpers
 */

const CheckoutOrderType = {

    /**
     * Seleciona tipo de pedido e atualiza visual/alertas
     * @param {string} type - 'local' | 'retirada' | 'entrega'
     * @param {HTMLElement} element - Card clicado (pode ser null)
     */
    selectOrderType: function (type, element) {
        // 1. Ativa o card visual
        element = this._activateCard(type, element);

        // 2. Esconde alertas e fecha painel de entrega se necessário
        this._hideAllAlerts();
        if (type !== 'entrega') {
            this._closeDeliveryIfOpen();
        }

        // 3. Processa tipo específico
        const keepOpenInput = document.getElementById('keep_open_value');

        if (type === 'retirada') {
            if (keepOpenInput) keepOpenInput.value = 'true';
            this._handleRetirada();
        } else if (type === 'entrega') {
            if (keepOpenInput) keepOpenInput.value = 'false';
            this._handleEntrega();
        } else {
            // Local
            if (keepOpenInput) keepOpenInput.value = 'false';
        }

        // 4. Atualiza botão "Pagar Depois"
        this._updateSavePickupButton(type);

        // 5. Finaliza
        if (typeof lucide !== 'undefined') lucide.createIcons();
        CheckoutUI.updateCheckoutUI();
    },

    // ==========================================
    // SUB-FUNÇÕES PRIVADAS
    // ==========================================

    /**
     * Ativa visualmente o card do tipo selecionado
     */
    _activateCard: function (type, element) {
        // Reset todos os cards
        document.querySelectorAll('.order-type-card').forEach(el => {
            if (!el.classList.contains('disabled')) {
                el.classList.remove('active');
                el.style.border = '2px solid #cbd5e1';
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

        return element;
    },

    /**
     * Esconde todos os alertas de tipo de pedido
     */
    _hideAllAlerts: function () {
        const alertBoxRetirada = document.getElementById('retirada-client-alert');
        const alertBoxEntrega = document.getElementById('entrega-alert');
        if (alertBoxRetirada) alertBoxRetirada.style.display = 'none';
        if (alertBoxEntrega) alertBoxEntrega.style.display = 'none';
    },

    /**
     * Fecha painel de entrega e reseta flag
     */
    _closeDeliveryIfOpen: function () {
        if (typeof CheckoutEntrega !== 'undefined') {
            CheckoutEntrega.closePanel();
            CheckoutEntrega.dataFilled = false;
        }
    },

    /**
     * Processa lógica específica de Retirada
     */
    _handleRetirada: function () {
        const ctx = CheckoutHelpers.getContextIds();
        const displayName = this._getDisplayName(ctx);

        const alertBox = document.getElementById('retirada-client-alert');
        const clientSelectedBox = document.getElementById('retirada-client-selected');
        const noClientBox = document.getElementById('retirada-no-client');

        if (alertBox) alertBox.style.display = 'block';

        // Aceita cliente OU mesa para liberar Retirada
        if ((ctx.hasClient || ctx.hasTable) && displayName) {
            if (clientSelectedBox) {
                clientSelectedBox.style.display = 'block';
                document.getElementById('retirada-client-name').innerText = displayName;
            }
            if (noClientBox) noClientBox.style.display = 'none';
        } else {
            if (clientSelectedBox) clientSelectedBox.style.display = 'none';
            if (noClientBox) noClientBox.style.display = 'block';
        }
    },

    /**
     * Processa lógica específica de Entrega
     */
    _handleEntrega: function () {
        const alertBox = document.getElementById('entrega-alert');
        const dadosOk = document.getElementById('entrega-dados-ok');
        const dadosPendente = document.getElementById('entrega-dados-pendente');

        if (alertBox) alertBox.style.display = 'block';

        // Verifica se dados já foram preenchidos
        const isFilled = typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled;
        if (isFilled) {
            if (dadosOk) dadosOk.style.display = 'block';
            if (dadosPendente) dadosPendente.style.display = 'none';
        } else {
            if (dadosOk) dadosOk.style.display = 'none';
            if (dadosPendente) dadosPendente.style.display = 'block';
        }
    },

    /**
     * Atualiza estado do botão "Pagar Depois"
     */
    _updateSavePickupButton: function (type) {
        const btnSavePickup = document.getElementById('btn-save-pickup');
        if (!btnSavePickup) return;

        if (type === 'retirada' || type === 'entrega') {
            btnSavePickup.style.display = 'flex';

            const ctx = CheckoutHelpers.getContextIds();
            let canEnable = false;

            if (type === 'retirada') {
                canEnable = ctx.hasClient || ctx.hasTable;
            } else if (type === 'entrega') {
                const isFilled = typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled;
                canEnable = ctx.hasClient || ctx.hasTable || isFilled;
            }

            btnSavePickup.disabled = !canEnable;
            btnSavePickup.style.opacity = canEnable ? '1' : '0.5';
            btnSavePickup.style.cursor = canEnable ? 'pointer' : 'not-allowed';
        } else {
            btnSavePickup.style.display = 'none';
        }
    },

    /**
     * Obtém o nome para exibição (cliente ou mesa)
     */
    _getDisplayName: function (ctx) {
        // Tenta pegar o nome de várias fontes
        let displayName = document.getElementById('current_client_name')?.value;

        if (!displayName) {
            displayName = document.getElementById('current_table_name')?.value;
        }

        // Se tem mesa com número, usa "Mesa X"
        if (!displayName && ctx.hasTable) {
            const tableNumber = document.getElementById('current_table_number')?.value;
            if (tableNumber) displayName = 'Mesa ' + tableNumber;
        }

        if (!displayName) {
            const selectedName = document.getElementById('selected-client-name')?.innerText;
            if (selectedName && selectedName !== 'Nome' && selectedName.trim() !== '') {
                displayName = selectedName;
            }
        }

        return displayName || '';
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutOrderType = CheckoutOrderType;

