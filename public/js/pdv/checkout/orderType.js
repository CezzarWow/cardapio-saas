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
        // Remove toast se existir (dismiss ao clicar em qualquer opção)
        const existingToast = document.getElementById('pdv-toast');
        if (existingToast) existingToast.remove();

        // 0. Validação imediata: Retirada requer cliente ou mesa
        if (type === 'retirada') {
            const ctx = typeof CheckoutHelpers !== 'undefined'
                ? CheckoutHelpers.getContextIds()
                : this._getBasicContext();

            if (!ctx.hasClient && !ctx.hasTable) {
                // Mostra toast sutil
                this._showToast('⚠️ Vincule um cliente ou mesa primeiro');
                return; // Não prossegue
            }
        }

        // 1. Se mudando de entrega para outro, limpa dados primeiro
        if (type !== 'entrega') {
            if (typeof CheckoutEntrega !== 'undefined' && CheckoutEntrega.isDataFilled()) {
                CheckoutEntrega.clearData();
            }
            this._closeDeliveryIfOpen();
        }

        // 2. Ativa o card visual
        element = this._activateCard(type, element);

        // 3. Esconde alertas
        this._hideAllAlerts();

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

    /**
     * Fallback para obter contexto básico se CheckoutHelpers não disponível
     */
    _getBasicContext: function () {
        return {
            hasClient: !!document.getElementById('current_client_id')?.value,
            hasTable: !!document.getElementById('current_table_id')?.value
        };
    },

    /**
     * Mostra um toast sutil que desaparece automaticamente
     */
    _showToast: function (message, duration = 2500) {
        // Remove toast anterior se existir
        const existing = document.getElementById('pdv-toast');
        if (existing) existing.remove();

        // Cria toast
        const toast = document.createElement('div');
        toast.id = 'pdv-toast';
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: #fef3c7;
            color: #92400e;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 99999;
            opacity: 1;
            transition: opacity 0.3s ease;
        `;
        toast.innerText = message;
        document.body.appendChild(toast);

        // Auto-dismiss com fade
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    // ==========================================
    // SUB-FUNÇÕES PRIVADAS
    // ==========================================

    /**
     * Ativa visualmente o botão do tipo selecionado (com estilos inline)
     */
    _activateCard: function (type, element) {
        // Atualiza hidden input com o tipo selecionado
        const selectedTypeInput = document.getElementById('selected_order_type');
        if (selectedTypeInput) selectedTypeInput.value = type;

        // Cores por tipo - Azul padrão, Verde para confirmados
        const colors = {
            local: { border: '#2563eb', bg: '#eff6ff', text: '#2563eb' },
            retirada: { border: '#2563eb', bg: '#eff6ff', text: '#2563eb' },
            retirada_ok: { border: '#16a34a', bg: '#dcfce7', text: '#16a34a' }, // Verde quando válido
            entrega: { border: '#2563eb', bg: '#eff6ff', text: '#2563eb' },
            entrega_ok: { border: '#16a34a', bg: '#dcfce7', text: '#16a34a' }  // Verde quando preenchido
        };
        const inactive = { border: '#cbd5e1', bg: 'white', text: '#1e293b' };

        // Reset todos os toggle buttons para inativo e remove checkmarks
        document.querySelectorAll('.order-toggle-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderColor = inactive.border;
            btn.style.background = inactive.bg;
            btn.style.color = inactive.text;
            // Remove checkmark se existir
            const check = btn.querySelector('.btn-checkmark');
            if (check) check.remove();
        });

        // Se element não foi passado, busca pelo data-type
        if (!element) {
            element = document.querySelector(`.order-toggle-btn[data-type="${type}"]`);
        }

        // Determina se Retirada deve ficar verde (tem cliente/mesa)
        let useGreen = false;
        if (type === 'retirada') {
            const ctx = typeof CheckoutHelpers !== 'undefined'
                ? CheckoutHelpers.getContextIds()
                : this._getBasicContext();
            useGreen = ctx.hasClient || ctx.hasTable;
        }

        // Ativa o selecionado com cores específicas
        if (element) {
            element.classList.add('active');
            const colorKey = useGreen ? 'retirada_ok' : type;
            const c = colors[colorKey] || colors.local;
            element.style.borderColor = c.border;
            element.style.background = c.bg;
            element.style.color = c.text;

            // Adiciona checkmark se verde
            if (useGreen) {
                const checkmark = document.createElement('span');
                checkmark.className = 'btn-checkmark';
                checkmark.innerHTML = '✓';
                checkmark.style.cssText = 'position:absolute; top:-6px; right:-6px; background:#16a34a; color:white; width:18px; height:18px; border-radius:50%; font-size:11px; display:flex; align-items:center; justify-content:center; font-weight:bold;';
                element.style.position = 'relative';
                element.appendChild(checkmark);
            }
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
     * Abre modal de entrega automaticamente se dados não preenchidos
     */
    _handleEntrega: function () {
        // Verifica se dados já foram preenchidos
        const isFilled = typeof CheckoutEntrega !== 'undefined' && CheckoutEntrega.isDataFilled();

        if (!isFilled) {
            // Abre modal de entrega automaticamente
            if (typeof CheckoutEntrega !== 'undefined') {
                CheckoutEntrega.openPanel();
            } else if (typeof openDeliveryPanel === 'function') {
                openDeliveryPanel();
            }
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

