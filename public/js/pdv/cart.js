/**
 * PDV CART - Gerenciamento do Carrinho (Caixa/Admin)
 * Dependências: PDVState, Utils (se houver, senão nativo)
 */

let pendingProduct = null;
let extrasQty = 1; // Quantidade selecionada no modal

const PDVCart = {
    items: [],
    backupItems: [],

    init: function () {
},

    setItems: function (newItems) {
        if (!newItems) newItems = [];
        // Migração simples para garantir cartItemId se vier do PHP
        this.items = newItems.map(item => ({
            ...item,
            cartItemId: item.cartItemId || ('legacy_' + item.id + '_' + Math.random()),
            extras: item.extras || [],
            price: parseFloat(item.price) // Garante float
        }));
        this.updateUI();
    },

    /**
     * Adiciona item ao carrinho
     * @param {number} id - Product ID
     * @param {string} name - Product Name
     * @param {number} price - Unit Price (Base + Extras)
     * @param {number} quantity - Qty
     * @param {Array} extras - Array of extra objects {id, name, price}
     */
    add: function (id, name, price, quantity = 1, extras = []) {
        this.backupItems = [];
        const numId = parseInt(id);
        const floatPrice = parseFloat(price);

        // Gera chave única baseada nos extras para agrupar iguais
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

    // Remove por cartItemId (único)
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
            // Salva backup antes de limpar
            this.backupItems = JSON.parse(JSON.stringify(this.items));
        }
        this.items = [];

        // Limpa Mesa/Cliente selecionado (sem focar para não abrir dropdown)
        const clientId = document.getElementById('current_client_id');
        const tableId = document.getElementById('current_table_id');
        const clientName = document.getElementById('current_client_name');

        if (clientId) clientId.value = '';
        if (tableId) tableId.value = '';
        if (clientName) clientName.value = '';

        const selectedArea = document.getElementById('selected-client-area');
        const searchArea = document.getElementById('client-search-area');
        const searchInput = document.getElementById('client-search');
        const results = document.getElementById('client-results');

        if (selectedArea) selectedArea.style.display = 'none';
        if (searchArea) searchArea.style.display = 'flex';
        if (searchInput) searchInput.value = '';
        if (results) results.style.display = 'none';

        // Reseta o botão Finalizar para estado padrão
        const btn = document.getElementById('btn-finalizar');
        if (btn) {
            btn.innerText = 'Finalizar';
            btn.style.backgroundColor = '';
        }

        // Esconde botão Salvar Comanda se existir
        const btnSave = document.getElementById('btn-save-command');
        if (btnSave) btnSave.style.display = 'none';

        // Reseta estado do PDV
        if (typeof PDVState !== 'undefined') {
            PDVState.set({ modo: 'balcao', mesaId: null, clienteId: null });
        }

        this.updateUI();
    },

    undoClear: function () {
        if (this.backupItems.length > 0) {
            this.items = JSON.parse(JSON.stringify(this.backupItems));
            this.backupItems = []; // Consome o backup
            this.updateUI();
        }
    },

    calculateTotal: function () {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    // A Mágica: Desenha o carrinho na tela (Refatorado de updateCartUI)
    updateUI: function () {
        // Referências
        const cartContainer = document.getElementById('cart-items-area');
        const emptyState = document.getElementById('cart-empty-state');
        const totalElement = document.getElementById('cart-total');
        const btnFinalizar = document.getElementById('btn-finalizar');
        const btnUndo = document.getElementById('btn-undo-clear');

        // Safety check
        if (!cartContainer) return;

        cartContainer.innerHTML = '';
        const total = this.calculateTotal();

        // Lógica do botão Desfazer (Header)
        if (btnUndo) {
            if (this.items.length === 0 && this.backupItems.length > 0) {
                btnUndo.style.display = 'flex';
            } else {
                btnUndo.style.display = 'none';
            }
        }

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

            // Renderiza Itens
            let html = '';
            this.items.forEach(item => {
                // Renderiza extras
                let extrasHtml = '';
                if (item.extras && item.extras.length > 0) {
                    extrasHtml = '<div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">';
                    item.extras.forEach(ex => {
                        extrasHtml += `+ ${ex.name}<br>`;
                    });
                    extrasHtml += '</div>';
                }

                html += `
                <div style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.9rem; color: #1f2937;">${item.name}</div>
                        ${extrasHtml}
                        <div style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;">
                            ${item.quantity}x R$ ${PDVCart.formatMoney(item.price)}
                        </div>
                    </div>
                    <div style="display: flex; gap: 5px; align-items: center; margin-top: 5px;">
                         <button onclick="PDVCart.remove('${item.cartItemId}')" style="background: #fee2e2; color: #991b1b; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">-</button>
                         <button onclick='PDVCart.add(${item.id}, "${item.name.replace(/"/g, '&quot;').replace(/'/g, "\\'")}", ${item.price}, 1, ${JSON.stringify(item.extras || []).replace(/'/g, "&#39;")})' style="background: #dcfce7; color: #166534; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">+</button>
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

window.PDVCart = PDVCart;


// ==========================================
// EXPOSIÇÃO GLOBAL E EVENTOS
// ==========================================

// Namespace Global PDV (se não existir)
window.PDV = window.PDV || {};

/**
 * Função Global para clique no produto (chamada pelo HTML onclick)
 * Mais robusto que event listeners em estruturas dinâmicas/PHP misto.
 */
window.PDV.clickProduct = function (id, name, price, hasExtras, encodedExtras = '[]') {
    // Normaliza booleanos que podem vir como string ou int do PHP
    const hasExtrasBool = (hasExtras === true || hasExtras === 'true' || hasExtras === 1 || hasExtras === '1');
if (hasExtrasBool) {
        pendingProduct = { id, name, price: parseFloat(price) };
        openExtrasModal(id);
    } else {
        PDVCart.add(id, name, parseFloat(price));
    }
};

// Mantém compatibilidade com chamadas antigas se existirem
window.addToCart = function (id, name, price, hasExtras) {
    window.PDV.clickProduct(id, name, price, hasExtras);
};

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
});

// ==========================================
// COMPATIBILIDADE LEGADA E MODAL
// ==========================================

// Mapeia array global 'cart' para PDVCart.items
window.cart = PDVCart.items;
Object.defineProperty(window, 'cart', {
    get: () => PDVCart.items,
    set: (val) => PDVCart.setItems(val)
});

// Funções Globais (se algo externo chamar ainda)
window.addToCart = function (id, name, price, hasExtras = false) {
if (hasExtras) {
        pendingProduct = { id, name, price };
        openExtrasModal(id);
    } else {
        PDVCart.add(id, name, price);
    }
};

window.openExtrasModal = async function (productId) {
    const modal = document.getElementById('extrasModal');
    const content = document.getElementById('extras-modal-content');

    if (!modal) {
        console.error('Modal #extrasModal não encontrado no DOM!');
        alert('Erro interno: Modal de adicionais não encontrado.');
        return;
    }

    // Reseta quantidade para 1
    extrasQty = 1;
    const qtyDisplay = document.getElementById('extras-qty-display');
    if (qtyDisplay) qtyDisplay.innerText = '1';

    modal.style.display = 'flex';
    content.innerHTML = '<div style="text-align: center; margin-top: 20px; color: #64748b;">Carregando opções... <span class="loader"></span></div>';

    // Garante que BASE_URL não tenha barras duplas ou falte
    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
    const url = `${baseUrl}/admin/loja/adicionais/get-product-extras?product_id=${productId}`;
try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP Error ${response.status}`);

        const groups = await response.json();
        renderExtras(groups);
    } catch (e) {
        console.error(e);
        content.innerHTML = `
            <div style="color:#ef4444; text-align: center; padding: 20px;">
                <p><strong>Erro ao carregar opções.</strong></p>
                <p style="font-size: 0.8rem; color: #7f1d1d;">${e.message}</p>
                <button onclick="closeExtrasModal()" style="margin-top:10px; padding:5px 10px;">Fechar</button>
            </div>
        `;
    }
};

window.closeExtrasModal = function () {
    const modal = document.getElementById('extrasModal');
    if (modal) modal.style.display = 'none';
    pendingProduct = null;
    // Limpa checkboxes
    const content = document.getElementById('extras-modal-content');
    if (content) content.innerHTML = '';
};

window.renderExtras = function (groups) {
    const container = document.getElementById('extras-modal-content');
    container.innerHTML = '';

    if (!groups || groups.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <p style="color:#64748b;">Nenhuma opção extra disponível para este produto.</p>
                <button onclick="confirmExtras()" style="background: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;"> Adicionar sem extras</button>
            </div>
        `;
        return;
    }

    // ... (rest of render logic is fine, keeping simple)
    groups.forEach(group => {
        const groupDiv = document.createElement('div');
        groupDiv.style.marginBottom = '20px';

        const title = document.createElement('h4');
        title.innerHTML = `${group.name}`;
        if (group.required == 1) {
            title.innerHTML += ' <span style="color:#ef4444; font-size:0.8em;">(Obrigatório)</span>';
        }
        title.style.margin = '0 0 10px 0';
        title.style.color = '#334155';
        groupDiv.appendChild(title);

        group.items.forEach(item => {
            const label = document.createElement('label');
            label.style.display = 'flex';
            label.style.justifyContent = 'space-between';
            label.style.padding = '10px';
            label.style.border = '1px solid #e2e8f0';
            label.style.marginBottom = '8px';
            label.style.borderRadius = '8px';
            label.style.cursor = 'pointer';
            label.style.transition = 'all 0.2s';

            const formattedPrice = parseFloat(item.price).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            label.innerHTML = `
                <div style="display:flex; align-items:center; gap: 10px;">
                    <input type="checkbox" name="extra_group_${group.id}" value="${item.id}" 
                           data-name="${item.name}" data-price="${item.price}" 
                           class="extra-input" style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="font-size: 0.95rem; color: #1e293b;">${item.name}</span>
                </div>
                <span style="font-weight:600; color:#059669; font-size: 0.9rem;">+ ${formattedPrice}</span>
            `;

            const checkbox = label.querySelector('input');
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    label.style.borderColor = '#16a34a';
                    label.style.background = '#f0fdf4';
                } else {
                    label.style.borderColor = '#e2e8f0';
                    label.style.background = 'white';
                }
            });

            groupDiv.appendChild(label);
        });

        container.appendChild(groupDiv);
    });
};

window.confirmExtras = function () {
    if (!pendingProduct) return;

    const selectedExtras = [];
    let totalPrice = parseFloat(pendingProduct.price);

    const inputs = document.querySelectorAll('.extra-input:checked');
    inputs.forEach(input => {
        const price = parseFloat(input.dataset.price);
        selectedExtras.push({
            id: parseInt(input.value),
            name: input.dataset.name,
            price: price
        });
        totalPrice += price;
    });

    // [FIX] Usa a quantidade selecionada no modal
    PDVCart.add(pendingProduct.id, pendingProduct.name, totalPrice, extrasQty, selectedExtras);
    closeExtrasModal();
};

// Funções de controle de quantidade no modal de adicionais
window.increaseExtrasQty = function () {
    extrasQty++;
    const display = document.getElementById('extras-qty-display');
    if (display) display.innerText = extrasQty;
};

window.decreaseExtrasQty = function () {
    if (extrasQty > 1) {
        extrasQty--;
        const display = document.getElementById('extras-qty-display');
        if (display) display.innerText = extrasQty;
    }
};

window.removeFromCart = (id) => {
    const item = PDVCart.items.find(i => i.id === id);
    if (item) PDVCart.remove(item.cartItemId);
};
window.updateCartUI = () => PDVCart.updateUI();
window.calculateTotal = () => PDVCart.calculateTotal();
window.clearCart = () => PDVCart.clear();

