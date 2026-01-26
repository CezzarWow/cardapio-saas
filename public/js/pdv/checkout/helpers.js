/**
 * PDV CHECKOUT - Helpers
 * Funções utilitárias de formatação
 * 
 * Dependências: Nenhuma
 */

window.CheckoutHelpers = {

    /**
     * Formata input de valor monetário (máscara BRL)
     * @param {HTMLInputElement} input 
     */
    formatMoneyInput: function (input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') { input.value = ''; return; }
        value = (parseInt(value) / 100).toFixed(2).replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        input.value = value;
    },

    /**
     * Formata número para moeda BRL
     * @param {number} val 
     * @returns {string}
     */
    formatCurrency: function (val) {
        return parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    },

    /**
     * Traduz método de pagamento para label exibível
     * @param {string} method 
     * @returns {string}
     */
    formatMethodLabel: function (method) {
        const map = { 'dinheiro': 'Dinheiro', 'pix': 'Pix', 'credito': 'Cartão Crédito', 'debito': 'Cartão Débito' };
        return map[method] || method;
    },

    /**
     * Obtém IDs de contexto do DOM (mesa, cliente, pedido)
     * Centraliza a lógica repetitiva de obter e validar IDs
     * @returns {Object} { tableId, clientId, orderId, hasTable, hasClient }
     */
    getContextIds: function () {
        const tableIdRaw = document.getElementById('current_table_id')?.value;
        const clientIdRaw = document.getElementById('current_client_id')?.value;
        const orderIdRaw = document.getElementById('current_order_id')?.value;

        const hasTable = !!(tableIdRaw && tableIdRaw !== '' && tableIdRaw !== '0');
        const hasClient = !!(clientIdRaw && clientIdRaw !== '' && clientIdRaw !== '0');

        // Tenta obter ID do pedido de múltiplas fontes para evitar duplicação
        let orderId = orderIdRaw ? parseInt(orderIdRaw) : null;
        if (!orderId && typeof PDVState !== 'undefined' && PDVState.state && PDVState.state.pedidoId) {
            orderId = parseInt(PDVState.state.pedidoId);
        }
        if (!orderId) {
            // Fallback para URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('order_id')) orderId = parseInt(urlParams.get('order_id'));
        }

        return {
            tableId: hasTable ? parseInt(tableIdRaw) : null,
            clientId: hasClient ? parseInt(clientIdRaw) : null,
            orderId: orderId,
            hasTable: hasTable,
            hasClient: hasClient
        };
    },

    /**
     * Verifica se é um ID válido (não vazio, não zero)
     * @param {string|number} id 
     * @returns {boolean}
     */
    isValidId: function (id) {
        return !!(id && id !== '' && id !== '0' && id !== 0);
    }

};

// Expõe globalmente para uso pelos outros módulos
// window.CheckoutHelpers = CheckoutHelpers; // Já definido acima
