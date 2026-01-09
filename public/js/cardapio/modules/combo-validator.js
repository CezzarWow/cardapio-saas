/**
 * ComboValidator.js
 * Lógica de negócios e validação para Combos
 */
const ComboValidator = {

    /**
     * Calcula o preço total do combo com base nos extras selecionados e quantidade
     * @param {number} basePrice Preço base do combo
     * @param {object} selections Objeto com seleções {productId: [{price: 1.0}, ...]}
     * @param {number} quantity Quantidade de combos
     * @returns {number} Preço total
     */
    calculateTotal: function (basePrice, selections, quantity) {
        let extrasTotal = 0;

        Object.values(selections).forEach(list => {
            if (Array.isArray(list)) {
                list.forEach(item => {
                    extrasTotal += (parseFloat(item.price) || 0);
                });
            }
        });

        return (basePrice + extrasTotal) * quantity;
    },

    /**
     * Valida se todos os itens obrigatórios foram preenchidos (Futuro)
     * Por enquanto, apenas retorna true pois não há regras rígidas de obrigatórios implementadas no front legado
     */
    validate: function (combo, selections) {
        // Exemplo de expansão futura: verificar min/max de itens
        return true;
    },

    /**
     * Prepara objeto para adicionar ao carrinho
     */
    prepareCartItem: function (combo, selections, quantity, observation) {
        const totalExtras = this.calculateTotal(0, selections, 1); // Extras unitários

        const productsList = combo.items.map(item => {
            const extras = selections[item.product_id] || [];
            return {
                id: item.product_id,
                name: item.product_name,
                additionals: extras
            };
        });

        return {
            id: Date.now() + Math.random(),
            isCombo: true,
            comboId: combo.id,
            name: combo.name,
            image: combo.image,
            basePrice: parseFloat(combo.price),
            quantity: quantity,
            products: productsList,
            observation: observation || '',
            unitPrice: parseFloat(combo.price) + totalExtras
        };
    }
};

window.ComboValidator = ComboValidator;
