/**
 * CheckoutService.js
 * Responsável APENAS pela comunicação com a API (Fetch calls)
 */
const CheckoutService = {

    _getCsrf: function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    },

    _headers: function () {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this._getCsrf()
        };
    },

    /**
     * Envia requisição de finalizar venda
     * @param {string} endpoint 
     * @param {object} payload 
     */
    sendSaleRequest: async function (endpoint, payload) {
        const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + endpoint;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify(payload)
            });
            return await response.json();
        } catch (error) {
            throw new Error('Falha na comunicação: ' + error.message);
        }
    },

    /**
     * Força entrega (Fechar comanda paga)
     */
    closePaidTab: async function (orderId) {
        return this.sendSaleRequest('venda/fechar-comanda', {
            order_id: orderId,
            payments: [],
            keep_open: false
        });
    },

    /**
     * Envia pedido de cliente/mesa (Salvar Comanda)
     */
    saveTabOrder: async function (payload) {
        return this.sendSaleRequest('/admin/loja/venda/finalizar', payload);
    }
};

window.CheckoutService = CheckoutService;
