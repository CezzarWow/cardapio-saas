/**
 * Submit EXCLUSIVO do fluxo Balcão
 * 
 * Endpoint FIXO, payload FIXO, sem decisões.
 * Não compartilha com outros fluxos.
 */
const BalcaoSubmit = {

    // Endpoint fixo (não muda)
    ENDPOINT: '/api/v1/balcao/venda',

    /**
     * Envia venda balcão
     * @returns {Promise<boolean>} Sucesso ou falha
     */
    async submit() {
        // 1. Validar localmente
        if (BalcaoState.cart.length === 0) {
            alert('Carrinho vazio. Adicione produtos para continuar.');
            return false;
        }

        if (BalcaoState.payments.length === 0) {
            alert('Informe o pagamento para finalizar a venda.');
            return false;
        }

        if (!BalcaoState.isPaymentSufficient()) {
            const total = BalcaoState.getTotal().toFixed(2);
            const paid = BalcaoState.getPaidAmount().toFixed(2);
            alert(`Pagamento insuficiente. Total: R$ ${total}, Pago: R$ ${paid}`);
            return false;
        }

        // 2. Montar payload EXATO (contrato é lei)
        const payload = {
            flow_type: 'balcao',          // SEMPRE 'balcao' - não muda
            cart: BalcaoState.cart,
            payments: BalcaoState.payments,
            discount: BalcaoState.discount
        };

        // 3. Enviar para endpoint fixo
        try {
            const response = await fetch(this.ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                // Sucesso - limpar estado e mostrar feedback
                BalcaoState.reset();

                if (typeof CheckoutUI !== 'undefined' && CheckoutUI.showSuccessModal) {
                    CheckoutUI.showSuccessModal();
                } else {
                    alert('Venda realizada com sucesso!');
                }

                // Reload após 1.5s
                setTimeout(() => window.location.reload(), 1500);
                return true;

            } else {
                // Erro de validação ou negócio
                const errorMsg = data.message || 'Erro ao processar venda';
                const errorDetails = data.errors
                    ? '\n' + Object.values(data.errors).join('\n')
                    : '';
                alert(errorMsg + errorDetails);
                return false;
            }

        } catch (err) {
            console.error('[BALCAO_SUBMIT] Erro:', err);
            alert('Erro de conexão. Verifique sua internet e tente novamente.');
            return false;
        }
    }
};

// Export para uso global
window.BalcaoSubmit = BalcaoSubmit;
