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

    // Monitora redimensionamento (teclado virtual)
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', onVV);
        window.visualViewport.addEventListener('scroll', onVV);
    }

    // Ajuste de padding para inputs quando focados
    document.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('focus', () => {
            adjustPaddingForKeyboard(true);
            ensureVisible(input);
        });
        input.addEventListener('blur', () => {
            adjustPaddingForKeyboard(false);
        });
    });

    // 2. Busca e Filtros
    const searchInput = document.getElementById('cardapioSearchInput'); // ID corrigido conforme HTML
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    // 3. Listeners de Categoria
    const categoryButtons = document.querySelectorAll('.cardapio-category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const category = this.getAttribute('data-category');
            filterByCategory(category);
        });
    });
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
    const sections = document.querySelectorAll('.cardapio-category-section');
    const buttons = document.querySelectorAll('.cardapio-category-btn');

    // Visual botões
    buttons.forEach(btn => {
        if (btn.getAttribute('data-category') === categoryName) { // Comparação exata ou lógica de 'todos'
            btn.classList.add('active');
        } else if (categoryName === 'todos' && btn.getAttribute('data-category') === 'todos') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Fallback: se clicou em um botão que não é 'todos', remove active do 'todos'
    if (categoryName !== 'todos') {
        const btnTodos = document.querySelector('.cardapio-category-btn[data-category="todos"]');
        if (btnTodos) btnTodos.classList.remove('active');
    }

    // Filtra seções
    sections.forEach(sec => {
        const secId = sec.getAttribute('data-category-id');

        // Se for 'todos', mostra tudo. Se não, mostra só se o nome bater.
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
                const id = card.getAttribute('data-product-id');
                if (id) {
                    if (window.openProductModal) window.openProductModal(id);
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
