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

        // Valida Tipo de Pedido Inicial (caso Entrega esteja desabilitado)
        setTimeout(() => {
            const currentRadio = document.querySelector(`input[name="orderType"][value="${CardapioCheckout.selectedOrderType}"]`);
            if (!currentRadio || currentRadio.disabled) {
                const enabledRadio = document.querySelector('input[name="orderType"]:not([disabled])');
                if (enabledRadio) {
                    CardapioCheckout.setOrderType(enabledRadio.value);
                }
            }
        }, 100);
    },

    // ==========================================
    // SELECIONAR TIPO DE PEDIDO
    // ==========================================
    // ==========================================
    // LÓGICA DE TAXAS E TOTAIS
    // ==========================================
    getDeliveryFee: function () {
        if (this.selectedOrderType !== 'entrega') return 0;
        const config = window.cardapioConfig || {};
        return parseFloat(config.delivery_fee || 0);
    },

    getFinalTotal: function () {
        const cartTotal = CardapioCart.getTotals().value;
        const fee = this.getDeliveryFee();
        return cartTotal + fee;
    },

    updateTotals: function () {
        const fee = this.getDeliveryFee();
        const total = this.getFinalTotal();
        const feeRow = document.getElementById('deliveryFeeRow');

        // Atualiza Linha da Taxa (Review Modal)
        if (feeRow) {
            // Mostra apenas se tem taxa E é entrega
            // Se taxa for 0, mas for entrega, opcionalmente mostra "Grátis" ou esconde.
            // O usuário pediu pra mostrar o valor. Se for 0, mostra R$ 0,00 ou Grátis?
            // Vou mostrar valor normal.
            if (this.selectedOrderType === 'entrega' && fee > 0) {
                feeRow.style.display = 'flex';
                const elDiv = document.getElementById('deliveryFeeValue');
                if (elDiv) elDiv.innerText = Utils.formatCurrency(fee);
            } else {
                feeRow.style.display = 'none';
            }
        }

        // Atualiza Total Visual em todos os lugares
        const totalFormatted = Utils.formatCurrency(total);

        const reviewTotal = document.getElementById('orderReviewTotal');
        if (reviewTotal) reviewTotal.innerText = totalFormatted;

        const paymentTotal = document.getElementById('paymentTotalValue');
        if (paymentTotal) paymentTotal.innerText = totalFormatted;

        // Atualiza também o botão flutuante principal se estiver visível (opcional, mas bom pra consistência)
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

        // Atualiza Totais (Taxas mudam)
        this.updateTotals();
    },

    updateFieldsVisibility: function () {
        const type = this.selectedOrderType;
        const msgLocal = document.getElementById('msgLocal');
        const msgRetirada = document.getElementById('msgRetirada');

        if (msgLocal) msgLocal.style.display = (type === 'local') ? 'block' : 'none';
        if (msgRetirada) msgRetirada.style.display = (type === 'retirada') ? 'block' : 'none';

        // Campos de entrega (Endereço, Bairro, Número)
        document.querySelectorAll('.delivery-only').forEach(el => {
            el.style.display = (type === 'entrega') ? '' : 'none';
        });

        // Label do Número muda
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
        this.updateTotals(); // Garante cálculo correto ao abrir

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
            container.innerHTML += `
                <div class="order-review-item">
                    <div class="order-review-item-qty">${item.quantity}x</div>
                    <div class="order-review-item-info">
                        <div class="order-review-item-name">${item.name}</div>
                        ${item.additionals.length ? `<div class="order-review-item-extras">+ ${item.additionals.map(a => a.name).join(', ')}</div>` : ''}
                        ${item.observation ? `<div class="order-review-item-obs">Obs: ${item.observation}</div>` : ''}
                    </div>
                    <div class="order-review-item-actions">
                        <span class="order-review-item-price">${Utils.formatCurrency(item.unitPrice * item.quantity)}</span>
                        <button class="order-review-remove-btn" onclick="CardapioCart.remove(${item.id}); CardapioCheckout.renderReviewItems();">
                            <i data-lucide="trash-2" size="16"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        Utils.initIcons();

        // Atualiza total se remover e re-renderizar
        this.updateTotals();

        if (items.length === 0) this.closeOrderReview();
    },

    // ==========================================
    // PAGAMENTO
    // ==========================================
    goToPayment: function () {
        this.closeOrderReview();
        this.updateTotals(); // Garante total atualizado no modal pagamento

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

    backToReview: function () {
        this.closePayment();
        // Garante que o modal de revisão abra imediatamente
        this.openOrderReview();
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

        // Montar mensagem para o WhatsApp (Lógica Nova)
        // Recupera mensagens configuradas globalmente (via PHP)
        const config = window.cardapioConfig || {};
        const whatsappNumber = (config.whatsapp_number || '').replace(/\D/g, ''); // Remove não dígitos

        // Recupera array de mensagens (índice 0 = antes, 1 = depois)
        let msgBefore = 'Olá! Gostaria de fazer um pedido:';
        let msgAfter = 'Aguardo a confirmação.';

        try {
            if (config.whatsapp_message) {
                const parsed = JSON.parse(config.whatsapp_message);

                // Formato Novo { before: [], after: [] }
                if (parsed && (typeof parsed === 'object') && (parsed.before || parsed.after)) {
                    if (Array.isArray(parsed.before) && parsed.before.length > 0) {
                        msgBefore = parsed.before.join('\n');
                    }
                    if (Array.isArray(parsed.after) && parsed.after.length > 0) {
                        msgAfter = parsed.after.join('\n');
                    }
                }
                // Formato Legado [msg1, msg2]
                else if (Array.isArray(parsed)) {
                    if (parsed.length > 0 && parsed[0]) msgBefore = parsed[0];
                    if (parsed.length > 1 && parsed[1]) msgAfter = parsed[1];
                }
            }
        } catch (e) {
            console.warn('Erro ao decodificar mensagens do WhatsApp', e);
        }


        // Monta o Corpo do Pedido
        let orderSummary = '*NOVO PEDIDO*\n\n' +
            '👤 *Nome:* ' + name + '\n';

        if (this.selectedOrderType === 'entrega') {
            orderSummary += '📍 *Entrega:* ' + address + ', ' + number + '\n' +
                '🏘️ *Bairro:* ' + neighborhood + '\n';
        } else {
            const label = (this.selectedOrderType === 'local') ? 'Mesa/Comanda' : 'Retirada';
            orderSummary += '🏪 *' + label + ':* ' + number + '\n';
        }

        orderSummary += '\n🛒 *ITENS:*\n';
        CardapioCart.items.forEach(item => {
            orderSummary += `• ${item.quantity}x ${item.name} ` +
                (item.additionals.length ? `(${item.additionals.map(a => a.name).join(', ')})` : '') + '\n';
            if (item.observation) orderSummary += `  _Obs: ${item.observation}_\n`;
        });



        // Adiciona Taxa de Entrega se houver
        const fee = this.getDeliveryFee();
        if (this.selectedOrderType === 'entrega' && fee > 0) {
            orderSummary += '🛵 *Taxa de Entrega:* ' + Utils.formatCurrency(fee) + '\n';
        }

        orderSummary += '\n💰 *Total Final:* ' + Utils.formatCurrency(this.getFinalTotal()) + '\n';
        orderSummary += '💳 *Pagamento:* ' + this.selectedPaymentMethod.toUpperCase();

        if (this.selectedPaymentMethod === 'dinheiro' && changeAmount) {
            orderSummary += ' (Troco para: ' + changeAmount + ')';
        }

        if (obs) orderSummary += '\n📝 *Obs Geral:* ' + obs;

        // Monta Mensagem Final Completa
        const finalMessage = `${msgBefore}\n\n${orderSummary}\n\n${msgAfter}`;

        // Envia para o WhatsApp
        if (whatsappNumber) {
            const url = `https://wa.me/55${whatsappNumber}?text=${encodeURIComponent(finalMessage)}`;
            window.open(url, '_blank');
        } else {
            alert('Número do WhatsApp não configurado!\n\n' + finalMessage);
        }

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
