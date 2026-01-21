/**
 * ADDITIONALS-GROUP-MODAL.JS - Modais de Grupo
 * Dependências: additionals.js, MultiSelect
 * 
 * Este módulo gerencia os modais relacionados a grupos:
 * - Modal de criar grupo
 * - Modal de vincular itens ao grupo
 * - Modal de vincular categorias ao grupo
 */

(function () {
    'use strict';

    // ==========================================
    // MODAL DE GRUPO (CRIAR)
    // ==========================================
    window.openGroupModal = function () {
        document.getElementById('groupModal').style.display = 'flex';

        // Resetar campos de itens se existirem
        MultiSelect.reset('group-items');
        updateGroupItemsTriggerText();
    };

    window.closeGroupModal = function () {
        document.getElementById('groupModal').style.display = 'none';
    };

    // ==========================================
    // MODAL VINCULAR ITENS AO GRUPO
    // ==========================================
    window.openLinkModal = function (groupId, groupName) {
        document.getElementById('linkGroupId').value = groupId;
        document.getElementById('linkGroupName').textContent = 'Grupo: ' + groupName;
        document.getElementById('linkModal').style.display = 'flex';

        // Resetar checkboxes
        MultiSelect.reset('items');
        updateItemsTriggerText();

        // Fechar dropdown
        const list = document.querySelector('.options-list-items');
        if (list) list.style.display = 'none';
    };

    window.closeLinkModal = function () {
        document.getElementById('linkModal').style.display = 'none';
    };

    // ==========================================
    // MODAL VINCULAR CATEGORIA AO GRUPO
    // ==========================================
    window.openLinkCategoryModal = function (groupId, groupName) {
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
    };

    window.closeLinkCategoryModal = function () {
        document.getElementById('linkCategoryModal').style.display = 'none';
    };

    // ==========================================
    // FECHAR MODAIS AO CLICAR FORA
    // ==========================================
    const groupModalEl = document.getElementById('groupModal');
    if (groupModalEl) {
        groupModalEl.addEventListener('click', function (e) {
            if (e.target === this) closeGroupModal();
        });
    }

    const linkModalEl = document.getElementById('linkModal');
    if (linkModalEl) {
        linkModalEl.addEventListener('click', function (e) {
            if (e.target === this) closeLinkModal();
        });
    }

    const linkCategoryModalEl = document.getElementById('linkCategoryModal');
    if (linkCategoryModalEl) {
        linkCategoryModalEl.addEventListener('click', function (e) {
            if (e.target === this) closeLinkCategoryModal();
        });
    }

})();
