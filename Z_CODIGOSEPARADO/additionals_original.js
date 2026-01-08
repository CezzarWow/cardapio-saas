console.log('Additionals JS Loaded');
// alert('JS Carregado!'); // Descomente se console não for visível
/**
 * ADDITIONALS - JavaScript
 * Gerencia modais, multi-selects e busca da página de Adicionais
 * 
 * Extraído de: views/admin/additionals/index.php
 * Data: Janeiro 2026
 * 
 * DEPENDÊNCIA: multi-select.js deve ser carregado antes
 */

// ==========================================
// MULTI-SELECTS (usando componente genérico)
// ==========================================

// Funções de compatibilidade que delegam para MultiSelect
function toggleGroupItemsSelect(el) {
    MultiSelect.toggle(el, 'group-items');
}

function updateGroupItemsTriggerText() {
    MultiSelect.updateTriggerText('group-items', 'Item', 'Item(ns)', 'Selecione os itens...');
}

function toggleCategorySelect(el) {
    MultiSelect.toggle(el, 'cat');
}

function updateCategoryTriggerText(checkbox) {
    MultiSelect.updateTriggerText('cat', 'Selecionada', 'Selecionada(s)', 'Selecione...');
}

function toggleItemsSelect(el) {
    MultiSelect.toggle(el, 'items');
}

function updateItemsTriggerText() {
    MultiSelect.updateTriggerText('items', 'item', 'item(ns)', 'Selecione os itens...');
}

function toggleGroupsSelect(el) {
    MultiSelect.toggle(el, 'groups');
}

function updateGroupsTriggerText() {
    MultiSelect.updateTriggerText('groups', 'Grupo', 'Grupo(s)', 'Selecione os grupos...');
}

// Inicializa click-outside para todos os multi-selects
MultiSelect.initClickOutside(['group-items', 'cat', 'items', 'groups']);

// ==========================================
// EVENT DELEGATION (Padrão Robusto)
// ==========================================
// Centraliza handlers de clique para evitar onclick inline com parâmetros PHP
document.addEventListener('click', function (e) {
    // 1. Vincular Categoria ao Grupo
    const btnCat = e.target.closest('.btn-action-category');
    if (btnCat) {
        e.preventDefault();
        const groupId = btnCat.dataset.groupId;
        const groupName = btnCat.dataset.groupName;
        openLinkCategoryModal(groupId, groupName);
        return;
    }

    // 2. Vincular Item ao Grupo
    const btnLink = e.target.closest('.btn-action-link');
    if (btnLink) {
        e.preventDefault();
        const groupId = btnLink.dataset.groupId;
        const groupName = btnLink.dataset.groupName;
        openLinkModal(groupId, groupName);
        return;
    }

    // 3. Excluir Grupo
    const btnDelGroup = e.target.closest('.btn-action-delete-group');
    if (btnDelGroup) {
        e.preventDefault();
        const url = btnDelGroup.dataset.url;
        const name = btnDelGroup.dataset.name;
        openDeleteModal(url, name);
        return;
    }

    // 4. Excluir Item
    const btnDelItem = e.target.closest('.btn-action-delete-item');
    if (btnDelItem) {
        e.preventDefault();
        const url = btnDelItem.dataset.url;
        const name = btnDelItem.dataset.name;
        openDeleteModal(url, name);
        return;
    }
});

// ==========================================
// MODAIS DE GRUPO
// ==========================================

function openGroupModal() {
    document.getElementById('groupModal').style.display = 'flex';

    // Resetar campos de itens se existirem
    MultiSelect.reset('group-items');
    updateGroupItemsTriggerText();
}

function closeGroupModal() {
    document.getElementById('groupModal').style.display = 'none';
}

// ==========================================
// MODAL VINCULAR ITENS
// ==========================================

function openLinkModal(groupId, groupName) {
    document.getElementById('linkGroupId').value = groupId;
    document.getElementById('linkGroupName').textContent = 'Grupo: ' + groupName;
    document.getElementById('linkModal').style.display = 'flex';

    // Resetar checkboxes
    MultiSelect.reset('items');
    updateItemsTriggerText();

    // Fechar dropdown
    const list = document.querySelector('.options-list-items');
    if (list) list.style.display = 'none';
}

function closeLinkModal() {
    document.getElementById('linkModal').style.display = 'none';
}

// ==========================================
// MODAL VINCULAR CATEGORIA
// ==========================================

function openLinkCategoryModal(groupId, groupName) {
    document.getElementById('linkCategoryGroupId').value = groupId;
    document.getElementById('linkCategoryGroupName').textContent = 'Grupo: ' + groupName;
    document.getElementById('linkCategoryModal').style.display = 'flex';

    // Resetar estado visual
    MultiSelect.reset('cat');

    const trigger = document.querySelector('.trigger-text-cat');
    if (trigger) {
        trigger.textContent = 'Carregando...';
        trigger.style.color = '#6b7280';
    }

    // BUSCAR VÍNCULOS VIA AJAX
    fetch(window.BASE_URL + '/admin/loja/adicionais/get-linked-categories?group_id=' + groupId)
        .then(res => res.json())
        .then(ids => {
            const checkboxes = document.querySelectorAll('.options-list-cat input[type="checkbox"]');
            let count = 0;
            checkboxes.forEach(cb => {
                if (ids.includes(parseInt(cb.value))) {
                    cb.checked = true;
                    count++;
                }
            });

            // Atualizar texto do trigger
            if (trigger) {
                if (count === 0) {
                    trigger.textContent = 'Selecione...';
                    trigger.style.color = '#6b7280';
                    trigger.style.fontWeight = '400';
                } else {
                    trigger.textContent = count + ' Selecionada(s)';
                    trigger.style.color = '#1f2937';
                    trigger.style.fontWeight = '600';
                }
            }
        })
        .catch(err => {
            console.error('Erro ao buscar vínculos:', err);
            if (trigger) trigger.textContent = 'Erro ao carregar';
        });
}

function closeLinkCategoryModal() {
    document.getElementById('linkCategoryModal').style.display = 'none';
}

// ==========================================
// FECHAR MODAIS AO CLICAR FORA
// ==========================================

document.getElementById('groupModal').addEventListener('click', function (e) {
    if (e.target === this) closeGroupModal();
});
document.getElementById('linkModal').addEventListener('click', function (e) {
    if (e.target === this) closeLinkModal();
});
document.getElementById('linkCategoryModal').addEventListener('click', function (e) {
    if (e.target === this) closeLinkCategoryModal();
});

// ==========================================
// ABAS E TOGGLE DE VISUALIZAÇÃO
// ==========================================

let currentAdditionalView = 'groups';

function setAdditionalView(view, btn) {
    currentAdditionalView = view;
    const viewGroups = document.getElementById('view-groups');
    const viewItems = document.getElementById('view-items');
    const buttons = document.querySelectorAll('.stock-view-btn');

    // Atualizar classe ativa dos botões
    buttons.forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    // Alternar containers
    if (view === 'groups') {
        viewGroups.style.display = 'block';
        viewItems.style.display = 'none';
    } else {
        viewGroups.style.display = 'none';
        viewItems.style.display = 'block';
    }

    // Reaplicar busca se houver
    const searchVal = document.getElementById('searchInput').value;
    handleSearch(searchVal);
}

function handleSearch(query) {
    const q = query.toLowerCase().trim();

    if (currentAdditionalView === 'groups') {
        // Filtrar Grupos
        const cards = document.querySelectorAll('.group-card');
        cards.forEach(card => {
            const name = card.dataset.name || '';
            card.style.display = name.includes(q) ? 'block' : 'none';
        });
    } else {
        // Filtrar Itens
        const items = document.querySelectorAll('.item-card-row');
        items.forEach(item => {
            const name = item.dataset.name || '';
            item.style.display = name.includes(q) ? 'flex' : 'none';
        });
    }
}

// Inicializa ícones ao carregar (garantia)
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});

// ==========================================
// MODAL DE ITEM (CREATE & EDIT)
// ==========================================

// 1. Abrir Modal para CRIAR
function openCreateItemModal() {
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
}

// Função de compatibilidade (alias)
function openItemModal() {
    openCreateItemModal();
}

// 2. Abrir Modal para EDITAR (AJAX)
function openEditItemModal(id) {
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
            const groups = data.groups; // Array de IDs como strings ou ints

            // Preencher Form
            document.getElementById('itemIdInputHidden').value = item.id;
            document.getElementById('itemForm').action = window.BASE_URL + '/admin/loja/adicionais/item/atualizar-modal';
            document.getElementById('itemModalTitle').textContent = 'Editar Item';

            // Campos
            document.querySelector('input[name="name"]').value = item.name;

            const priceInput = document.getElementById('itemPriceInput');
            // Checkbox grátis - selecionar pelo onchange pois não tem ID
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
                // Formatar preço
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
}

// ==========================================
// MODAL DELETE
// ==========================================

function openDeleteModal(actionUrl, itemName) {
    const btn = document.getElementById('confirmDeleteBtn');
    if (btn) btn.href = actionUrl;

    const nameSpan = document.getElementById('deleteItemName');
    if (nameSpan) nameSpan.textContent = itemName;

    const modal = document.getElementById('deleteModal');
    if (modal) modal.style.display = 'flex';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.style.display = 'none';
}

function closeItemModal() {
    document.getElementById('itemModal').style.display = 'none';
}

// ==========================================
// HELPERS
// ==========================================

// Máscara de Moeda (Direita para Esquerda)
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, ""); // Remove não dígitos
    value = (Number(value) / 100).toLocaleString("pt-BR", { minimumFractionDigits: 2 });
    input.value = value;
}

function toggleItemFree(checkbox) {
    const input = document.getElementById('itemPriceInput');
    if (checkbox.checked) {
        input.value = '0,00';
        input.disabled = true;
        input.style.background = '#f3f4f6';
    } else {
        input.value = '';
        input.disabled = false;
        input.style.background = 'white';
        input.focus();
    }
}

// ==========================================
// FECHAR MODAL ITEM AO CLICAR FORA
// ==========================================

document.getElementById('itemModal').addEventListener('click', function (e) {
    if (e.target === this) closeItemModal();
});
