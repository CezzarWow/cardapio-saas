/**
 * Submit EXCLUSIVO do fluxo Comanda
 * 
 * Endpoints FIXOS para cada operação.
 * Não compartilha com outros fluxos.
 */
const ComandaSubmit = {

    ENDPOINTS: {
        OPEN: '/api/v1/comanda/abrir',
        ADD_ITEMS: '/api/v1/comanda/itens',
        CLOSE: '/api/v1/comanda/fechar'
    },

    /**
     * Abre nova comanda para cliente
     */
    async open() {
        if (!ComandaState.hasClient()) {
            alert('Selecione um cliente para abrir comanda');
            return false;
        }

        if (ComandaState.cart.length === 0) {
            alert('Carrinho vazio. Adicione produtos para abrir comanda.');
            return false;
        }

        const payload = {
            flow_type: 'comanda',
            client_id: ComandaState.clientId,
            cart: ComandaState.cart
        };

        return await this._send(this.ENDPOINTS.OPEN, payload, 'Comanda aberta com sucesso!');
    },

    /**
     * Adiciona itens a comanda aberta
     */
    async addItems() {
        if (!ComandaState.orderId) {
            alert('Comanda não identificada');
            return false;
        }

        if (ComandaState.cart.length === 0) {
            alert('Carrinho vazio. Adicione produtos.');
            return false;
        }

        const payload = {
            flow_type: 'comanda_add',
            order_id: ComandaState.orderId,
            cart: ComandaState.cart
        };

        return await this._send(this.ENDPOINTS.ADD_ITEMS, payload, 'Itens adicionados!');
    },

    /**
     * Fecha comanda com pagamento
     */
    async close() {
        if (!ComandaState.orderId) {
            alert('Comanda não identificada');
            return false;
        }

        if (ComandaState.payments.length === 0) {
            alert('Informe o pagamento para fechar comanda.');
            return false;
        }

        if (!ComandaState.isPaymentSufficient()) {
            const total = ComandaState.getGrandTotal().toFixed(2);
            const paid = ComandaState.getPaidAmount().toFixed(2);
            alert(`Pagamento insuficiente. Total: R$ ${total}, Pago: R$ ${paid}`);
            return false;
        }

        const payload = {
            flow_type: 'comanda_fechar',
            order_id: ComandaState.orderId,
            payments: ComandaState.payments
        };

        return await this._send(this.ENDPOINTS.CLOSE, payload, 'Comanda fechada com sucesso!');
    },

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
                ComandaState.reset();

                if (typeof CheckoutUI !== 'undefined' && CheckoutUI.showSuccessModal) {
                    CheckoutUI.showSuccessModal();
                } else {
                    alert(successMessage);
                }

                setTimeout(() => window.location.reload(), 1500);
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
            console.error('[COMANDA_SUBMIT] Erro:', err);
            alert('Erro de conexão. Verifique sua internet.');
            return false;
        }
    }
};

window.ComandaSubmit = ComandaSubmit;
