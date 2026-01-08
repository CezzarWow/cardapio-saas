/**
 * COMBOS-EDIT.JS - Edição de Combos
 * 
 * Gerencia carregamento e cancelamento de edição de combos.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Carrega dados de um combo existente para edição in-place
     * @param {number} comboId ID do combo a editar
     */
    CardapioAdmin.loadComboForEdit = function (comboId) {
        const mainForm = document.getElementById('formCardapio');
        let baseUrl = mainForm ? mainForm.action.replace('/salvar', '/combo/editar') : 'admin/loja/cardapio/combo/editar';
        baseUrl += `?id=${comboId}&json=1`;

        const formContainer = document.getElementById('comboFormContainer');
        if (formContainer) {
            formContainer.style.opacity = '0.6';
            formContainer.style.pointerEvents = 'none';
        }

        fetch(baseUrl)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erro ao carregar combo: ' + (data.error || 'Desconhecido'));
                    return;
                }

                const combo = data.combo;
                const items = data.items;

                // 1. Preencher campos do formulário
                document.getElementById('combo_id').value = combo.id;
                document.getElementById('combo_name').value = combo.name || '';
                document.getElementById('combo_description').value = combo.description || '';

                const priceInput = document.getElementById('combo_price');
                if (priceInput && combo.price) {
                    priceInput.value = parseFloat(combo.price).toFixed(2).replace('.', ',');
                }

                // Validade
                const validityType = document.getElementById('combo_validity_type');
                const validUntilInput = document.getElementById('combo_valid_until');
                if (validityType && validUntilInput) {
                    if (!combo.valid_until) {
                        validityType.value = 'always';
                        validUntilInput.style.display = 'none';
                    } else {
                        const today = new Date().toISOString().split('T')[0];
                        if (combo.valid_until === today) {
                            validityType.value = 'today';
                            validUntilInput.style.display = 'none';
                        } else {
                            validityType.value = 'date';
                            validUntilInput.value = combo.valid_until;
                            validUntilInput.style.display = 'block';
                        }
                    }
                }

                // 2. Limpar quantidades anteriores
                document.querySelectorAll('.combo-product-qty').forEach(input => {
                    input.value = 0;
                    const pid = input.getAttribute('data-id');
                    const display = document.getElementById('display_qty_' + pid);
                    const card = document.getElementById('card_prod_' + pid);
                    if (display) display.textContent = '0';
                    if (card) card.classList.remove('selected');
                });

                // 3. Preencher quantidades dos itens do combo
                items.forEach(item => {
                    const qtyInput = document.getElementById('qty_prod_' + item.id);
                    const display = document.getElementById('display_qty_' + item.id);
                    const card = document.getElementById('card_prod_' + item.id);
                    const toggle = document.querySelector(`.combo-additional-toggle[data-prod-id="${item.id}"]`);

                    if (qtyInput) qtyInput.value = item.qty;
                    if (display) display.textContent = item.qty;
                    if (card && item.qty > 0) card.classList.add('selected');
                    if (toggle) toggle.checked = item.allow_additionals == 1;
                });

                // 4. Atualizar preço original calculado
                if (typeof calculateComboOriginalPrice === 'function') {
                    calculateComboOriginalPrice();
                }

                // 5. Atualizar lista resumo
                this.updateComboItemsSummary(items);

                // 6. Atualizar UI do formulário para modo edição
                const title = document.getElementById('comboFormTitle');
                const subtitle = document.getElementById('comboFormSubtitle');
                const btnSaveText = document.getElementById('btnSaveComboText');
                const btnCancel = document.getElementById('btnCancelCombo');
                const summary = document.getElementById('comboItemsSummary');

                if (title) title.textContent = '✏️ Editar Combo: ' + combo.name;
                if (subtitle) subtitle.textContent = 'Modifique os dados e clique em Salvar Alterações.';
                if (btnSaveText) btnSaveText.textContent = 'Salvar Alterações';
                if (btnCancel) btnCancel.style.display = 'inline-flex';
                if (summary) summary.style.display = 'block';

                // 7. Scroll suave até o formulário
                formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(error => {
                console.error('Erro ao carregar combo:', error);
                alert('Erro de conexão ao carregar combo.');
            })
            .finally(() => {
                if (formContainer) {
                    formContainer.style.opacity = '1';
                    formContainer.style.pointerEvents = 'auto';
                }
            });
    };

    /**
     * Cancela edição e volta ao modo criação
     */
    CardapioAdmin.cancelComboEdit = function () {
        // 1. Limpar ID do combo
        const comboIdInput = document.getElementById('combo_id');
        if (comboIdInput) comboIdInput.value = '';

        // 2. Limpar campos do formulário
        document.getElementById('combo_name').value = '';
        document.getElementById('combo_description').value = '';
        document.getElementById('combo_price').value = '';
        document.getElementById('combo_original_price').value = '';

        const validityType = document.getElementById('combo_validity_type');
        const validUntilInput = document.getElementById('combo_valid_until');
        if (validityType) validityType.value = 'always';
        if (validUntilInput) {
            validUntilInput.value = '';
            validUntilInput.style.display = 'none';
        }

        // 3. Limpar quantidades
        this.clearComboItems();

        // 4. Restaurar UI para modo criação
        const title = document.getElementById('comboFormTitle');
        const subtitle = document.getElementById('comboFormSubtitle');
        const btnSaveText = document.getElementById('btnSaveComboText');
        const btnCancel = document.getElementById('btnCancelCombo');
        const summary = document.getElementById('comboItemsSummary');

        if (title) title.textContent = 'Criar Novo Combo';
        if (subtitle) subtitle.textContent = 'Configure sua oferta especial em uma única tela.';
        if (btnSaveText) btnSaveText.textContent = 'Salvar Promoção';
        if (btnCancel) btnCancel.style.display = 'none';
        if (summary) summary.style.display = 'none';

        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
