/**
 * COMBOS-SAVE.JS - Salvamento de Combos via AJAX
 * 
 * Gerencia salvamento de combos (criar/atualizar).
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Salva Combo via AJAX (simula form submission)
     */
    CardapioAdmin.saveCombo = function () {
        const btn = document.querySelector('#comboFormContainer .cardapio-admin-btn-primary');
        if (!btn) return;

        const originalText = btn.innerHTML;

        // 1. Coleta dados básicos
        const nameInput = document.getElementById('combo_name');
        const priceInput = document.getElementById('combo_price');
        const originalPrice = document.getElementById('combo_original_price');
        const descInput = document.getElementById('combo_description');
        const validityType = document.getElementById('combo_validity_type');
        const validUntilInput = document.getElementById('combo_valid_until');
        const imageInput = document.getElementById('combo_image');

        if (!nameInput || !priceInput) return;

        const name = nameInput.value.trim();
        const price = priceInput.value.trim();
        const desc = descInput ? descInput.value.trim() : '';
        let validUntil = validUntilInput ? validUntilInput.value : '';

        // Validação simples
        if (!name) {
            alert('Por favor, informe o nome do combo.');
            nameInput.focus();
            return;
        }
        if (!price) {
            alert('Por favor, informe o preço promocional.');
            priceInput.focus();
            return;
        }

        // Validação de data
        if (validityType) {
            if (validityType.value === 'today') {
                const today = new Date();
                validUntil = today.toISOString().split('T')[0];
            } else if (validityType.value === 'always') {
                validUntil = '';
            } else if (validityType.value === 'date' && !validUntil) {
                alert('Por favor, selecione a data de validade.');
                return;
            }
        }

        // 2. Coleta produtos selecionados (Com Quantidade)
        const selectedProducts = [];
        const allowAdditionals = {};

        document.querySelectorAll('.combo-product-qty').forEach(input => {
            const qty = parseInt(input.value) || 0;
            const pid = input.getAttribute('data-id');

            if (qty > 0) {
                for (let i = 0; i < qty; i++) {
                    selectedProducts.push(pid);
                }

                const toggle = document.querySelector(`.combo-additional-toggle[data-prod-id="${pid}"]`);
                if (toggle && toggle.checked) {
                    allowAdditionals[pid] = 1;
                }
            }
        });

        if (selectedProducts.length === 0) {
            alert('Selecione pelo menos um produto para o combo.');
            return;
        }

        // 3. Monta FormData
        const formData = new FormData();
        formData.append('name', name);
        formData.append('price', price);
        formData.append('description', desc);
        formData.append('display_order', 0);
        formData.append('is_active', 1);
        if (validUntil) formData.append('valid_until', validUntil);

        if (imageInput && imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }

        selectedProducts.forEach(pid => {
            formData.append('products[]', pid);
            if (allowAdditionals[pid]) {
                formData.append(`allow_additionals[${pid}]`, 1);
            }
        });

        // 4. Detecta modo edição
        const comboIdInput = document.getElementById('combo_id');
        const isEditMode = comboIdInput && comboIdInput.value && comboIdInput.value !== '';

        if (isEditMode) {
            formData.append('id', comboIdInput.value);
        }

        // 5. Envia via AJAX
        btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
        btn.disabled = true;

        const mainForm = document.getElementById('formCardapio');
        const action = isEditMode ? '/combo/atualizar' : '/combo/salvar';
        let baseUrl = mainForm ? mainForm.action.replace('/salvar', action) : window.location.href;

        if (!baseUrl.includes('combo/')) {
            baseUrl = isEditMode ? 'cardapio/combo/atualizar' : 'cardapio/combo/salvar';
        }

        fetch(baseUrl, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Erro ao salvar combo. Tente novamente.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro de conexão ao salvar combo.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
