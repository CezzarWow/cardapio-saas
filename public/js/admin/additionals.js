/**
 * ADDITIONALS.JS - Orquestrador da Página de Adicionais
 * 
 * Este arquivo inicializa o sistema e delega para os módulos:
 * - additionals-group-modal.js (Modais de Grupo)
 * - additionals-item-modal.js (Modal de Item CRUD)
 * - additionals-delete-modal.js (Modal de Exclusão)
 * - additionals-ui.js (Abas, Busca, Helpers)
 * 
 * DEPENDÊNCIA: multi-select.js deve ser carregado antes
 * ORDEM DE CARREGAMENTO:
 * 1. additionals.js (este arquivo)
 * 2. additionals-group-modal.js
 * 3. additionals-item-modal.js
 * 4. additionals-delete-modal.js
 * 5. additionals-ui.js
 */

console.log('Additionals JS Loaded');

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
// EVENT DELEGATION (Router de Cliques)
// ==========================================
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
// INICIALIZAÇÃO
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});
