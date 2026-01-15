/**
 * Stock Dashboard - JavaScript
 * Filtros e modais para a página de estoque
 */
const StockDashboard = (function () {
    'use strict';

    let selectedCategory = '';

    // =========================================================================
    // FILTROS
    // =========================================================================

    function filterProducts() {
        const searchInput = document.getElementById('searchProduct');
        const search = searchInput ? searchInput.value.toLowerCase() : '';
        const rows = document.querySelectorAll('.product-row');

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const cat = row.dataset.category || '';

            const matchName = name.includes(search);
            const matchCategory = !selectedCategory || cat === selectedCategory;

            row.style.display = (matchName && matchCategory) ? '' : 'none';
        });
    }

    function initCategoryChips() {
        document.querySelectorAll('.category-chip').forEach(chip => {
            chip.addEventListener('click', function () {
                // Remove active de todos
                document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
                // Adiciona no clicado
                this.classList.add('active');

                // Atualiza categoria selecionada
                selectedCategory = this.dataset.category || '';

                // Aplica filtro
                filterProducts();
            });
        });
    }

    // =========================================================================
    // MODAL DE EXCLUSÃO
    // =========================================================================

    function openDeleteModal(productId, productName, baseUrl) {
        const modal = document.getElementById('deleteModal');
        const nameEl = document.getElementById('deleteProductName');
        const btn = document.getElementById('deleteConfirmBtn');

        if (nameEl) nameEl.textContent = productName;
        if (btn) btn.href = baseUrl + '/admin/loja/produtos/deletar?id=' + productId;
        if (modal) modal.style.display = 'flex';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (modal) modal.style.display = 'none';
    }

    function initDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === this) closeDeleteModal();
            });
        }
    }

    // =========================================================================
    // TAB TRANSITIONS (Navegação Suave)
    // =========================================================================

    function initTabTransitions() {
        const tabs = document.querySelectorAll('.stock-tab');

        tabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                // Se for a aba ativa, não faz nada
                if (this.classList.contains('active')) {
                    e.preventDefault();
                    return;
                }

                // Previne navegação imediata
                e.preventDefault();
                const targetUrl = this.href;

                // Adiciona classe de animação de saída
                const mainContent = document.querySelector('.main-content');
                if (mainContent) {
                    mainContent.classList.add('navigating');

                    // Espera a animação terminar antes de navegar
                    setTimeout(() => {
                        window.location.href = targetUrl;
                    }, 150); // Mesmo tempo da animação CSS
                } else {
                    // Fallback se não encontrar o container
                    window.location.href = targetUrl;
                }
            });
        });
    }

    // =========================================================================
    // INICIALIZAÇÃO
    // =========================================================================

    function init() {
        initCategoryChips();
        initDeleteModal();
        initTabTransitions();

        // Bind do input de busca
        const searchInput = document.getElementById('searchProduct');
        if (searchInput) {
            searchInput.addEventListener('input', filterProducts);
        }
    }

    // Auto-init quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // API pública
    return {
        filterProducts,
        openDeleteModal,
        closeDeleteModal
    };
})();

// Expor funções globais para onclick inline (compatibilidade)
function filterProducts() { StockDashboard.filterProducts(); }
function openDeleteModal(id, name, url) { StockDashboard.openDeleteModal(id, name, url); }
function closeDeleteModal() { StockDashboard.closeDeleteModal(); }
