/**
 * PDV Search Module
 * Gerencia a busca textual e filtros por categoria no PDV.
 */
const PDVSearch = {
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

    bindEvents: function () {
        // Eventos de Chips (Categorias)
        this.chips.forEach(chip => {
            chip.addEventListener('click', (e) => {
                this.chips.forEach(c => c.classList.remove('active'));
                e.currentTarget.classList.add('active');

                this.selectedCategory = e.currentTarget.dataset.category;
                this.filterProducts();
            });
        });

        // Eventos de Busca (Input)
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value.toLowerCase().trim();
                this.filterProducts();
            });

            // Atalho F2
            document.addEventListener('keydown', (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    this.searchInput.focus();
                }
            });
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
window.PDVSearch = PDVSearch;
