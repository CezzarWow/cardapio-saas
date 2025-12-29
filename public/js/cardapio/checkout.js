/**
 * CHECKOUT.JS - Fluxo de Finalização e Pagamento
 * Dependências: Cart.js, Utils.js
 */

const CardapioCheckout = {
    selectedOrderType: 'entrega', // Default
    selectedPaymentMethod: null,
    hasNoNumber: false,
    hasNoChange: false,

    init: function () {
        console.log('[Checkout] Inicializado');

        // Inicializa Máscara de Telefone
        const phoneInput = document.getElementById('customerPhone');
        if (phoneInput) {
            phoneInput.oninput = function () {
                Utils.formatPhone(this);
            };
        }

        // Listener para fechar modal de pagamento ao clicar fora
        const modal = document.getElementById('paymentModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) CardapioCheckout.closePayment();
            });
        }
    },

    // ==========================================
    // SELECIONAR TIPO DE PEDIDO
    // ==========================================
    setOrderType: function (type) {
        this.selectedOrderType = type;

        // Atualiza UI
        document.querySelectorAll('input[name="orderType"]').forEach(radio => {
            radio.checked = (radio.value === type);
        });

        // Atualiza campos visíveis
        this.updateFieldsVisibility();
    },

    updateFieldsVisibility: function () {
        const addressGroup = document.getElementById('addressGroup'); // Container endereço
        const numberGroup = document.getElementById('numberGroup'); // Container número
        // Nota: IDs dos grupos podem precisar ser adicionados no PHP se não existirem
        // O código original controla por classes ou inline styles.
        // Vamos manter a lógica original de "Alerta" e validação por enquanto, 
        // e assumir que o CSS cuida do resto ou implementar lógica de esconder.

        // No original, updatePaymentFieldsByOrderType fazia isso. Vamos portar.

        const type = this.selectedOrderType;
        const msgLocal = document.getElementById('msgLocal');
        const msgRetirada = document.getElementById('msgRetirada');
        const deliveryFields = document.getElementById('deliveryFields');

        if (msgLocal) msgLocal.style.display = (type === 'local') ? 'block' : 'none';
        if (msgRetirada) msgRetirada.style.display = (type === 'retirada') ? 'block' : 'none';

        // Campos de entrega (Endereço, Bairro, Número)
        // Classe usada no HTML é 'delivery-only'
        document.querySelectorAll('.delivery-only').forEach(el => {
            el.style.display = (type === 'entrega') ? '' : 'none';
        });

        // Label do Número muda?
        const numLabel = document.querySelector('label[for="customerNumber"]');
        if (numLabel) {
            if (type === 'local') numLabel.textContent = 'Mesa / Comanda';
            else if (type === 'retirada') numLabel.textContent = 'Identificação (Nome/ID)';
            else numLabel.textContent = 'Número';
        }
    },

    // ==========================================
    // RESUMO DO PEDIDO
    // ==========================================
    openOrderReview: function () {
        if (CardapioCart.items.length === 0) {
            alert('Seu carrinho está vazio!');
            return;
        }

        this.renderReviewItems();

        // Atualiza total
        const totals = CardapioCart.getTotals();
        document.getElementById('orderReviewTotal').innerText = Utils.formatCurrency(totals.value); // float btn

        document.getElementById('orderReviewModal').classList.add('show');
    },

    closeOrderReview: function () {
        document.getElementById('orderReviewModal').classList.remove('show');
    },

    renderReviewItems: function () {
        const container = document.getElementById('orderReviewItems');
        if (!container) return;

        container.innerHTML = '';
        const items = CardapioCart.items;

        items.forEach(item => {
            // Container flex para alinhar preço e lixeira na mesma linha
            container.innerHTML += `
                <div class="order-review-item">
                    <div class="order-review-item-qty">${item.quantity}x</div>
                    <div class="order-review-item-info">
                        <div class="order-review-item-name">${item.name}</div>
                        ${item.additionals.length ? `<div class="order-review-item-extras">+ ${item.additionals.map(a => a.name).join(', ')}</div>` : ''}
                        ${item.observation ? `<div class="order-review-item-obs">Obs: ${item.observation}</div>` : ''}
                    </div>
                    
                    <div class="order-review-item-actions">
                        <div class="order-review-price-row">
                            <span class="order-review-item-price">${Utils.formatCurrency(item.unitPrice * item.quantity)}</span>
                            <button class="order-review-remove-icon-btn" onclick="CardapioCart.remove(${item.id}); CardapioCheckout.renderReviewItems();">
                                <i data-lucide="trash-2" size="18"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        Utils.initIcons();

        // Atualiza total se remover
        const totals = CardapioCart.getTotals();
        document.getElementById('orderReviewTotal').innerText = Utils.formatCurrency(totals.value);
        if (items.length === 0) this.closeOrderReview();
    },

    // ==========================================
    // PAGAMENTO
    // ==========================================
    goToPayment: function () {
        this.closeOrderReview();

        // Atualiza Total no Modal de Pagamento
        const totals = CardapioCart.getTotals();
        const paymentTotal = document.getElementById('paymentTotalValue');
        if (paymentTotal) paymentTotal.textContent = Utils.formatCurrency(totals.value);

        // RESET: Esconde o card de troco (só aparece ao selecionar Dinheiro)
        const changeContainer = document.getElementById('changeContainer');
        if (changeContainer) {
            changeContainer.style.display = 'none';
        }

        // RESET: Limpa seleção de método de pagamento
        this.selectedPaymentMethod = null;
        document.querySelectorAll('input[name="paymentMethod"]').forEach(r => r.checked = false);
        document.getElementById('paymentModal').classList.remove('has-change');

        // Aplica máscara de dinheiro ao campo de troco
        const changeInput = document.getElementById('changeAmount');
        if (changeInput) {
            changeInput.value = '';
            // Remove listener antigo se existir e adiciona novo
            changeInput.oninput = function () {
                Utils.formatMoneyInput(this);
            };
        }

        document.getElementById('paymentModal').classList.add('show');
        this.updateFieldsVisibility(); // Garante estado correto
    },

    closePayment: function () {
        document.getElementById('paymentModal').classList.remove('show');
    },

    selectPaymentMethod: function (method) {
        this.selectedPaymentMethod = method;

        // Visual
        // O código original usa radio buttons, então o browser cuida do visual 'checked'.
        // Se precisarmos de logica extra (ex: mostrar campo troco):
        const changeContainer = document.getElementById('changeContainer');
        if (method === 'dinheiro') {
            changeContainer.style.display = 'block';
            document.getElementById('paymentModal').classList.add('has-change');
            setTimeout(() => this.scrollToChange(), 250);
        } else {
            changeContainer.style.display = 'none';
            document.getElementById('paymentModal').classList.remove('has-change');
            this.hasNoChange = false; // Reset
        }
    },

    toggleNoNumber: function () {
        const input = document.getElementById('customerNumber');
        const btn = document.querySelector('.no-number-btn');

        this.hasNoNumber = !this.hasNoNumber;

        if (this.hasNoNumber) {
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

    toggleNoChange: function () {
        const input = document.getElementById('changeAmount');
        const btn = document.querySelector('.no-change-btn');

        this.hasNoChange = !this.hasNoChange;

        if (this.hasNoChange) {
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

    confirmChange: function () {
        // Lógica de "OK" no troco (compactar)
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

    editChange: function () {
        const inputGroup = document.getElementById('changeInputGroup');
        const summary = document.getElementById('changeSummary');
        const input = document.getElementById('changeAmount');

        if (inputGroup) inputGroup.style.display = 'block';
        if (summary) summary.style.display = 'none';

        document.getElementById('changeContainer').classList.remove('summary-mode');

        // Reset robusto do input
        if (input) {
            input.disabled = false;
            // Limpa se tiver qualquer texto relacionado a sem troco
            if (input.value && (input.value.includes('Sem') || input.value.includes('troco'))) {
                input.value = '';
            }
        }

        // Remove active de TODOS os botões de sem troco (prevenção)
        document.querySelectorAll('.no-change-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        this.hasNoChange = false;

        // Força foco com pequeno delay para garantir renderização
        if (input) {
            setTimeout(() => input.focus(), 50);
        }
    },

    scrollToChange: function () {
        const el = document.getElementById('changeContainer');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    // ==========================================
    // ENVIAR PEDIDO
    // ==========================================
    sendOrder: function () {
        const name = document.getElementById('customerName').value.trim();
        const address = document.getElementById('customerAddress').value.trim();
        const number = document.getElementById('customerNumber').value.trim();
        const neighborhood = document.getElementById('customerNeighborhood').value.trim();
        const obs = document.getElementById('customerObs').value.trim();
        const changeAmount = document.getElementById('changeAmount').value.trim();

        // Validações
        if (!name) return alert('Por favor, preencha seu nome.');

        // Validação de Endereço (só para Entrega)
        if (this.selectedOrderType === 'entrega') {
            if (!address) return alert('Por favor, preencha o endereço.');
        }

        // Número é obrigatório para todos
        if (!number && !this.hasNoNumber) {
            return alert('Por favor, preencha o número (ou Mesa/Comanda) ou selecione "Sem nº".');
        }

        if (!this.selectedPaymentMethod) {
            return alert('Por favor, selecione a forma de pagamento.');
        }

        const totals = CardapioCart.getTotals();

        // Montar mensagem
        let msg = '🎉 Pedido enviado!\n\n' +
            'Tipo: ' + this.selectedOrderType.toUpperCase() + '\n' +
            'Nome: ' + name + '\n';

        if (this.selectedOrderType === 'entrega') {
            msg += 'Endereço: ' + address + ', ' + number + '\n' +
                'Bairro: ' + neighborhood + '\n';
        } else {
            msg += 'Número/Mesa: ' + number + '\n';
        }

        msg += 'Pagamento: ' + this.selectedPaymentMethod.toUpperCase();

        if (this.selectedPaymentMethod === 'dinheiro' && changeAmount) {
            msg += ' (Troco: ' + changeAmount + ')';
        }

        msg += '\nTotal: ' + Utils.formatCurrency(totals.value);

        if (obs) msg += '\nObs: ' + obs;

        // Itens
        msg += '\n\nItens:\n';
        CardapioCart.items.forEach(item => {
            msg += `${item.quantity}x ${item.name} ` +
                (item.additionals.length ? `(+${item.additionals.length} add)` : '') + '\n';
        });

        msg += '\n\n(Integração com WhatsApp/backend será implementada)';

        alert(msg);

        this.reset();
    },

    reset: function () {
        CardapioCart.clear();
        this.selectedPaymentMethod = null;
        this.selectedOrderType = 'entrega';
        this.hasNoNumber = false;

        document.getElementById('paymentModal').classList.remove('show');

        // Reset Inputs
        document.querySelectorAll('input').forEach(i => i.value = '');
        document.getElementById('changeContainer').style.display = 'none';

        // Reset Radios
        document.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
        // Reseta tipo de pedido default
        this.setOrderType('entrega');
    }
};

// ==========================================
// EXPOR VARIÁVEIS PARA COMPATIBILIDADE
// ==========================================
window.CardapioCheckout = CardapioCheckout;

// Variáveis Globais (Mapping)
// Variáveis Globais (Mapping)
try {
    Object.defineProperty(window, 'selectedOrderType', {
        get: () => CardapioCheckout.selectedOrderType,
        set: (val) => CardapioCheckout.selectedOrderType = val,
        configurable: true
    });
    Object.defineProperty(window, 'selectedPaymentMethod', {
        get: () => CardapioCheckout.selectedPaymentMethod,
        set: (val) => CardapioCheckout.selectedPaymentMethod = val,
        configurable: true
    });
    Object.defineProperty(window, 'hasNoNumber', {
        get: () => CardapioCheckout.hasNoNumber,
        set: (val) => CardapioCheckout.hasNoNumber = val,
        configurable: true
    });
} catch (e) {
    console.warn('[CardapioCheckout] Falha ao definir getters globais:', e);
}

// Funções Legado
window.selectOrderType = (t) => CardapioCheckout.setOrderType(t);
window.openOrderReviewModal = () => CardapioCheckout.openOrderReview();
window.closeOrderReviewModal = () => CardapioCheckout.closeOrderReview();
window.finalizarPedido = () => CardapioCheckout.openOrderReview(); // Alias
window.goToPayment = () => CardapioCheckout.goToPayment();
window.closePaymentModal = () => CardapioCheckout.closePayment();
window.sendOrder = () => CardapioCheckout.sendOrder();
window.toggleNoNumber = () => CardapioCheckout.toggleNoNumber();
window.toggleNoChange = () => CardapioCheckout.toggleNoChange();
window.confirmChange = () => CardapioCheckout.confirmChange();
window.editChange = () => CardapioCheckout.editChange();

// Evento Global de Pagamento (Delegado)
document.addEventListener('change', function (e) {
    if (e.target.name === 'paymentMethod') {
        CardapioCheckout.selectPaymentMethod(e.target.value);
    }
});
