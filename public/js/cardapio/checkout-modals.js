/**
 * CHECKOUT-MODALS.JS - Módulo de Modais do Checkout
 * Funções para abrir/fechar modais e renderizar revisão
 */

const CheckoutModals = {
    /**
     * Abre modal de revisão do pedido
     * @param {Object} checkout - Referência ao CardapioCheckout
     */
    openOrderReview: function (checkout) {
        if (CardapioCart.items.length === 0) {
            alert('Seu carrinho está vazio!');
            return;
        }

        this.renderReviewItems();
        checkout.updateTotals();

        document.getElementById('orderReviewModal').classList.add('show');
    },

    /**
     * Fecha modal de revisão
     */
    closeOrderReview: function () {
        document.getElementById('orderReviewModal').classList.remove('show');
    },

    /**
     * Renderiza itens no modal de revisão
     */
    renderReviewItems: function () {
        const container = document.getElementById('orderReviewItems');
        if (!container) return;

        container.innerHTML = '';
        const items = CardapioCart.items;

        items.forEach(item => {
            container.innerHTML += `
                <div class="order-review-item">
                    <div class="order-review-item-qty">${item.quantity}x</div>
                    <div class="order-review-item-info">
                        <div class="order-review-item-name">${item.name}</div>
                        
                        ${item.isCombo && item.products ? `
                            <div class="order-review-combo-details" style="font-size: 0.85rem; color: #666; margin-top: 2px;">
                                ${item.products.map(p => `
                                    <div>
                                        <span>• ${p.name}</span>
                                        ${p.additionals && p.additionals.length > 0 ? `
                                            <span style="font-size: 0.8rem; color: #888;">(+ ${p.additionals.map(a => a.name).join(', ')})</span>
                                        ` : ''}
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}

                        ${!item.isCombo && item.additionals && item.additionals.length > 0 ? `<div class="order-review-item-extras">+ ${item.additionals.map(a => a.name).join(', ')}</div>` : ''}
                        ${item.observation ? `<div class="order-review-item-obs">Obs: ${item.observation}</div>` : ''}
                    </div>
                    <div class="order-review-item-actions">
                        <span class="order-review-item-price">${Utils.formatCurrency(item.unitPrice * item.quantity)}</span>
                        <button class="order-review-remove-btn" onclick="CardapioCart.remove(${item.id}); CheckoutModals.renderReviewItems(); CardapioCheckout.updateTotals(); if(CardapioCart.items.length === 0) CheckoutModals.closeOrderReview();">
                            <i data-lucide="trash-2" size="16"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        Utils.initIcons();
    },

    /**
     * Abre modal de pagamento
     * @param {Object} checkout - Referência ao CardapioCheckout
     */
    goToPayment: function (checkout) {
        this.closeOrderReview();
        checkout.updateTotals();

        // Reset: Esconde o card de troco
        const changeContainer = document.getElementById('changeContainer');
        if (changeContainer) {
            changeContainer.style.display = 'none';
        }

        // Reset: Limpa seleção de método de pagamento
        checkout.selectedPaymentMethod = null;
        document.querySelectorAll('input[name="paymentMethod"]').forEach(r => r.checked = false);
        document.getElementById('paymentModal').classList.remove('has-change');

        // Aplica máscara de dinheiro
        const changeInput = document.getElementById('changeAmount');
        if (changeInput) {
            changeInput.value = '';
            changeInput.oninput = function () {
                Utils.formatMoneyInput(this);
            };
        }

        document.getElementById('paymentModal').classList.add('show');
        CheckoutFields.updateFieldsVisibility(checkout.selectedOrderType);
    },

    /**
     * Fecha modal de pagamento
     */
    closePayment: function () {
        document.getElementById('paymentModal').classList.remove('show');
    },

    /**
     * Volta para revisão do pedido
     * @param {Object} checkout - Referência ao CardapioCheckout
     */
    backToReview: function (checkout) {
        this.closePayment();
        this.openOrderReview(checkout);
    },

    /**
     * Seleciona método de pagamento
     * @param {Object} checkout - Referência ao CardapioCheckout
     * @param {string} method - Método de pagamento selecionado
     */
    selectPaymentMethod: function (checkout, method) {
        checkout.selectedPaymentMethod = method;

        const changeContainer = document.getElementById('changeContainer');
        if (method === 'dinheiro') {
            changeContainer.style.display = 'block';
            document.getElementById('paymentModal').classList.add('has-change');
            setTimeout(() => CheckoutFields.scrollToChange(), 250);
        } else {
            changeContainer.style.display = 'none';
            document.getElementById('paymentModal').classList.remove('has-change');
            checkout.hasNoChange = false;
        }
    }
};

// Expor globalmente
window.CheckoutModals = CheckoutModals;
