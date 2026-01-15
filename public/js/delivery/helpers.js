/**
 * ============================================
 * DELIVERY JS — Helpers
 * Funções utilitárias compartilhadas
 * ============================================
 */

const DeliveryHelpers = {

    /**
     * Retorna a BASE_URL segura
     */
    getBaseUrl: function () {
        return typeof BASE_URL !== 'undefined' ? BASE_URL : '';
    },

    /**
     * Retorna o token CSRF
     */
    getCsrf: function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    },

    /**
     * Formata valor como moeda BRL
     */
    formatCurrency: function (val) {
        return 'R$ ' + parseFloat(val || 0).toFixed(2).replace('.', ',');
    },

    /**
     * Headers padrão para requisições JSON com CSRF
     */
    getJsonHeaders: function () {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.getCsrf()
        };
    }
};

// Expõe globalmente
window.DeliveryHelpers = DeliveryHelpers;
