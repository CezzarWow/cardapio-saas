/**
 * PDV CART - Gerenciamento do Carrinho (Caixa/Admin)
 * Dependências: PDVState, Utils (se houver, senão nativo)
 */

const PDVCart = {
    items: [],

    init: function () {
        console.log('[PDVCart] Inicializado');
    },

    setItems: function (newItems) {
        this.items = newItems;
        this.updateUI();
    },

    add: function (id, name, price, quantity = 1) {
        // Converte tipos para garantir segurança (mesma lógica do legacy)
        const numId = parseInt(id);
        const floatPrice = parseFloat(price);

        const existing = this.items.find(item => item.id === numId);

        if (existing) {
            existing.quantity++;
        } else {
            this.items.push({
                id: numId,
                name: name,
                price: floatPrice,
                quantity: quantity
            });
        }
        this.updateUI();
    },

    remove: function (id) {
        const index = this.items.findIndex(item => item.id === id);

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
        this.items = [];
        this.updateUI();
    },

    calculateTotal: function () {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    // A Mágica: Desenha o carrinho na tela (Refatorado de updateCartUI)
    updateUI: function () {
        const cartContainer = document.getElementById('cart-items-area');
        const emptyState = document.getElementById('cart-empty-state');
        const totalElement = document.getElementById('cart-total');
        const btnFinalizar = document.getElementById('btn-finalizar');

        // Safety check
        if (!cartContainer) return;

        cartContainer.innerHTML = '';
        const total = this.calculateTotal();

        if (this.items.length === 0) {
            cartContainer.style.display = 'none';
            if (emptyState) emptyState.style.display = 'flex';
            if (btnFinalizar) btnFinalizar.disabled = true;
        } else {
            cartContainer.style.display = 'block';
            if (emptyState) emptyState.style.display = 'none';
            if (btnFinalizar) btnFinalizar.disabled = false;

            // Renderiza Itens
            let html = '';
            this.items.forEach(item => {
                html += `
                <div style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.9rem; color: #1f2937;">${item.name}</div>
                        <div style="font-size: 0.8rem; color: #6b7280;">
                            ${item.quantity}x R$ ${PDVCart.formatMoney(item.price)}
                        </div>
                    </div>
                    <div style="display: flex; gap: 5px; align-items: center;">
                         <button onclick="PDVCart.remove(${item.id})" style="background: #fee2e2; color: #991b1b; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">-</button>
                         <button onclick="PDVCart.add(${item.id}, '${item.name.replace(/'/g, "\\'")}', ${item.price})" style="background: #dcfce7; color: #166534; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">+</button>
                    </div>
                </div>
                `;
            });
            cartContainer.innerHTML = html;
        }

        // --- ATUALIZA TOTAIS ---

        // 1. Total da Mesa (Já salvo)
        const tableInitialValue = document.getElementById('table-initial-total')?.value || "0";
        const tableInitialTotal = parseFloat(tableInitialValue);

        // 2. Grand Total (Mesa + Carrinho Atual)
        const grandTotal = total + tableInitialTotal;

        // 3. Atualiza Display
        if (totalElement) {
            totalElement.innerText = this.formatMoney(total, true);
        }

        const grandTotalElement = document.getElementById('grand-total');
        if (grandTotalElement) {
            grandTotalElement.innerText = this.formatMoney(grandTotal, true);
        }
    },

    // Helper simples de formatação (para não depender de utils externo se não quiser)
    formatMoney: function (value, withSymbol = false) {
        // Se tiver o Utils global do cardapio, use-o? Nem sempre está carregado no admin.
        // Melhor ter o próprio ou usar Intl.
        const formatted = value.toFixed(2).replace('.', ',');
        return withSymbol ? `R$ ${formatted}` : formatted;
        // Ou usar Intl:
        // return new Intl.NumberFormat('pt-BR', { style: withSymbol ? 'currency' : 'decimal', currency: 'BRL', minimumFractionDigits: 2 }).format(value);
        // O original usa .toFixed(2).replace('.', ',') manual na renderização.
    }
};

// Expor Globalmente
window.PDVCart = PDVCart;

// ==========================================
// COMPATIBILIDADE LEGADA (MANTÉM FUNCIONANDO)
// ==========================================

// Mapeia array global 'cart' para PDVCart.items
// Isso é perigoso se reatribuírem 'cart = []'. No main.js, vamos mudar isso.
window.cart = PDVCart.items;
Object.defineProperty(window, 'cart', {
    get: () => PDVCart.items,
    set: (val) => PDVCart.setItems(val)
});

// Funções Globais chamadas pelo HTML (onclick)
window.addToCart = (id, name, price) => PDVCart.add(id, name, price);
window.removeFromCart = (id) => PDVCart.remove(id);
window.updateCartUI = () => PDVCart.updateUI();
window.calculateTotal = () => PDVCart.calculateTotal(); // Usado no checkout
