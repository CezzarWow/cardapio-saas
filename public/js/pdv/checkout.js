/**
 * PDV CHECKOUT - Lógica de Pagamento e Finalização
 * Dependências: PDVState, PDVCart
 */

const PDVCheckout = {
    currentPayments: [],
    totalPaid: 0,
    selectedMethod: 'dinheiro',
    closingOrderId: null, // Para fechar comanda específica

    init: function () {
        console.log('[PDVCheckout] Inicializado');
        this.bindEvents();
    },

    bindEvents: function () {
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
    },

    formatMoneyInput: function (input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') { input.value = ''; return; }
        value = (parseInt(value) / 100).toFixed(2).replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        input.value = value;
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
            this.submitSale(); // Salva direto (sem checkout modal)
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
        document.getElementById('checkout-total-display').innerText = this.formatCurrency(PDVCart.calculateTotal());
        document.getElementById('checkoutModal').style.display = 'flex';
        this.setMethod('dinheiro');
        this.updateCheckoutUI();
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
    },

    updatePaymentList: function () {
        const listEl = document.getElementById('payment-list');
        listEl.innerHTML = '';
        if (this.currentPayments.length === 0) {
            listEl.style.display = 'none';
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
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    updateCheckoutUI: function () {
        const finalTotal = this.getFinalTotal();
        const remaining = finalTotal - this.totalPaid;
        const btnFinish = document.getElementById('btn-finish-sale');

        document.getElementById('checkout-remaining').innerText = this.formatCurrency(Math.max(0, remaining));

        const changeBox = document.getElementById('change-box');
        const epsilon = 0.009;

        if (remaining < -epsilon) {
            // Troco
            changeBox.style.display = 'block';
            document.getElementById('checkout-change').innerText = this.formatCurrency(Math.abs(remaining));
            btnFinish.disabled = false;
            btnFinish.style.background = '#22c55e';
            btnFinish.style.cursor = 'pointer';
        } else if (Math.abs(remaining) <= epsilon) {
            // Exato
            changeBox.style.display = 'none';
            btnFinish.disabled = false;
            btnFinish.style.background = '#22c55e';
            btnFinish.style.cursor = 'pointer';
        } else {
            // Falta
            changeBox.style.display = 'none';
            btnFinish.disabled = true;
            btnFinish.style.background = '#cbd5e1';
            btnFinish.style.cursor = 'not-allowed';
        }

        // Validação Extra: Retirada sem Cliente
        const keepOpenValue = document.getElementById('keep_open_value')?.value;
        const clientId = document.getElementById('current_client_id')?.value;
        const tableId = document.getElementById('current_table_id')?.value;

        if (keepOpenValue === 'true' && !clientId && !tableId) {
            btnFinish.disabled = true;
            btnFinish.style.background = '#fbbf24';
            btnFinish.style.cursor = 'not-allowed';
        }
    },

    getFinalTotal: function () {
        const { modo } = PDVState.getState();
        let total = 0;

        if (modo === 'mesa' || modo === 'comanda') {
            const val = document.getElementById('table-initial-total')?.value;
            total = parseFloat(val) || 0;
        } else {
            total = PDVCart.calculateTotal(); // Carrinho normal
        }

        // Se editando pago, desconta o original
        if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder && typeof originalPaidTotal !== 'undefined') {
            const diff = total - originalPaidTotal;
            return diff > 0 ? diff : 0;
        }
        return total;
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

        const payload = {
            cart: PDVCart.items,
            table_id: tableId ? parseInt(tableId) : null,
            client_id: clientId ? parseInt(clientId) : null,
            payments: this.currentPayments,
            keep_open: keepOpen
        };

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
                    setTimeout(() => {
                        if (isPaidLoop || modo === 'mesa' || modo === 'comanda') {
                            // Se fechou mesa/comanda, vai pro mapa. Se editou, reload?
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
        const orderId = document.getElementById('current_order_id') ? document.getElementById('current_order_id').value : null;

        if (PDVCart.items.length === 0) return alert('Carrinho vazio!');
        if (!clientId) return alert('Selecione um cliente!');

        PDVState.set({ modo: 'comanda', clienteId: parseInt(clientId) });

        fetch('venda/finalizar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cart: PDVCart.items,
                client_id: clientId,
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
        // Lógica visual do card Local/Retirada
        document.querySelectorAll('.order-type-card').forEach(el => el.classList.remove('active'));
        if (element) element.classList.add('active');
        // Logica de keep open
        // Chama updateCheckoutUI para validar se Retirada precisa de cliente
        const keepOpenInput = document.getElementById('keep_open_value');
        const alertBox = document.getElementById('retirada-client-alert');

        if (type === 'retirada') {
            if (keepOpenInput) keepOpenInput.value = 'true';
            const clientId = document.getElementById('current_client_id')?.value;
            if (!clientId && alertBox) alertBox.style.display = 'block';
        } else {
            if (keepOpenInput) keepOpenInput.value = 'false';
            if (alertBox) alertBox.style.display = 'none';
        }
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
