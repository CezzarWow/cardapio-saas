/**
 * CARDAPIO.JS - Ponto de Entrada (Main Entry Point)
 * Refatorado: Utils, Cart, Modals, Checkout, CardapioManager
 */

(function () {
    'use strict';

    const CardapioManager = {
        // ==========================================
        // INICIALIZAÇÃO
        // ==========================================
        init: function () {
            this.initModules();
            this.bindEvents();
            this.recoverCart();
            this.initIcons();
            console.log('[CardapioManager] Initialized');
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
                searchInput.addEventListener('input', (e) => this.ui.handleSearch(e));
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
            handleSearch: function (e) {
                const term = e.target.value.toLowerCase();
                const products = document.querySelectorAll('.cardapio-product-card');
                const categories = document.querySelectorAll('.cardapio-category-section');

                products.forEach(product => {
                    const name = (product.getAttribute('data-product-name') || '').toLowerCase();
                    const desc = (product.getAttribute('data-product-description') || '').toLowerCase();

                    if (name.includes(term) || desc.includes(term)) {
                        product.style.display = 'flex';
                    } else {
                        product.style.display = 'none';
                    }
                });

                // Esconde categorias vazias
                categories.forEach(category => {
                    const visibleProducts = category.querySelectorAll('.cardapio-product-card[style="display: flex;"]');
                    category.style.display = (visibleProducts.length === 0 && term !== '') ? 'none' : 'block';
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
