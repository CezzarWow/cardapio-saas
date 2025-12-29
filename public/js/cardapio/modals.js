/**
 * MODALS.JS - Gerenciamento de Janelas Modais
 * Dependências: Cart.js, Utils.js
 */

const CardapioModals = {
    // Estado do Produto Atual (sendo visualizado)
    currentProduct: null,
    currentQuantity: 1,
    selectedAdditionals: [],
    basePrice: 0,

    init: function () {
        // Listeners globais de modal seriam configurados aqui ou no main.js
        console.log('[Modals] Inicializado');
    },

    // ==========================================
    // MODAL DE PRODUTO
    // ==========================================
    openProduct: function (productId) {
        let product = null;

        // 1. Tenta buscar no array global (melhor cenário, tem adicionais)
        if (typeof products !== 'undefined' && Array.isArray(products)) {
            product = products.find(p => p.id == productId);
        }

        // 2. Fallback: Busca no DOM (se array falhar ou produto não achado)
        if (!product) {
            console.warn('[CardapioModals] Produto não encontrado no array global. Tentando ler do DOM. ID:', productId);
            const card = document.querySelector(`.cardapio-product-card[data-product-id="${productId}"]`);

            if (card) {
                product = {
                    id: productId,
                    name: card.getAttribute('data-product-name'),
                    price: parseFloat(card.getAttribute('data-product-price')),
                    description: card.getAttribute('data-product-description'),
                    image: card.getAttribute('data-product-image'),
                    additionals: [] // DOM não tem dados de adicionais complexos
                };
            }
        }

        if (!product) {
            console.error('[CardapioModals] Produto não encontrado nem no array nem no DOM.', productId);
            return;
        }

        // ============================================
        // LÓGICA ORIGINAL: SE NÃO TEM ADICIONAIS, ADICIONA DIRETO
        // ============================================
        // PRODUCT_RELATIONS é um objeto {produto_id: [grupo_id, grupo_id, ...]}
        // Definido em cardapio_publico.php
        const productRelations = (typeof PRODUCT_RELATIONS !== 'undefined') ? PRODUCT_RELATIONS : {};
        const relatedGroups = productRelations[product.id] || [];
        const hasAdditionals = relatedGroups.length > 0;

        if (!hasAdditionals) {
            // Adiciona direto ao carrinho sem abrir modal
            CardapioCart.addDirect(product.id, product.name, parseFloat(product.price), product.image);
            return;
        }

        // ============================================
        // SE TEM ADICIONAIS: ABRE O MODAL
        // ============================================
        this.currentProduct = product;
        this.currentQuantity = 1;
        this.selectedAdditionals = [];
        this.basePrice = parseFloat(product.price);

        // Preenche info básica
        document.getElementById('modalProductName').textContent = product.name;
        document.getElementById('modalProductDescription').textContent = product.description || '';
        document.getElementById('modalProductPrice').textContent = Utils.formatCurrency(this.basePrice);

        // Imagem
        const imgEl = document.getElementById('modalProductImage');
        if (product.image) {
            imgEl.src = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/uploads/' + product.image;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }

        // Renderiza Adicionais usando PRODUCT_RELATIONS
        this.renderAdditionals(relatedGroups);

        // Reset inputs
        const obsInput = document.getElementById('modalObservation');
        if (obsInput) obsInput.value = '';
        document.getElementById('modalQuantity').textContent = '1';

        // Atualiza preço total
        this.updateModalPrice();

        // Abre modal (adiciona classe e scroll reset)
        const modal = document.getElementById('productModal');
        modal.classList.add('show');

        // Fix de Scroll
        requestAnimationFrame(() => {
            const content = modal.querySelector('.cardapio-modal-content');
            const body = modal.querySelector('.cardapio-modal-body');
            if (content) content.scrollTop = 0;
            if (body) body.scrollTop = 0;
        });
    },

    closeProduct: function () {
        document.getElementById('productModal').classList.remove('show');
        this.currentProduct = null;
    },

    // Controle de Quantidade
    increaseQty: function () {
        this.currentQuantity++;
        this.updateQtyDisplay();
    },

    decreaseQty: function () {
        if (this.currentQuantity > 1) {
            this.currentQuantity--;
            this.updateQtyDisplay();
        }
    },

    updateQtyDisplay: function () {
        document.getElementById('modalQuantity').textContent = this.currentQuantity;
        this.updateModalPrice();
    },

    // Controle de Adicionais
    renderAdditionals: function (groupIds) {
        // 1. Resetar Checkboxes e Estado
        this.selectedAdditionals = [];
        document.querySelectorAll('.cardapio-additional-checkbox').forEach(cb => {
            cb.checked = false;
        });

        const container = document.getElementById('modalAdditionals'); // Wrapper visual
        const groups = document.querySelectorAll('.cardapio-additional-group');
        const title = document.querySelector('.cardapio-modal-additionals-title');

        // Se não tem grupos vinculados
        if (!groupIds || !Array.isArray(groupIds) || groupIds.length === 0) {
            if (title) title.style.display = 'none';
            groups.forEach(g => g.style.display = 'none');
            return;
        }

        if (title) title.style.display = 'block';

        // 2. Converter para Set de strings para comparação rápida
        const allowedGroupIds = new Set(groupIds.map(id => String(id)));

        // 3. Mostrar apenas os grupos permitidos
        let hasVisible = false;
        groups.forEach(group => {
            const groupId = group.getAttribute('data-group-id');
            if (allowedGroupIds.has(String(groupId))) {
                group.style.display = 'block';
                hasVisible = true;
            } else {
                group.style.display = 'none';
            }
        });

        // Se nenhum grupo for visível (ex: erro de config), esconde título
        if (!hasVisible && title) title.style.display = 'none';
    },

    // Toggle Adicional
    toggleAdditional: function (id, name, price, isChecked) {
        if (isChecked) {
            this.selectedAdditionals.push({ id, name, price });
        } else {
            this.selectedAdditionals = this.selectedAdditionals.filter(a => a.id !== id);
        }
        this.updateModalPrice();
    },

    updateModalPrice: function () {
        const addTotal = this.selectedAdditionals.reduce((sum, item) => sum + item.price, 0);
        const finalPrice = (this.basePrice + addTotal) * this.currentQuantity;
        document.getElementById('modalTotalPrice').textContent = Utils.formatCurrency(finalPrice);
    },

    // Adicionar ao Carrinho (Ação do Modal)
    addToCartAction: function () {
        if (!this.currentProduct) return;

        const observation = document.getElementById('modalObservation').value.trim();

        // Cria objeto item compatível com Cart.js
        const item = {
            id: Date.now() + Math.random(),
            productId: this.currentProduct.id,
            name: this.currentProduct.name,
            basePrice: this.basePrice,
            quantity: this.currentQuantity,
            additionals: [...this.selectedAdditionals],
            observation: observation,
            unitPrice: this.basePrice + this.selectedAdditionals.reduce((sum, item) => sum + item.price, 0)
        };

        CardapioCart.add(item);
        this.closeProduct();
    },

    // ==========================================
    // MODAL DE SUGESTÕES
    // ==========================================
    openSuggestions: function () {
        const modal = document.getElementById('suggestionsModal');
        if (modal) {
            modal.classList.add('show');
            Utils.initIcons();
        }
    },

    closeSuggestions: function () {
        document.getElementById('suggestionsModal').classList.remove('show');
    },

    // ==========================================
    // MODAL DE CARRINHO
    // ==========================================
    openCart: function () {
        document.getElementById('cartModal').classList.add('show');
        Utils.initIcons();
    },

    closeCart: function () {
        document.getElementById('cartModal').classList.remove('show');
    },

    // ==========================================
    // FLUXO DE CHECKOUT (Interações de Modal)
    // ==========================================
    goToCheckout: function () {
        this.closeCart();
        this.openSuggestions();
        CardapioCart.updateUI(); // Sincroniza visual
    }
};

// ==========================================
// EXPOR VARIÁVEIS PARA COMPATIBILIDADE
// ==========================================
window.CardapioModals = CardapioModals;

// Mapeamento Legado
window.openProductModal = (id) => CardapioModals.openProduct(id);
window.closeProductModal = () => CardapioModals.closeProduct();
window.increaseQuantity = () => CardapioModals.increaseQty();
window.decreaseQuantity = () => CardapioModals.decreaseQty();
window.openCartModal = () => CardapioModals.openCart();
window.closeCartModal = () => CardapioModals.closeCart();
window.openSuggestionsModal = () => CardapioModals.openSuggestions();
window.closeSuggestionsModal = () => CardapioModals.closeSuggestions();
window.goToCheckout = () => CardapioModals.goToCheckout(); // Nome conflituoso com funcionalidade futura? Não, é fluxo visual.

// Evento Global para Checkbox de Adicional (Delegado)
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('cardapio-additional-checkbox')) {
        const id = e.target.getAttribute('data-additional-id');
        const name = e.target.getAttribute('data-additional-name');
        const price = parseFloat(e.target.getAttribute('data-additional-price'));
        CardapioModals.toggleAdditional(id, name, price, e.target.checked);
    }
});

// Ação de adicionar do modal
// window.addToCart já foi definido em cart.js ou aqui? 
// No original 'addToCart' é a ação do modal. Em cart.js, 'add' é a lógica.
// Vamos sobrescrever window.addToCart para ser a ação de UI deste modal.
window.addToCart = () => CardapioModals.addToCartAction();
