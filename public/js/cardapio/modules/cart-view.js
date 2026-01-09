/**
 * CartView.js
 * Gerencia apenas a INTERFACE do carrinho (View)
 * Depende de: Utils.js
 */
const CartView = {

    update: function (totals, items) {
        this._updateTotals(totals);
        this._renderList(items);
        this._updateFloatingButton(totals);
    },

    _updateTotals: function (totals) {
        const formattedValue = Utils.formatCurrency(totals.value);

        // Atualiza todos os elementos de total na tela
        const elements = [
            document.getElementById('cartTotal'),
            document.getElementById('cartModalTotal'),
            document.getElementById('suggestionsCartTotal')
        ];

        elements.forEach(el => {
            if (el) el.textContent = formattedValue;
        });
    },

    _updateFloatingButton: function (totals) {
        const floatBtn = document.getElementById('floatingCart');
        if (floatBtn) {
            floatBtn.classList.toggle('show', totals.count > 0);
            // Animação de pulso
            floatBtn.classList.add('pulse');
            setTimeout(() => floatBtn.classList.remove('pulse'), 500);
        }
    },

    _renderList: function (items) {
        const container = document.getElementById('cartItemsContainer');
        if (!container) return;

        if (items.length === 0) {
            container.innerHTML = this._renderEmpty();
        } else {
            container.innerHTML = items.map(item => this._renderItem(item)).join('');
        }

        Utils.initIcons();
    },

    _renderEmpty: function () {
        return `
            <div class="cardapio-cart-empty">
                <div class="cardapio-cart-empty-icon">
                    <i data-lucide="shopping-bag" size="48"></i>
                </div>
                <p>Seu carrinho está vazio</p>
            </div>
        `;
    },

    _renderItem: function (item) {
        const totalItem = item.unitPrice * item.quantity;

        // Detalhes de Combo
        let detailsHtml = '';
        if (item.isCombo && item.products) {
            const subItems = item.products.map(p => {
                const extras = p.additionals?.length > 0
                    ? `<span style="font-size: 0.8rem; color: #888;">(+ ${p.additionals.map(a => a.name).join(', ')})</span>`
                    : '';
                return `<div><span>• ${p.name}</span>${extras}</div>`;
            }).join('');

            detailsHtml = `
                <div class="cardapio-cart-combo-details" style="font-size: 0.85rem; color: #666; margin-top: 4px; padding-left: 8px; border-left: 2px solid #eee;">
                    ${subItems}
                </div>`;
        }

        // Extras
        const extrasHtml = !item.isCombo && item.additionals?.length > 0
            ? `<p class="cardapio-cart-item-additionals">Extras: ${item.additionals.map(a => a.name).join(', ')}</p>`
            : '';

        // Obs
        const obsHtml = item.observation
            ? `<p class="cardapio-cart-item-obs">Obs: ${item.observation}</p>`
            : '';

        return `
            <div class="cardapio-cart-item">
                <div class="cardapio-cart-item-info">
                    <p class="cardapio-cart-item-name">${item.quantity}x ${item.name}</p>
                    ${detailsHtml}
                    ${extrasHtml}
                    ${obsHtml}
                </div>
                <div class="cardapio-cart-item-actions">
                    <span class="cardapio-cart-item-price">
                        ${Utils.formatCurrency(totalItem)}
                    </span>
                    <button class="cardapio-cart-remove-icon-btn" onclick="CardapioCart.remove(${item.id})">
                        <i data-lucide="trash-2" size="18"></i>
                    </button>
                </div>
            </div>
        `;
    },

    pulseButton: function (selector) {
        const btn = document.querySelector(selector);
        if (btn) {
            btn.classList.add('btn-pulse');
            setTimeout(() => btn.classList.remove('btn-pulse'), 300);
        }
    }
};

window.CartView = CartView;
