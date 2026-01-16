/**
 * CARDAPIO.JS - Ponto de Entrada (Main Entry Point)
 * Refatorado: Utils, Cart, Modals, Checkout, CardapioManager
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
                this.ui.cacheNodes();
                const ui = this.ui;
                searchInput.addEventListener('input', debounce((e) => ui.handleSearch(e), 180));
            }

            // 3. Filtros
            this.ui.bindCategoryFilters();

            // 4. Botões de Adicionar
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
                // Cacheia NodeLists convertidos para arrays para operações mais rápidas
                try {
                    this._products = Array.from(document.querySelectorAll('.cardapio-product-card'));
                    this._categories = Array.from(document.querySelectorAll('.cardapio-category-section'));
                } catch (err) {
                    this._products = [];
                    this._categories = [];
                }
            },

            handleSearch: function (e) {
                const term = (e.target.value || '').toLowerCase().trim();

                // Usa cache se disponível
                const products = this._products || Array.from(document.querySelectorAll('.cardapio-product-card'));
                const categories = this._categories || Array.from(document.querySelectorAll('.cardapio-category-section'));

                // Minimiza leituras de DOM, extrai dados uma vez
                products.forEach(product => {
                    let name = product.dataset.productName;
                    let desc = product.dataset.productDescription;
                    if (typeof name === 'undefined') name = (product.getAttribute('data-product-name') || '');
                    if (typeof desc === 'undefined') desc = (product.getAttribute('data-product-description') || '');

                    const hay = (name + ' ' + desc).toLowerCase();
                    const visible = term === '' || hay.indexOf(term) !== -1;

                    product.style.display = visible ? 'flex' : 'none';
                });

                // Esconde categorias vazias verificando filhos visíveis (melhor que seletor por style)
                categories.forEach(category => {
                    const children = Array.from(category.querySelectorAll('.cardapio-product-card'));
                    const hasVisible = children.some(c => c.style.display !== 'none');
                    category.style.display = (hasVisible || term === '') ? 'block' : 'none';
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
                    const secId = sec.getAttribute('data-category-id');
                    const isVisible = categoryName === 'todos' || secId === categoryName;
                    sec.style.display = isVisible ? 'block' : 'none';
                });

                // Reset da busca
                const searchInput = document.getElementById('cardapioSearchInput');
                if (searchInput) {
                    searchInput.value = '';
                    document.querySelectorAll('.cardapio-product-card').forEach(p => p.style.display = 'flex');
                }
            },

            // --- Botões Adicionar ---
            bindAddButtons: function () {
                document.querySelectorAll('.cardapio-add-btn').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation(); // Evita clique no card pai

                        const card = this.closest('.cardapio-product-card');
                        if (card) {
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
                });
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
