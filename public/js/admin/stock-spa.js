/**
 * StockSPA - Módulo de Navegação SPA para Catálogo/Estoque
 * 
 * Gerencia navegação entre abas via AJAX sem reload.
 * 
 * @version 2.1 - Com cache de abas
 */
window.StockSPA = {
    currentTab: 'produtos',
    isLoading: false,
    tabCache: {}, // Cache para armazenar HTML das abas já carregadas

    /**
     * Inicializa o módulo SPA
     */
    init() {
        // [StockSPA] Initializing...
        this.bindTabs();
        this.loadInitialTab();
        this.bindCategoryChips();
        this.bindSearch();

        // Re-init após popstate (back/forward)
        window.addEventListener('popstate', () => this.loadFromHash());
    },

    /**
     * Adiciona event listeners nas abas
     */
    bindTabs() {
        const tabsContainer = document.getElementById('stock-spa-tabs');
        if (!tabsContainer) {
            console.warn('[StockSPA] Tabs container not found');
            return;
        }

        tabsContainer.addEventListener('click', (e) => {
            const tab = e.target.closest('.stock-tab');
            if (!tab || tab.classList.contains('active') || this.isLoading) return;

            const tabName = tab.dataset.tab;
            this.loadTab(tabName);
        });
    },

    /**
     * Carrega aba inicial (do hash ou padrão)
     */
    loadInitialTab() {
        const hash = window.location.hash.replace('#', '');
        const validTabs = ['produtos', 'categorias', 'adicionais', 'reposicao', 'movimentacoes'];

        const initialTab = validTabs.includes(hash) ? hash : 'produtos';
        this.loadTab(initialTab);
    },

    /**
     * Carrega a aba a partir do hash da URL
     */
    loadFromHash() {
        const hash = window.location.hash.replace('#', '');
        if (hash && hash !== this.currentTab) {
            this.loadTab(hash);
        }
    },

    /**
     * Carrega uma aba via AJAX (com cache, skeleton loader e requestAnimationFrame)
     */
    async loadTab(tabName) {
        if (this.isLoading) return;

        const contentContainer = document.getElementById('stock-content');

        if (!contentContainer) {
            console.error('[StockSPA] Content container not found');
            return;
        }

        this.currentTab = tabName;
        this.updateActiveTab(tabName);
        history.replaceState(null, null, `#${tabName}`);

        // Se existe no cache, usa direto (instantâneo!) com requestAnimationFrame
        if (this.tabCache[tabName]) {
            requestAnimationFrame(() => {
                contentContainer.innerHTML = this.tabCache[tabName];
                this.executeScripts(contentContainer);
                this.reinitComponents();
            });
            return;
        }

        // Mostra SKELETON LOADER (não spinner) - UX profissional
        this.isLoading = true;
        this.showSkeleton(contentContainer, tabName);

        try {
            const url = `${BASE_URL}/admin/loja/catalogo/partial/${tabName}`;

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();

            // Salva no cache
            this.tabCache[tabName] = html;

            // Usa requestAnimationFrame para render suave (evita bloquear thread principal)
            requestAnimationFrame(() => {
                contentContainer.innerHTML = html;
                this.executeScripts(contentContainer);
                this.reinitComponents();
            });

            console.log('[StockSPA] Tab loaded and cached:', tabName);

        } catch (error) {
            console.error('[StockSPA] Error loading tab:', error);
            contentContainer.innerHTML = `
                <div class="stock-error" style="padding: 3rem; text-align: center; color: #dc2626;">
                    <i data-lucide="alert-circle" style="width: 48px; height: 48px; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px;">Erro ao carregar</h3>
                    <p style="color: #6b7280; margin-bottom: 20px;">Não foi possível carregar o conteúdo.</p>
                    <button onclick="StockSPA.loadTab('${tabName}')" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        Tentar Novamente
                    </button>
                </div>
            `;
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Mostra skeleton loader apropriado para cada tipo de aba
     */
    showSkeleton(container, tabName) {
        const isGridTab = ['produtos', 'reposicao', 'adicionais'].includes(tabName);

        if (isGridTab) {
            // Skeleton para grid de cards
            container.innerHTML = `
                <div class="skeleton-container">
                    <div class="skeleton-header"></div>
                    <div class="skeleton-chips">
                        <div class="skeleton-chip"></div>
                        <div class="skeleton-chip"></div>
                        <div class="skeleton-chip"></div>
                        <div class="skeleton-chip"></div>
                    </div>
                    <div class="skeleton-grid">
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                    </div>
                </div>
            `;
        } else {
            // Skeleton para tabelas (categorias, movimentações)
            container.innerHTML = `
                <div class="skeleton-container">
                    <div class="skeleton-header"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                </div>
            `;
        }
    },

    /**
     * Limpa o cache (útil após criar/editar/deletar)
     */
    clearCache(tabName = null) {
        if (tabName) {
            delete this.tabCache[tabName];
        } else {
            this.tabCache = {};
        }
    },

    /**
     * Atualiza visual da aba ativa
     */
    updateActiveTab(tabName) {
        const tabs = document.querySelectorAll('#stock-spa-tabs .stock-tab');
        tabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });
    },

    /**
     * Re-inicializa componentes após AJAX load
     */
    reinitComponents() {
        // Lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }

        // Re-bind eventos específicos do conteúdo
        this.bindCategoryChips();
        this.bindSearch();
    },

    /**
     * Bind category chips filtering
     */
    bindCategoryChips() {
        document.querySelectorAll('.category-chip').forEach(chip => {
            chip.addEventListener('click', function () {
                // Remove active de todos
                document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                const category = this.dataset.category || '';
                StockSPA.filterByCategory(category);
            });
        });
    },

    /**
     * Filtra produtos por categoria
     */
    filterByCategory(category) {
        const cards = document.querySelectorAll('.product-row');
        cards.forEach(card => {
            const cardCategory = card.dataset.category || '';
            const isMatch = !category || cardCategory === category;
            card.style.display = isMatch ? '' : 'none';
        });
    },

    /**
     * Bind search input
     */
    bindSearch() {
        const searchInput = document.getElementById('searchProduct');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                StockSPA.filterProducts(this.value);
            });
        }
    },

    /**
     * Filtra produtos por busca de texto
     */
    filterProducts(searchTerm = '') {
        const term = (searchTerm || document.getElementById('searchProduct')?.value || '').toLowerCase();
        const cards = document.querySelectorAll('.product-row');

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const isMatch = name.includes(term);
            card.style.display = isMatch ? '' : 'none';
        });
    },

    /**
     * Filtra categorias por texto
     */
    filterCategories(searchTerm) {
        const term = searchTerm.toLowerCase();
        document.querySelectorAll('.category-row').forEach(row => {
            const name = row.dataset.name || '';
            row.style.display = name.includes(term) ? '' : 'none';
        });
    },

    // =========================================================================
    // MODALS E AÇÕES
    // =========================================================================

    /**
     * Modal de Exclusão de Produto
     */
    openDeleteModal(id, name) {
        const modal = document.getElementById('deleteModal');
        if (!modal) return;

        document.getElementById('deleteProductName').textContent = name;
        document.getElementById('deleteConfirmBtn').href = `${BASE_URL}/admin/loja/produtos/deletar?id=${id}`;
        modal.style.display = 'flex';
    },

    closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (modal) modal.style.display = 'none';
    },

    /**
     * Modal de Nova Categoria
     */
    openCategoryModal() {
        const modal = document.getElementById('categoryModal');
        if (modal) modal.style.display = 'flex';
    },

    closeCategoryModal() {
        const modal = document.getElementById('categoryModal');
        if (modal) modal.style.display = 'none';
    },

    /**
     * Modal de Ajuste de Estoque
     */
    openAdjustModal(id, name, currentStock) {
        document.getElementById('adjustProductId').value = id;
        document.getElementById('adjustProductName').textContent = name;
        document.getElementById('adjustCurrentStock').textContent = currentStock;
        document.getElementById('adjustAmount').value = 0;

        const modal = document.getElementById('adjustModal');
        if (modal) modal.style.display = 'flex';
    },

    closeAdjustModal() {
        const modal = document.getElementById('adjustModal');
        if (modal) modal.style.display = 'none';
    },

    adjustAmount(delta) {
        const input = document.getElementById('adjustAmount');
        input.value = parseInt(input.value || 0) + delta;
    },

    async submitAdjust() {
        const productId = document.getElementById('adjustProductId').value;
        const amount = parseInt(document.getElementById('adjustAmount').value);

        if (!productId || amount === 0) {
            alert('Informe uma quantidade válida');
            return;
        }

        try {
            const response = await fetch(`${BASE_URL}/admin/loja/reposicao/ajustar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ product_id: productId, amount: amount })
            });

            const result = await response.json();

            if (result.success) {
                this.closeAdjustModal();
                this.loadTab('reposicao'); // Recarrega a aba
            } else {
                alert(result.message || 'Erro ao ajustar estoque');
            }
        } catch (error) {
            console.error('[StockSPA] Adjust error:', error);
            alert('Erro ao processar ajuste');
        }
    },

    /**
     * Executa scripts manualmente após inserção via innerHTML
     * Suporta data-spa-once="true" para evitar re-execução de libs/listeners globais
     */
    executeScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            // Verifica scripts únicos (libs/globais)
            // Requer que o script tenha data-spa-script definido para identificação
            if (oldScript.dataset.spaOnce === 'true' && oldScript.dataset.spaScript) {
                if (document.querySelector(`script[data-spa-script="${oldScript.dataset.spaScript}"]`)) {
                    console.log('[StockSPA] Skipping once-only script:', oldScript.dataset.spaScript);
                    return;
                }
            }

            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            if (!oldScript.src) {
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            }
            oldScript.parentNode.replaceChild(newScript, oldScript);

            // Se for once, movemos para o head ou mantemos onde está?
            // replaceChild mantém no DOM, então querySelector vai achar.
        });
    },


};

// ============================================================================
// INICIALIZAÇÃO
// ============================================================================
document.addEventListener('DOMContentLoaded', () => {
    StockSPA.init();
});
