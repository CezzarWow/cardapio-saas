/**
 * ============================================
 * CARDÁPIO ADMIN - Featured (Destaques)
 * Namespace CardapioAdmin.Destaques
 * ============================================
 */

(function (CardapioAdmin) {

    // [DESTAQUES] Namespace para aba Destaques
    CardapioAdmin.Destaques = {
        draggedItem: null,

        /**
         * Ativa modo de edição para produtos
         */
        enableEditMode: function () {
            const container = document.querySelector('.cardapio-admin-destaques-content-wrapper');
            const editBtn = document.querySelector('.cardapio-admin-btn-edit');
            const saveGroup = document.querySelector('.cardapio-admin-save-group');
            const viewHint = document.querySelector('.view-hint');
            const editHint = document.querySelector('.edit-hint');

            if (container) {
                container.classList.remove('disabled-overlay');
                // Habilita drag and drop
                container.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(card => {
                    card.setAttribute('draggable', 'true');
                    const handle = card.querySelector('.cardapio-admin-destaques-drag-handle');
                    if (handle) handle.style.display = 'block';
                });
            }

            if (editBtn) editBtn.style.display = 'none';
            if (saveGroup) saveGroup.style.display = 'flex';
            if (viewHint) viewHint.style.display = 'none';
            if (editHint) editHint.style.display = 'inline';

            // Re-inicializa ícones pois alteramos visibilidade
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        /**
         * Cancela edição
         */
        cancelEditMode: function () {
            // Recarrega a página para desfazer alterações visuais
            window.location.reload();
        },

        /**
         * Salva alterações de destaques (ordem e seleção)
         */
        saveDestaques: function () {
            // Submete o formulário principal
            const form = document.getElementById('formCardapio');
            if (form) form.submit();
        },

        /* --- Drag and Drop Logic --- */

        draggedItem: null,

        dragStart: function (e) {
            CardapioAdmin.Destaques.draggedItem = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', ''); // Necessário para Firefox
            setTimeout(() => {
                CardapioAdmin.Destaques.draggedItem.classList.add('dragging');
            }, 0);
        },

        dragOver: function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const target = e.currentTarget;
            const draggedItem = CardapioAdmin.Destaques.draggedItem;

            if (target && draggedItem && target !== draggedItem && target.classList.contains('cardapio-admin-destaques-product-card')) {
                // Remove indicador de outros
                document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
                target.classList.add('drag-over');
            }
        },

        drop: function (e) {
            e.stopPropagation();
            e.preventDefault();

            const target = e.currentTarget;
            const draggedItem = CardapioAdmin.Destaques.draggedItem;

            if (draggedItem && target && draggedItem !== target) {
                // Verifica se estão na mesma área (aba)
                const sourceArea = draggedItem.closest('.cardapio-admin-destaques-products-grid');
                const targetArea = target.closest('.cardapio-admin-destaques-products-grid');

                if (sourceArea === targetArea) {
                    // Determina a posição relativa
                    const allCards = Array.from(targetArea.querySelectorAll('.cardapio-admin-destaques-product-card'));
                    const draggedIndex = allCards.indexOf(draggedItem);
                    const targetIndex = allCards.indexOf(target);

                    if (draggedIndex < targetIndex) {
                        // Movendo para baixo - insere depois do target
                        targetArea.insertBefore(draggedItem, target.nextSibling);
                    } else {
                        // Movendo para cima - insere antes do target
                        targetArea.insertBefore(draggedItem, target);
                    }

                    // Atualiza input hidden de ordem
                    CardapioAdmin.Destaques.updateProductOrder(targetArea);
                }
            }

            // Limpa estados
            document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
            return false;
        },

        dragEnd: function (e) {
            const draggedItem = CardapioAdmin.Destaques.draggedItem;
            if (draggedItem) {
                draggedItem.classList.remove('dragging');
            }
            document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
            CardapioAdmin.Destaques.draggedItem = null;
        },

        updateProductOrder: function (container) {
            const cards = container.querySelectorAll('.cardapio-admin-destaques-product-card');

            // Se estamos na aba Destaques (ou qualquer aba), a ordem visual aqui define o valor 'display_order'
            // Mas cuidado: se for aba Destaques, estamos definindo ordem entre eles.
            // Se for aba Categoria, estamos definindo ordem total.

            // Para resolver o problema de "não salva", vamos garantir que ao mover, TODOS os inputs deste produto recebam o novo valor.
            // Mas o valor do índice depende do contexto.

            // ESTRATÉGIA:
            // Vamos assumir que se o usuário está ordenando, ele quer essa prioridade.
            // Vamos atualizar apenas o input deste card por enquanto, mas vamos garantir que no SUBMIT, inputs duplicados não atrapalhem.
            // OU melhor: Vamos forçar que o input da aba Categoria receba o valor se mexermos na aba Destaque?

            // Se mexermos na aba Destaques, os itens ganham indices 0, 1, 2...
            // Se aplicarmos isso nos inputs da aba Categoria, esses produtos vão pro topo das categorias. Isso é bom.

            cards.forEach((card, index) => {
                const productId = card.dataset.productId;
                const newValue = index;

                // Atualiza TODOS os inputs referentes a este produto em qualquer aba
                // Input de ordem
                const inputs = document.querySelectorAll(`[data-order-input="${productId}"]`);
                inputs.forEach(input => {
                    input.value = newValue;
                });
            });
        },

        /* --- Tab Logic --- */

        /**
         * Troca entre abas de categorias
         * @param {string} categoryName Nome da categoria ou 'featured'
         */
        switchTab: function (categoryName) {
            // Atualiza botões das abas
            const tabs = document.querySelectorAll('.cardapio-admin-destaques-tab-btn');
            tabs.forEach(tab => {
                if (tab.dataset.categoryTab === categoryName) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            // Atualiza conteúdo das abas
            const contents = document.querySelectorAll('.cardapio-admin-destaques-tab-content');
            contents.forEach(content => {
                if (content.dataset.categoryContent === categoryName) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });

            // Re-inicializa ícones Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        },

        /**
         * Adiciona/remove produto dos destaques
         * @param {number} productId ID do produto
         */
        toggleHighlight: function (productId) {
            // Busca todos os cards e inputs deste produto
            const cards = document.querySelectorAll(`[data-product-id="${productId}"]`);
            // Buscar TODOS os inputs para este produto
            const inputs = document.querySelectorAll(`[data-featured-input="${productId}"]`);

            if (inputs.length === 0) return;

            // Verifica estado atual pelo primeiro input
            const isCurrentlyFeatured = inputs[0].checked;

            // Inverte estado em TODOS os inputs
            inputs.forEach(input => {
                input.checked = !isCurrentlyFeatured;
            });

            // Atualiza todos os cards deste produto
            cards.forEach(card => {
                const btn = card.querySelector('.cardapio-admin-destaques-highlight-btn');
                const star = card.querySelector('.cardapio-admin-destaques-star');

                if (!isCurrentlyFeatured) {
                    // Adicionando ao destaque
                    card.classList.add('featured');
                    if (btn) {
                        btn.classList.add('active');
                        btn.innerHTML = '<i data-lucide="x" style="width: 16px; height: 16px;"></i> Remover';
                    }
                    if (!star) {
                        const info = card.querySelector('.cardapio-admin-destaques-product-info');
                        if (info) {
                            const newStar = document.createElement('span');
                            newStar.className = 'cardapio-admin-destaques-star';
                            newStar.textContent = '⭐';
                            info.insertBefore(newStar, info.firstChild);
                        }
                    }
                } else {
                    // Removendo do destaque
                    card.classList.remove('featured');
                    if (btn) {
                        btn.classList.remove('active');
                        btn.innerHTML = '<i data-lucide="star" style="width: 16px; height: 16px;"></i> Destacar';
                    }
                    if (star) star.remove();
                }
            });

            // Re-inicializa ícones Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Atualiza aba Destaques
            this.refreshFeaturedTab();
        },

        /**
         * Atualiza a aba Destaques - adiciona novos e remove os desmarcados
         */
        refreshFeaturedTab: function () {
            const featuredContent = document.querySelector('[data-category-content="featured"]');
            if (!featuredContent) return;

            let grid = featuredContent.querySelector('.cardapio-admin-destaques-products-grid');
            const emptyMsg = featuredContent.querySelector('.cardapio-admin-destaques-empty');

            // Cria o grid se não existir
            if (!grid) {
                grid = document.createElement('div');
                grid.className = 'cardapio-admin-destaques-products-grid';
                grid.dataset.sortableArea = 'featured';
                featuredContent.insertBefore(grid, emptyMsg);
            }

            // Busca todos os inputs featured marcados
            const allFeaturedInputs = document.querySelectorAll('[data-featured-input]');

            allFeaturedInputs.forEach(input => {
                const productId = input.dataset.featuredInput;
                const isChecked = input.checked;
                const existsInFeaturedTab = grid.querySelector(`[data-product-id="${productId}"]`);

                if (isChecked && !existsInFeaturedTab) {
                    // Produto foi destacado - adicionar à aba Destaques
                    const sourceCard = document.querySelector(`[data-category-content]:not([data-category-content="featured"]) [data-product-id="${productId}"]`);
                    if (sourceCard) {
                        const clonedCard = sourceCard.cloneNode(true);
                        // Ajusta o botão para mostrar "Remover"
                        const btn = clonedCard.querySelector('.cardapio-admin-destaques-highlight-btn');
                        if (btn) {
                            btn.classList.add('active');
                            btn.innerHTML = '<i data-lucide="x" style="width: 16px; height: 16px;"></i> Remover';
                        }
                        // Adiciona estrela se não tiver
                        const info = clonedCard.querySelector('.cardapio-admin-destaques-product-info');
                        if (info && !info.querySelector('.cardapio-admin-destaques-star')) {
                            const star = document.createElement('span');
                            star.className = 'cardapio-admin-destaques-star';
                            star.textContent = '⭐';
                            info.insertBefore(star, info.firstChild);
                        }
                        // Remove inputs do clone (evita duplicatas)
                        clonedCard.querySelectorAll('input').forEach(inp => inp.remove());
                        grid.appendChild(clonedCard);
                    }
                } else if (!isChecked && existsInFeaturedTab) {
                    // Produto foi removido do destaque - remover da aba Destaques
                    existsInFeaturedTab.remove();
                }
            });

            // Re-inicializa ícones Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Mostra/esconde mensagem vazia
            const remainingCards = grid.querySelectorAll('.cardapio-admin-destaques-product-card').length;
            if (remainingCards === 0) {
                grid.style.display = 'none';
                if (emptyMsg) {
                    emptyMsg.style.display = 'block';
                } else {
                    grid.insertAdjacentHTML('afterend', '<p class="cardapio-admin-destaques-empty">Nenhum produto em destaque. Use as outras abas para adicionar.</p>');
                }
            } else {
                grid.style.display = 'grid';
                if (emptyMsg) emptyMsg.style.display = 'none';
            }
        },

        /**
         * Move categoria para cima ou para baixo
         * @param {number} categoryId ID da categoria
         * @param {string} direction 'up' ou 'down'
         */
        moveCategory: function (categoryId, direction) {
            const list = document.getElementById('categoryList');
            if (!list) return;

            const currentRow = list.querySelector(`[data-category-id="${categoryId}"]`);
            if (!currentRow) return;

            const rows = Array.from(list.querySelectorAll('.cardapio-admin-destaques-category-row'));
            const currentIndex = rows.indexOf(currentRow);

            if (direction === 'up' && currentIndex > 0) {
                // Troca com o anterior
                const prevRow = rows[currentIndex - 1];
                list.insertBefore(currentRow, prevRow);
            } else if (direction === 'down' && currentIndex < rows.length - 1) {
                // Troca com o próximo
                const nextRow = rows[currentIndex + 1];
                list.insertBefore(nextRow, currentRow);
            }

            // Atualiza inputs hidden e botões
            this.updateCategoryOrder();
        },

        /**
         * Atualiza os inputs hidden com a nova ordem
         * e desabilita/habilita setas conforme posição
         */
        updateCategoryOrder: function () {
            const list = document.getElementById('categoryList');
            if (!list) return;

            const rows = Array.from(list.querySelectorAll('.cardapio-admin-destaques-category-row'));

            rows.forEach((row, index) => {
                // Atualiza input hidden
                const input = row.querySelector('[data-order-input]');
                if (input) input.value = index;

                // Atualiza botões
                const btnUp = row.querySelector('.cardapio-admin-destaques-arrow-btn:first-child');
                const btnDown = row.querySelector('.cardapio-admin-destaques-arrow-btn:last-child');

                if (btnUp) btnUp.disabled = (index === 0);
                if (btnDown) btnDown.disabled = (index === rows.length - 1);
            });

            // Re-inicializa ícones Lucide nos botões
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
