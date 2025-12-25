/**
 * ═══════════════════════════════════════════════════════════════════════════
 * LOCALIZAÇÃO: public/js/cardapio.js
 * ═══════════════════════════════════════════════════════════════════════════
 * DESCRIÇÃO: JavaScript do Cardápio Web - Entrega 1 (Cardápio + Carrinho)
 */

// ========== ESTADO GLOBAL ==========
let cart = [];
let currentProduct = null;
let currentQuantity = 1;
let selectedAdditionals = [];

// ========== INICIALIZAÇÃO ==========
document.addEventListener('DOMContentLoaded', function () {
    initializeEventListeners();
    updateCartDisplay();
    lucide.createIcons();
});

// ========== EVENT LISTENERS ==========
function initializeEventListeners() {
    const searchInput = document.getElementById('cardapioSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    const categoryButtons = document.querySelectorAll('.cardapio-category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterByCategory(this.dataset.category);
            categoryButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    const modals = document.querySelectorAll('.cardapio-modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeProductModal();
                closeCartModal();
            }
        });
    });
}

// ========== BUSCA ==========
function handleSearch(e) {
    const query = e.target.value.toLowerCase().trim();
    const productCards = document.querySelectorAll('.cardapio-product-card');

    productCards.forEach(card => {
        const name = card.dataset.productName.toLowerCase();
        const description = card.dataset.productDescription.toLowerCase();
        const matches = name.includes(query) || description.includes(query);
        card.style.display = matches ? 'flex' : 'none';
    });

    const sections = document.querySelectorAll('.cardapio-category-section');
    sections.forEach(section => {
        const visibleCards = section.querySelectorAll('.cardapio-product-card:not([style*="display: none"])');
        section.style.display = visibleCards.length > 0 ? 'block' : 'none';
    });
}

// ========== FILTRO POR CATEGORIA ==========
function filterByCategory(category) {
    const sections = document.querySelectorAll('.cardapio-category-section');

    if (category === 'todos') {
        sections.forEach(section => section.style.display = 'block');
    } else {
        sections.forEach(section => {
            section.style.display = section.dataset.categoryName === category ? 'block' : 'none';
        });
    }
}

// ========== MODAL DE PRODUTO ==========
function openProductModal(productId) {
    const card = document.querySelector(`.cardapio-product-card[data-product-id="${productId}"]`);
    if (!card) return;

    currentProduct = {
        id: card.dataset.productId,
        name: card.dataset.productName,
        price: parseFloat(card.dataset.productPrice),
        description: card.dataset.productDescription,
        image: card.dataset.productImage
    };

    currentQuantity = 1;
    selectedAdditionals = [];

    document.getElementById('modalProductName').textContent = currentProduct.name;
    document.getElementById('modalProductDescription').textContent = currentProduct.description;
    document.getElementById('modalProductPrice').textContent = formatCurrency(currentProduct.price);
    document.getElementById('modalQuantity').textContent = currentQuantity;
    document.getElementById('modalObservation').value = '';

    const modalImage = document.getElementById('modalProductImage');
    if (currentProduct.image) {
        // CORREÇÃO: Usar BASE_URL dinâmico
        modalImage.src = `${window.BASE_URL}/uploads/${currentProduct.image}`;
        modalImage.style.display = 'block';
    } else {
        modalImage.style.display = 'none';
    }

    document.querySelectorAll('.cardapio-additional-checkbox').forEach(cb => cb.checked = false);
    updateModalPrice();

    document.getElementById('productModal').classList.add('show');
    setTimeout(() => lucide.createIcons(), 100);
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('show');
    currentProduct = null;
}

// ========== QUANTIDADE ==========
function increaseQuantity() {
    currentQuantity++;
    document.getElementById('modalQuantity').textContent = currentQuantity;
    updateModalPrice();
}

function decreaseQuantity() {
    if (currentQuantity > 1) {
        currentQuantity--;
        document.getElementById('modalQuantity').textContent = currentQuantity;
        updateModalPrice();
    }
}

// ========== ADICIONAIS ==========
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('cardapio-additional-checkbox')) {
        updateSelectedAdditionals();
        updateModalPrice();
    }
});

function updateSelectedAdditionals() {
    selectedAdditionals = [];
    const checkboxes = document.querySelectorAll('.cardapio-additional-checkbox:checked');

    checkboxes.forEach(cb => {
        selectedAdditionals.push({
            id: cb.dataset.additionalId,
            name: cb.dataset.additionalName,
            price: parseFloat(cb.dataset.additionalPrice)
        });
    });
}

function updateModalPrice() {
    if (!currentProduct) return;

    const basePrice = currentProduct.price;
    const additionalsPrice = selectedAdditionals.reduce((sum, item) => sum + item.price, 0);
    const totalPrice = (basePrice + additionalsPrice) * currentQuantity;

    document.getElementById('modalTotalPrice').textContent = formatCurrency(totalPrice);
}

// ========== ADICIONAR AO CARRINHO ==========
function addToCart() {
    if (!currentProduct) return;

    const observation = document.getElementById('modalObservation').value.trim();

    const cartItem = {
        id: Date.now() + Math.random(),
        productId: currentProduct.id,
        name: currentProduct.name,
        basePrice: currentProduct.price,
        quantity: currentQuantity,
        additionals: [...selectedAdditionals],
        observation: observation,
        unitPrice: currentProduct.price + selectedAdditionals.reduce((sum, item) => sum + item.price, 0)
    };

    cart.push(cartItem);
    updateCartDisplay();
    closeProductModal();
}

// ========== ATUALIZAR DISPLAY DO CARRINHO ==========
function updateCartDisplay() {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);

    // Atualizar preço no botão flutuante
    const cartTotalEl = document.getElementById('cartTotal');
    if (cartTotalEl) {
        cartTotalEl.textContent = formatCurrency(totalPrice);
    }

    // Atualizar total no modal
    const cartModalTotal = document.getElementById('cartModalTotal');
    if (cartModalTotal) {
        cartModalTotal.textContent = formatCurrency(totalPrice);
    }

    // Mostrar/esconder botão flutuante
    const floatingCart = document.getElementById('floatingCart');
    if (floatingCart) {
        if (totalItems > 0) {
            floatingCart.classList.add('show');
        } else {
            floatingCart.classList.remove('show');
        }
    }

    renderCartItems();
}

// ========== RENDERIZAR ITENS DO CARRINHO ==========
function renderCartItems() {
    const container = document.getElementById('cartItemsContainer');

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="cardapio-cart-empty">
                <div class="cardapio-cart-empty-icon">
                    <i data-lucide="shopping-bag" size="48"></i>
                </div>
                <p>Seu carrinho está vazio</p>
            </div>
        `;
        lucide.createIcons();
        return;
    }

    let html = '';
    cart.forEach(item => {
        html += `
            <div class="cardapio-cart-item">
                <div class="cardapio-cart-item-info">
                    <p class="cardapio-cart-item-name">${item.quantity}x ${item.name}</p>
                    ${item.additionals.length > 0 ? `
                        <p class="cardapio-cart-item-additionals">
                            Extras: ${item.additionals.map(a => a.name).join(', ')}
                        </p>
                    ` : ''}
                    ${item.observation ? `
                        <p class="cardapio-cart-item-obs">Obs: ${item.observation}</p>
                    ` : ''}
                    <button class="cardapio-cart-item-remove" onclick="removeFromCart(${item.id})">
                        Remover
                    </button>
                </div>
                <span class="cardapio-cart-item-price">
                    ${formatCurrency(item.unitPrice * item.quantity)}
                </span>
            </div>
        `;
    });

    container.innerHTML = html;
    lucide.createIcons();
}

// ========== REMOVER DO CARRINHO ==========
function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    updateCartDisplay();
}

// ========== MODAL DO CARRINHO ==========
function openCartModal() {
    document.getElementById('cartModal').classList.add('show');
    setTimeout(() => lucide.createIcons(), 100);
}

function closeCartModal() {
    document.getElementById('cartModal').classList.remove('show');
}

// ========== PRÓXIMA ETAPA: BEBIDAS E MOLHOS ==========
function goToCheckout() {
    console.log('[CARDÁPIO] goToCheckout chamado');
    const cartModal = document.getElementById('cartModal');
    const suggestionsModal = document.getElementById('suggestionsModal');

    console.log('[CARDÁPIO] cartModal existe:', !!cartModal);
    console.log('[CARDÁPIO] suggestionsModal existe:', !!suggestionsModal);

    // Fecha o carrinho
    if (cartModal) {
        cartModal.classList.remove('show');
    }

    // Abre sugestões imediatamente
    if (suggestionsModal) {
        suggestionsModal.classList.add('show');
        setTimeout(() => lucide.createIcons(), 100);
    } else {
        console.error('[CARDÁPIO] suggestionsModal NÃO encontrado!');
    }
}

function openSuggestionsModal() {
    console.log('[CARDÁPIO] openSuggestionsModal chamado');
    const modal = document.getElementById('suggestionsModal');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => lucide.createIcons(), 100);
    }
}

function closeSuggestionsModal() {
    document.getElementById('suggestionsModal').classList.remove('show');
}

// Adicionar bebida ao carrinho
function addDrinkToCart(id, name, price, image) {
    const cartItem = {
        id: Date.now() + Math.random(),
        productId: id,
        name: name,
        basePrice: price,
        quantity: 1,
        additionals: [],
        observation: '',
        unitPrice: price
    };

    cart.push(cartItem);
    updateCartDisplay();

    // Feedback visual
    const btn = document.querySelector(`.suggestion-drink-btn[data-id="${id}"]`);
    if (btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" size="16"></i> Adicionado!';
        btn.style.background = '#10b981';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            lucide.createIcons();
        }, 1500);
    }
    lucide.createIcons();
}

// Adicionar molho ao carrinho (como item avulso)
function addSauceToCart(id, name, price) {
    const cartItem = {
        id: Date.now() + Math.random(),
        productId: 'sauce_' + id,
        name: 'Molho: ' + name,
        basePrice: price,
        quantity: 1,
        additionals: [],
        observation: '',
        unitPrice: price
    };

    cart.push(cartItem);
    updateCartDisplay();

    // Feedback visual
    const btn = document.querySelector(`.suggestion-sauce-btn[data-id="${id}"]`);
    if (btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" size="16"></i>';
        btn.style.background = '#10b981';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            lucide.createIcons();
        }, 1500);
    }
    lucide.createIcons();
}

// Finalizar pedido (por enquanto só mostra mensagem)
function finalizarPedido() {
    closeSuggestionsModal();

    // Calcular total
    const totalPrice = cart.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);

    // Mostrar confirmação
    alert('🎉 Pedido recebido!\n\nTotal: ' + formatCurrency(totalPrice) + '\n\n(Próximas etapas serão implementadas em breve)');

    // Limpar carrinho
    cart = [];
    updateCartDisplay();
}

// ========== UTILITÁRIOS ==========
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

console.log('[CARDÁPIO] JavaScript carregado ✓');
