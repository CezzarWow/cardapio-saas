/**
 * PDV CHECKOUT - Adjust (Ajuste de Total)
 * Permite definir o valor total final do pedido, criando um item de ajuste
 * (positivo ou negativo) automaticamente.
 * 
 * Dependências: PDVCart, CheckoutTotals, CheckoutUI, CheckoutHelpers
 */

const CheckoutAdjust = {

    isEditing: false,

    /**
     * Alterna modo de edição do Total
     */
    toggleEdit: function () {
        const input = document.getElementById('display-total-edit');
        const btnToggle = document.getElementById('btn-toggle-edit');
        const btnSave = document.getElementById('btn-save-total');

        if (!input) return;

        this.isEditing = !this.isEditing;

        if (this.isEditing) {
            // Habilita edição
            input.readOnly = false;
            input.style.background = 'white';
            input.style.borderColor = '#2563eb';
            input.focus();

            if (btnToggle) btnToggle.style.display = 'none';
            if (btnSave) {
                btnSave.style.display = 'flex';
                btnSave.disabled = false;
                btnSave.style.background = '#2563eb';
                btnSave.style.cursor = 'pointer';
            }
        } else {
            // Cancela/Desabilita
            this._resetUI();
        }
    },

    /**
     * Salva o novo total digitado
     */
    saveEdit: function () {
        const input = document.getElementById('display-total-edit');
        if (!input) return;

        let valStr = input.value.trim();
        // Remove pontos de milhar e troca virgula por ponto
        if (valStr.includes(',')) valStr = valStr.replace(/\./g, '').replace(',', '.');

        const newTotal = parseFloat(valStr);

        this.setTotal(newTotal);

        // Após salvar, sai do modo edição e força reset visual
        if (this.isEditing) {
            this.toggleEdit();
        }
    },

    /**
     * Reseta a UI para o estado inicial (readonly)
     */
    _resetUI: function () {
        const input = document.getElementById('display-total-edit');
        const btnToggle = document.getElementById('btn-toggle-edit');
        const btnSave = document.getElementById('btn-save-total');

        if (input) {
            input.readOnly = true;
            input.style.background = '#f1f5f9';
            input.style.borderColor = '#e2e8f0';

            // Restaura valor real atualizado (com delay pequeno para garantir sync)
            setTimeout(() => {
                const currentTotal = CheckoutTotals.getFinalTotal();
                input.value = currentTotal.toFixed(2).replace('.', ',');
                if (typeof CheckoutHelpers !== 'undefined' && CheckoutHelpers.formatMoneyInput) {
                    CheckoutHelpers.formatMoneyInput(input);
                }
            }, 50);
        }

        if (btnToggle) btnToggle.style.display = 'flex';
        if (btnSave) btnSave.style.display = 'none';

        this.isEditing = false;
    },

    /**
     * Define o novo total do pedido criando um item de ajuste.
     * @param {number} newTotal - O valor final desejado pelo usuário
     */
    setTotal: function (newTotal) {
        if (isNaN(newTotal) || newTotal < 0) {
            alert('Por favor, digite um valor válido.');
            return;
        }

        // Remove ajuste anterior se existir para recalcular limpo
        const ADJUST_ITEM_ID = -88888;
        this._removeExistingAdjustment(ADJUST_ITEM_ID);

        // Recalcula diferença após remover antigo ajuste (se havia)
        const cleanTotal = CheckoutTotals.getFinalTotal();
        const finalDifference = newTotal - cleanTotal;

        console.log('--- Debug Ajuste Total ---');
        console.log('Novo Total (Alvo):', newTotal);
        console.log('Total Limpo (Sem ajustes):', cleanTotal);
        console.log('Diferença a aplicar:', finalDifference);

        if (Math.abs(finalDifference) > 0.005) {
            // Adiciona o novo item de ajuste
            const ADJUST_ITEM_NAME = 'Ajuste';

            PDVCart.add(
                ADJUST_ITEM_ID,
                ADJUST_ITEM_NAME,
                finalDifference,
                1,
                [] // sem extras
            );
        } else {
            console.log('Diferença insignificante. Nenhum ajuste criado.');
        }

        // IMPORTANTE: Recalcula cache de totais para refletir adição do item
        if (typeof CheckoutTotals.refreshBaseTotal === 'function') {
            CheckoutTotals.refreshBaseTotal();
        }

        // Atualiza UI Geral
        CheckoutUI.updateCheckoutUI();

        // Garante que o input mostre o valor final atingido
        if (document.getElementById('display-total-edit')) {
            const finalVal = CheckoutTotals.getFinalTotal();
            document.getElementById('display-total-edit').value = finalVal.toFixed(2).replace('.', ',');
        }
    },

    /**
     * Remove item de ajuste anterior do carrinho se existir
     */
    _removeExistingAdjustment: function (adjustId) {
        if (typeof PDVCart !== 'undefined' && PDVCart.items) {
            const index = PDVCart.items.findIndex(i => i.id === adjustId);
            if (index > -1) {
                const item = PDVCart.items[index];
                if (item.cartItemId) {
                    PDVCart.remove(item.cartItemId);
                } else {
                    PDVCart.items.splice(index, 1);
                    PDVCart.updateUI();
                }
                // Após remover, precisamos refrescar o total cached também
                if (typeof CheckoutTotals.refreshBaseTotal === 'function') {
                    CheckoutTotals.refreshBaseTotal();
                }
            }
        }
    }

};

window.CheckoutAdjust = CheckoutAdjust;
