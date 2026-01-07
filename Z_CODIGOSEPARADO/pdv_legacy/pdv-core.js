// ============================================
// PDV-CORE.JS - Variáveis Globais e Carrinho
// ============================================
// Este arquivo contém o núcleo compartilhado por todos os modos

// --- VARIÁVEIS GLOBAIS ---
let cart = [];
let currentPayments = [];
let totalPaid = 0;
let selectedMethod = 'dinheiro';
let isClosingTable = false;
let isClosingCommand = false;
let closingOrderId = null;

// --- FUNÇÕES DO CARRINHO ---

function addToCart(id, name, price) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ id, name, price, quantity: 1 });
    }
    updateCartUI();
}

function removeFromCart(id) {
    const index = cart.findIndex(item => item.id === id);
    if (index !== -1) {
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
        } else {
            cart.splice(index, 1);
        }
        updateCartUI();
    }
}

function calculateTotal() {
    let total = 0;
    cart.forEach(item => {
        total += item.price * item.quantity;
    });
    return total;
}

// --- FUNÇÕES UTILITÁRIAS ---

function formatCurrency(value) {
    return 'R$ ' + value.toFixed(2).replace('.', ',');
}

function formatMoneyInput(input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    value = (parseInt(value) / 100).toFixed(2);
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = value;
}

// --- CARREGAR CARRINHO RECUPERADO ---

function loadRecoveredCart() {
    if (typeof recoveredCart !== 'undefined' && Array.isArray(recoveredCart) && recoveredCart.length > 0) {
        recoveredCart.forEach(item => {
            const id = parseInt(item.product_id || item.id);
            const name = item.product_name || item.name;
            const price = parseFloat(item.price);
            const qty = parseInt(item.quantity || 1);

            const existing = cart.find(i => i.id === id);
            if (existing) {
                existing.quantity += qty;
            } else {
                cart.push({ id, name, price, quantity: qty });
            }
        });
    }
}

// --- MOSTRAR MODAL DE SUCESSO ---

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(() => { modal.style.display = 'none'; }, 1500);
    }
}

console.log('[PDV] Core carregado ✓');
