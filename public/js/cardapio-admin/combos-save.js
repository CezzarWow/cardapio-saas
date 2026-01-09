/**
 * COMBOS-SAVE.JS - Salvamento de Combos via AJAX
 * 
 * Gerencia salvamento de combos (criar/atualizar).
 * Parte do módulo CardapioAdmin.
 * Refatorado para usar BASE_URL
 */

(function (CardapioAdmin) {
    'use strict';

    // Helper URL
    const getBaseUrl = () => typeof BASE_URL !== 'undefined' ? BASE_URL : '/cardapio-saas/public';

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

        // 4. Detecta modo edição e define URL
        const comboIdInput = document.getElementById('combo_id');
        const isEditMode = comboIdInput && comboIdInput.value && comboIdInput.value !== '';

        if (isEditMode) {
            formData.append('id', comboIdInput.value);
        }

        // URL robusta usando BASE_URL
        const endpoint = isEditMode
            ? '/admin/cardapio/combo/atualizar'
            : '/admin/cardapio/combo/salvar';

        const url = getBaseUrl() + endpoint;

        // 5. Envia via AJAX
        btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
        btn.disabled = true;

        // CSRF Token
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrf) {
            // FormData não aceita headers diretos no objeto, 
            // mas fetch aceita
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf
            },
            body: formData
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.reload();
                } else {
                    return response.text().then(text => { throw new Error(text || 'Erro desconhecido'); });
                }
            })
            .catch(error => {
                console.error('Erro ao salvar combo:', error);
                alert('Erro ao salvar combo. Verifique o console.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
