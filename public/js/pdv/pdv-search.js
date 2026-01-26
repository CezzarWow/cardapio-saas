/**
 * PDV Search Module
 * Gerencia a busca textual e filtros por categoria no PDV.
 */
window.PDVSearch = {
    selectedCategory: '',
    searchTerm: '',

    init: function () {
        this.cacheDOM();
        this.bindEvents();
    },

    cacheDOM: function () {
        this.searchInput = document.getElementById('product-search-input');
        this.chips = document.querySelectorAll('.pdv-category-chip');
        this.cards = document.querySelectorAll('.product-card');
    },

    keydownHandler: null,

    bindEvents: function () {
        // Eventos de Chips (Categorias)
        if (this.chips) {
            this.chips.forEach(chip => {
                // removeEventListener não é necessário pois elementos são substituídos no SPA
                chip.addEventListener('click', (e) => {
                    this.chips.forEach(c => c.classList.remove('active'));
                    e.currentTarget.classList.add('active');

                    this.selectedCategory = e.currentTarget.dataset.category;
                    this.filterProducts();
                });
            });
        }

        // Eventos de Busca (Input)
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value.toLowerCase().trim();
                this.filterProducts();
            });

            // Atalho F2 (Global)
            if (this.keydownHandler) {
                document.removeEventListener('keydown', this.keydownHandler);
            }

            this.keydownHandler = (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    if (this.searchInput) this.searchInput.focus();
                }
            };
            document.addEventListener('keydown', this.keydownHandler);
        }
    },

    filterProducts: function () {
        // Otimização: Se não tiver busca e categoria vazia, mostra tudo rápido
        if (!this.selectedCategory && !this.searchTerm) {
            this.cards.forEach(card => card.style.display = '');
            return;
        }

        this.cards.forEach(card => {
            const cat = card.dataset.category;
            const nameEl = card.querySelector('h3');
            const name = nameEl ? nameEl.innerText.toLowerCase() : '';

            const matchCat = (!this.selectedCategory || cat === this.selectedCategory);
            const matchText = name.includes(this.searchTerm);

            if (matchCat && matchText) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
};

// Expor globalmente
// window.PDVSearch = PDVSearch; // Já definido acima
