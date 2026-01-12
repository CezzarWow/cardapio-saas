/**
 * Submit EXCLUSIVO do fluxo Delivery
 * 
 * Endpoints FIXOS para operações de delivery.
 * Não compartilha com outros fluxos.
 */
const DeliverySubmit = {

    ENDPOINTS: {
        CREATE: '/api/v1/delivery/criar',
        STATUS: '/api/v1/delivery/status'
    },

    /**
     * Cria novo pedido de delivery
     */
    async create() {
        if (!DeliveryState.hasClient()) {
            alert('Informe o nome do cliente');
            return false;
        }

        if (!DeliveryState.hasAddress()) {
            alert('Endereço é obrigatório para delivery');
            return false;
        }

        if (DeliveryState.cart.length === 0) {
            alert('Carrinho vazio. Adicione produtos.');
            return false;
        }

        const payload = {
            flow_type: 'delivery',
            client_id: DeliveryState.clientId,
            client_name: DeliveryState.clientName,
            phone: DeliveryState.phone,
            address: DeliveryState.address,
            address_number: DeliveryState.addressNumber,
            complement: DeliveryState.complement,
            neighborhood: DeliveryState.neighborhood,
            reference: DeliveryState.reference,
            cart: DeliveryState.cart,
            delivery_fee: DeliveryState.deliveryFee,
            discount: DeliveryState.discount,
            observation: DeliveryState.observation,
            change_for: DeliveryState.changeFor,
            payments: DeliveryState.payments.length > 0 ? DeliveryState.payments : undefined
        };

        return await this._send(this.ENDPOINTS.CREATE, payload, 'Pedido de delivery criado!');
    },

    /**
     * Atualiza status do delivery
     */
    async updateStatus(orderId, newStatus) {
        if (!orderId || !newStatus) {
            alert('ID do pedido e novo status são obrigatórios');
            return false;
        }

        const payload = {
            flow_type: 'delivery_status',
            order_id: orderId,
            new_status: newStatus
        };

        return await this._send(this.ENDPOINTS.STATUS, payload, 'Status atualizado!');
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
                if (endpoint === this.ENDPOINTS.CREATE) {
                    DeliveryState.reset();
                }

                if (typeof CheckoutUI !== 'undefined' && CheckoutUI.showSuccessModal) {
                    CheckoutUI.showSuccessModal();
                } else {
                    alert(successMessage);
                }

                return data;

            } else {
                const errorMsg = data.message || 'Erro ao processar';
                const errorDetails = data.errors
                    ? '\n' + Object.values(data.errors).join('\n')
                    : '';
                alert(errorMsg + errorDetails);
                return false;
            }

        } catch (err) {
            console.error('[DELIVERY_SUBMIT] Erro:', err);
            alert('Erro de conexão. Verifique sua internet.');
            return false;
        }
    }
};

window.DeliverySubmit = DeliverySubmit;
