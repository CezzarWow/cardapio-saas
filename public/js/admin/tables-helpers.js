/**
 * ============================================
 * TABLES JS — Constants & Helpers
 * Constantes e funções compartilhadas entre módulos de mesas
 * ============================================
 */

const TablesHelpers = {

    /**
     * Retorna a BASE_URL segura
     */
    getBaseUrl: function () {
        return typeof BASE_URL !== 'undefined' ? BASE_URL : '/cardapio-saas/public';
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
    }
};

// Expõe globalmente
window.TablesHelpers = TablesHelpers;
