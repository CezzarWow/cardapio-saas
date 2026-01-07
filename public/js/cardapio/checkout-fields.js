/**
 * CHECKOUT-FIELDS.JS - Módulo de Campos e Toggles
 * Funções de toggle para campos de formulário (Sem nº, Sem troco, etc)
 */

const CheckoutFields = {
    /**
     * Toggle para "Sem Número" no campo de endereço
     * @param {Object} checkout - Referência ao CardapioCheckout
     */
    toggleNoNumber: function (checkout) {
        const input = document.getElementById('customerNumber');
        const btn = document.querySelector('.no-number-btn');

        checkout.hasNoNumber = !checkout.hasNoNumber;

        if (checkout.hasNoNumber) {
            input.value = 'S/N';
            input.disabled = true;
            btn.classList.add('active');
        } else {
            input.value = '';
            input.disabled = false;
            btn.classList.remove('active');
            input.focus();
        }
    },

    /**
     * Toggle para "Sem Troco" no campo de pagamento
     * @param {Object} checkout - Referência ao CardapioCheckout
     */
    toggleNoChange: function (checkout) {
        const input = document.getElementById('changeAmount');
        const btn = document.querySelector('.no-change-btn');

        checkout.hasNoChange = !checkout.hasNoChange;

        if (checkout.hasNoChange) {
            input.value = 'Sem troco';
            input.disabled = true;
            btn.classList.add('active');
            this.confirmChange();
        } else {
            input.value = 'R$ 0,00';
            input.disabled = false;
            btn.classList.remove('active');
        }
    },

    /**
     * Confirma valor do troco (compacta UI)
     */
    confirmChange: function () {
        const input = document.getElementById('changeAmount');
        const inputGroup = document.getElementById('changeInputGroup');
        const summary = document.getElementById('changeSummary');
        const summaryText = document.getElementById('changeSummaryText');

        let val = input.value;
        if (!val || val === 'R$ 0,00') val = 'Sem troco';

        summaryText.textContent = (val === 'Sem troco') ? 'Sem troco' : 'Troco: ' + val;

        if (inputGroup) inputGroup.style.display = 'none';
        if (summary) summary.style.display = 'flex';

        document.getElementById('changeContainer').classList.add('summary-mode');
    },

    /**
     * Edita valor do troco (expande UI)
     * @param {Object} checkout - Referência ao CardapioCheckout
     */
    editChange: function (checkout) {
        const inputGroup = document.getElementById('changeInputGroup');
        const summary = document.getElementById('changeSummary');
        const input = document.getElementById('changeAmount');

        if (inputGroup) inputGroup.style.display = 'block';
        if (summary) summary.style.display = 'none';

        document.getElementById('changeContainer').classList.remove('summary-mode');

        if (input) {
            input.disabled = false;
            if (input.value && (input.value.includes('Sem') || input.value.includes('troco'))) {
                input.value = '';
            }
        }

        document.querySelectorAll('.no-change-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        checkout.hasNoChange = false;

        if (input) {
            setTimeout(() => input.focus(), 50);
        }
    },

    /**
     * Scroll suave para campo de troco
     */
    scrollToChange: function () {
        const el = document.getElementById('changeContainer');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    /**
     * Atualiza visibilidade dos campos conforme tipo de pedido
     * @param {string} type - Tipo de pedido (entrega, retirada, local)
     */
    updateFieldsVisibility: function (type) {
        const msgLocal = document.getElementById('msgLocal');
        const msgRetirada = document.getElementById('msgRetirada');

        if (msgLocal) msgLocal.style.display = (type === 'local') ? 'block' : 'none';
        if (msgRetirada) msgRetirada.style.display = (type === 'retirada') ? 'block' : 'none';

        // Campos de entrega (Endereço, Bairro)
        document.querySelectorAll('.delivery-only').forEach(el => {
            el.style.display = (type === 'entrega') ? '' : 'none';
        });

        // Campo de Número
        const numberField = document.getElementById('numberFieldRow');
        if (numberField) {
            numberField.style.display = (type === 'entrega') ? '' : 'none';
        }

        // Placeholder do campo Número
        const numInput = document.getElementById('customerNumber');
        if (numInput) {
            numInput.placeholder = (type === 'local') ? 'Mesa *' : 'Nº *';
        }

        // Label do Número
        const numLabel = document.querySelector('label[for="customerNumber"]');
        if (numLabel) {
            if (type === 'local') numLabel.textContent = 'Mesa / Comanda';
            else if (type === 'retirada') numLabel.textContent = 'Identificação (Nome/ID)';
            else numLabel.textContent = 'Número';
        }
    }
};

// Expor globalmente
window.CheckoutFields = CheckoutFields;
