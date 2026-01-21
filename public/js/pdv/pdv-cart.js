/**
 * PDVCart - Módulo Unificado do Carrinho PDV
 * Responsável por:
 * 1. State do Carrinho (items, backup)
 * 2. Lógica Core (add, remove, total)
 * 3. Renderização da UI do Carrinho
 */
const PDVCart = {
    items: [],
    backupItems: [],

    // ==========================================
    // INIT
    // ==========================================
    init: function () {
        // Inicialização se necessária
    },

    // ==========================================
    // CORE LOGIC
    // ==========================================
    setItems: function (newItems) {
        if (!newItems) newItems = [];
        this.items = newItems.map(item => ({
            ...item,
            cartItemId: item.cartItemId || ('legacy_' + item.id + '_' + Math.random()),
            extras: item.extras || [],
            price: parseFloat(item.price)
        }));
        this.updateUI();
    },

    add: function (id, name, price, quantity = 1, extras = []) {
        this.backupItems = [];
        const numId = parseInt(id);
        const floatPrice = parseFloat(price);
        const extrasKey = JSON.stringify(extras.sort((a, b) => a.id - b.id));

        const existing = this.items.find(item =>
            item.id === numId &&
            JSON.stringify(item.extras.sort((a, b) => a.id - b.id)) === extrasKey
        );

        if (existing) {
            existing.quantity += quantity;
        } else {
            this.items.push({
                cartItemId: 'item_' + Date.now() + '_' + Math.random(),
                id: numId,
                name: name,
                price: floatPrice,
                quantity: quantity,
                extras: extras
            });
        }
        this.updateUI();
    },



    increaseQty: function (cartItemId) {
        const item = this.items.find(i => i.cartItemId === cartItemId);
        if (item) {
            item.quantity++;
            this.updateUI();
        }
    },

    remove: function (cartItemId) {
        const index = this.items.findIndex(item => item.cartItemId === cartItemId);
        if (index > -1) {
            const item = this.items[index];
            if (item.quantity > 1) {
                item.quantity--;
            } else {
                this.items.splice(index, 1);
            }
        }
        this.updateUI();
    },

    clear: function () {
        if (this.items.length > 0) {
            this.backupItems = JSON.parse(JSON.stringify(this.items));
        }
        this.items = [];

        // Verifica contexto travado (Mesa/Comanda)
        const currentOrderIdInput = document.getElementById('current_order_id');
        const isContextLocked = currentOrderIdInput && currentOrderIdInput.value && currentOrderIdInput.value !== '';

        if (!isContextLocked) {
            // Limpa inputs de contexto
            ['current_client_id', 'current_table_id', 'current_client_name'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });

            // Reseta UI de Cliente
            const selectedArea = document.getElementById('selected-client-area');
            const searchArea = document.getElementById('client-search-area');
            const searchInput = document.getElementById('client-search');
            const results = document.getElementById('client-results');

            if (selectedArea) selectedArea.style.display = 'none';
            if (searchArea) searchArea.style.display = 'flex';
            if (searchInput) searchInput.value = '';
            if (results) results.style.display = 'none';

            const btnSave = document.getElementById('btn-save-command');
            if (btnSave) btnSave.style.display = 'none';

            if (typeof PDVState !== 'undefined') {
                PDVState.set({ modo: 'balcao', mesaId: null, clienteId: null });
            }

            // [FIX] Resetar tipo de pedido para 'local' - Abordagem Híbrida (Lógica + Visual Forçado)

            // 1. Tenta via lógica oficial
            if (typeof CheckoutOrderType !== 'undefined') {
                CheckoutOrderType.selectOrderType('local', null);
            } else if (typeof selectOrderType === 'function') {
                selectOrderType('local', null);
            }

            // 2. FORÇA O RESET VISUAL (Garante que a UI atualize mesmo se a lógica falhar)
            try {
                const allBtns = document.querySelectorAll('.order-toggle-btn');
                allBtns.forEach(btn => {
                    btn.classList.remove('active');
                    // Reset estilos inline para inativo
                    btn.style.borderColor = '#cbd5e1';
                    btn.style.background = 'white';
                    btn.style.color = '#1e293b';
                    // Remove checkmark
                    const check = btn.querySelector('.btn-checkmark');
                    if (check) check.remove();
                });

                const btnLocal = document.querySelector('.order-toggle-btn[data-type="local"]');
                if (btnLocal) {
                    btnLocal.classList.add('active');
                    // Estilos ativo (local)
                    btnLocal.style.borderColor = '#2563eb';
                    btnLocal.style.background = '#eff6ff';
                    btnLocal.style.color = '#2563eb';
                }

                const inputType = document.getElementById('selected_order_type');
                if (inputType) inputType.value = 'local';
            } catch (e) {
                console.error('Erro no reset visual forçado:', e);
            }
        }

        const btn = document.getElementById('btn-finalizar');
        if (btn) {
            btn.innerText = 'Finalizar';
            btn.style.backgroundColor = '';
        }

        this.updateUI();
    },

    undoClear: function () {
        if (this.backupItems.length > 0) {
            this.items = JSON.parse(JSON.stringify(this.backupItems));
            this.backupItems = [];
            this.updateUI();
        }
    },

    calculateTotal: function () {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    // ==========================================
    // UI LOGIC
    // ==========================================
    updateUI: function () {
        const cartContainer = document.getElementById('cart-items-area');
        const emptyState = document.getElementById('cart-empty-state');
        const totalElement = document.getElementById('cart-total');
        const btnFinalizar = document.getElementById('btn-finalizar');
        const btnUndo = document.getElementById('btn-undo-clear');

        if (!cartContainer) return;

        cartContainer.innerHTML = '';
        const total = this.calculateTotal();

        // Botão Undo
        if (btnUndo) {
            btnUndo.style.display = (this.items.length === 0 && this.backupItems.length > 0) ? 'flex' : 'none';
        }

        // Empty State vs Items
        if (this.items.length === 0) {
            cartContainer.style.display = 'none';
            if (emptyState) {
                emptyState.style.display = 'flex';
                emptyState.innerHTML = `
                    <i data-lucide="shopping-cart" size="48" color="#e5e7eb" style="margin-bottom: 1rem;"></i>
                    <p>Carrinho Vazio</p>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
            if (btnFinalizar) btnFinalizar.disabled = true;
        } else {
            cartContainer.style.display = 'block';
            if (emptyState) emptyState.style.display = 'none';
            if (btnFinalizar) btnFinalizar.disabled = false;

            // Render Items
            let html = '';
            this.items.forEach(item => {
                let extrasHtml = '';
                if (item.extras && item.extras.length > 0) {
                    extrasHtml = '<div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">';
                    item.extras.forEach(ex => extrasHtml += `+ ${ex.name}<br>`);
                    extrasHtml += '</div>';
                }

                html += `
                <div style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.9rem; color: #1f2937;">${item.name}</div>
                        ${extrasHtml}
                        <div style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;">
                            ${item.quantity}x ${this.formatMoney(item.price, true)}
                        </div>
                    </div>
                    <div style="display: flex; gap: 5px; align-items: center; margin-top: 5px;">
                         <button onclick="PDVCart.remove('${item.cartItemId}')" style="background: #fee2e2; color: #991b1b; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">-</button>
                         <button onclick="PDVCart.increaseQty('${item.cartItemId}')" style="background: #dcfce7; color: #166534; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">+</button>
                    </div>
                </div>`;
            });
            cartContainer.innerHTML = html;
        }

        // Totais
        const tableInitialValue = document.getElementById('table-initial-total')?.value || "0";
        const tableInitialTotal = parseFloat(tableInitialValue);
        const grandTotal = total + tableInitialTotal;

        if (totalElement) totalElement.innerText = this.formatMoney(total, true);
        const grandTotalElement = document.getElementById('grand-total');
        if (grandTotalElement) grandTotalElement.innerText = this.formatMoney(grandTotal, true);
    },

    formatMoney: function (value, withSymbol = false) {
        const formatted = value.toFixed(2).replace('.', ',');
        return withSymbol ? `R$ ${formatted}` : formatted;
    },

    // ==========================================
    // PERSISTÊNCIA / MIGRAÇÃO
    // ==========================================
    saveForMigration: function () {
        if (this.items.length > 0) {
            sessionStorage.setItem('pdv_migration_cart', JSON.stringify(this.items));
        }
    },

    recoverFromMigration: function () {
        const data = sessionStorage.getItem('pdv_migration_cart');
        if (data) {
            try {
                const items = JSON.parse(data);
                if (Array.isArray(items)) {
                    items.forEach(item => {
                        const { cartItemId, ...cleanItem } = item;
                        this.add(cleanItem.id, cleanItem.name, cleanItem.price, cleanItem.quantity, cleanItem.extras);
                    });
                }
            } catch (e) {
                console.error('Erro ao recuperar carrinho:', e);
            }
            sessionStorage.removeItem('pdv_migration_cart');
        }
    }
};

// ==========================================
// FUNÇÕES DE CLIQUE (Mapeamento HTML onclick)
// ==========================================
window.PDV = window.PDV || {};
window.PDV.clickProduct = function (id, name, price, hasExtras, encodedExtras = '[]') {
    const hasExtrasBool = (hasExtras === true || hasExtras === 'true' || hasExtras === 1 || hasExtras === '1');
    const floatPrice = parseFloat(price);

    if (hasExtrasBool) {
        if (window.PDVExtras) {
            PDVExtras.open(id, name, floatPrice);
        } else {
            console.error('PDVExtras module not loaded');
            alert('Erro: Módulo de adicionais não carregado');
        }
    } else {
        if (window.PDVCart) {
            PDVCart.add(id, name, floatPrice);
        } else {
            console.error('PDVCart not loaded');
        }
    }
};

// ==========================================
// GLOBALS & ALIASES (Compatibilidade)
// ==========================================
window.PDVCart = PDVCart;

// IMPORTANTE: Usar getter para que window.cart sempre aponte para o array atual
// (evita referência stale quando this.items = [] substitui o array)
Object.defineProperty(window, 'cart', {
    get: function () { return PDVCart.items; },
    configurable: true
});

window.addToCart = (id, name, price, hasExtras = false) => {
    if (hasExtras) {
        if (window.PDVExtras) {
            PDVExtras.open(id, name, price);
        } else {
            console.error('PDVExtras module not loaded');
            alert('Erro: Módulo de adicionais não carregado');
        }
    } else {
        PDVCart.add(id, name, price);
    }
};
window.removeFromCart = (id) => {
    const item = PDVCart.items.find(i => i.id === id);
    if (item) PDVCart.remove(item.cartItemId);
};
window.updateCartUI = () => PDVCart.updateUI();
window.calculateTotal = () => PDVCart.calculateTotal();
window.clearCart = () => PDVCart.clear();

// Init removido. O orquestrador PDV.init() chama setItems().
// document.addEventListener('DOMContentLoaded', () => {
//     if (typeof recoveredCart !== 'undefined' && recoveredCart.length > 0) {
//         PDVCart.setItems(recoveredCart);
//     }
// });
