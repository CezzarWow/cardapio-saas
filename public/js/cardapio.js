/**
 * CARDAPIO.JS - Ponto de Entrada (Main Entry Point)
 * Refatorado: Utils, Cart, Modals, Checkout, CardapioManager, CatalogVirtualizer
 */

(function () {
    'use strict';

    // Pequeno utilitário debounce para reduzir re-renders na busca
    function debounce(fn, wait) {
        let t = null;
        return function () {
            const args = arguments;
            const ctx = this;
            clearTimeout(t);
            t = setTimeout(() => fn.apply(ctx, args), wait);
        };
    }

    const CardapioManager = {
        // ==========================================
        // INICIALIZAÇÃO
        // ==========================================
        init: function () {
            this.initModules();
            this.bindEvents();
            this.recoverCart();
            this.initIcons();

            // Init Virtualization
            if (window.CatalogVirtualizer) window.CatalogVirtualizer.init();
        },

        initModules: function () {
            if (window.CardapioModals && CardapioModals.init) CardapioModals.init();
            if (window.CardapioCheckout && CardapioCheckout.init) CardapioCheckout.init();
        },

        recoverCart: function () {
            // Recupera carrinho se tiver (futuro: localStorage)
            if (window.CardapioCart) CardapioCart.updateUI();
        },

        initIcons: function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        // ==========================================
        // EVENTOS GLOBAIS
        // ==========================================
        bindEvents: function () {
            // 1. Viewport e Ajustes Mobile
            this.ui.bindViewportAdjustments();

            // 2. Busca
            const searchInput = document.getElementById('cardapioSearchInput');
            if (searchInput) {
                // Cache nodes once and debounce o handler para reduzir trabalho no input
                this.ui.cacheNodes(); // Cache inicial (apenas SSR items)
                const ui = this.ui;
                searchInput.addEventListener('input', debounce((e) => ui.handleSearch(e), 300));
            }

            // 3. Filtros
            this.ui.bindCategoryFilters();

            // 4. Botões de Adicionar (Event Delegation para suportar Virtualization)
            this.ui.bindAddButtons();
        },

        // ==========================================
        // UI - Lógica de Interface
        // ==========================================
        ui: {
            // --- Viewport ---
            bindViewportAdjustments: function () {
                // Monitora redimensionamento (teclado virtual)
                if (window.visualViewport && typeof window.onVV === 'function') {
                    window.visualViewport.addEventListener('resize', window.onVV);
                    window.visualViewport.addEventListener('scroll', window.onVV);
                }

                // Ajuste de padding para inputs
                if (typeof window.adjustPaddingForKeyboard === 'function' && typeof window.ensureVisible === 'function') {
                    document.querySelectorAll('input, textarea').forEach(input => {
                        input.addEventListener('focus', () => {
                            window.adjustPaddingForKeyboard(true);
                            window.ensureVisible(input);
                        });
                        input.addEventListener('blur', () => {
                            window.adjustPaddingForKeyboard(false);
                        });
                    });
                }
            },

            // --- Busca ---
            cacheNodes: function () {
                // Legacy cache logic - útil apenas onde já foi renderizado.
                try {
                    this._categories = Array.from(document.querySelectorAll('.cardapio-category-section'));
                } catch (err) {
                    this._categories = [];
                }
            },

            handleSearch: function (e) {
                const term = (e.target.value || '').toLowerCase().trim();
                const container = document.querySelector('.cardapio-products');

                // Se a busca estiver vazia, restaura o estado normal (Virtualizado) e reseta visibilidade
                if (term === '') {
                    if (container) container.classList.remove('searching-mode');
                    // Re-exibe todas as seções
                    document.querySelectorAll('.cardapio-category-section').forEach(sec => {
                        sec.style.display = 'block';
                        // Restaura produtos dentro delas
                        sec.querySelectorAll('.cardapio-product-card').forEach(p => p.style.display = 'flex');
                    });
                    return;
                }

                if (container) container.classList.add('searching-mode');

                // Lógica de busca global usando window.products (Memory Search)
                // 1. Oculta tudo visualmente
                const sections = document.querySelectorAll('.cardapio-category-section');
                sections.forEach(sec => sec.style.display = 'none');

                if (!window.products) return;

                // 2. Filtra produtos
                const matches = window.products.filter(p => {
                    const name = (p.name || '').toLowerCase();
                    const desc = (p.description || '').toLowerCase();
                    return name.includes(term) || desc.includes(term);
                });

                // 3. Agrupa matches por categoria
                const matchesByCat = matches.reduce((acc, p) => {
                    if (!acc[p.category]) acc[p.category] = [];
                    acc[p.category].push(p);
                    return acc;
                }, {});

                // 4. Exibe e Renderiza (se necessário) as categorias com matches
                Object.keys(matchesByCat).forEach(catName => {
                    // Encontra a seção da categoria
                    const section = Array.from(sections).find(s => {
                        // Tenta achar pelo data-lazy-category OU pelo título (caso SSR)
                        const lazyAttr = s.getAttribute('data-lazy-category');
                        if (lazyAttr === catName) return true;

                        // Fallback para SSR categories: tenta inferir pelo título ou id
                        // Como não temos mapeamento fácil ID->Nome aqui, vamos confiar na renderização
                        // Mas espera, as sections SSR não tem "data-lazy-category".
                        return false;
                    });

                    if (section) {
                        // É uma seção Lazy que tem matches.
                        // Precisamos renderizá-la SE ainda não estiver.
                        if (!section.classList.contains('rendered') && window.CatalogVirtualizer) {
                            window.CatalogVirtualizer.loadCategory(section);
                        }

                        // Agora filtramos visualmente os produtos DENTRO dela
                        section.style.display = 'block';
                        const productCards = section.querySelectorAll('.cardapio-product-card');
                        productCards.forEach(card => {
                            const pId = card.getAttribute('data-product-id');
                            const isMatch = matches.some(m => m.id == pId);
                            card.style.display = isMatch ? 'flex' : 'none';
                        });
                    }
                });

                // 5. Fallback para seções SSR (Combo/Featured/Default1) que não têm data-lazy-category
                sections.forEach(s => {
                    if (!s.getAttribute('data-lazy-category')) {
                        const cards = s.querySelectorAll('.cardapio-product-card');
                        let hasMatch = false;
                        cards.forEach(c => {
                            const cId = c.getAttribute('data-product-id') || c.getAttribute('data-combo-id');
                            let isMatch = false;

                            if (c.dataset.comboId) {
                                // Busca simples em combo (DOM-based)
                                const name = (c.dataset.comboName || '').toLowerCase();
                                if (name.includes(term)) isMatch = true;
                            } else {
                                // Busca em produtos
                                if (cId) isMatch = matches.some(m => m.id == cId);
                            }

                            c.style.display = isMatch ? 'flex' : 'none';
                            if (isMatch) hasMatch = true;
                        });

                        if (hasMatch) s.style.display = 'block';
                    }
                });
            },

            // --- Filtros ---
            bindCategoryFilters: function () {
                // Listeners diretos
                document.querySelectorAll('.cardapio-category-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.filterByCategory(btn.getAttribute('data-category'));
                    });
                });

                // Fallback: Event Delegation
                const container = document.querySelector('.cardapio-categories');
                if (container) {
                    container.addEventListener('click', (e) => {
                        const btn = e.target.closest('.cardapio-category-btn');
                        if (btn) {
                            e.preventDefault();
                            this.filterByCategory(btn.getAttribute('data-category'));
                        }
                    });
                }
            },

            filterByCategory: function (categoryName) {
                const sections = document.querySelectorAll('.cardapio-category-section');
                const buttons = document.querySelectorAll('.cardapio-category-btn');

                // Visual botões
                buttons.forEach(btn => {
                    const isActive = btn.getAttribute('data-category') === categoryName;
                    btn.classList.toggle('active', isActive);
                });

                // Filtra seções
                sections.forEach(sec => {
                    const secId = sec.getAttribute('data-category-id'); // ID da categoria
                    const isVisible = categoryName === 'todos' || secId === categoryName;

                    if (isVisible) {
                        sec.style.display = 'block';
                        // Se for Lazy e tiver que mostrar, força o render (pois observer pode não ter pego se estiver fora da tela mas usuário clicou filtro)
                        if (sec.hasAttribute('data-lazy-category') && !sec.classList.contains('rendered') && window.CatalogVirtualizer) {
                            window.CatalogVirtualizer.loadCategory(sec);
                        }
                    } else {
                        sec.style.display = 'none';
                    }
                });

                // Reset da busca
                const searchInput = document.getElementById('cardapioSearchInput');
                if (searchInput) {
                    searchInput.value = '';
                    document.querySelectorAll('.cardapio-product-card').forEach(p => p.style.display = 'flex');
                }
            },

            // --- Botões Adicionar (DELEGATION) ---
            bindAddButtons: function () {
                const container = document.querySelector('.cardapio-products');
                if (!container) return;

                container.addEventListener('click', function (e) {
                    // Verifica se clicou no botão ou ícone dentro dele
                    const btn = e.target.closest('.cardapio-add-btn');
                    // Verifica se clicou no Card (para abrir modal), mas NÃO no botão
                    const card = e.target.closest('.cardapio-product-card');

                    if (btn) {
                        e.stopPropagation();
                        e.preventDefault();
                        if (card) {
                            const productId = card.getAttribute('data-product-id');
                            const comboId = card.getAttribute('data-combo-id');
                            CardapioManager.ui.handleAddClick(productId, comboId);
                        }
                        return;
                    }

                    if (card) {
                        // Click no card -> Open Modal
                        const productId = card.getAttribute('data-product-id');
                        const comboId = card.getAttribute('data-combo-id');

                        if (productId) {
                            if (window.CardapioModals) CardapioModals.openProduct(productId);
                            else if (window.openProductModal) window.openProductModal(productId);
                        } else if (comboId) {
                            if (window.CardapioModals && CardapioModals.openCombo) CardapioModals.openCombo(comboId);
                        }
                    }
                });
            },

            handleAddClick: function (productId, comboId) {
                if (productId) {
                    if (window.CardapioModals) CardapioModals.openProduct(productId);
                    else if (window.openProductModal) window.openProductModal(productId);
                } else if (comboId) {
                    if (window.CardapioModals && CardapioModals.openCombo) CardapioModals.openCombo(comboId);
                }
            }
        }
    };

    // ==========================================
    // EXPORTAR GLOBALMENTE
    // ==========================================
    window.CardapioManager = CardapioManager;

    // Aliases Legado
    window.filterByCategory = (cat) => CardapioManager.ui.filterByCategory(cat);

    // Inicialização
    document.addEventListener('DOMContentLoaded', () => CardapioManager.init());

})();
