/**
 * ComboController.js
 * Controlador principal do fluxo de Combos
 * Gerencia Estado e Eventos
 */
const ComboController = {

    // Estado Local
    currentCombo: null,
    quantity: 1,
    selections: {}, // { productId: [ {id, name, price} ] }

    /**
     * Inicializa listeners globais
     */
    init: function () {
        // Delegação de eventos para inputs de adicionais
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[name^="combo_extras_"]')) {
                this.handleAdditionalChange(e.target);
            }
        });
    },

    open: function (comboId) {
        const combo = (typeof combos !== 'undefined') ? combos.find(c => c.id == comboId) : null;
        if (!combo) return console.error('Combo não encontrado');

        this.currentCombo = combo;
        this.quantity = 1;
        this.selections = {};

        // Inicializa seleções vazias para cada produto do combo
        if (combo.items) {
            combo.items.forEach(item => this.selections[item.product_id] = []);
        }

        // Renderiza View
        ComboView.renderModal(combo);
        this.updateTotal();
        ComboView.open();
    },

    close: function () {
        ComboView.close();
        this.currentCombo = null;
    },

    // --- MANIPULAÇÃO DE DADOS ---

    handleAdditionalChange: function (input) {
        const productId = input.dataset.comboProductId;
        const addId = input.dataset.comboAddId;
        const name = input.dataset.comboAddName;
        const price = parseFloat(input.dataset.comboAddPrice);
        const isChecked = input.checked;

        if (!this.selections[productId]) this.selections[productId] = [];

        if (isChecked) {
            this.selections[productId].push({ id: addId, name, price });
        } else {
            this.selections[productId] = this.selections[productId].filter(a => a.id != addId);
        }

        // Atualiza View
        const count = this.selections[productId].length;
        ComboView.updateBadge(productId, count);
        this.updateTotal();
    },

    increaseQty: function () {
        this.quantity++;
        ComboView.updateQuantity(this.quantity);
        this.updateTotal();
    },

    decreaseQty: function () {
        if (this.quantity > 1) {
            this.quantity--;
            ComboView.updateQuantity(this.quantity);
            this.updateTotal();
        }
    },

    updateTotal: function () {
        if (!this.currentCombo) return;
        const total = ComboValidator.calculateTotal(parseFloat(this.currentCombo.price), this.selections, this.quantity);
        ComboView.updatePrice(total);
    },

    addToCart: function () {
        if (!this.currentCombo) return;

        const obs = document.getElementById('modalComboObservation')?.value;
        const cartItem = ComboValidator.prepareCartItem(this.currentCombo, this.selections, this.quantity, obs);

        CardapioCart.add(cartItem);
        this.close();
    }
};

window.ComboController = ComboController;
