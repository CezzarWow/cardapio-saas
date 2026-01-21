/**
 * ADDITIONALS-BUNDLE.JS
 * 
 * Script unificado para a aba de Adicionais (Estoque).
 * Consolida lógica de:
 * - Orchestrator (additionals.js)
 * - UI Helpers (additionals-ui.js)
 * - Group Modal (additionals-group-modal.js)
 * - Item Modal (additionals-item-modal.js)
 * 
 * Removeu-se a lógica de "Vincular Categoria" conforme solicitado.
 */

(function () {
    'use strict';

    // ==========================================
    // 1. INICIALIZAÇÃO ROBUSTA
    // ==========================================
    function waitForMultiSelect(callback, attempts) {
        attempts = attempts || 0;
        if (typeof window.MultiSelect !== 'undefined') {
            callback();
        } else if (attempts < 40) { // 2 segundos max
            setTimeout(function () { waitForMultiSelect(callback, attempts + 1); }, 50);
        } else {
            console.error('[AdditionalsBundle] MultiSelect não carregou após 2 segundos.');
        }
    }

    waitForMultiSelect(initAdditionals);

    function initAdditionals() {
        console.log('[AdditionalsBundle] Initializing...');

        // ==========================================
        // 2. ESTADO E UI HELPERS
        // ==========================================
        window.currentAdditionalView = 'groups';

        window.setAdditionalView = function (view, btn) {
            window.currentAdditionalView = view;
            const viewGroups = document.getElementById('view-groups');
            const viewItems = document.getElementById('view-items');
            const buttons = document.querySelectorAll('.stock-view-btn');

            buttons.forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');

            if (view === 'groups') {
                if (viewGroups) viewGroups.style.display = 'block';
                if (viewItems) viewItems.style.display = 'none';
            } else {
                if (viewGroups) viewGroups.style.display = 'none';
                if (viewItems) viewItems.style.display = 'block';
            }

            const searchVal = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
            handleSearch(searchVal);
        };

        window.handleSearch = function (query) {
            const q = query.toLowerCase().trim();

            if (window.currentAdditionalView === 'groups') {
                const cards = document.querySelectorAll('.group-card');
                cards.forEach(card => {
                    const name = card.dataset.name || '';
                    card.style.display = name.includes(q) ? 'block' : 'none';
                });
            } else {
                const items = document.querySelectorAll('.item-card-row');
                items.forEach(item => {
                    const name = item.dataset.name || '';
                    item.style.display = name.includes(q) ? 'flex' : 'none';
                });
            }
        };

        window.formatCurrency = function (input) {
            let value = input.value.replace(/\D/g, "");
            value = (Number(value) / 100).toLocaleString("pt-BR", { minimumFractionDigits: 2 });
            input.value = value;
        };

        window.toggleItemFree = function (checkbox) {
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
        };

        // ==========================================
        // 3. MULTI-SELECT HELPERS (Compatibilidade)
        // ==========================================
        window.toggleGroupItemsSelect = function (el) { MultiSelect.toggle(el, 'group-items'); };
        window.updateGroupItemsTriggerText = function () { MultiSelect.updateTriggerText('group-items', 'Item', 'Item(ns)', 'Selecione os itens...'); };

        // Categoria removida: window.toggleCategorySelect, window.updateCategoryTriggerText

        window.toggleItemsSelect = function (el) { MultiSelect.toggle(el, 'items'); };
        window.updateItemsTriggerText = function () { MultiSelect.updateTriggerText('items', 'item', 'item(ns)', 'Selecione os itens...'); };

        window.toggleGroupsSelect = function (el) { MultiSelect.toggle(el, 'groups'); };
        window.updateGroupsTriggerText = function () { MultiSelect.updateTriggerText('groups', 'Grupo', 'Grupo(s)', 'Selecione os grupos...'); };

        // Inicializa click-outside
        MultiSelect.initClickOutside(['group-items', 'items', 'groups']); // 'cat' removido

        // ==========================================
        // 4. MODAIS DE GRUPO E VINCULAR
        // ==========================================
        window.openGroupModal = function () {
            const modal = document.getElementById('groupModal');
            if (modal) {
                modal.style.display = 'flex';
                MultiSelect.reset('group-items');
                if (typeof updateGroupItemsTriggerText === 'function') updateGroupItemsTriggerText();
            }
        };

        window.closeGroupModal = function () {
            const modal = document.getElementById('groupModal');
            if (modal) modal.style.display = 'none';
        };

        window.openLinkModal = function (groupId, groupName) {
            const modal = document.getElementById('linkModal');
            if (modal) {
                document.getElementById('linkGroupId').value = groupId;
                document.getElementById('linkGroupName').textContent = 'Grupo: ' + groupName;
                modal.style.display = 'flex';

                MultiSelect.reset('items');
                if (typeof updateItemsTriggerText === 'function') updateItemsTriggerText();

                const list = document.querySelector('.options-list-items');
                if (list) list.style.display = 'none';
            }
        };

        window.closeLinkModal = function () {
            const modal = document.getElementById('linkModal');
            if (modal) modal.style.display = 'none';
        };

        // Modal de Categoria REMOVIDO (openLinkCategoryModal, closeLinkCategoryModal)

        // ==========================================
        // 5. MODAL DE ITEM (CRUD)
        // ==========================================
        window.openCreateItemModal = function () {
            const form = document.getElementById('itemForm');
            if (form) {
                form.reset();
                form.action = window.BASE_URL + '/admin/loja/adicionais/item/salvar-modal';
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

            MultiSelect.reset('groups');
            if (typeof updateGroupsTriggerText === 'function') updateGroupsTriggerText();

            const modal = document.getElementById('itemModal');
            if (modal) modal.style.display = 'flex';
        };

        window.openItemModal = function () {
            openCreateItemModal();
        };

        window.openEditItemModal = function (id) {
            document.getElementById('itemForm').reset();
            document.getElementById('itemModalTitle').textContent = 'Carregando...';
            const modal = document.getElementById('itemModal');
            if (modal) modal.style.display = 'flex';

            MultiSelect.reset('groups');
            if (typeof updateGroupsTriggerText === 'function') updateGroupsTriggerText();

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

                    document.getElementById('itemIdInputHidden').value = item.id;
                    document.getElementById('itemForm').action = window.BASE_URL + '/admin/loja/adicionais/item/atualizar-modal';
                    document.getElementById('itemModalTitle').textContent = 'Editar Item';

                    const nameInput = document.querySelector('input[name="name"]');
                    if (nameInput) nameInput.value = item.name;

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

                    const groupIds = groups.map(id => parseInt(id));
                    const checkboxes = document.querySelectorAll('.custom-select-container-groups input[type="checkbox"]');

                    checkboxes.forEach(cb => {
                        if (groupIds.includes(parseInt(cb.value))) {
                            cb.checked = true;
                        }
                    });
                    if (typeof updateGroupsTriggerText === 'function') updateGroupsTriggerText();

                })
                .catch(err => {
                    console.error(err);
                    alert('Erro ao carregar dados.');
                    closeItemModal();
                });
        };

        window.closeItemModal = function () {
            const modal = document.getElementById('itemModal');
            if (modal) modal.style.display = 'none';
        };

        // ==========================================
        // 6. EVENT DELEGATION E CLICK OUTSIDE
        // ==========================================

        // Listeners globais de clique (re-bind safe)
        // Remove listener antigo se existir (opcional, mas JS anônimo acumula se não cuidado, 
        // mas aqui estamos no contexto de SPA reload, então window limpa ou acumula? 
        // O ideal é usar { once: false } e confiar que o elemento pai é estático ou recriado.

        // Para garantir que não duplique logica ao recarregar via AJAX, 
        // o ideal seria limpar, mas como é função anonima, não dá pra remover.
        // A estratégia do SPA é substituir o main container.

        // Vamos usar delegation no document, mas verificar se o elemento ainda está no DOM atual.

        document.addEventListener('click', function (e) {
            // 1. Vincular Item ao Grupo
            const btnLink = e.target.closest('.btn-action-link');
            if (btnLink) {
                e.preventDefault();
                const groupId = btnLink.dataset.groupId;
                const groupName = btnLink.dataset.groupName;
                openLinkModal(groupId, groupName);
                return;
            }

            // vincular categoria removido...

            // 3. Excluir - Já é link direto com confirm, ou usa modal?
            // O código original usava openDeleteModal para itens e grupos em alguns casos,
            // mas no HTML do partial `_additionals.php` o botão de deletar grupo é um <a href> direto com onclick confirm.
            // Vou manter compatibilidade com o que vi no additionals.js original para itens se necessário via JS.
        });

        // Click Outside para Fechar Modais
        // Adiciona listeners apenas se os elementos existirem
        const modalsToCheck = ['groupModal', 'linkModal', 'itemModal']; // linkCategoryModal removido

        modalsToCheck.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                // Remove clone para limpar listeners antigos se o elemento foi preservado? 
                // Não, o elemento foi recriado pelo innerHTML do SPA.
                el.onclick = function (e) {
                    if (e.target === this) this.style.display = 'none';
                }
            }
        });

        // ==========================================
        // 7. FINALIZAÇÃO
        // ==========================================
        if (window.lucide) lucide.createIcons();
    }

})();
