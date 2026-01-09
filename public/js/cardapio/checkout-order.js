/**
 * CHECKOUT-ORDER.JS - Módulo de Envio de Pedidos
 * Dependências: CardapioCart, CardapioCheckout (para estado), Utils
 * 
 * Este módulo contém toda a lógica de:
 * - Validação de formulário
 * - Preparação de dados para API
 * - Envio para API
 * - Formatação de mensagem WhatsApp
 */

const CheckoutOrder = {
    /**
     * Envia o pedido para a API e abre WhatsApp
     * @param {Object} checkout - Referência ao CardapioCheckout
     */
    send: async function (checkout) {
        // Captura dados do formulário
        const formData = this.captureFormData();

        // Validações
        const validationError = this.validate(formData, checkout);
        if (validationError) {
            alert(validationError);
            return;
        }

        // Prepara dados para API
        const orderData = this.prepareOrderData(formData, checkout);

        try {
            // 1. Envia para a API
            const result = await this.submitToApi(orderData);

            if (!result.success) {
                alert('Erro ao enviar pedido: ' + (result.message || 'Erro desconhecido'));
                return;
            }

            // 2. Envia para WhatsApp se configurado
            this.sendToWhatsApp(formData, checkout);

            // 3. Reset
            checkout.reset();

        } catch (error) {
            console.error('Erro ao enviar pedido:', error);
            alert('Erro de conexão ao enviar pedido. Por favor, tente novamente.');
        }
    },

    /**
     * Captura todos os dados do formulário
     */
    captureFormData: function () {
        return {
            name: document.getElementById('customerName')?.value.trim() || '',
            phone: document.getElementById('customerPhone')?.value.trim() || '',
            address: document.getElementById('customerAddress')?.value.trim() || '',
            number: document.getElementById('customerNumber')?.value.trim() || '',
            neighborhood: document.getElementById('customerNeighborhood')?.value.trim() || '',
            obs: document.getElementById('customerObs')?.value.trim() || '',
            changeAmount: document.getElementById('changeAmount')?.value.trim() || ''
        };
    },

    /**
     * Valida os dados do formulário
     * @returns {string|null} Mensagem de erro ou null se válido
     */
    validate: function (formData, checkout) {
        if (!formData.name) {
            return 'Por favor, preencha seu nome.';
        }

        if (checkout.selectedOrderType === 'entrega') {
            if (!formData.address) {
                return 'Por favor, preencha o endereço.';
            }
            if (!formData.number && !checkout.hasNoNumber) {
                return 'Por favor, preencha o número ou selecione "Sem nº".';
            }
        }

        if (!checkout.selectedPaymentMethod) {
            return 'Por favor, selecione a forma de pagamento.';
        }

        return null; // Válido
    },

    /**
     * Prepara os dados no formato esperado pela API
     */
    prepareOrderData: function (formData, checkout) {
        const config = window.cardapioConfig || {};
        const restaurantId = window.restaurantId || config.restaurant_id;
        const deliveryFee = checkout.getDeliveryFee();

        return {
            restaurant_id: restaurantId,
            customer_name: formData.name,
            customer_phone: formData.phone,
            customer_address: formData.address,
            customer_number: formData.number,
            customer_neighborhood: formData.neighborhood,
            order_type: checkout.selectedOrderType,
            payment_method: checkout.selectedPaymentMethod,
            change_amount: formData.changeAmount,
            observation: formData.obs,
            delivery_fee: deliveryFee,
            items: CardapioCart.items.map(item => ({
                product_id: item.productId || null,
                name: item.name,
                quantity: item.quantity,
                unit_price: item.unitPrice,
                observation: item.observation || '',
                additionals: (item.additionals || []).map(add => ({
                    id: add.id,
                    name: add.name,
                    price: add.price || 0
                }))
            }))
        };
    },

    /**
     * Envia dados para a API
     */
    submitToApi: async function (orderData) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await fetch(window.BASE_URL + '/api/order/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(orderData)
        });

        return await response.json();
    },

    /**
     * Monta e envia mensagem para WhatsApp
     */
    sendToWhatsApp: function (formData, checkout) {
        const config = window.cardapioConfig || {};
        const whatsappNumber = (config.whatsapp_number || '').replace(/\D/g, '');

        // Parse mensagens customizadas
        let msgBefore = 'Olá! Gostaria de fazer um pedido:';
        let msgAfter = 'Aguardo a confirmação.';

        try {
            if (config.whatsapp_message) {
                const parsed = JSON.parse(config.whatsapp_message);
                if (parsed && (typeof parsed === 'object') && (parsed.before || parsed.after)) {
                    if (Array.isArray(parsed.before) && parsed.before.length > 0) {
                        msgBefore = parsed.before.join('\n');
                    }
                    if (Array.isArray(parsed.after) && parsed.after.length > 0) {
                        msgAfter = parsed.after.join('\n');
                    }
                } else if (Array.isArray(parsed)) {
                    if (parsed.length > 0 && parsed[0]) msgBefore = parsed[0];
                    if (parsed.length > 1 && parsed[1]) msgAfter = parsed[1];
                }
            }
        } catch (e) {
            console.warn('Erro ao decodificar mensagens do WhatsApp', e);
        }

        // Monta resumo do pedido
        const orderSummary = this.formatOrderSummary(formData, checkout);

        // Monta mensagem final
        const finalMessage = `${msgBefore}\n\n${orderSummary}\n\n${msgAfter}`;

        // Envia
        if (whatsappNumber) {
            const url = `https://wa.me/55${whatsappNumber}?text=${encodeURIComponent(finalMessage)}`;
            window.open(url, '_blank');
            alert('Pedido enviado com sucesso! ✅\n\nSeu pedido foi registrado e aparecerá para o restaurante.');
        } else {
            alert('Pedido registrado com sucesso! ✅\n\nO restaurante foi notificado.\n\n' +
                '(WhatsApp não configurado para este restaurante)');
        }
    },

    /**
     * Formata o resumo do pedido para WhatsApp
     */
    formatOrderSummary: function (formData, checkout) {
        const deliveryFee = checkout.getDeliveryFee();

        let summary = '*NOVO PEDIDO*\n\n' +
            '👤 *Nome:* ' + formData.name + '\n';

        if (checkout.selectedOrderType === 'entrega') {
            summary += '📍 *Entrega:* ' + formData.address + ', ' + formData.number + '\n' +
                '🏘️ *Bairro:* ' + formData.neighborhood + '\n';
        } else {
            const label = (checkout.selectedOrderType === 'local') ? 'Mesa/Comanda' : 'Retirada';
            summary += '🏪 *' + label + ':* ' + formData.number + '\n';
        }

        summary += '\n🛒 *ITENS:*\n';
        CardapioCart.items.forEach(item => {
            summary += `• ${item.quantity}x ${item.name} ` +
                (item.additionals.length ? `(${item.additionals.map(a => a.name).join(', ')})` : '') + '\n';
            if (item.observation) summary += `  _Obs: ${item.observation}_\n`;
        });

        if (checkout.selectedOrderType === 'entrega' && deliveryFee > 0) {
            summary += '🛵 *Taxa de Entrega:* ' + Utils.formatCurrency(deliveryFee) + '\n';
        }

        summary += '\n💰 *Total Final:* ' + Utils.formatCurrency(checkout.getFinalTotal()) + '\n';
        summary += '💳 *Pagamento:* ' + checkout.selectedPaymentMethod.toUpperCase();

        if (checkout.selectedPaymentMethod === 'dinheiro' && formData.changeAmount) {
            summary += ' (Troco para: ' + formData.changeAmount + ')';
        }

        if (formData.obs) {
            summary += '\n📝 *Obs Geral:* ' + formData.obs;
        }

        return summary;
    }
};

// Expor globalmente
window.CheckoutOrder = CheckoutOrder;
