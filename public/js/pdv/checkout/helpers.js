/**
 * PDV CHECKOUT - Helpers
 * Funções utilitárias de formatação
 * 
 * Dependências: Nenhuma
 */

const CheckoutHelpers = {

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
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutHelpers = CheckoutHelpers;
