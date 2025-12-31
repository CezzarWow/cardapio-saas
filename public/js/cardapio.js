/**
 * CARDAPIO.JS - Ponto de Entrada (Main Entry Point)
 * Refatorado em Módulos: Utils, Cart, Modals, Checkout
 */

// Referências Globais para Debug e Compatibilidade
// window.CardapioCart, window.CardapioModals, etc já estão definidos nos módulos.

document.addEventListener('DOMContentLoaded', function () {
    console.log('[MAIN] Inicializando Cardápio...');

    // Inicialização dos Módulos (se tiverem init)
    if (window.CardapioModals && CardapioModals.init) CardapioModals.init();
    if (window.CardapioCheckout && CardapioCheckout.init) CardapioCheckout.init();

    initializeEventListeners();

    // Recupera carrinho se tiver (futuro: localStorage)
    if (window.CardapioCart) CardapioCart.updateUI();

    // Ícones
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

// ========== EVENT LISTENERS GLOBAIS ==========
// ========== EVENT LISTENERS GLOBAIS ==========
function initializeEventListeners() {
    // 1. Scroll e Ajustes de Viewport (Mantido do original)
    // Esses hacks são específicos para Safari iOS e teclados virtuais

    // Monitora redimensionamento (teclado virtual) - só se a função existir
    if (window.visualViewport && typeof onVV === 'function') {
        window.visualViewport.addEventListener('resize', onVV);
        window.visualViewport.addEventListener('scroll', onVV);
    }

    // Ajuste de padding para inputs quando focados - só se as funções existirem
    if (typeof adjustPaddingForKeyboard === 'function' && typeof ensureVisible === 'function') {
        document.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('focus', () => {
                adjustPaddingForKeyboard(true);
                ensureVisible(input);
            });
            input.addEventListener('blur', () => {
                adjustPaddingForKeyboard(false);
            });
        });
    }

    // 2. Busca e Filtros
    const searchInput = document.getElementById('cardapioSearchInput'); // ID corrigido conforme HTML
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    // 3. Listeners de Categoria
    const categoryButtons = document.querySelectorAll('.cardapio-category-btn');
    console.log('[MAIN] Botões de categoria encontrados:', categoryButtons.length);

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const category = this.getAttribute('data-category');
            console.log('[MAIN] Clique na categoria:', category);
            filterByCategory(category);
        });
    });

    // Fallback: Event Delegation (caso os listeners diretos não funcionem)
    const categoriesContainer = document.querySelector('.cardapio-categories');
    if (categoriesContainer) {
        categoriesContainer.addEventListener('click', function (e) {
            const btn = e.target.closest('.cardapio-category-btn');
            if (btn) {
                e.preventDefault();
                const category = btn.getAttribute('data-category');
                console.log('[DELEGATION] Clique na categoria:', category);
                filterByCategory(category);
            }
        });
    }
}

// ... (Funções de UI mantidas) ...

// ========== BUSCA E FILTROS ==========

function handleSearch(e) {
    const term = e.target.value.toLowerCase();
    const products = document.querySelectorAll('.cardapio-product-card');
    const categories = document.querySelectorAll('.cardapio-category-section');

    products.forEach(product => {
        const name = (product.getAttribute('data-product-name') || '').toLowerCase();
        const desc = (product.getAttribute('data-product-description') || '').toLowerCase(); // Garante busca na descrição

        if (name.includes(term) || desc.includes(term)) {
            product.style.display = 'flex';
        } else {
            product.style.display = 'none';
        }
    });

    // Esconde categorias vazias
    categories.forEach(category => {
        const visibleProducts = category.querySelectorAll('.cardapio-product-card[style="display: flex;"]');
        if (visibleProducts.length === 0 && term !== '') {
            category.style.display = 'none';
        } else {
            category.style.display = 'block';
        }
    });
}

function filterByCategory(categoryName) {
    console.log('[FILTER] Filtrando por categoria:', categoryName);

    const sections = document.querySelectorAll('.cardapio-category-section');
    const buttons = document.querySelectorAll('.cardapio-category-btn');

    // Visual botões - Simplificado
    buttons.forEach(btn => {
        const btnCategory = btn.getAttribute('data-category');
        if (btnCategory === categoryName) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Filtra seções
    sections.forEach(sec => {
        const secId = sec.getAttribute('data-category-id');
        console.log('[FILTER] Seção:', secId, '| Categoria clicada:', categoryName);

        // Se for 'todos', mostra tudo. Se não, mostra só se o ID bater.
        if (categoryName === 'todos' || secId === categoryName) {
            sec.style.display = 'block';
        } else {
            sec.style.display = 'none';
        }
    });

    // Reset da busca ao filtrar
    const searchInput = document.getElementById('cardapioSearchInput');
    if (searchInput) {
        searchInput.value = '';
        // Reseta display dos produtos
        document.querySelectorAll('.cardapio-product-card').forEach(p => p.style.display = 'flex');
    }
}

// 3. LISTENERS PARA BOTÕES DE ADICIONAR (Restaurado)
// A função original permitia que o botão "+" abrisse o modal explicitamente
function initAddButtons() {
    document.querySelectorAll('.cardapio-add-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation(); // Evita duplo clique se o card pai tiver onclick

            // Busca o ID do pai
            const card = this.closest('.cardapio-product-card');
            if (card) {
                const productId = card.getAttribute('data-product-id');
                const comboId = card.getAttribute('data-combo-id');

                if (productId) {
                    if (window.openProductModal) window.openProductModal(productId);
                } else if (comboId) {
                    // Suporte a Combos
                    if (window.CardapioModals && CardapioModals.openCombo) {
                        CardapioModals.openCombo(comboId);
                    }
                }
            }
        });
    });
}
// Chama na inicialização
document.addEventListener('DOMContentLoaded', initAddButtons); // Adiciona ao ciclo

// Expor filtro globalmente
window.filterByCategory = filterByCategory;

console.log('[MAIN] Cardápio carregado e módulos integrados.');
