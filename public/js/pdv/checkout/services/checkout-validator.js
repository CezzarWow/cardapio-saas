/**
 * CheckoutValidator.js
 * Responsável por validar se a venda pode prosseguir
 */
const CheckoutValidator = {

    /**
     * Valida carrinho
     */
    validateCart: function (cartItems) {
        if (!cartItems || cartItems.length === 0) {
            alert('Carrinho vazio!');
            return false;
        }
        return true;
    },

    /**
     * Valida seleção de cliente/mesa para Comanda
     */
    validateClientOrTable: function (clientId, tableId) {
        if (!clientId && !tableId) {
            alert('Selecione um cliente ou mesa!');
            return false;
        }
        return true;
    },

    /**
     * Valida dados de entrega
     */
    validateDeliveryData: function (selectedOrderType) {
        if (selectedOrderType === 'delivery') {
            const isFilled = typeof CheckoutEntrega !== 'undefined'
                ? CheckoutEntrega.isDataFilled()
                : (typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled);

            if (!isFilled) {
                alert('Preencha os dados de entrega primeiro!');
                if (typeof openDeliveryPanel === 'function') openDeliveryPanel();
                return false;
            }
        }
        return true;
    }
};

window.CheckoutValidator = CheckoutValidator;
