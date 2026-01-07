/**
 * PDV CHECKOUT - State
 * Estado central do checkout
 * 
 * Dependências: Nenhuma
 */

const CheckoutState = {

    // Lista de pagamentos adicionados
    currentPayments: [],

    // Total já pago
    totalPaid: 0,

    // Valor de desconto aplicado
    discountValue: 0,

    // Total armazenado quando o modal abre (cache)
    cachedTotal: 0,

    // Método de pagamento selecionado
    selectedMethod: 'dinheiro',

    // ID para fechar comanda específica
    closingOrderId: null,

    /**
     * Reseta o estado para valores iniciais
     */
    reset: function () {
        this.currentPayments = [];
        this.totalPaid = 0;
        this.discountValue = 0;
        this.cachedTotal = 0;
        this.selectedMethod = 'dinheiro';
        this.closingOrderId = null;
    },

    /**
     * Reseta apenas pagamentos (para reabrir checkout)
     */
    resetPayments: function () {
        this.currentPayments = [];
        this.totalPaid = 0;
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutState = CheckoutState;
