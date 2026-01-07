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
    // ==========================================
    // MODAL DE COMBOS
    // ==========================================
    currentCombo: null,
    comboQuantity: 1,
    comboSelections: {}, // { productId: [ {id, name, price} ] }

    openCombo: function (comboId) {
        const combo = (typeof combos !== 'undefined') ? combos.find(c => c.id == comboId) : null;

        if (!combo) {
            console.error('Combo não encontrado:', comboId);
            return;
        }

        this.currentCombo = combo;
        this.comboQuantity = 1;
        this.comboSelections = {};

        if (combo.items) {
            combo.items.forEach(item => {
                this.comboSelections[item.product_id] = [];
            });
        }

        // Header
        document.getElementById('modalComboName').textContent = combo.name;
        document.getElementById('modalComboDescription').textContent = combo.description || '';
        document.getElementById('modalComboPrice').textContent = Utils.formatCurrency(parseFloat(combo.price));

        const imgEl = document.getElementById('modalComboImage');
        if (combo.image) {
            imgEl.src = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/uploads/' + combo.image;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }

        // Check for existing instruction or create it
        let instruction = document.getElementById('comboInstructionText');
        if (!instruction) {
            instruction = document.createElement('p');
            instruction.id = 'comboInstructionText';
            instruction.style.textAlign = 'left';
            instruction.style.marginBottom = '15px';
            instruction.style.marginTop = '10px';
            instruction.style.fontWeight = '700';
            instruction.style.fontSize = '0.9rem';
            instruction.style.color = '#e63946'; // Highlight color (Red/Pinkish)
            instruction.textContent = 'Itens inclusos no combo. Clique para adicionar.';

            // Insert before the product list container
            const container = document.getElementById('comboProductsContainer');
            container.parentNode.insertBefore(instruction, container);
        }

        // Renderiza Produtos
        const container = document.getElementById('comboProductsContainer');
        container.innerHTML = '';

        if (combo.items && combo.items.length > 0) {
            combo.items.forEach(item => {
                container.appendChild(this.renderComboProductItem(item));
            });
        } else {
            container.innerHTML = '<p style="color:#666; font-style:italic;">Nenhum item neste combo.</p>';
        }

        // Reset
        document.getElementById('modalComboQuantity').textContent = '1';
        const obsInput = document.getElementById('modalComboObservation');
        if (obsInput) obsInput.value = '';
        this.updateComboPrice();

        // Abre
        const modal = document.getElementById('comboModal');
        modal.classList.add('show');
        Utils.initIcons();
    },

    closeCombo: function () {
        document.getElementById('comboModal').classList.remove('show');
        this.currentCombo = null;
    },

    renderComboProductItem: function (item) {
        const fullProduct = (typeof products !== 'undefined') ? products.find(p => p.id == item.product_id) : null;

        // Verifica se tem adicionais E se está permitido
        // PRODUCT_RELATIONS deve estar disponível
        const relations = (typeof PRODUCT_RELATIONS !== 'undefined') ? PRODUCT_RELATIONS : {};
        const hasAdditionals = (item.allow_additionals == 1) && fullProduct && relations[fullProduct.id] && relations[fullProduct.id].length > 0;

        const wrapper = document.createElement('div');
        wrapper.className = 'combo-product-collapse open';

        const header = document.createElement('div');
        header.className = 'combo-product-header';

        if (hasAdditionals) {
            header.onclick = () => {
                wrapper.classList.toggle('open');
            };
        } else {
            wrapper.classList.remove('open');
        }

        header.innerHTML = `
            <div class="combo-product-info">
                <span>${item.product_name}</span>
                ${hasAdditionals ? `<span class="combo-extras-badge" id="badge-${item.product_id}">0 extras</span>` : ''}
            </div>
            ${hasAdditionals ? `<i data-lucide="chevron-down" class="combo-toggle-icon" size="16"></i>` : ''}
        `;

        wrapper.appendChild(header);

        if (hasAdditionals) {
            const body = document.createElement('div');
            body.className = 'combo-product-body';

            const list = document.createElement('div');
            list.className = 'combo-additional-list';

            const groupIds = [...new Set(relations[fullProduct.id])];

            groupIds.forEach(groupId => {
                // Clona do DOM oculto existente (estratégia segura)
                const originalGroup = document.querySelector(`.cardapio-additional-group[data-group-id="${groupId}"]`);
                if (originalGroup) {
                    const clone = originalGroup.cloneNode(true);
                    clone.style.display = 'block';

                    clone.querySelectorAll('input').forEach(input => {
                        const addId = input.getAttribute('data-additional-id');
                        const price = parseFloat(input.getAttribute('data-additional-price'));
                        const name = input.getAttribute('data-additional-name');
                        const uniqueId = `combo-${item.product_id}-${addId}`;

                        input.id = uniqueId;
                        input.name = `combo_extras_${item.product_id}[]`;
                        input.checked = false;

                        // Atualiza Label
                        // Tenta achar o label pelo 'for' antigo ou pelo parent
                        const oldId = input.getAttribute('id') || ''; // id original pode não existir no clone limpo
                        // No clone, proximo elemento label
                        let label = clone.querySelector(`label[for="${input.getAttribute('id')}"]`);
                        if (!label) label = input.nextElementSibling; // Fallback comum
                        if (label && label.tagName === 'LABEL') label.setAttribute('for', uniqueId);
                        else {
                            // Se o input estiver dentro do label
                            const parentLabel = input.closest('label');
                            if (parentLabel) parentLabel.setAttribute('for', uniqueId);
                        }

                        // Evento manual pois o listener global não pega ids dinâmicos complexos facilmente ou queremos isolar
                        input.onclick = (e) => {
                            // Importante: stopPropagation se estiver em label clicável
                            e.stopPropagation();
                        };
                        input.onchange = (e) => {
                            this.toggleComboAdditional(item.product_id, addId, name, price, e.target.checked);
                        };

                        // Remove classe global para não disparar o listener global do modals.js
                        input.classList.remove('cardapio-additional-checkbox');
                    });

                    list.appendChild(clone);
                }
            });

            body.appendChild(list);
            wrapper.appendChild(body);
        }

        return wrapper;
    },

    toggleComboAdditional: function (productId, addId, name, price, isChecked) {
        if (!this.comboSelections[productId]) this.comboSelections[productId] = [];

        if (isChecked) {
            this.comboSelections[productId].push({ id: addId, name, price });
        } else {
            this.comboSelections[productId] = this.comboSelections[productId].filter(a => a.id != addId);
        }

        const count = this.comboSelections[productId].length;
        const badge = document.getElementById(`badge-${productId}`);
        if (badge) {
            badge.textContent = `${count} extra${count !== 1 ? 's' : ''}`;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }

        this.updateComboPrice();
    },

    increaseComboQty: function () {
        this.comboQuantity++;
        this.updateComboPrice();
    },

    decreaseComboQty: function () {
        if (this.comboQuantity > 1) {
            this.comboQuantity--;
            this.updateComboPrice();
        }
    },

    updateComboPrice: function () {
        let totalExtras = 0;
        Object.values(this.comboSelections).forEach(list => {
            list.forEach(item => totalExtras += item.price);
        });

        const total = (parseFloat(this.currentCombo.price) + totalExtras) * this.comboQuantity;

        document.getElementById('modalComboQuantity').textContent = this.comboQuantity;
        document.getElementById('modalComboTotalPrice').textContent = Utils.formatCurrency(total);
    },

    addComboToCart: function () {
        if (!this.currentCombo) return;

        const observation = document.getElementById('modalComboObservation').value.trim();

        let totalExtras = 0;
        Object.values(this.comboSelections).forEach(list => {
            list.forEach(item => totalExtras += item.price);
        });

        const productsList = this.currentCombo.items.map(item => {
            const extras = this.comboSelections[item.product_id] || [];
            return {
                id: item.product_id,
                name: item.product_name,
                additionals: extras
            };
        });

        const cartItem = {
            id: Date.now() + Math.random(),
            isCombo: true,
            comboId: this.currentCombo.id,
            name: this.currentCombo.name,
            image: this.currentCombo.image,
            basePrice: parseFloat(this.currentCombo.price),
            quantity: this.comboQuantity,
            products: productsList,
            observation: observation,
            unitPrice: parseFloat(this.currentCombo.price) + totalExtras
        };

        CardapioCart.add(cartItem);
        this.closeCombo();
    },

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
