/**
 * ADDITIONALS-BUNDLE.JS
 * 
 * Script unificado para a aba de Adicionais (Estoque).
 * 
 * ARQUITETURA:
 * - Funções de modal são expostas IMEDIATAMENTE (não dependem de MultiSelect)
 * - Funções de MultiSelect são inicializadas quando disponível (fallback gracioso)
 * - Event delegation para cliques em botões dinâmicos
 * 
 * @version 2.0 - Refatorado para robustez no contexto SPA
 */

(function () {
    'use strict';

    // ==========================================
    // 1. FUNÇÕES CRÍTICAS (SEMPRE DISPONÍVEIS)
    // ==========================================
    // Estas funções são expostas IMEDIATAMENTE no window
    // para garantir que onclick="" inline funcione

    /**
     * Abre modal de novo grupo
     */
    window.openGroupModal = function () {
        const modal = document.getElementById('groupModal');
        if (modal) {
            modal.style.display = 'flex';
            // Reset MultiSelect se disponível
            if (window.MultiSelect) {
                MultiSelect.reset('group-items');
                if (typeof window.updateGroupItemsTriggerText === 'function') {
                    window.updateGroupItemsTriggerText();
                }
            }
        }
    };

    window.closeGroupModal = function () {
        const modal = document.getElementById('groupModal');
        if (modal) modal.style.display = 'none';
    };

    /**
     * Abre modal de vincular itens a grupo
     * @param {string|number} groupId - ID do grupo
     * @param {string} groupName - Nome do grupo
     * @param {Array<number>} linkedItemIds - IDs dos itens já vinculados (para pré-selecionar)
     */
    window.openLinkModal = function (groupId, groupName, linkedItemIds) {
        const modal = document.getElementById('linkModal');
        if (modal) {
            const groupIdInput = document.getElementById('linkGroupId');
            const groupNameSpan = document.getElementById('linkGroupName');

            if (groupIdInput) groupIdInput.value = groupId;
            if (groupNameSpan) groupNameSpan.textContent = 'Grupo: ' + groupName;

            modal.style.display = 'flex';

            // Reset todos os checkboxes primeiro
            const container = document.querySelector('.custom-select-container-items');
            if (container) {
                const checkboxes = container.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(function (cb) {
                    cb.checked = false;
                });

                // Pré-selecionar itens já vinculados
                if (linkedItemIds && linkedItemIds.length > 0) {
                    checkboxes.forEach(function (cb) {
                        var itemId = parseInt(cb.value);
                        if (linkedItemIds.indexOf(itemId) !== -1) {
                            cb.checked = true;
                        }
                    });
                }
            }

            // Atualizar texto do trigger
            if (typeof window.updateItemsTriggerText === 'function') {
                window.updateItemsTriggerText();
            }

            const list = document.querySelector('.options-list-items');
            if (list) list.style.display = 'none';
        }
    };

    window.closeLinkModal = function () {
        const modal = document.getElementById('linkModal');
        if (modal) modal.style.display = 'none';
    };

    /**
     * Abre modal de novo item (create mode)
     */
    window.openCreateItemModal = function () {
        const form = document.getElementById('itemForm');
        if (form) {
            form.reset();
            form.action = (window.BASE_URL || '') + '/admin/loja/adicionais/item/salvar-modal';
        }

        const hiddenId = document.getElementById('itemIdInputHidden');
        if (hiddenId) hiddenId.value = '';

        const title = document.getElementById('itemModalTitle');
        if (title) title.textContent = 'Novo Item';

        const priceInput = document.getElementById('itemPriceInput');
        if (priceInput) {
            priceInput.disabled = false;
            priceInput.style.background = 'white';
        }

        // Reset MultiSelect se disponível
        if (window.MultiSelect) {
            MultiSelect.reset('groups');
            if (typeof window.updateGroupsTriggerText === 'function') {
                window.updateGroupsTriggerText();
            }
        }

        const modal = document.getElementById('itemModal');
        if (modal) modal.style.display = 'flex';
    };

    /**
     * Alias para openCreateItemModal (compatibilidade com onclick)
     */
    window.openItemModal = function () {
        window.openCreateItemModal();
    };

    /**
     * Abre modal de edição de item
     */
    window.openEditItemModal = function (id) {
        const form = document.getElementById('itemForm');
        if (form) form.reset();

        const title = document.getElementById('itemModalTitle');
        if (title) title.textContent = 'Carregando...';

        const modal = document.getElementById('itemModal');
        if (modal) modal.style.display = 'flex';

        // Reset MultiSelect se disponível
        if (window.MultiSelect) {
            MultiSelect.reset('groups');
            if (typeof window.updateGroupsTriggerText === 'function') {
                window.updateGroupsTriggerText();
            }
        }

        fetch((window.BASE_URL || '') + '/admin/loja/adicionais/get-item-data?id=' + id)
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.error) {
                    alert(data.error);
                    window.closeItemModal();
                    return;
                }

                var item = data.item;
                var groups = data.groups;

                var hiddenId = document.getElementById('itemIdInputHidden');
                if (hiddenId) hiddenId.value = item.id;

                var formEl = document.getElementById('itemForm');
                if (formEl) formEl.action = (window.BASE_URL || '') + '/admin/loja/adicionais/item/atualizar-modal';

                var titleEl = document.getElementById('itemModalTitle');
                if (titleEl) titleEl.textContent = 'Editar Item';

                var nameInput = document.querySelector('input[name="name"]');
                if (nameInput) nameInput.value = item.name;

                var priceInput = document.getElementById('itemPriceInput');
                var freeCheck = document.querySelector('input[type="checkbox"][onchange="toggleItemFree(this)"]');

                if (parseFloat(item.price) === 0) {
                    if (freeCheck) freeCheck.checked = true;
                    if (priceInput) {
                        priceInput.value = '0,00';
                        priceInput.disabled = true;
                        priceInput.style.background = '#f3f4f6';
                    }
                } else {
                    if (freeCheck) freeCheck.checked = false;
                    if (priceInput) {
                        priceInput.disabled = false;
                        priceInput.style.background = 'white';
                        priceInput.value = (parseFloat(item.price)).toLocaleString("pt-BR", { minimumFractionDigits: 2 });
                    }
                }

                // Marcar checkboxes dos grupos vinculados
                var groupIds = groups.map(function (gid) { return parseInt(gid); });
                var checkboxes = document.querySelectorAll('.custom-select-container-groups input[type="checkbox"]');

                checkboxes.forEach(function (cb) {
                    if (groupIds.indexOf(parseInt(cb.value)) !== -1) {
                        cb.checked = true;
                    }
                });

                if (typeof window.updateGroupsTriggerText === 'function') {
                    window.updateGroupsTriggerText();
                }
            })
            .catch(function (err) {
                console.error('[AdditionalsBundle] Error loading item:', err);
                alert('Erro ao carregar dados.');
                window.closeItemModal();
            });
    };

    window.closeItemModal = function () {
        const modal = document.getElementById('itemModal');
        if (modal) modal.style.display = 'none';
    };

    // ==========================================
    // 2. HELPER FUNCTIONS (SEMPRE DISPONÍVEIS)
    // ==========================================

    window.formatCurrency = function (input) {
        var value = input.value.replace(/\D/g, "");
        value = (Number(value) / 100).toLocaleString("pt-BR", { minimumFractionDigits: 2 });
        input.value = value;
    };

    window.toggleItemFree = function (checkbox) {
        var input = document.getElementById('itemPriceInput');
        if (!input) return;

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
    };

    window.currentAdditionalView = 'groups';

    window.setAdditionalView = function (view, btn) {
        window.currentAdditionalView = view;
        var viewGroups = document.getElementById('view-groups');
        var viewItems = document.getElementById('view-items');
        var buttons = document.querySelectorAll('.stock-view-btn');

        buttons.forEach(function (b) { b.classList.remove('active'); });
        if (btn) btn.classList.add('active');

        if (view === 'groups') {
            if (viewGroups) viewGroups.style.display = 'block';
            if (viewItems) viewItems.style.display = 'none';
        } else {
            if (viewGroups) viewGroups.style.display = 'none';
            if (viewItems) viewItems.style.display = 'block';
        }

        var searchVal = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
        if (typeof window.handleSearch === 'function') {
            window.handleSearch(searchVal);
        }
    };

    window.handleSearch = function (query) {
        var q = (query || '').toLowerCase().trim();

        if (window.currentAdditionalView === 'groups') {
            var cards = document.querySelectorAll('.group-card');
            cards.forEach(function (card) {
                var name = (card.dataset.name || '').toLowerCase();
                card.style.display = name.indexOf(q) !== -1 ? 'block' : 'none';
            });
        } else {
            var items = document.querySelectorAll('.item-card-row');
            items.forEach(function (item) {
                var name = (item.dataset.name || '').toLowerCase();
                item.style.display = name.indexOf(q) !== -1 ? 'flex' : 'none';
            });
        }
    };

    // ==========================================
    // 3. MULTISELECT WRAPPERS (COM FALLBACK)
    // ==========================================
    // Funções wrapper que funcionam mesmo sem MultiSelect

    window.toggleGroupItemsSelect = function (el) {
        if (window.MultiSelect) {
            MultiSelect.toggle(el, 'group-items');
        }
    };

    window.updateGroupItemsTriggerText = function () {
        if (window.MultiSelect) {
            MultiSelect.updateTriggerText('group-items', 'Item', 'Item(ns)', 'Selecione os itens...');
        }
    };

    window.toggleItemsSelect = function (el) {
        if (window.MultiSelect) {
            MultiSelect.toggle(el, 'items');
        }
    };

    window.updateItemsTriggerText = function () {
        if (window.MultiSelect) {
            MultiSelect.updateTriggerText('items', 'item', 'item(ns)', 'Selecione os itens...');
        }
    };

    window.toggleGroupsSelect = function (el) {
        if (window.MultiSelect) {
            MultiSelect.toggle(el, 'groups');
        }
    };

    window.updateGroupsTriggerText = function () {
        if (window.MultiSelect) {
            MultiSelect.updateTriggerText('groups', 'Grupo', 'Grupo(s)', 'Selecione os grupos...');
        }
    };

    // ==========================================
    // 4. INICIALIZAÇÃO (APÓS DOM ESTAR PRONTO)
    // ==========================================

    function initAdditionals() {
        console.log('[AdditionalsBundle] Initializing...');

        // Inicializa click-outside para MultiSelect se disponível
        if (window.MultiSelect && typeof MultiSelect.initClickOutside === 'function') {
            MultiSelect.initClickOutside(['group-items', 'items', 'groups']);
        }

        // Event Delegation para botões dinâmicos
        document.addEventListener('click', handleDelegatedClicks);

        // Click outside para fechar modais
        var modalsToCheck = ['groupModal', 'linkModal', 'itemModal'];
        modalsToCheck.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.onclick = function (e) {
                    if (e.target === this) {
                        this.style.display = 'none';
                    }
                };
            }
        });

        // Reinit Lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }

        console.log('[AdditionalsBundle] Initialized successfully.');
    }

    /**
     * Handler de event delegation para cliques
     */
    function handleDelegatedClicks(e) {
        // Botão "Itens" - Vincular itens ao grupo
        var btnLink = e.target.closest('.btn-action-link');
        if (btnLink) {
            e.preventDefault();
            var groupId = btnLink.dataset.groupId;
            var groupName = btnLink.dataset.groupName;

            // Extrair IDs dos itens já vinculados
            var linkedItemIds = [];
            try {
                var itemIdsJson = btnLink.dataset.itemIds;
                if (itemIdsJson) {
                    linkedItemIds = JSON.parse(itemIdsJson).map(function (id) {
                        return parseInt(id);
                    });
                }
            } catch (err) {
                console.warn('[AdditionalsBundle] Erro ao parsear item IDs:', err);
            }

            window.openLinkModal(groupId, groupName, linkedItemIds);
            return;
        }
    }

    // ==========================================
    // 5. BOOTSTRAP
    // ==========================================
    // Executar inicialização imediatamente
    // As funções já estão no window, apenas configura event listeners

    // Aguarda um tick para garantir que o DOM foi atualizado pelo SPA
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdditionals);
    } else {
        // DOM já carregado (contexto SPA)
        setTimeout(initAdditionals, 0);
    }

})();
