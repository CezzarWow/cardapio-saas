/**
 * ============================================
 * CARDÁPIO ADMIN - Combos
 * Funções relacionadas a combos/promoções
 * ============================================
 */

(function (CardapioAdmin) {

    /**
     * [NOVO] Salva Combo via AJAX (simula form submission)
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
                // Adiciona o ID N vezes
                for (let i = 0; i < qty; i++) {
                    selectedProducts.push(pid);
                }

                // Verifica toggle de adicionais
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
        formData.append('display_order', 0); // Padrão
        formData.append('is_active', 1);
        if (validUntil) formData.append('valid_until', validUntil);

        if (imageInput && imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }

        // Produtos (array)
        selectedProducts.forEach(pid => {
            formData.append('products[]', pid);
            // Allow Additionals é enviado apenas uma vez por PID (backend map logic)
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

        // 5. Envia via AJAX (Fetch)
        btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
        btn.disabled = true;

        // Detecta URL base do form principal e ajusta para combo/salvar ou combo/atualizar
        const mainForm = document.getElementById('formCardapio');
        const action = isEditMode ? '/combo/atualizar' : '/combo/salvar';
        let baseUrl = mainForm ? mainForm.action.replace('/salvar', action) : window.location.href;

        // Fallback se URL falhar
        if (!baseUrl.includes('combo/')) {
            // Tenta construir relativo
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

    /**
     * [NOVO] Carrega dados de um combo existente para edição in-place
     * @param {number} comboId ID do combo a editar
     */
    CardapioAdmin.loadComboForEdit = function (comboId) {
        // Detecta URL base
        const mainForm = document.getElementById('formCardapio');
        let baseUrl = mainForm ? mainForm.action.replace('/salvar', '/combo/editar') : 'admin/loja/cardapio/combo/editar';
        baseUrl += `?id=${comboId}&json=1`;

        // Feedback visual
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

                // Preço formatado
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

                    if (qtyInput) {
                        qtyInput.value = item.qty;
                    }
                    if (display) {
                        display.textContent = item.qty;
                    }
                    if (card && item.qty > 0) {
                        card.classList.add('selected');
                    }
                    if (toggle) {
                        toggle.checked = item.allow_additionals == 1;
                    }
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

                // 8. Re-inicializar ícones Lucide
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            })
            .catch(error => {
                console.error('Erro ao carregar combo:', error);
                alert('Erro de conexão ao carregar combo.');
            })
            .finally(() => {
                // Restaurar interatividade
                if (formContainer) {
                    formContainer.style.opacity = '1';
                    formContainer.style.pointerEvents = 'auto';
                }
            });
    };

    /**
     * [NOVO] Cancela edição e volta ao modo criação
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

        // 5. Re-inicializar ícones Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    };

    /**
     * [NOVO] Limpa todos os itens selecionados do combo
     */
    CardapioAdmin.clearComboItems = function () {
        document.querySelectorAll('.combo-product-qty').forEach(input => {
            input.value = 0;
            const pid = input.getAttribute('data-id');
            const display = document.getElementById('display_qty_' + pid);
            const card = document.getElementById('card_prod_' + pid);
            if (display) display.textContent = '0';
            if (card) card.classList.remove('selected');
        });

        // Atualizar preço original
        if (typeof calculateComboOriginalPrice === 'function') {
            calculateComboOriginalPrice();
        }

        // Limpar lista resumo
        const list = document.getElementById('comboItemsList');
        if (list) list.innerHTML = '';
    };

    /**
     * [NOVO] Atualiza a lista visual de itens do combo
     * @param {Array} items Lista de itens com {id, name, qty}
     */
    CardapioAdmin.updateComboItemsSummary = function (items) {
        const list = document.getElementById('comboItemsList');
        if (!list) return;

        list.innerHTML = '';

        items.forEach(item => {
            if (item.qty > 0) {
                const li = document.createElement('li');
                li.style.cssText = 'background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 0.85rem; font-weight: 500;';
                li.textContent = `${item.qty}x ${item.name}`;
                list.appendChild(li);
            }
        });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});

// Função global para toggle de combo
window.toggleComboActive = function (id, isActive) {
    fetch('admin/loja/cardapio/combo/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            active: isActive
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Status atualizado');
            } else {
                alert('Erro ao atualizar status: ' + (data.error || 'Desconhecido'));
                const checkbox = document.querySelector(`input[onchange*="${id}"]`);
                if (checkbox) checkbox.checked = !isActive;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro de conexão');
            const checkbox = document.querySelector(`input[onchange*="${id}"]`);
            if (checkbox) checkbox.checked = !isActive;
        });
};
