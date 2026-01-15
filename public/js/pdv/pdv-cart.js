/**
 * PDVCart - Módulo Unificado do Carrinho PDV
 * Consolida: State, Core Logic, UI e Extras Modal.
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
                         <button onclick='PDVCart.add(${item.id}, "${item.name.replace(/"/g, '&quot;').replace(/'/g, "\\'")}", ${item.price}, 1, ${JSON.stringify(item.extras || []).replace(/'/g, "&#39;")})' style="background: #dcfce7; color: #166534; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">+</button>
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
    // EXTRAS MODAL LOGIC
    // ==========================================
    Extras: {
        pendingProduct: null,
        qty: 1,

        open: async function (productId) {
            const modal = document.getElementById('extrasModal');
            const content = document.getElementById('extras-modal-content');

            if (!modal) return alert('Erro: Modal de adicionais não encontrado.');

            this.qty = 1;
            const qtyDisplay = document.getElementById('extras-qty-display');
            if (qtyDisplay) qtyDisplay.innerText = '1';

            modal.style.display = 'flex';
            content.innerHTML = '<div style="text-align: center; margin-top: 20px;">Carregando...</div>';

            const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
            try {
                const response = await fetch(`${baseUrl}/admin/loja/adicionais/get-product-extras?product_id=${productId}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const groups = await response.json();
                this.render(groups);
            } catch (e) {
                content.innerHTML = `<div style="color:red; text-align:center;">Erro ao carregar: ${e.message}</div>`;
            }
        },

        close: function () {
            const modal = document.getElementById('extrasModal');
            if (modal) modal.style.display = 'none';
            this.pendingProduct = null;
        },

        render: function (groups) {
            const container = document.getElementById('extras-modal-content');
            container.innerHTML = '';

            if (!groups || groups.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 20px;">
                        <p>Sem adicionais.</p>
                        <button onclick="PDVCart.Extras.confirm()" style="background: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; border: none;">Adicionar sem extras</button>
                    </div>`;
                return;
            }

            groups.forEach(group => {
                const groupDiv = document.createElement('div');
                groupDiv.style.marginBottom = '20px';
                groupDiv.innerHTML = `<h4>${group.name} ${group.required == 1 ? '<span style="color:red">(Obrigatório)</span>' : ''}</h4>`;

                group.items.forEach(item => {
                    const label = document.createElement('label');
                    label.style.display = 'flex';
                    label.style.justifyContent = 'space-between';
                    label.style.padding = '10px';
                    label.style.border = '1px solid #e2e8f0';
                    label.style.marginBottom = '5px';
                    label.innerHTML = `
                        <div>
                            <input type="checkbox" name="extra_group_${group.id}" value="${item.id}" 
                                   data-name="${item.name}" data-price="${item.price}" class="extra-input">
                            <span>${item.name}</span>
                        </div>
                        <span style="color:green">+ R$ ${parseFloat(item.price).toFixed(2).replace('.', ',')}</span>`;
                    groupDiv.appendChild(label);
                });
                container.appendChild(groupDiv);
            });
        },

        confirm: function () {
            if (!this.pendingProduct) return;
            const selectedExtras = [];
            let totalPrice = parseFloat(this.pendingProduct.price);

            document.querySelectorAll('.extra-input:checked').forEach(input => {
                const price = parseFloat(input.dataset.price);
                selectedExtras.push({ id: parseInt(input.value), name: input.dataset.name, price: price });
                totalPrice += price;
            });

            PDVCart.add(this.pendingProduct.id, this.pendingProduct.name, totalPrice, this.qty, selectedExtras);
            this.close();
        },

        increaseQty: function () {
            this.qty++;
            document.getElementById('extras-qty-display').innerText = this.qty;
        },

        decreaseQty: function () {
            if (this.qty > 1) {
                this.qty--;
                document.getElementById('extras-qty-display').innerText = this.qty;
            }
        }
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
// GLOBALS & ALIASES (Compatibilidade)
// ==========================================
window.PDVCart = PDVCart;
window.cart = PDVCart.items;
window.addToCart = (id, name, price, hasExtras = false) => {
    if (hasExtras) {
        PDVCart.Extras.pendingProduct = { id, name, price };
        PDVCart.Extras.open(id);
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

// Extras Globals (Legacy Onclick support)
window.openExtrasModal = (id) => PDVCart.Extras.open(id);
window.closeExtrasModal = () => PDVCart.Extras.close();
window.confirmExtras = () => PDVCart.Extras.confirm();
window.increaseExtrasQty = () => PDVCart.Extras.increaseQty();
window.decreaseExtrasQty = () => PDVCart.Extras.decreaseQty();

// Init
document.addEventListener('DOMContentLoaded', () => {
    if (typeof recoveredCart !== 'undefined' && recoveredCart.length > 0) {
        PDVCart.setItems(recoveredCart);
    }
});
