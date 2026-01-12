/**
 * Submit EXCLUSIVO do fluxo Mesa
 * 
 * Endpoints FIXOS para cada operação.
 * Não compartilha com outros fluxos.
 */
const MesaSubmit = {

    // Endpoints fixos
    ENDPOINTS: {
        OPEN: '/api/v1/mesa/abrir',
        ADD_ITEMS: '/api/v1/mesa/itens',
        CLOSE: '/api/v1/mesa/fechar'
    },

    /**
     * Abre nova conta de mesa
     */
    async open() {
        // Validações locais
        if (!MesaState.tableId) {
            alert('Selecione uma mesa');
            return false;
        }

        if (MesaState.cart.length === 0) {
            alert('Carrinho vazio. Adicione produtos para abrir a mesa.');
            return false;
        }

        // Payload EXATO
        const payload = {
            flow_type: 'mesa',
            table_id: MesaState.tableId,
            cart: MesaState.cart
        };

        return await this._send(this.ENDPOINTS.OPEN, payload, 'Mesa aberta com sucesso!');
    },

    /**
     * Adiciona itens a mesa já aberta
     */
    async addItems() {
        // Validações
        if (!MesaState.orderId) {
            alert('Mesa não tem pedido aberto');
            return false;
        }

        if (MesaState.cart.length === 0) {
            alert('Carrinho vazio. Adicione produtos.');
            return false;
        }

        // Payload EXATO
        const payload = {
            flow_type: 'mesa_add',
            order_id: MesaState.orderId,
            cart: MesaState.cart
        };

        return await this._send(this.ENDPOINTS.ADD_ITEMS, payload, 'Itens adicionados!');
    },

    /**
     * Fecha conta de mesa com pagamento
     */
    async close() {
        // Validações
        if (!MesaState.tableId) {
            alert('Mesa não identificada');
            return false;
        }

        if (MesaState.payments.length === 0) {
            alert('Informe o pagamento para fechar a mesa.');
            return false;
        }

        if (!MesaState.isPaymentSufficient()) {
            const total = MesaState.getGrandTotal().toFixed(2);
            const paid = MesaState.getPaidAmount().toFixed(2);
            alert(`Pagamento insuficiente. Total: R$ ${total}, Pago: R$ ${paid}`);
            return false;
        }

        // Payload EXATO
        const payload = {
            flow_type: 'mesa_fechar',
            table_id: MesaState.tableId,
            payments: MesaState.payments
        };

        return await this._send(this.ENDPOINTS.CLOSE, payload, 'Mesa fechada com sucesso!');
    },

    /**
     * Helper interno de envio
     */
    async _send(endpoint, payload, successMessage) {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                MesaState.reset();

                if (typeof CheckoutUI !== 'undefined' && CheckoutUI.showSuccessModal) {
                    CheckoutUI.showSuccessModal();
                } else {
                    alert(successMessage);
                }

                // Redireciona para mesas após 1.5s
                setTimeout(() => {
                    window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                }, 1500);

                return true;

            } else {
                const errorMsg = data.message || 'Erro ao processar';
                const errorDetails = data.errors
                    ? '\n' + Object.values(data.errors).join('\n')
                    : '';
                alert(errorMsg + errorDetails);
                return false;
            }

        } catch (err) {
            console.error('[MESA_SUBMIT] Erro:', err);
            alert('Erro de conexão. Verifique sua internet.');
            return false;
        }
    }
};

// Export
window.MesaSubmit = MesaSubmit;
