/**
 * PDV CHECKOUT - Entrega
 * Módulo de dados de entrega (encapsulado)
 * 
 * Dependências: CheckoutUI, CheckoutOrderType, CheckoutTotals
 */

const CheckoutEntrega = {

    // Constante: IDs dos campos de entrega
    FIELD_IDS: [
        'delivery_name', 'delivery_address', 'delivery_number',
        'delivery_neighborhood', 'delivery_phone',
        'delivery_complement', 'delivery_observation'
    ],

    // Estado interno (não mais global)
    dataFilled: false,

    /**
     * Abre o painel de entrega
     */
    openPanel: function () {
        const panel = document.getElementById('delivery-panel');
        if (!panel) return;

        // Só auto-preenche nome se tiver CLIENTE selecionado (não mesa)
        // Mesa não deve preencher o nome no formulário de entrega
        const clientId = document.getElementById('current_client_id')?.value;

        if (clientId && clientId !== '' && clientId !== '0') {
            const clientName = document.getElementById('current_client_name')?.value || '';

            if (clientName && clientName.trim()) {
                document.getElementById('delivery_name').value = clientName.replace('🔹 ', '').split(' (')[0].trim();
            }
        }

        // Mostra o painel
        panel.style.display = 'flex';

        // Foca no primeiro campo vazio
        const nameInput = document.getElementById('delivery_name');
        const addressInput = document.getElementById('delivery_address');
        if (nameInput && !nameInput.value) {
            nameInput.focus();
        } else if (addressInput) {
            addressInput.focus();
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    /**
     * Fecha o painel de entrega
     */
    closePanel: function () {
        const panel = document.getElementById('delivery-panel');
        if (panel) panel.style.display = 'none';
    },

    /**
     * Confirma dados de entrega e atualiza total com taxa
     */
    confirmData: function () {
        // Valida campos obrigatórios
        const name = document.getElementById('delivery_name').value.trim();
        const address = document.getElementById('delivery_address').value.trim();
        const neighborhood = document.getElementById('delivery_neighborhood').value.trim();

        if (!name) {
            alert('Digite o nome do cliente!');
            document.getElementById('delivery_name').focus();
            return;
        }
        if (!address) {
            alert('Digite o endereço!');
            document.getElementById('delivery_address').focus();
            return;
        }
        if (!neighborhood) {
            alert('Digite o bairro!');
            document.getElementById('delivery_neighborhood').focus();
            return;
        }

        // Marca como preenchido
        this.dataFilled = true;

        // Fecha o painel
        this.closePanel();

        // ====== FEEDBACK VISUAL 1: Toast verde ======
        this._showToast('✓ Dados de entrega confirmados!');

        // ====== FEEDBACK VISUAL 2: Card "Entrega" fica verde ======
        this._setCardGreen();

        // ====== FEEDBACK VISUAL 3: Badge com check no card ======
        this._addCheckBadge();

        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Re-executa selectOrderType para manter estado
        // Não chamamos selectOrderType aqui porque já deixamos o card verde

        // Atualiza o TOTAL exibido com a taxa de entrega
        if (typeof CheckoutTotals !== 'undefined') {
            let newTotal = CheckoutTotals.getFinalTotal();

            const totalDisplay = document.getElementById('checkout-total-display');
            if (totalDisplay) {
                totalDisplay.innerText = 'R$ ' + newTotal.toFixed(2).replace('.', ',');
            }

            // Atualiza o Input "Valor a Lançar"
            const payInput = document.getElementById('pay-amount');
            const paidDisplay = document.getElementById('display-paid');

            if (payInput) {
                let paidValue = 0;
                if (paidDisplay) {
                    const raw = paidDisplay.innerText.replace(/[^\d,]/g, '').replace(',', '.');
                    paidValue = parseFloat(raw) || 0;
                }

                if (paidValue < 0.01) {
                    payInput.value = newTotal.toFixed(2).replace('.', ',');
                    payInput.dispatchEvent(new Event('input'));
                }
            }
        }

        // Atualiza UI do checkout
        if (typeof CheckoutUI !== 'undefined') {
            CheckoutUI.updateCheckoutUI();
        }
    },

    /**
     * Mostra toast de confirmação verde
     */
    _showToast: function (message) {
        // Remove toast anterior se existir
        const existing = document.getElementById('delivery-toast');
        if (existing) existing.remove();

        // Cria toast
        const toast = document.createElement('div');
        toast.id = 'delivery-toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #059669;
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
            z-index: 9999;
            animation: slideUp 0.3s ease;
        `;
        toast.textContent = message;

        // Adiciona animação CSS se não existir
        if (!document.getElementById('toast-animation-style')) {
            const style = document.createElement('style');
            style.id = 'toast-animation-style';
            style.textContent = `
                @keyframes slideUp {
                    from { opacity: 0; transform: translateX(-50%) translateY(20px); }
                    to { opacity: 1; transform: translateX(-50%) translateY(0); }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(toast);

        // Remove após 3 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    /**
     * Muda card de entrega para verde
     */
    _setCardGreen: function () {
        const card = document.querySelector('.order-toggle-btn[data-type="entrega"]');
        if (card) {
            card.style.borderColor = '#059669';
            card.style.background = '#ecfdf5';
            card.style.color = '#059669';
        }
    },

    /**
     * Adiciona badge de check no card de entrega
     */
    _addCheckBadge: function () {
        const card = document.querySelector('.order-toggle-btn[data-type="entrega"]');
        if (!card) return;

        // Remove badge anterior se existir
        const existingBadge = card.querySelector('.delivery-check-badge');
        if (existingBadge) existingBadge.remove();

        // Cria badge
        const badge = document.createElement('span');
        badge.className = 'delivery-check-badge';
        badge.style.cssText = `
            position: absolute;
            top: -6px;
            right: -6px;
            width: 20px;
            height: 20px;
            background: #059669;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.4);
        `;
        badge.textContent = '✓';

        // Card precisa ser position relative
        card.style.position = 'relative';
        card.appendChild(badge);
    },

    /**
     * Retorna objeto com dados de entrega preenchidos
     * @returns {Object|null}
     */
    getData: function () {
        if (!this.dataFilled) return null;

        return {
            name: document.getElementById('delivery_name')?.value || '',
            address: document.getElementById('delivery_address')?.value || '',
            number: document.getElementById('delivery_number')?.value || '',
            neighborhood: document.getElementById('delivery_neighborhood')?.value || '',
            phone: document.getElementById('delivery_phone')?.value || '',
            complement: document.getElementById('delivery_complement')?.value || '',
            observation: document.getElementById('delivery_observation')?.value || ''
        };
    },

    /**
     * Limpa dados de entrega
     */
    clearData: function () {
        this.dataFilled = false;
        this._clearFields();
        this._clearVisualState();

        // Atualiza alertas
        const dadosOk = document.getElementById('entrega-dados-ok');
        const dadosPendente = document.getElementById('entrega-dados-pendente');

        if (dadosOk) dadosOk.style.display = 'none';
        if (dadosPendente) dadosPendente.style.display = 'block';

        if (typeof lucide !== 'undefined') lucide.createIcons();
        if (typeof CheckoutUI !== 'undefined') CheckoutUI.updateCheckoutUI();
    },

    /**
     * Reset ao fechar checkout
     */
    resetOnClose: function () {
        this.dataFilled = false;
        this.closePanel();
        this._clearFields();
        this._clearVisualState();
    },

    /**
     * Verifica se dados estão preenchidos
     */
    isDataFilled: function () {
        return this.dataFilled;
    },

    /**
     * Helper: Limpa todos os campos de entrega
     */
    _clearFields: function () {
        this.FIELD_IDS.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    },

    /**
     * Helper: Limpa estado visual (badge e cor verde)
     */
    _clearVisualState: function () {
        const card = document.querySelector('.order-toggle-btn[data-type="entrega"]');
        if (!card) return;

        // Remove badge
        const badge = card.querySelector('.delivery-check-badge');
        if (badge) badge.remove();

        // Reseta cores para inativo (branco com texto preto)
        card.style.borderColor = '#cbd5e1';
        card.style.background = 'white';
        card.style.color = '#1e293b';
    }

};

// Expõe globalmente
window.CheckoutEntrega = CheckoutEntrega;

// Aliases de compatibilidade (HTML usa esses)
window.openDeliveryPanel = () => CheckoutEntrega.openPanel();
window.closeDeliveryPanel = () => CheckoutEntrega.closePanel();
window.confirmDeliveryData = () => CheckoutEntrega.confirmData();
window.getDeliveryData = () => CheckoutEntrega.getData();
window.clearDeliveryData = () => CheckoutEntrega.clearData();
window._resetDeliveryOnClose = () => CheckoutEntrega.resetOnClose();

// Para compatibilidade com código legado que checa deliveryDataFilled
Object.defineProperty(window, 'deliveryDataFilled', {
    get: function () { return CheckoutEntrega.dataFilled; },
    set: function (val) { CheckoutEntrega.dataFilled = val; }
});
