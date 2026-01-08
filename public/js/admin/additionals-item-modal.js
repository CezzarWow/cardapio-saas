/**
 * ADDITIONALS-ITEM-MODAL.JS - Modal de Item CRUD
 * Dependências: additionals.js, MultiSelect
 * 
 * Este módulo gerencia o modal de criação e edição de itens.
 */

(function () {
    'use strict';

    // ==========================================
    // ABRIR MODAL PARA CRIAR ITEM
    // ==========================================
    window.openCreateItemModal = function () {
        // Resetar Form
        const form = document.getElementById('itemForm');
        if (form) {
            form.reset();
            form.action = window.BASE_URL + '/admin/loja/adicionais/item/salvar-modal';
        }

        const hiddenId = document.getElementById('itemIdInputHidden');
        if (hiddenId) hiddenId.value = '';

        const title = document.getElementById('itemModalTitle');
        if (title) {
            title.textContent = 'Novo Item';
            title.style.color = '#1f2937';
        }

        // Resetar campos visuais
        const priceInput = document.getElementById('itemPriceInput');
        if (priceInput) {
            priceInput.disabled = false;
            priceInput.style.background = 'white';
        }

        // Resetar checkboxes de grupos
        MultiSelect.reset('groups');
        updateGroupsTriggerText();

        const modal = document.getElementById('itemModal');
        if (modal) modal.style.display = 'flex';
    };

    // Função de compatibilidade (alias)
    window.openItemModal = function () {
        openCreateItemModal();
    };

    // ==========================================
    // ABRIR MODAL PARA EDITAR ITEM (AJAX)
    // ==========================================
    window.openEditItemModal = function (id) {
        // Preparar Modal
        document.getElementById('itemForm').reset();
        document.getElementById('itemModalTitle').textContent = 'Carregando...';
        document.getElementById('itemModal').style.display = 'flex';

        // Resetar checkboxes
        MultiSelect.reset('groups');
        updateGroupsTriggerText();

        // Buscar dados
        fetch(window.BASE_URL + '/admin/loja/adicionais/get-item-data?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    closeItemModal();
                    return;
                }

                const item = data.item;
                const groups = data.groups;

                // Preencher Form
                document.getElementById('itemIdInputHidden').value = item.id;
                document.getElementById('itemForm').action = window.BASE_URL + '/admin/loja/adicionais/item/atualizar-modal';
                document.getElementById('itemModalTitle').textContent = 'Editar Item';

                // Campos
                document.querySelector('input[name="name"]').value = item.name;

                const priceInput = document.getElementById('itemPriceInput');
                const freeCheck = document.querySelector('input[type="checkbox"][onchange="toggleItemFree(this)"]');

                if (parseFloat(item.price) == 0) {
                    if (freeCheck) freeCheck.checked = true;
                    priceInput.value = '0,00';
                    priceInput.disabled = true;
                    priceInput.style.background = '#f3f4f6';
                } else {
                    if (freeCheck) freeCheck.checked = false;
                    priceInput.disabled = false;
                    priceInput.style.background = 'white';
                    priceInput.value = (parseFloat(item.price)).toLocaleString("pt-BR", { minimumFractionDigits: 2 });
                }

                // Marcar Grupos
                const groupIds = groups.map(id => parseInt(id));
                const checkboxes = document.querySelectorAll('.custom-select-container-groups input[type="checkbox"]');

                checkboxes.forEach(cb => {
                    if (groupIds.includes(parseInt(cb.value))) {
                        cb.checked = true;
                    }
                });
                updateGroupsTriggerText();

            })
            .catch(err => {
                console.error(err);
                alert('Erro ao carregar dados.');
                closeItemModal();
            });
    };

    // ==========================================
    // FECHAR MODAL DE ITEM
    // ==========================================
    window.closeItemModal = function () {
        document.getElementById('itemModal').style.display = 'none';
    };

    // ==========================================
    // FECHAR MODAL AO CLICAR FORA
    // ==========================================
    document.getElementById('itemModal').addEventListener('click', function (e) {
        if (e.target === this) closeItemModal();
    });

})();
