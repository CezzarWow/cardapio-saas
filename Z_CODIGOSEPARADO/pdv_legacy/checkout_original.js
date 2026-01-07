/**
 * PDV CHECKOUT - Lógica de Pagamento e Finalização
 * Dependências: PDVState, PDVCart
 */

const PDVCheckout = {
    currentPayments: [],
    totalPaid: 0,
    discountValue: 0, // [NOVO]
    cachedTotal: 0, // [FIX] Total armazenado quando o modal abre
    selectedMethod: 'dinheiro',
    closingOrderId: null, // Para fechar comanda específica

    init: function () {
        console.log('[PDVCheckout] Inicializado');
        this.bindEvents();
    },

    bindEvents: function () {
        // [FIX] Removido bind manual para evitar conflitos com onclick inline
        // O onclick="finalizeSale()" do HTML chamará window.finalizeSale() -> PDVCheckout.finalizeSale()

        // Input de pagamento
        const payInput = document.getElementById('pay-amount');
        if (payInput) {
            payInput.addEventListener('input', function () { PDVCheckout.formatMoneyInput(this); });
            payInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    PDVCheckout.addPayment();
                }
            });
        }

        // Input Desconto [FIX]
        const discInput = document.getElementById('discount-amount');
        if (discInput) {
            discInput.addEventListener('input', function () {
                PDVCheckout.formatMoneyInput(this);
                PDVCheckout.applyDiscount(this.value);
            });
        }
    },

    formatMoneyInput: function (input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') { input.value = ''; return; }
        value = (parseInt(value) / 100).toFixed(2).replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        input.value = value;
    },

    // [NOVO] Aplica desconto
    applyDiscount: function (valStr) {
        if (!valStr) {
            this.discountValue = 0;
        } else {
            let val = valStr.replace(/\./g, '').replace(',', '.');
            this.discountValue = parseFloat(val) || 0;
        }
        this.updateCheckoutUI();

        // Se mudou o desconto, reseta o valor sugerido no input de pagamento
        const finalTotal = this.getFinalTotal();
        const remaining = Math.max(0, finalTotal - this.totalPaid);
        const input = document.getElementById('pay-amount');
        if (input) {
            input.value = remaining.toFixed(2).replace('.', ',');
            this.formatMoneyInput(input);
        }
    },

    // ==========================================
    // LÓGICA DE ABERTURA DO CHECKOUT
    // ==========================================

    // Botão Principal "Finalizar" (ou Salvar/Entregar dinâmico)
    finalizeSale: function () {
        // SE ESTIVER EM MESA (ADICIONAR ITENS) -> SALVA DIRETO SEM PAGAMENTO
        const tableId = document.getElementById('current_table_id').value;

        // VERIFICAÇÃO ESPECIAL: Edição de Pedido Pago
        if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder) {
            this.handlePaidOrderInclusion();
            return;
        }

        if (tableId) {
            if (PDVCart.items.length === 0) { alert('Carrinho vazio!'); return; }
            // Abre modal de pagamento (não salva direto)
            this.openCheckoutModal();
            return;
        }

        // BALCÃO -> ABRE PAGAMENTO
        const stateBalcao = PDVState.getState();

        // Se não é retirada, assume balcão
        if (!tableId && stateBalcao.modo !== 'retirada') {
            PDVState.set({ modo: 'balcao' });
        }

        if (PDVCart.items.length === 0) { alert('Carrinho vazio!'); return; }

        this.openCheckoutModal();
    },

    handlePaidOrderInclusion: function () {
        const cartTotal = PDVCart.calculateTotal();

        if (PDVCart.items.length > 0 && cartTotal > 0.01) {
            // Tem novos itens -> Cobra diferença
            this.currentPayments = [];
            this.totalPaid = 0;
            document.getElementById('checkout-total-display').innerText = this.formatCurrency(cartTotal);
            document.getElementById('checkoutModal').style.display = 'flex';
            this.setMethod('dinheiro');
            this.updateCheckoutUI();
            window.isPaidOrderInclusion = true; // Flag legacy para o submitSale saber
        } else {
            alert('Carrinho vazio! Adicione novos itens para cobrar.');
        }
    },

    // Fechar Conta Mesa
    fecharContaMesa: function (mesaId) {
        PDVState.set({ modo: 'mesa', mesaId: mesaId, fechandoConta: true });
        const state = PDVState.getState();

        if (state.status === 'editando_pago') {
            alert('Mesa não permite editar pedido pago.');
            return;
        }

        this.currentPayments = [];
        this.totalPaid = 0;

        // Total da mesa (Inputs hidden)
        const tableTotalStr = document.getElementById('table-initial-total').value;
        const tableTotal = parseFloat(tableTotalStr);

        document.getElementById('checkout-total-display').innerText = this.formatCurrency(tableTotal);
        document.getElementById('checkoutModal').style.display = 'flex';
        this.setMethod('dinheiro');
        this.updateCheckoutUI();
    },

    // Fechar Comanda
    fecharComanda: function (orderId) {
        const isPaid = document.getElementById('current_order_is_paid') ? document.getElementById('current_order_is_paid').value == '1' : false;

        PDVState.set({
            modo: 'comanda',
            pedidoId: orderId ? parseInt(orderId) : null,
            fechandoConta: true
        });

        if (isPaid) {
            // JÁ PAGO -> FINALIZAR / ENTREGAR DIRETO
            if (!confirm('Este pedido já está PAGO. Deseja entregá-lo e finalizar?')) return;
            this.forceDelivery(orderId);
            return;
        }

        // Checkout Normal
        this.closingOrderId = orderId;
        this.currentPayments = [];
        this.totalPaid = 0;

        const totalStr = document.getElementById('table-initial-total').value;
        document.getElementById('checkout-total-display').innerText = this.formatCurrency(parseFloat(totalStr));

        // Default to Local
        const cards = document.querySelectorAll('.order-type-card');
        if (cards.length > 0) this.selectOrderType('local', cards[0]);

        document.getElementById('checkoutModal').style.display = 'flex';
        this.setMethod('dinheiro');
        this.updateCheckoutUI();
    },

    openCheckoutModal: function () {
        this.currentPayments = [];
        this.totalPaid = 0;
        this.discountValue = 0; // Reset

        // Calcula o total usando a função global (pdv-core.js) ou PDVCart
        let cartTotal = 0;
        if (typeof calculateTotal === 'function') {
            // Usa a função global de pdv-core.js (variável global 'cart')
            cartTotal = calculateTotal();
        } else if (typeof PDVCart !== 'undefined') {
            // Fallback para PDVCart
            cartTotal = PDVCart.calculateTotal();
        }
        let tableInitialTotal = parseFloat(document.getElementById('table-initial-total')?.value || 0);
        this.cachedTotal = cartTotal + tableInitialTotal;

        // Reset Inputs
        const discInput = document.getElementById('discount-amount');
        if (discInput) discInput.value = '';

        // Reset payment list
        this.updatePaymentList();

        document.getElementById('checkout-total-display').innerText = this.formatCurrency(this.cachedTotal);
        document.getElementById('checkoutModal').style.display = 'flex';
        this.setMethod('dinheiro');

        // SEMPRE abre com "Local" selecionado
        const localCard = document.querySelector('.order-type-card:first-child');
        if (localCard) {
            this.selectOrderType('local', localCard);
        } else {
            // Fallback: reseta manualmente
            document.getElementById('keep_open_value').value = 'false';
            const alertBox = document.getElementById('retirada-client-alert');
            if (alertBox) alertBox.style.display = 'none';
        }

        this.updateCheckoutUI();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    closeCheckout: function () {
        document.getElementById('checkoutModal').style.display = 'none';
        this.currentPayments = [];
        this.totalPaid = 0;

        // Limpa visual
        const alertBox = document.getElementById('retirada-client-alert');
        if (alertBox) alertBox.style.display = 'none';

        // Se era Balcão e cancelou checkout, limpa cliente SE foi selecionado no fluxo de retirada?
        // Lógica original: limpar se não tem mesaId.
        const tableId = document.getElementById('current_table_id')?.value;
        if (!tableId) {
            // Talvez não queira limpar o cliente se ele já estava selecionado antes de abrir o modal?
            // Mantendo lógica original
            if (document.getElementById('current_client_id')) {
                // document.getElementById('current_client_id').value = ''; // Original faz isso
                // Mas e se eu selecionei o cliente ANTES de clicar em finalizar?
                // Original linha 1377 verifica '!tableId'.
            }
        }
    },

    // ==========================================
    // LÓGICA DE PAGAMENTO (UI)
    // ==========================================
    setMethod: function (method) {
        this.selectedMethod = method; // Atualiza estado local
        // Visual
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderColor = '#cbd5e1';
            btn.style.background = 'white';
            const icon = btn.querySelector('svg');
            if (icon) icon.style.color = 'currentColor';
        });
        const activeBtn = document.getElementById('btn-method-' + method);
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.borderColor = '#2563eb';
            activeBtn.style.background = '#eff6ff';
            const icon = activeBtn.querySelector('svg');
            if (icon) icon.style.color = '#2563eb';
        }

        // Auto-preenchimento
        const finalTotal = this.getFinalTotal();
        const remaining = finalTotal - this.totalPaid;
        const input = document.getElementById('pay-amount');
        if (remaining > 0) {
            input.value = remaining.toFixed(2).replace('.', ',');
        } else {
            input.value = '';
        }
        setTimeout(() => input.focus(), 100);
    },

    addPayment: function () {
        const amountInput = document.getElementById('pay-amount');
        let valStr = amountInput.value.trim();
        if (valStr.includes(',')) valStr = valStr.replace(/\./g, '').replace(',', '.');
        let amount = parseFloat(valStr);

        if (!amount || amount <= 0 || isNaN(amount)) {
            alert('Digite um valor válido.');
            return;
        }

        const finalTotal = this.getFinalTotal();
        const remaining = finalTotal - this.totalPaid;

        // Regra de troco simples (se não for dinheiro, trava no restante)
        if (this.selectedMethod !== 'dinheiro' && amount > remaining + 0.01) {
            amount = remaining;
            if (amount <= 0.01) { alert('Valor restante já pago!'); return; }
        }

        this.currentPayments.push({
            method: this.selectedMethod,
            amount: amount,
            label: this.formatMethodLabel(this.selectedMethod)
        });
        this.totalPaid += amount;

        // DEBUG
        console.log('[addPayment] amount:', amount, 'totalPaid:', this.totalPaid, 'finalTotal:', this.getFinalTotal());

        amountInput.value = '';

        this.updatePaymentList();
        this.updateCheckoutUI();

        // Foca no botão se terminou
        const newRemaining = finalTotal - this.totalPaid;
        if (newRemaining <= 0.01) {
            document.getElementById('btn-finish-sale').focus();
        } else {
            // Preenche restante
            let rest = newRemaining.toFixed(2).replace('.', ',');
            document.getElementById('pay-amount').value = rest;
            amountInput.focus();
        }
    },

    removePayment: function (index) {
        const removed = this.currentPayments.splice(index, 1)[0];
        this.totalPaid -= removed.amount;
        this.updatePaymentList();
        this.updateCheckoutUI();

        // [FIX] Restaura o valor restante no campo "Valor a Lançar"
        const finalTotal = this.getFinalTotal();
        const remaining = Math.max(0, finalTotal - this.totalPaid);
        const input = document.getElementById('pay-amount');
        if (input && remaining > 0) {
            input.value = remaining.toFixed(2).replace('.', ',');
            this.formatMoneyInput(input);
            input.focus();
        }
    },

    updatePaymentList: function () {
        const listEl = document.getElementById('payment-list');
        listEl.innerHTML = '';
        if (this.currentPayments.length === 0) {
            // Mantém o espaço mas mostra placeholder
            listEl.style.display = 'block';
            listEl.innerHTML = '<div style="text-align: center; color: #94a3b8; font-size: 0.9rem; padding: 20px 0;">Nenhum pagamento lançado</div>';
            return;
        }
        listEl.style.display = 'block';
        this.currentPayments.forEach((pay, index) => {
            const row = document.createElement('div');
            row.style.cssText = "display: flex; justify-content: space-between; padding: 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; align-items: center; margin-bottom: 5px; border-radius: 6px;";
            row.innerHTML = `
                <span style="font-weight:600; color:#334155;">${pay.label}</span>
                <div style="display:flex; align-items:center; gap:10px;">
                    <strong>${this.formatCurrency(pay.amount)}</strong>
                    <button onclick="PDVCheckout.removePayment(${index})" style="color:#ef4444; border:none; background:#fee2e2; width:24px; height:24px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
                </div>
            `;
            listEl.appendChild(row);
        });

        // Auto-scroll para o final
        setTimeout(() => {
            listEl.scrollTop = listEl.scrollHeight;
        }, 50);

        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    updateCheckoutUI: function () {
        const finalTotal = this.getFinalTotal();
        const discount = this.discountValue || 0;
        // Subtotal é o total FINAL + Desconto (ou seja, o valor original antes do desconto)
        // Se a gente quiser mostrar o total bruto do carrinho:
        const subtotal = finalTotal + discount;

        // document.getElementById('display-subtotal').innerText = this.formatCurrency(subtotal);
        document.getElementById('display-discount').innerText = '- ' + this.formatCurrency(discount);
        document.getElementById('display-paid').innerText = this.formatCurrency(this.totalPaid);
        document.getElementById('checkout-total-display').innerText = this.formatCurrency(finalTotal);

        const remaining = finalTotal - this.totalPaid;
        const btnFinish = document.getElementById('btn-finish-sale');

        // Debug
        console.log('[Checkout] remaining:', remaining, 'btnFinish:', btnFinish);

        document.getElementById('display-remaining').innerText = this.formatCurrency(Math.max(0, remaining));

        const changeBox = document.getElementById('change-box');

        // Verifica se elemento existe
        if (!btnFinish) {
            console.error('[Checkout] btn-finish-sale não encontrado!');
            return;
        }

        // Lógica Simplificada: Se falta <= 1 centavo, libera
        if (remaining <= 0.01) {
            // Pode ter troco ou ser exato
            if (remaining < -0.01) {
                // Tem troco
                changeBox.style.display = 'block';
                document.getElementById('checkout-change').innerText = this.formatCurrency(Math.abs(remaining));
            } else {
                changeBox.style.display = 'none';
            }
            btnFinish.disabled = false;
            btnFinish.style.background = '#22c55e';
            btnFinish.style.cursor = 'pointer';
        } else {
            // Falta pagar
            changeBox.style.display = 'none';
            btnFinish.disabled = true;
            btnFinish.style.background = '#cbd5e1';
            btnFinish.style.cursor = 'not-allowed';
        }

        // Validação Extra: Retirada sem Cliente
        // Só bloqueia se input existir, for 'true' E não tiver cliente selecionado
        const keepOpenInput = document.getElementById('keep_open_value');
        const clientId = document.getElementById('current_client_id')?.value;
        const tableId = document.getElementById('current_table_id')?.value;

        if (keepOpenInput && keepOpenInput.value === 'true' && !clientId && !tableId) {
            btnFinish.disabled = true;
            btnFinish.style.background = '#cbd5e1'; // Cinza padrão disabled
            btnFinish.style.cursor = 'not-allowed';
        }
    },

    getFinalTotal: function () {
        // [FIX] Sempre usa cachedTotal que já inclui table-initial-total + cart
        let total = this.cachedTotal || 0;

        // Se editando pago, desconta o original
        if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder && typeof originalPaidTotal !== 'undefined') {
            const diff = total - originalPaidTotal;
            total = diff > 0 ? diff : 0;
        }

        // [NOVO] Aplica desconto
        total = total - this.discountValue;

        // [NOVO] Adiciona taxa de entrega se for Entrega com dados preenchidos
        const orderTypeCards = document.querySelectorAll('.order-type-card.active');
        let isDelivery = false;
        orderTypeCards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('entrega')) isDelivery = true;
        });

        if (isDelivery && typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled) {
            if (typeof PDV_DELIVERY_FEE !== 'undefined') {
                total += PDV_DELIVERY_FEE;
            }
        }

        return total > 0 ? total : 0;
    },

    // ==========================================
    // API SUBMIT
    // ==========================================
    submitSale: function () {
        const tableId = document.getElementById('current_table_id').value;
        const clientId = document.getElementById('current_client_id').value;

        let endpoint = '/admin/loja/venda/finalizar';
        const keepOpenStr = document.getElementById('keep_open_value') ? document.getElementById('keep_open_value').value : 'false';
        const keepOpen = keepOpenStr === 'true';

        // Usa o carrinho global (pdv-core.js) ou PDVCart
        let cartItems = [];
        if (typeof cart !== 'undefined' && Array.isArray(cart)) {
            cartItems = cart;
        } else if (typeof PDVCart !== 'undefined') {
            cartItems = PDVCart.items;
        }

        // [FIX] Detecta qual tipo de pedido está selecionado
        const orderTypeCards = document.querySelectorAll('.order-type-card.active');
        let selectedOrderType = 'local'; // default

        orderTypeCards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('retirada')) selectedOrderType = 'pickup';
            else if (label.includes('entrega')) selectedOrderType = 'delivery';
            else if (label.includes('local')) selectedOrderType = 'local';
        });

        // Para Retirada/Entrega, verificar se já pagou ou não
        // [FIX] Se tem pagamentos registrados, significa que pagou (botão só habilita com pagamento completo)
        let isPaid = (this.currentPayments && this.currentPayments.length > 0) ? 1 : 0;

        const payload = {
            cart: cartItems,
            table_id: tableId ? parseInt(tableId) : null,
            client_id: clientId ? parseInt(clientId) : null,
            payments: this.currentPayments,
            discount: this.discountValue,
            keep_open: keepOpen,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: isPaid,
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0
        };

        // Se for Entrega, adiciona dados de entrega
        if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
            const deliveryData = getDeliveryData();
            if (deliveryData) {
                payload.delivery_data = deliveryData;
            }
        }

        // Lógica de Estado (Mesa/Comanda/Inclusão)
        const { modo, fechandoConta } = PDVState.getState();

        let isPaidLoop = false;
        if (window.isPaidOrderInclusion && typeof editingPaidOrderId !== 'undefined') {
            payload.order_id = editingPaidOrderId;
            payload.save_account = true;
            isPaidLoop = true;
        } else if (modo === 'mesa' && fechandoConta) {
            endpoint = '/admin/loja/mesa/fechar';
        } else if (modo === 'comanda' && fechandoConta) {
            endpoint = '/admin/loja/venda/fechar-comanda';
            payload.order_id = this.closingOrderId;
        }

        const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + endpoint;

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.showSuccessModal();
                    PDVCart.clear();
                    // Limpa o carrinho global também
                    if (typeof cart !== 'undefined') cart.length = 0;

                    setTimeout(() => {
                        // Se veio do modal de pagamento (finalize_now), sempre reload
                        // Só redireciona para mesas se fechou conta de mesa existente
                        if (isPaidLoop) {
                            window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(err => alert('Erro de conexão: ' + err.message));
    },

    // Entrega imediata de comanda paga
    forceDelivery: function (orderId) {
        fetch('venda/fechar-comanda', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, payments: [], keep_open: false })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Entregue!');
                    window.location.href = BASE_URL + '/admin/loja/mesas';
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    },

    saveClientOrder: function () {
        // Salva Comanda Aberta (Botão Laranja)
        const clientId = document.getElementById('current_client_id').value;
        const tableId = document.getElementById('current_table_id').value;
        const orderId = document.getElementById('current_order_id') ? document.getElementById('current_order_id').value : null;

        if (PDVCart.items.length === 0) return alert('Carrinho vazio!');

        // [FIX] Aceita cliente OU mesa
        if (!clientId && !tableId) return alert('Selecione um cliente ou mesa!');

        PDVState.set({ modo: tableId ? 'mesa' : 'comanda', clienteId: clientId ? parseInt(clientId) : null, mesaId: tableId ? parseInt(tableId) : null });

        fetch('venda/finalizar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cart: PDVCart.items,
                client_id: clientId || null,
                table_id: tableId || null,
                order_id: orderId,
                save_account: true
            })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.showSuccessModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else { alert('Erro: ' + data.message); }
            });
    },

    // UI Helpers
    showSuccessModal: function () {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => modal.style.display = 'none', 1500);
        }
    },

    selectOrderType: function (type, element) {
        // Reset todos os cards
        document.querySelectorAll('.order-type-card').forEach(el => {
            if (!el.classList.contains('disabled')) {
                el.classList.remove('active');
                el.style.border = '1px solid #cbd5e1';
                el.style.background = 'white';
            }
        });

        // [FIX] Se element não foi passado, busca pelo tipo
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

            // [FIX] Tenta pegar o nome de várias fontes (cliente OU mesa)
            let displayName = document.getElementById('current_client_name')?.value;
            if (!displayName) {
                displayName = document.getElementById('current_table_name')?.value;
            }
            if (!displayName) {
                // Tenta pegar do display lateral
                displayName = document.getElementById('selected-client-name')?.innerText;
            }

            if (alertBoxRetirada) alertBoxRetirada.style.display = 'block';

            // [FIX] Aceita cliente OU mesa para liberar Retirada
            if ((clientId || tableId) && displayName) {
                // Tem cliente ou mesa - mostra verde
                if (clientSelectedBox) {
                    clientSelectedBox.style.display = 'block';
                    document.getElementById('retirada-client-name').innerText = displayName;
                }
                if (noClientBox) noClientBox.style.display = 'none';
            } else {
                // Não tem cliente nem mesa - mostra aviso
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

        // [NOVO] Mostra/esconde botão "Pagar Depois" para Retirada/Entrega
        const btnSavePickup = document.getElementById('btn-save-pickup');
        if (btnSavePickup) {
            if (type === 'retirada' || type === 'entrega') {
                btnSavePickup.style.display = 'flex';

                // Verifica se pode habilitar
                const clientId = document.getElementById('current_client_id')?.value;
                const tableId = document.getElementById('current_table_id')?.value;

                // Para Retirada: precisa de cliente ou mesa
                // Para Entrega: precisa de cliente OU dados de entrega preenchidos
                let canEnable = false;

                if (type === 'retirada') {
                    canEnable = !!(clientId || tableId);
                } else if (type === 'entrega') {
                    // Para Entrega, habilita se tiver dados de entrega preenchidos
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
        this.updateCheckoutUI();
    },

    formatCurrency: function (val) {
        return parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    },

    formatMethodLabel: function (method) {
        const map = { 'dinheiro': 'Dinheiro', 'pix': 'Pix', 'credito': 'Cartão Crédito', 'debito': 'Cartão Débito' };
        return map[method] || method;
    }
};

window.PDVCheckout = PDVCheckout;

// Compatibilidade
window.finalizeSale = () => PDVCheckout.finalizeSale();
window.fecharContaMesa = (id) => PDVCheckout.fecharContaMesa(id);
window.fecharComanda = (mid) => PDVCheckout.fecharComanda(mid);
window.includePaidOrderItems = () => PDVCheckout.finalizeSale(); // Redireciona
window.saveClientOrder = () => PDVCheckout.saveClientOrder();
window.submitSale = () => PDVCheckout.submitSale();
window.setMethod = (m) => PDVCheckout.setMethod(m);
window.addPayment = () => PDVCheckout.addPayment();
window.removePayment = (i) => PDVCheckout.removePayment(i);
window.closeCheckout = () => PDVCheckout.closeCheckout();
window.selectOrderType = (t, e) => PDVCheckout.selectOrderType(t, e);

// Funções auxiliares para Retirada
window.openClientSelector = function () {
    // Foca na busca de cliente na barra lateral
    const selectedArea = document.getElementById('selected-client-area');
    const searchArea = document.getElementById('client-search-area');
    const searchInput = document.getElementById('client-search');

    // Limpa cliente atual se houver
    document.getElementById('current_client_id').value = '';
    if (document.getElementById('current_client_name')) {
        document.getElementById('current_client_name').value = '';
    }

    // Mostra a área de busca
    if (selectedArea) selectedArea.style.display = 'none';
    if (searchArea) searchArea.style.display = 'flex';
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
        // Dispara o evento para mostrar as mesas/opções
        searchInput.dispatchEvent(new Event('focus'));
    }

    // Alerta visual
    alert('Selecione um cliente na barra lateral à direita');
};

window.clearRetiradaClient = function () {
    // Limpa o cliente selecionado
    document.getElementById('current_client_id').value = '';
    if (document.getElementById('current_client_name')) {
        document.getElementById('current_client_name').value = '';
    }

    // Limpa a barra lateral também
    const selectedArea = document.getElementById('selected-client-area');
    const searchArea = document.getElementById('client-search-area');
    const searchInput = document.getElementById('client-search');

    if (selectedArea) selectedArea.style.display = 'none';
    if (searchArea) searchArea.style.display = 'flex';
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }

    // Mostra o aviso de "Vincule um cliente"
    const clientSelectedBox = document.getElementById('retirada-client-selected');
    const noClientBox = document.getElementById('retirada-no-client');

    if (clientSelectedBox) clientSelectedBox.style.display = 'none';
    if (noClientBox) noClientBox.style.display = 'block';

    PDVCheckout.updateCheckoutUI();
};

// ==========================================
// FUNÇÕES PARA PAINEL DE ENTREGA
// ==========================================

let deliveryDataFilled = false; // Flag para saber se dados foram preenchidos

window.openDeliveryPanel = function () {
    const panel = document.getElementById('delivery-panel');
    if (!panel) return;

    // Só auto-preenche nome se realmente tiver cliente/mesa selecionado
    const clientId = document.getElementById('current_client_id')?.value;
    const tableId = document.getElementById('current_table_id')?.value;

    if (clientId || tableId) {
        // Tem cliente ou mesa - tenta pegar o nome
        const clientName = document.getElementById('current_client_name')?.value ||
            document.getElementById('current_table_name')?.value || '';

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
};

window.closeDeliveryPanel = function () {
    const panel = document.getElementById('delivery-panel');
    if (panel) panel.style.display = 'none';
};

window.confirmDeliveryData = function () {
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
    deliveryDataFilled = true;

    // Fecha o painel
    closeDeliveryPanel();

    // Atualiza o alerta para mostrar que dados estão OK
    const alertEntrega = document.getElementById('entrega-alert');
    const dadosOk = document.getElementById('entrega-dados-ok');
    const dadosPendente = document.getElementById('entrega-dados-pendente');

    if (alertEntrega) alertEntrega.style.display = 'block';
    if (dadosOk) dadosOk.style.display = 'block';
    if (dadosPendente) dadosPendente.style.display = 'none';

    // Atualiza ícones
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // [NOVO] Re-executa selectOrderType para atualizar botões
    PDVCheckout.selectOrderType('entrega');

    // [NOVO] Atualiza o TOTAL exibido com a taxa de entrega
    // [CORRECÃO] Usa PDVCheckout.getFinalTotal() que já calcula corretamente (cache + taxa)
    let newTotal = 0;
    if (typeof PDVCheckout !== 'undefined') {
        newTotal = PDVCheckout.getFinalTotal();
    } else {
        // Fallback apenas se PDVCheckout falhar
        const deliveryFee = (typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0;
        newTotal = deliveryFee;
    }

    const totalDisplay = document.getElementById('checkout-total-display');
    if (totalDisplay) {
        totalDisplay.innerText = 'R$ ' + newTotal.toFixed(2).replace('.', ',');
    }

    // [NOVO - MOVIDO PRA CIMA] Atualiza o Input "Valor a Lançar"
    // Verifica visualmente se o valor PAGO é R$ 0,00 para garantir atualização
    const payInput = document.getElementById('pay-amount'); // [CORRIGIDO ID]
    const paidDisplay = document.getElementById('display-paid');

    if (payInput) {
        let paidValue = 0;
        if (paidDisplay) {
            // Limpa tudo que não é digito ou virgula
            const raw = paidDisplay.innerText.replace(/[^\d,]/g, '').replace(',', '.');
            paidValue = parseFloat(raw) || 0;
        }

        console.log('[ConfirmDelivery] NewTotal:', newTotal, 'Paid:', paidValue);

        if (paidValue < 0.01) { // Considera zero se for menor que 1 centavo
            payInput.value = newTotal.toFixed(2).replace('.', ',');
            // Dispara evento para garantir formatação da máscara (R$)
            payInput.dispatchEvent(new Event('input'));
        }
    }

    // Atualiza UI do checkout (restante, troco, botão)
    if (typeof updateCheckoutUI === 'function') {
        updateCheckoutUI();
    } else if (typeof PDVCheckout !== 'undefined' && typeof PDVCheckout.updateCheckoutUI === 'function') {
        PDVCheckout.updateCheckoutUI();
    }
};

// Função para obter dados de entrega preenchidos
window.getDeliveryData = function () {
    if (!deliveryDataFilled) return null;

    return {
        name: document.getElementById('delivery_name')?.value || '',
        address: document.getElementById('delivery_address')?.value || '',
        number: document.getElementById('delivery_number')?.value || '',
        neighborhood: document.getElementById('delivery_neighborhood')?.value || '',
        phone: document.getElementById('delivery_phone')?.value || '',
        complement: document.getElementById('delivery_complement')?.value || ''
    };
};

// Reseta dados de entrega quando o checkout fecha
const originalCloseCheckout = PDVCheckout.closeCheckout;
PDVCheckout.closeCheckout = function () {
    deliveryDataFilled = false;
    closeDeliveryPanel();

    // Limpa campos
    ['delivery_name', 'delivery_address', 'delivery_number', 'delivery_neighborhood', 'delivery_phone', 'delivery_complement'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    // Chama original
    originalCloseCheckout.call(PDVCheckout);
};

// Função para excluir dados de entrega
window.clearDeliveryData = function () {
    deliveryDataFilled = false;

    // Limpa campos
    ['delivery_name', 'delivery_address', 'delivery_number', 'delivery_neighborhood', 'delivery_phone', 'delivery_complement'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    // Atualiza alertas
    const dadosOk = document.getElementById('entrega-dados-ok');
    const dadosPendente = document.getElementById('entrega-dados-pendente');

    if (dadosOk) dadosOk.style.display = 'none';
    if (dadosPendente) dadosPendente.style.display = 'block';

    if (typeof lucide !== 'undefined') lucide.createIcons();
    PDVCheckout.updateCheckoutUI();
};

// ==========================================
// SALVAR PEDIDO DE RETIRADA/ENTREGA (PAGAR DEPOIS)
// ==========================================

window.savePickupOrder = function () {
    // Detecta qual tipo de pedido está selecionado
    const orderTypeCards = document.querySelectorAll('.order-type-card.active');
    let selectedOrderType = 'pickup'; // default para Retirada

    orderTypeCards.forEach(card => {
        const label = card.innerText.toLowerCase().trim();
        if (label.includes('retirada')) selectedOrderType = 'pickup';
        else if (label.includes('entrega')) selectedOrderType = 'delivery';
    });

    // Para Entrega, verifica se dados foram preenchidos
    if (selectedOrderType === 'delivery') {
        if (typeof deliveryDataFilled === 'undefined' || !deliveryDataFilled) {
            alert('Preencha os dados de entrega primeiro!');
            openDeliveryPanel();
            return;
        }
    }

    // Pega o carrinho
    let cartItems = [];
    if (typeof cart !== 'undefined' && Array.isArray(cart)) {
        cartItems = cart;
    } else if (typeof PDVCart !== 'undefined') {
        cartItems = PDVCart.items;
    }

    if (cartItems.length === 0) {
        alert('Carrinho vazio!');
        return;
    }

    // Pega a forma de pagamento selecionada (para mostrar no Kanban como "vai pagar com...")
    const selectedPaymentMethod = PDVCheckout.selectedMethod || 'dinheiro';

    // [NOVO] Taxa de entrega (apenas para delivery)
    const deliveryFee = (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0;

    const payload = {
        cart: cartItems,
        table_id: null,
        client_id: document.getElementById('current_client_id')?.value || null,
        payments: [], // Sem pagamentos - vai pagar depois
        discount: PDVCheckout.discountValue || 0,
        delivery_fee: deliveryFee, // [NOVO]
        keep_open: false,
        finalize_now: true,
        order_type: selectedOrderType,
        is_paid: 0, // NÃO PAGO - vai pagar na retirada
        payment_method_expected: selectedPaymentMethod // Forma que vai pagar
    };

    // Se for Entrega, adiciona dados de entrega
    if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
        const deliveryData = getDeliveryData();
        if (deliveryData) {
            payload.delivery_data = deliveryData;
        }
    }

    const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/venda/finalizar';

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                PDVCheckout.showSuccessModal();
                PDVCart.clear();
                if (typeof cart !== 'undefined') cart.length = 0;

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(err => alert('Erro de conexão: ' + err.message));
};
