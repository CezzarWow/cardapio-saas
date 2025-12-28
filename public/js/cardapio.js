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

    // Mover cursor para o final ao clicar em inputs de texto
    document.addEventListener('focus', function (e) {
        if (e.target.classList.contains('payment-input') ||
            e.target.classList.contains('payment-textarea')) {
            // Move o cursor para o final do texto
            const length = e.target.value.length;
            e.target.setSelectionRange(length, length);
        }
    }, true);

    // Também ao clicar (para garantir mesmo com duplo clique)
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('payment-input') ||
            e.target.classList.contains('payment-textarea')) {
            // Usar timeout para garantir que execute depois do comportamento padrão
            setTimeout(() => {
                const length = e.target.value.length;
                e.target.setSelectionRange(length, length);
            }, 0);
        }
    }, true);

    // Formatação de moeda para o campo de troco (digita da direita para esquerda)
    const changeAmountInput = document.getElementById('changeAmount');
    if (changeAmountInput) {
        changeAmountInput.addEventListener('input', function (e) {
            // Remove tudo que não é dígito
            let value = e.target.value.replace(/\D/g, '');
            if (value === '') {
                e.target.value = '';
                return;
            }

            // Converte para centavos e formata
            value = (parseInt(value) / 100).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });

            e.target.value = value;
        });
    }

    // Máscara de Telefone (XX) XXXXX-XXXX
    const phoneInput = document.getElementById('customerPhone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    }

    // Lógica para scroll e visualViewport (Global para todos os inputs)
    function getModalBody() {
        return document.querySelector('.payment-modal .cardapio-modal-body');
    }

    function adjustPaddingForKeyboard() {
        const modalBody = getModalBody();
        if (!modalBody) return;

        const vv = window.visualViewport;
        const keyboardHeight = vv ? Math.max(0, window.innerHeight - vv.height) : 0;
        const basePad = 60; // Aumentado para garantir scroll confortável
        modalBody.style.paddingBottom = `${basePad + keyboardHeight}px`;
    }

    function ensureVisible(inputEl) {
        const modalBody = getModalBody();
        if (!modalBody) return;
        const elRect = inputEl.getBoundingClientRect();
        const bodyRect = modalBody.getBoundingClientRect();

        const hiddenBy = elRect.bottom - bodyRect.bottom;
        if (hiddenBy > 0) {
            modalBody.scrollTop = modalBody.scrollTop + hiddenBy + 16;
            return;
        }

        try {
            inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (e) {
            modalBody.scrollTop = modalBody.scrollHeight;
        }
    }

    // Aplica o handler robusto a TODOS os inputs do modal
    const allPaymentInputs = document.querySelectorAll('.payment-input');
    allPaymentInputs.forEach(input => {
        input.addEventListener('focus', function () {
            if (this.id === 'changeAmount') this.select();

            if (window.visualViewport) {
                adjustPaddingForKeyboard();
                ensureVisible(this);

                const onVV = () => {
                    adjustPaddingForKeyboard();
                    // Garante que o scroll aconteça APÓS o padding ser aplicado visualmente
                    requestAnimationFrame(() => requestAnimationFrame(() => ensureVisible(this)));
                };
                window.visualViewport.addEventListener('resize', onVV);
                window.visualViewport.addEventListener('scroll', onVV);

                // Polling agressivo durante a animação (aprox 400ms)
                let attempts = 0;
                const interval = setInterval(() => {
                    ensureVisible(this);
                    attempts++;
                    if (attempts > 8) clearInterval(interval);
                }, 50);

                setTimeout(() => {
                    try {
                        clearInterval(interval);
                        window.visualViewport.removeEventListener('resize', onVV);
                        window.visualViewport.removeEventListener('scroll', onVV);
                    } catch (e) { }
                }, 1500);
            } else {
                setTimeout(() => {
                    try { this.scrollIntoView({ block: 'center' }); } catch (e) { }
                    setTimeout(() => {
                        const modalBody = getModalBody();
                        if (modalBody) modalBody.scrollTop = modalBody.scrollHeight;
                    }, 200);
                }, 140);
            }
            requestAnimationFrame(() => requestAnimationFrame(() => ensureVisible(this)));
        });
    });

    // Lógica antiga (será limpa no próximo passo)

    if (changeAmountInput) {
        changeAmountInput.value = 'R$ 0,00';




        changeAmountInput.addEventListener('keydown', function (e) {
            // Permitir apenas números, backspace, delete, tab e enter
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' || e.key === 'Enter' ||
                e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.blur();
                }
                return;
            }

            // Bloquear tudo que não seja número
            if (!/^\d$/.test(e.key)) {
                e.preventDefault();
                return;
            }
        });

        changeAmountInput.addEventListener('input', function (e) {
            let value = this.value.replace(/\D/g, ''); // Remove tudo que não é dígito

            if (value === '') {
                value = '0';
            }

            // Converte para número e divide por 100 (centavos)
            let numValue = parseInt(value) / 100;

            // Limite de R$ 200,00
            if (numValue > 200) {
                numValue = 200;
            }

            // Formata como moeda
            this.value = 'R$ ' + numValue.toFixed(2).replace('.', ',');
        });
    }
}

// ========== FUNÇÃO DE SCROLL AUTOMÁTICO PARA CAMPO DE TROCO ==========
function scrollToChangeAmount() {
    const modalBody = document.querySelector('.payment-modal .cardapio-modal-body');
    const changeContainer = document.getElementById('changeContainer');
    const changeInput = document.getElementById('changeAmount');

    if (!modalBody || !changeContainer) return;

    // garante que o elemento existe e já foi renderizado
    const elRect = changeContainer.getBoundingClientRect();
    const bodyRect = modalBody.getBoundingClientRect();

    const hiddenBy = elRect.bottom - bodyRect.bottom;

    if (hiddenBy > 0) {
        // Rolar diretamente sem smooth para garantir que funcione
        modalBody.scrollTop = modalBody.scrollTop + hiddenBy + 16;
    } else {
        // centraliza levemente para UX melhor
        modalBody.scrollTo({
            top: modalBody.scrollTop + (elRect.top - bodyRect.top) - 80,
            behavior: 'smooth'
        });
    }

    // Opcional: focar o input depois do scroll (pode ser removido se abrir teclado indesejado)
    if (changeInput) {
        setTimeout(() => changeInput.focus(), 150);
    }
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
    const cartModal = document.getElementById('cartModal');
    const suggestionsModal = document.getElementById('suggestionsModal');

    // Fecha o carrinho
    if (cartModal) {
        cartModal.classList.remove('show');
    }

    // Abre sugestões e sincroniza carrinho
    if (suggestionsModal) {
        suggestionsModal.classList.add('show');
        updateSuggestionsCartDisplay(); // Sincroniza os valores do carrinho
        setTimeout(() => lucide.createIcons(), 100);
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
    updateSuggestionsCartDisplay();

    // Animação simples de pulse no botão
    const btn = document.querySelector(`.suggestion-drink-btn[data-id="${id}"]`);
    if (btn) {
        btn.classList.add('btn-pulse');
        setTimeout(() => btn.classList.remove('btn-pulse'), 300);
    }
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
    updateSuggestionsCartDisplay();

    // Animação simples de pulse no botão
    const btn = document.querySelector(`.suggestion-sauce-btn[data-id="${id}"]`);
    if (btn) {
        btn.classList.add('btn-pulse');
        setTimeout(() => btn.classList.remove('btn-pulse'), 300);
    }
}

// Atualizar display do carrinho na tela de sugestões
function updateSuggestionsCartDisplay() {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);

    const countEl = document.getElementById('suggestionsCartCount');
    const totalEl = document.getElementById('suggestionsCartTotal');

    if (countEl) countEl.textContent = totalItems + ' ' + (totalItems === 1 ? 'item' : 'itens');
    if (totalEl) totalEl.textContent = formatCurrency(totalPrice);
}

// Ir para tela de resumo do pedido
function finalizarPedido() {
    closeSuggestionsModal();
    openOrderReviewModal();
}

// ========== MODAL DE RESUMO DO PEDIDO ==========
function openOrderReviewModal() {
    const modal = document.getElementById('orderReviewModal');
    if (modal) {
        modal.classList.add('show');
        renderOrderReviewItems();
        updateOrderReviewTotal();
        setTimeout(() => lucide.createIcons(), 100);
    }
}

function closeOrderReviewModal() {
    document.getElementById('orderReviewModal').classList.remove('show');
    // Volta para sugestões
    openSuggestionsModal();
}

function renderOrderReviewItems() {
    const container = document.getElementById('orderReviewItems');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="order-review-empty">
                <i data-lucide="shopping-bag" size="48"></i>
                <p>Seu pedido está vazio</p>
            </div>
        `;
        lucide.createIcons();
        return;
    }

    let html = '';
    cart.forEach(item => {
        html += `
            <div class="order-review-item">
                <div class="order-review-item-qty">${item.quantity}x</div>
                <div class="order-review-item-info">
                    <p class="order-review-item-name">${item.name}</p>
                    ${item.additionals.length > 0 ? `
                        <p class="order-review-item-extras">+ ${item.additionals.map(a => a.name).join(', ')}</p>
                    ` : ''}
                    ${item.observation ? `
                        <p class="order-review-item-obs">Obs: ${item.observation}</p>
                    ` : ''}
                </div>
                <div class="order-review-item-actions">
                    <span class="order-review-item-price">${formatCurrency(item.unitPrice * item.quantity)}</span>
                    <button class="order-review-remove-btn" onclick="removeFromOrderReview(${item.id})">
                        <i data-lucide="trash-2" size="14"></i>
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function updateOrderReviewTotal() {
    const totalPrice = cart.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);
    const totalEl = document.getElementById('orderReviewTotal');
    if (totalEl) totalEl.textContent = formatCurrency(totalPrice);
}

// Remover item da tela de resumo
function removeFromOrderReview(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    updateCartDisplay();
    renderOrderReviewItems();
    updateOrderReviewTotal();

    // Se carrinho ficou vazio, volta para cardápio
    if (cart.length === 0) {
        setTimeout(() => {
            document.getElementById('orderReviewModal').classList.remove('show');
        }, 500);
    }

    lucide.createIcons();
}

// ========== MODAL DE PAGAMENTO ==========
let selectedPaymentMethod = null;
let selectedOrderType = 'entrega'; // Pré-selecionado como padrão
let hasNoNumber = false;

function goToPayment() {
    // Validar se tipo de pedido foi selecionado
    if (!selectedOrderType) {
        alert('Por favor, selecione o tipo de pedido.');
        return;
    }

    document.getElementById('orderReviewModal').classList.remove('show');
    openPaymentModal();
}

function openPaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.classList.add('show');
        updatePaymentTotal();

        // Configurar campos baseados no tipo de pedido
        updatePaymentFieldsByOrderType();

        setTimeout(() => lucide.createIcons(), 100);
    }
}

// Atualiza visibilidade dos campos baseado no tipo de pedido
function updatePaymentFieldsByOrderType() {
    const orderType = selectedOrderType; // 'entrega', 'retirada', ou 'local'
    const isDelivery = orderType === 'entrega';

    // Elementos
    const alert = document.getElementById('orderTypeAlert');
    const alertText = document.getElementById('orderTypeAlertText');
    const phoneInput = document.getElementById('customerPhone');
    const changeContainer = document.getElementById('changeContainer');

    // Pegar elementos delivery-only EXCETO o changeContainer (ele tem lógica própria)
    const deliveryOnlyElements = Array.from(document.querySelectorAll('.delivery-only'))
        .filter(el => el.id !== 'changeContainer');

    if (isDelivery) {
        // ENTREGA: oculta aviso, mostra campos de endereço
        // Telefone SEMPRE visível (para todos os tipos)
        if (alert) alert.style.display = 'none';
        deliveryOnlyElements.forEach(el => el.style.display = '');

        // changeContainer continua com lógica própria (só aparece se dinheiro)
        // Não alteramos o display dele aqui
    } else {
        // RETIRADA/LOCAL: mostra aviso e telefone, oculta campos de endereço
        const typeText = orderType === 'retirada' ? '🛍️ RETIRADA' : '🍽️ LOCAL';
        const messageText = orderType === 'retirada'
            ? 'Você escolheu RETIRADA. Pagamento no local.'
            : 'Você escolheu comer no LOCAL. Pagamento no local.';

        if (alert) alert.style.display = 'flex';
        if (alertText) alertText.textContent = messageText;
        if (phoneInput) phoneInput.style.display = '';
        deliveryOnlyElements.forEach(el => el.style.display = 'none');

        // Em retirada/local, sempre oculta o changeContainer
        if (changeContainer) changeContainer.style.display = 'none';
    }
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
    // Volta para resumo
    openOrderReviewModal();
}

function updatePaymentTotal() {
    const totalPrice = cart.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);
    const totalEl = document.getElementById('paymentTotalValue');
    if (totalEl) totalEl.textContent = formatCurrency(totalPrice);
}

function selectPaymentMethod(method) {
    // Ao trocar, fecha teclado para evitar bugs de layout
    const activeEl = document.activeElement;
    if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA')) {
        activeEl.blur();
    }

    selectedPaymentMethod = method;

    // Mostra/esconde campo de troco
    const changeContainer = document.getElementById('changeContainer');
    const modal = document.querySelector('.payment-modal');

    if (changeContainer && modal) {
        if (method === 'dinheiro') {
            // Limpa estado anterior se houver
            editChange();
            const changeInput = document.getElementById('changeAmount');
            if (changeInput) {
                changeInput.value = 'R$ 0,00';
                changeInput.disabled = false;
            }
            const noChangeBtn = document.querySelector('.no-change-btn');
            if (noChangeBtn) noChangeBtn.classList.remove('active');
            window.hasNoChange = false;

            changeContainer.style.display = 'block';
            modal.classList.add('has-change');

            // Aguarda o DOM renderizar o bloco do troco
            setTimeout(() => scrollToChangeAmount(), 80);
            setTimeout(() => scrollToChangeAmount(), 250);
        } else {
            // Se saiu do dinheiro, também limpa para garantir estado novo ao voltar
            editChange();

            changeContainer.style.display = 'none';
            modal.classList.remove('has-change');

            const modalBody = document.querySelector('.payment-modal .cardapio-modal-body');
            if (modalBody) {
                modalBody.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }
    }
}

function selectOrderType(type) {
    selectedOrderType = type;
}

function toggleNoChange() {
    const input = document.getElementById('changeAmount');
    const btn = document.querySelector('.no-change-btn');

    window.hasNoChange = !window.hasNoChange;

    if (window.hasNoChange) {
        input.value = 'Sem troco';
        input.disabled = true;
        btn.classList.add('active');

        // Ativa modo resumo imediatamente para "Sem troco"
        confirmChange();
    } else {
        input.value = 'R$ 0,00';
        input.disabled = false;
        btn.classList.remove('active');
    }
}

function confirmChange() {
    // 1. Fecha o teclado
    const activeEl = document.activeElement;
    if (activeEl) activeEl.blur();

    const input = document.getElementById('changeAmount');
    const inputGroup = document.getElementById('changeInputGroup');
    const summary = document.getElementById('changeSummary');
    const summaryText = document.getElementById('changeSummaryText');
    const modal = document.querySelector('.payment-modal');

    // 2. Atualiza texto do resumo
    let val = input.value;
    if (!val || val === 'R$ 0,00') val = 'Sem troco';
    // Se for "Sem troco", exibe só "Sem troco". Se for valor, "Troco: R$ XX"
    if (val === 'Sem troco') {
        summaryText.textContent = 'Sem troco';
    } else {
        summaryText.textContent = 'Troco: ' + val;
    }

    // 3. Troca interfaces
    if (inputGroup) inputGroup.style.display = 'none';
    if (summary) summary.style.display = 'flex';

    // Adiciona classe para estilização compacta do container
    const changeContainer = document.getElementById('changeContainer');
    if (changeContainer) changeContainer.classList.add('summary-mode');

    // 4. Ativa modo compacto no modal
    if (modal) {
        // modal.classList.add('compact-layout'); // Desabilitado a pedido do usuário

        // Rolar para o TOPO (pedido do usuário) e limpar padding residual da teclado
        setTimeout(() => {
            const modalBody = modal.querySelector('.cardapio-modal-body');
            if (modalBody) {
                // Remove o padding extra que foi colocado para o teclado (60px+)
                modalBody.style.paddingBottom = '0';
                modalBody.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }, 100);
    }
}

function editChange() {
    const inputGroup = document.getElementById('changeInputGroup');
    const summary = document.getElementById('changeSummary');
    const modal = document.querySelector('.payment-modal');
    const input = document.getElementById('changeAmount');
    const changeContainer = document.getElementById('changeContainer');

    // 1. Troca interfaces de volta
    if (summary) summary.style.display = 'none';
    if (inputGroup) inputGroup.style.display = 'block';

    if (changeContainer) changeContainer.classList.remove('summary-mode');

    // 2. Remove modo compacto
    if (modal) {
        modal.classList.remove('compact-layout');
    }

    // 3. Foca no input se não for "Sem troco"
    if (input && !input.disabled) {
        // Pequeno delay para a UI renderizar
        setTimeout(() => {
            input.focus();
        }, 50);
    }
}

// Resetar modo compacto se o usuário voltar a editar
document.addEventListener('focus', function (e) {
    if (e.target.closest('.payment-input')) {
        const modal = document.querySelector('.payment-modal');
        if (modal) {
            modal.classList.remove('compact-layout');
        }
    }
}, true);

function toggleNoNumber() {
    hasNoNumber = !hasNoNumber;
    const input = document.getElementById('customerNumber');
    const btn = document.querySelector('.no-number-btn');

    if (hasNoNumber) {
        input.value = 'S/N';
        input.disabled = true;
        btn.classList.add('active');
    } else {
        input.value = '';
        input.disabled = false;
        btn.classList.remove('active');
    }
}

function sendOrder() {
    const name = document.getElementById('customerName').value.trim();
    const address = document.getElementById('customerAddress').value.trim();
    const number = document.getElementById('customerNumber').value.trim();
    const neighborhood = document.getElementById('customerNeighborhood').value.trim();
    const obs = document.getElementById('customerObs').value.trim();
    const changeAmount = document.getElementById('changeAmount').value.trim();

    // Validações
    if (!name) {
        alert('Por favor, preencha seu nome.');
        return;
    }
    if (!address) {
        alert('Por favor, preencha o endereço.');
        return;
    }
    if (!number && !hasNoNumber) {
        alert('Por favor, preencha o número ou selecione "Sem nº".');
        return;
    }
    if (!selectedPaymentMethod) {
        alert('Por favor, selecione a forma de pagamento.');
        return;
    }

    const totalPrice = cart.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);

    // Montar mensagem
    let msg = '🎉 Pedido enviado!\n\n' +
        'Tipo: ' + selectedOrderType.toUpperCase() + '\n' +
        'Nome: ' + name + '\n' +
        'Endereço: ' + address + ', ' + number + '\n' +
        'Bairro: ' + neighborhood + '\n' +
        'Pagamento: ' + selectedPaymentMethod.toUpperCase();

    if (selectedPaymentMethod === 'dinheiro' && changeAmount) {
        msg += ' (Troco: ' + changeAmount + ')';
    }

    msg += '\nTotal: ' + formatCurrency(totalPrice);

    if (obs) {
        msg += '\nObs: ' + obs;
    }

    msg += '\n\n(Integração com WhatsApp/backend será implementada)';

    alert(msg);

    // Limpar carrinho e fechar
    cart = [];
    selectedPaymentMethod = null;
    selectedOrderType = 'entrega';
    hasNoNumber = false;
    updateCartDisplay();
    document.getElementById('paymentModal').classList.remove('show');

    // Limpar campos
    document.getElementById('customerName').value = '';
    document.getElementById('customerAddress').value = '';
    document.getElementById('customerNumber').value = '';
    document.getElementById('customerNeighborhood').value = '';
    document.getElementById('customerObs').value = '';
    document.getElementById('changeAmount').value = '';
    document.getElementById('changeContainer').style.display = 'none';
    document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => radio.checked = false);
    document.querySelectorAll('input[name="orderType"]').forEach(radio => radio.checked = false);
}

// ========== UTILITÁRIOS ==========
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

console.log('[CARDÁPIO] JavaScript carregado ✓');
