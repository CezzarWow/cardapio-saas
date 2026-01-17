/**
 * CATALOG VIRTUALIZER
 * Responsável pelo Lazy Loading das categorias usando IntersectionObserver
 */
(function () {
    'use strict';

    const CatalogVirtualizer = {
        observer: null,

        init: function () {
            if (!('IntersectionObserver' in window)) {
                // Fallback para navegadores antigos: Renderiza tudo imediatamente
                this.renderAllImmediately();
                return;
            }

            this.createObserver();
            this.observeCategories();
        },

        createObserver: function () {
            const options = {
                root: null, // viewport
                rootMargin: '200px', // Carrega 200px antes de entrar na tela
                threshold: 0.01 // Gatilho logo que 1% aparecer
            };

            this.observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadCategory(entry.target);
                        observer.unobserve(entry.target); // Para de observar após carregar
                    }
                });
            }, options);
        },

        observeCategories: function () {
            const lazySections = document.querySelectorAll('.cardapio-category-section[data-lazy-category]');
            lazySections.forEach(section => {
                // Se já estiver renderizado (por algum motivo), ignora
                if (section.classList.contains('rendered')) return;
                this.observer.observe(section);
            });
        },

        loadCategory: function (section) {
            const categoryName = section.getAttribute('data-lazy-category');
            if (window.CatalogRenderer) {
                window.CatalogRenderer.renderCategory(categoryName, section);
            }
        },

        renderAllImmediately: function () {
            const lazySections = document.querySelectorAll('.cardapio-category-section[data-lazy-category]');
            lazySections.forEach(section => this.loadCategory(section));
        }
    };

    window.CatalogVirtualizer = CatalogVirtualizer;
})();
