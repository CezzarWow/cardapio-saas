/**
 * ============================================
 * DELIVERY JS — Constants
 * Constantes compartilhadas entre módulos
 * ============================================
 */

window.DeliveryConstants = window.DeliveryConstants || {

    /**
     * Labels de status para exibição
     */
    statusLabels: {
        'novo': 'Novo',
        'preparo': 'Em Preparo',
        'rota': 'Em Rota',
        'entregue': 'Entregue',
        'cancelado': 'Cancelado'
    },

    /**
     * Labels de métodos de pagamento
     */
    methodLabels: {
        'dinheiro': '💵 Dinheiro',
        'pix': '📱 Pix',
        'credito': '💳 Crédito',
        'debito': '💳 Débito',
        'multiplo': '💰 Múltiplo'
    },

    /**
     * Transições de status (delivery)
     */
    nextStatusDelivery: {
        'novo': 'preparo',
        'preparo': 'rota',
        'rota': 'entregue'
    },

    /**
     * Transições de status (pickup)
     */
    nextStatusPickup: {
        'novo': 'preparo',
        'preparo': 'rota',
        'rota': 'entregue'
    },

    /**
     * Retorna label do status
     */
    getStatusLabel: function (status) {
        return this.statusLabels[status] || status;
    },

    /**
     * Retorna label do método de pagamento
     */
    getMethodLabel: function (method) {
        return this.methodLabels[method] || method || 'A pagar';
    }
};

// Expõe globalmente
window.DeliveryConstants = DeliveryConstants;
