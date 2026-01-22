/* pdv-bundle - Generated 2026-01-22T03:59:56.806Z */


/* ========== pdv/state.js ========== */
/**
 * PDV STATE - Gerenciamento de Estado Global do PDV
 * Centraliza o estado da aplicação (Balcão, Mesa, Comanda, Retirada)
 */

const PDVState = (() => {
    // Estado Privado
    let state = {
        modo: 'balcao',        // balcao | mesa | comanda | retirada
        pedidoId: null,        // ID do pedido (se existir)
        mesaId: null,          // ID da mesa
        clienteId: null,       // ID do cliente
        status: 'aberto',      // aberto | pago | editando_pago
        fechandoConta: false   // true = processo de checkout iniciado
    };

    // Transições Válidas de Status
    const TRANSICOES = {
        'aberto': ['pago'],
        'pago': ['editando_pago'],
        'editando_pago': ['pago']
    };

    return {
        // Getter (Retorna cópia para imutabilidade superficial)
        getState() {
            return { ...state };
        },

        // Reset completo (Volta ao Balcão)
        reset() {
            state = {
                modo: 'balcao',
                pedidoId: null,
                mesaId: null,
                clienteId: null,
                status: 'aberto',
                fechandoConta: false
            };
        },

        // Atualizador Genérico (exceto status)
        set(patch) {
            const { status, ...rest } = patch;
            if (status) console.warn('[PDVState] Use mudarStatus() para alterar status.');
            state = { ...state, ...rest };

            // Console Debug (Opcional)

        },

        // Inicializador de Status (Bypass de validação - apenas no Load)
        initStatus(novoStatus) {
            if (['aberto', 'pago', 'editando_pago'].includes(novoStatus)) {
                state.status = novoStatus;
                return true;
            }
            console.error(`[PDVState] Status inicial inválido: ${novoStatus}`);
            return false;
        },

        // Transição de Status (Com validação)
        mudarStatus(novoStatus) {
            if (!TRANSICOES[state.status]?.includes(novoStatus)) {
                console.error(`[PDVState] Transição inválida: ${state.status} → ${novoStatus}`);
                return false;
            }
            state.status = novoStatus;
            return true;
        }
    };
})();

// Expor Globalmente
window.PDVState = PDVState;


/* ========== pdv/pdv-extras.js ========== */
/**
 * PDVExtras - Módulo para Gerenciamento de Adicionais
 * Responsável por:
 * 1. Abrir/Fechar Modal de Adicionais
 * 2. Buscar grupos de adicionais via API
 * 3. Renderizar opções
 * 4. Coletar seleção e enviar para o Carrinho
 */
const PDVExtras = {
    pendingProduct: null,
    qty: 1,

    init: function () {
        // Inicializa listeners se necessário
    },

    open: async function (productId, productName, productPrice) {
        const modal = document.getElementById('extrasModal');
        const content = document.getElementById('extras-modal-content');

        if (!modal) return alert('Erro: Modal de adicionais não encontrado.');

        // Salva estado pendente
        this.pendingProduct = { id: productId, name: productName, price: parseFloat(productPrice) };
        this.qty = 1;

        const qtyDisplay = document.getElementById('extras-qty-display');
        if (qtyDisplay) qtyDisplay.innerText = '1';

        modal.style.display = 'flex';
        // Reset position just in case
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.zIndex = '9999';

        // Small delay to allow display flex to apply before opacity transition
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });

        content.innerHTML = '<div style="text-align: center; margin-top: 100px; color: #64748b;">Carregando opções...</div>';

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
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200); // Matches CSS transition
        }
        this.pendingProduct = null;
    },

    render: function (groups) {
        const container = document.getElementById('extras-modal-content');
        container.innerHTML = '';

        if (!groups || groups.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <p>Sem adicionais.</p>
                    <button onclick="PDVExtras.confirm()" style="background: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; border: none;">Adicionar sem extras</button>
                </div>`;
            return;
        }

        groups.forEach(group => {
            const groupDiv = document.createElement('div');
            groupDiv.className = 'extras-group';
            groupDiv.innerHTML = `<h4>${group.name} ${group.required == 1 ? '<span style="color:red; font-size: 0.8em">*</span>' : ''}</h4>`;

            group.items.forEach(item => {
                const label = document.createElement('label');
                label.className = 'extras-option-label';
                // Add click listener to toggle 'checked' class
                label.addEventListener('change', (e) => {
                    if (e.target.checked) label.classList.add('checked');
                    else label.classList.remove('checked');
                });

                label.innerHTML = `
                    <div style="display:flex; align-items:center;">
                        <input type="checkbox" name="extra_group_${group.id}" value="${item.id}" 
                               data-name="${item.name}" data-price="${item.price}" class="extra-input extras-option-checkbox">
                        <span>${item.name}</span>
                    </div>
                    <span class="extras-price-tag">+ R$ ${parseFloat(item.price).toFixed(2).replace('.', ',')}</span>`;
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

        // Chama o Carrinho para adicionar
        if (window.PDVCart) {
            PDVCart.add(this.pendingProduct.id, this.pendingProduct.name, totalPrice, this.qty, selectedExtras);
        } else {
            console.error('PDVCart não encontrado!');
        }

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
};

// Globals (Legacy Support & HTML onclicks)
window.PDVExtras = PDVExtras;
window.openExtrasModal = (id) => console.warn('Use PDVExtras.open()'); // Deprecated but safe
window.closeExtrasModal = () => PDVExtras.close();
window.confirmExtras = () => PDVExtras.confirm();
window.increaseExtrasQty = () => PDVExtras.increaseQty();
window.decreaseExtrasQty = () => PDVExtras.decreaseQty();


/* ========== pdv/pdv-cart.js ========== */
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


/* ========== pdv/tables.js ========== */
/**
 * TABLES.JS - Orquestrador de Gestão de Mesas e Clientes
 * 
 * Este arquivo inicializa o objeto PDVTables e expõe as funções globais.
 * As implementações estão em arquivos separados:
 * - tables-mesa.js (Lógica de Mesas)
 * - tables-cliente.js (Lógica de Clientes)
 * - tables-client-modal.js (Modal de Novo Cliente)
 * 
 * Dependências: PDVState
 * ORDEM DE CARREGAMENTO:
 * 1. tables.js (este arquivo) - cria o objeto base
 * 2. tables-mesa.js - estende com funções de mesa
 * 3. tables-cliente.js - estende com funções de cliente
 * 4. tables-client-modal.js - estende com modal de cliente
 */

const PDVTables = {
    // Armazena referência para limpeza
    documentClickHandler: null,

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================
    init: function () {
        this.bindEvents();
    },

    // ==========================================
    // BIND DE EVENTOS
    // ==========================================
    bindEvents: function () {
        const input = document.getElementById('client-search');
        if (!input) return;

        // 0. Click Outside: Limpa listener antigo e cria novo
        if (this.documentClickHandler) {
            document.removeEventListener('click', this.documentClickHandler);
        }

        this.documentClickHandler = (e) => {
            const results = document.getElementById('client-results');
            // Verifica se elementos ainda existem no DOM
            const currentInput = document.getElementById('client-search');

            // Se o input mudou (SPA reload), este listener pode ser antigo.
            // Mas como removemos no init(), teoricamente está ok.
            // Proteção extra: se currentInput !== input, este listener é fantasma (mas não deveria existir)

            if (currentInput && results && !currentInput.contains(e.target) && !results.contains(e.target)) {
                results.style.display = 'none';
            }
        };
        document.addEventListener('click', this.documentClickHandler);

        // 1. Focus: Mostra mesas (sem digitar)
        input.addEventListener('focus', () => {
            if (input.value.trim() === '') {
                this.fetchTables();
            }
        });

        // 2. Click no Input: Também dispara a busca se já estiver focado
        // Isso resolve o caso onde o usuário clica, fecha (clicando fora) e clica de novo no input focado
        input.addEventListener('click', () => {
            const results = document.getElementById('client-results');
            // Se estiver vazio e fechado, abre
            if (input.value.trim() === '' && (!results || results.style.display === 'none')) {
                this.fetchTables();
            }
        });

        // 3. Input: Busca Clientes
        input.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            const term = e.target.value;

            if (term.length < 2) {
                if (term.length === 0) this.fetchTables(); // Voltou a vazio -> mesas
                else document.getElementById('client-results').style.display = 'none';
                return;
            }

            this.searchTimeout = setTimeout(() => {
                fetch('clientes/buscar?q=' + term)
                    .then(r => r.json())
                    .then(data => this.renderClientResults(data));
            }, 300);
        });
    }
};

// ==========================================
// EXPOR GLOBALMENTE
// ==========================================
window.PDVTables = PDVTables;

// ==========================================
// COMPATIBILIDADE (Aliases Globais)
// ==========================================
window.fetchTables = () => PDVTables.fetchTables();
window.selectTable = (t) => PDVTables.selectTable(t);
window.selectClient = (id, n) => PDVTables.selectClient(id, n);
window.clearClient = () => PDVTables.clearClient();
window.saveClient = () => PDVTables.saveClient();
window.renderClientResults = (d) => PDVTables.renderClientResults(d);
window.renderTableResults = (d) => PDVTables.renderTableResults(d);
window.openClientModal = () => document.getElementById('clientModal').style.display = 'flex';


/* ========== pdv/tables-mesa.js ========== */
/**
 * TABLES-MESA.JS - Lógica de Mesas
 * Dependências: PDVTables (tables.js), PDVState
 * 
 * Este módulo estende PDVTables com as funções de mesas.
 */

(function () {
    'use strict';

    // ==========================================
    // BUSCAR MESAS
    // ==========================================
    PDVTables.fetchTables = function () {
        fetch('mesas/buscar?nocache=' + new Date().getTime())
            .then(r => r.json())
            .then(data => this.renderTableResults(data));
    };

    // ==========================================
    // RENDERIZAR RESULTADOS DE MESAS
    // ==========================================
    PDVTables.renderTableResults = function (tables) {
        const results = document.getElementById('client-results');
        results.innerHTML = '';

        if (!tables.length) {
            results.style.display = 'none';
            return;
        }

        results.style.display = 'block';

        // Header com botão fechar
        const header = document.createElement('div');
        header.style.cssText = "display: flex; justify-content: space-between; align-items: center; padding: 10px 15px 5px;";
        header.innerHTML = `
            <small style="color:#64748b; font-weight:700; font-size:0.75rem;">MESAS DISPONÍVEIS</small>
            <button onclick="document.getElementById('client-results').style.display='none'" 
                    style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1.2rem; font-weight: bold; padding: 0; line-height: 1;"
                    title="Fechar">&times;</button>
        `;
        results.appendChild(header);

        // Grid (5 por linha, alinhado)
        const grid = document.createElement('div');
        grid.style.cssText = "display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; padding: 10px;";

        tables.forEach(table => {
            const isOccupied = table.status === 'ocupada';
            const bg = isOccupied ? '#fef2f2' : '#f0fdf4';
            const border = isOccupied ? '#ef4444' : '#22c55e';
            const text = isOccupied ? '#991b1b' : '#166534';

            const card = document.createElement('div');
            card.className = 'table-card-item';
            card.style.cssText = `
                width: 50px; height: 50px; 
                background: ${bg}; 
                border: 2px solid ${border}; 
                border-radius: 10px; 
                display: flex; flex-direction: column; 
                align-items: center; justify-content: center; 
                cursor: pointer; transition: transform 0.1s;
                position: relative;
            `;

            card.innerHTML = `
                <span style="font-weight:800; font-size:1.1rem; color:${text};">${table.number}</span>
                ${isOccupied ? '<span style="font-size:0.6rem; color:#dc2626; font-weight:bold;">OCP</span>' : ''}
            `;

            // Events
            card.onmouseover = () => card.style.transform = 'scale(1.05)';
            card.onmouseout = () => card.style.transform = 'scale(1)';
            card.onclick = () => this.selectTable(table);

            grid.appendChild(card);
        });

        results.appendChild(grid);
    };

    // ==========================================
    // SELECIONAR MESA
    // ==========================================
    PDVTables.selectTable = function (table) {
        // [ALTERADO] Não navega mais automaticamente para comanda
        // A navegação para comanda só ocorre via grid na aba Mesas
        // Aqui apenas vinculamos a mesa ao pedido atual (para Balcão)

        // Atualiza Estado
        PDVState.set({ modo: 'mesa', mesaId: table.id, clienteId: null });

        // Atualiza UI inputs hidden
        document.getElementById('current_table_id').value = table.id;
        document.getElementById('current_client_id').value = '';

        // [FIX] Armazena o nome/número da mesa para funcionar com Retirada
        let tableNameInput = document.getElementById('current_table_name');
        if (!tableNameInput) {
            tableNameInput = document.createElement('input');
            tableNameInput.type = 'hidden';
            tableNameInput.id = 'current_table_name';
            document.body.appendChild(tableNameInput);
        }
        tableNameInput.value = `Mesa ${table.number}`;

        // Visual - com indicador de ocupada e link para ver comanda dentro do card
        const isOccupied = table.status === 'ocupada' && table.current_order_id;
        const occupiedTag = isOccupied ? '<span style="color:#ef4444; font-size:0.8rem; margin-left:5px;">(OCUPADA)</span>' : '';

        // Link "Ver Comanda" dentro do card (se ocupada)
        const verComandaLink = isOccupied
            ? `<a href="#" id="table-ver-comanda" style="color:#2563eb; font-size:0.75rem; margin-left:8px; text-decoration:underline;">Ver Comanda</a>`
            : '';

        document.getElementById('selected-client-name').innerHTML = `🔹 Mesa ${table.number} ${occupiedTag} ${verComandaLink}`;

        // Bind evento no link (se existir)
        if (isOccupied) {
            const linkEl = document.getElementById('table-ver-comanda');
            if (linkEl) {
                linkEl.onclick = (e) => {
                    e.preventDefault();
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.navigateTo('balcao', true, true, {
                            order_id: table.current_order_id,
                            mesa_id: table.id,
                            mesa_numero: table.number
                        });
                    } else {
                        window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/pdv?order_id=' + table.current_order_id;
                    }
                };
            }
        }

        const selectedArea = document.getElementById('selected-client-area');
        selectedArea.style.display = 'flex';
        document.getElementById('client-search-area').style.display = 'none';
        document.getElementById('client-results').style.display = 'none';

        // Mostra botão Salvar (laranja) e mantém Finalizar (azul)
        const btnFinalizar = document.getElementById('btn-finalizar');
        if (btnFinalizar) {
            btnFinalizar.innerText = "Finalizar";
            btnFinalizar.style.backgroundColor = "";
        }

        const btnSave = document.getElementById('btn-save-command');
        if (btnSave) btnSave.style.display = 'flex';

        // Atualiza a view de Retirada se estiver visível (igual cliente)
        const retiradaAlert = document.getElementById('retirada-client-alert');
        if (retiradaAlert && retiradaAlert.style.display !== 'none') {
            const clientSelectedBox = document.getElementById('retirada-client-selected');
            const noClientBox = document.getElementById('retirada-no-client');
            const clientNameDisplay = document.getElementById('retirada-client-name');

            if (clientSelectedBox) {
                clientSelectedBox.style.display = 'block';
                if (clientNameDisplay) clientNameDisplay.innerText = `Mesa ${table.number}`;
            }
            if (noClientBox) noClientBox.style.display = 'none';

            if (typeof lucide !== 'undefined') lucide.createIcons();
            if (typeof PDVCheckout !== 'undefined') PDVCheckout.updateCheckoutUI();
        }
    };

})();


/* ========== pdv/tables-cliente.js ========== */
/**
 * TABLES-CLIENTE.JS - Lógica de Clientes
 * Dependências: PDVTables (tables.js), PDVState
 * 
 * Este módulo estende PDVTables com as funções de clientes.
 */

(function () {
    'use strict';

    // ==========================================
    // RENDERIZAR RESULTADOS DE CLIENTES
    // ==========================================
    PDVTables.renderClientResults = function (clients) {
        const results = document.getElementById('client-results');
        results.innerHTML = '';

        if (!clients.length) {
            results.innerHTML = '<div class="client-results-empty">Nenhum cliente encontrado</div>';
            results.style.display = 'block';
            return;
        }

        results.style.display = 'block';

        const header = document.createElement('div');
        header.innerHTML = '<small class="client-results-header">CLIENTES ENCONTRADOS</small>';
        results.appendChild(header);

        clients.forEach(client => {
            const div = document.createElement('div');
            const hasOpenOrder = client.has_open_order;

            // Usar classes CSS
            div.className = hasOpenOrder ? 'client-item client-item--open' : 'client-item';

            let tag = '';
            if (hasOpenOrder) {
                tag = `<span class="client-badge">OCUPADO</span>`;
            }

            const hasCrediario = client.credit_limit && parseFloat(client.credit_limit) > 0;
            const badge = hasCrediario ? '<span style="background: #ea580c; color: white; font-size: 0.6rem; padding: 2px 4px; border-radius: 4px; font-weight: 800; margin-left: 6px;">CREDIÁRIO</span>' : '';

            div.innerHTML = `
                <div class="client-avatar">
                    <span>${client.name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="client-info">
                    <div class="client-name" style="display:flex; align-items:center;">${client.name} ${badge}</div>
                    ${client.phone ? `<div class="client-phone">${client.phone}</div>` : ''}
                </div>
                ${tag}
            `;

            div.onclick = () => this.selectClient(client.id, client.name, client.open_order_id, client.credit_limit);
            results.appendChild(div);
        });
    };

    // ==========================================
    // SELECIONAR CLIENTE
    // ==========================================
    PDVTables.selectClient = function (id, name, openOrderId = null, creditLimit = 0) {
        // [ALTERADO] Não navega mais automaticamente para comanda
        // A navegação para comanda só ocorre via grid na aba Mesas
        // Aqui apenas vinculamos o cliente ao pedido atual (para Balcão)

        // Atualiza Estado
        PDVState.set({ modo: 'balcao', clienteId: id, mesaId: null });

        // Atualiza inputs hidden
        document.getElementById('current_client_id').value = id;
        document.getElementById('current_table_id').value = '';

        // Armazena o nome e crédito do cliente
        let clientNameInput = document.getElementById('current_client_name');
        if (!clientNameInput) {
            clientNameInput = document.createElement('input');
            clientNameInput.type = 'hidden';
            clientNameInput.id = 'current_client_name';
            document.body.appendChild(clientNameInput);
        }
        clientNameInput.value = name;

        let clientCreditInput = document.getElementById('current_client_credit_limit');
        if (!clientCreditInput) {
            clientCreditInput = document.createElement('input');
            clientCreditInput.type = 'hidden';
            clientCreditInput.id = 'current_client_credit_limit';
            document.body.appendChild(clientCreditInput);
        }
        clientCreditInput.value = creditLimit || 0;

        const hasCrediario = creditLimit && parseFloat(creditLimit) > 0;
        const badge = hasCrediario ? '<span style="background: #ea580c; color: white; font-size: 0.65rem; padding: 2px 4px; border-radius: 4px; font-weight: 800; margin-left: 6px;">CREDIÁRIO</span>' : '';

        // Tag de comanda aberta e link "Ver Comanda" dentro do card
        const openTag = openOrderId ? '<span style="color:#ef4444; font-size:0.8rem; margin-left:5px;">(COMANDA ABERTA)</span>' : '';
        const verComandaLink = openOrderId
            ? `<a href="#" id="client-ver-comanda" style="color:#2563eb; font-size:0.75rem; margin-left:8px; text-decoration:underline;">Ver Comanda</a>`
            : '';

        document.getElementById('selected-client-name').innerHTML = `${name} ${badge} ${openTag} ${verComandaLink}`;

        // Bind evento no link (se existir)
        if (openOrderId) {
            const linkEl = document.getElementById('client-ver-comanda');
            if (linkEl) {
                linkEl.onclick = (e) => {
                    e.preventDefault();
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.navigateTo('balcao', true, true, { order_id: openOrderId });
                    } else {
                        window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/pdv?order_id=' + openOrderId;
                    }
                };
            }
        }

        const selectedArea = document.getElementById('selected-client-area');
        const searchArea = document.getElementById('client-search-area');
        const resultsArea = document.getElementById('client-results');

        if (selectedArea) selectedArea.style.display = 'flex';
        if (searchArea) searchArea.style.display = 'none';
        if (resultsArea) resultsArea.style.display = 'none';

        // Atualiza a view de Retirada se estiver visível
        this._updateRetiradaView(name);

        // Botões
        this._updateButtons(true);

        // Fetch Async para Dados Atualizados (Dívida/Limite) Do Cliente
        const baseUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : '');
        fetch(`${baseUrl}/admin/loja/clientes/detalhes?id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.client) {

                    // Atualiza Credit Limit Hidden (garante dado fresco)
                    let credInp = document.getElementById('current_client_credit_limit');
                    if (credInp) credInp.value = data.client.credit_limit || 0;

                    // Cria/Atualiza Debt Hidden
                    let debtInp = document.getElementById('current_client_debt');
                    if (!debtInp) {
                        debtInp = document.createElement('input');
                        debtInp.type = 'hidden';
                        debtInp.id = 'current_client_debt';
                        document.body.appendChild(debtInp);
                    }
                    debtInp.value = data.client.current_debt || 0;

                    // Atualiza UI Checkout se necessário (para mostrar Labels)
                    if (typeof CheckoutUI !== 'undefined' && typeof CheckoutUI.updateCheckoutUI === 'function') {
                        CheckoutUI.updateCheckoutUI();
                    }
                }
            })
            .catch(e => console.error('Erro buscando detalhes do cliente:', e));
    };

    // ==========================================
    // LIMPAR CLIENTE/MESA
    // ==========================================
    PDVTables.clearClient = function () {
        // Atualiza Estado
        PDVState.set({ clienteId: null, mesaId: null });

        // Limpa inputs hidden
        document.getElementById('current_client_id').value = '';
        document.getElementById('current_table_id').value = '';
        const credInp = document.getElementById('current_client_credit_limit');
        if (credInp) credInp.value = '';
        const debtInp = document.getElementById('current_client_debt');
        if (debtInp) debtInp.value = '';

        // Visual - usando style.display para compatibilidade
        const selectedArea = document.getElementById('selected-client-area');
        const searchArea = document.getElementById('client-search-area');
        const searchInput = document.getElementById('client-search');

        if (selectedArea) selectedArea.style.display = 'none';
        if (searchArea) searchArea.style.display = 'flex';
        if (searchInput) {
            searchInput.value = '';
            // Não faz focus automático para evitar abrir o dropdown de mesas
        }

        // Botões
        this._updateButtons(false);

        // Se estava em Retirada, volta automaticamente para Local
        const selectedType = document.getElementById('selected_order_type')?.value;
        if (selectedType === 'retirada') {
            // Volta para Local automaticamente
            if (typeof selectOrderType === 'function') {
                selectOrderType('local', null);
            } else if (typeof CheckoutOrderType !== 'undefined') {
                CheckoutOrderType.selectOrderType('local', null);
            }
        }

        // Lógica Específica de Retirada (função global em retirada.js)
        if (typeof handleRetiradaValidation === 'function') {
            handleRetiradaValidation();
        }
    };

    // ==========================================
    // HELPERS PRIVADOS
    // ==========================================

    /**
     * Atualiza a view de Retirada quando cliente é selecionado
     */
    PDVTables._updateRetiradaView = function (name) {
        const retiradaAlert = document.getElementById('retirada-client-alert');
        if (!retiradaAlert || retiradaAlert.classList.contains('u-hidden')) return;

        const clientSelectedBox = document.getElementById('retirada-client-selected');
        const noClientBox = document.getElementById('retirada-no-client');
        const clientNameDisplay = document.getElementById('retirada-client-name');

        if (clientSelectedBox) {
            clientSelectedBox.classList.remove('u-hidden');
            if (clientNameDisplay) clientNameDisplay.innerText = name;
        }
        if (noClientBox) noClientBox.classList.add('u-hidden');

        if (typeof lucide !== 'undefined') lucide.createIcons();
        if (typeof PDVCheckout !== 'undefined') PDVCheckout.updateCheckoutUI();
    };

    /**
     * Atualiza estado dos botões Finalizar e Salvar
     */
    PDVTables._updateButtons = function (clientSelected) {
        const btnFinalizar = document.getElementById('btn-finalizar');
        const btnSave = document.getElementById('btn-save-command');

        if (btnFinalizar) {
            btnFinalizar.innerText = 'Finalizar';
            btnFinalizar.style.backgroundColor = '';
        }

        if (btnSave) {
            // Usa display flex para garantir visibilidade sobrepondo estilo inline do PHP
            btnSave.style.display = clientSelected ? 'flex' : 'none';
        }
    };

})();



/* ========== pdv/tables-client-modal.js ========== */
/**
 * TABLES-CLIENT-MODAL.JS - Modal de Novo Cliente
 * Dependências: PDVTables (tables.js), PDVState
 * 
 * Este módulo estende PDVTables com as funções do modal de cliente.
 */

(function () {
    'use strict';

    // ==========================================
    // ESTADO DO MODAL
    // ==========================================
    PDVTables.modalSearchTimeout = null;

    // ==========================================
    // BUSCAR CLIENTE NO MODAL
    // ==========================================
    PDVTables.searchClientInModal = function (term) {
        clearTimeout(this.modalSearchTimeout);
        const results = document.getElementById('modal-client-results');
        const btnSave = document.getElementById('btn-save-new-client');

        if (term.length < 2) {
            results.style.display = 'none';
            if (btnSave) btnSave.disabled = false;
            if (btnSave) btnSave.style.opacity = '1';
            return;
        }

        this.modalSearchTimeout = setTimeout(() => {
            fetch('clientes/buscar?q=' + term)
                .then(r => r.json())
                .then(data => {
                    results.innerHTML = '';
                    let exactMatch = false;

                    if (data.length > 0) {
                        results.style.display = 'block';

                        data.forEach(client => {
                            // Verifica duplicidade exata (case insensitive)
                            if (client.name.toLowerCase() === term.toLowerCase()) {
                                exactMatch = true;
                            }

                            const hasCrediario = client.credit_limit && parseFloat(client.credit_limit) > 0;
                            const badge = hasCrediario ? '<span style="background: #ea580c; color: white; font-size: 0.65rem; padding: 2px 4px; border-radius: 4px; font-weight: 800; margin-left: 6px;">CREDIÁRIO</span>' : '';

                            const div = document.createElement('div');
                            div.style.cssText = "padding: 8px 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center;";
                            div.innerHTML = `
                                <div>
                                    <div style="font-weight:600; font-size:0.85rem; display: flex; align-items: center;">${client.name} ${badge}</div>
                                    ${client.phone ? `<div style="font-size:0.75rem; color:#64748b;">${client.phone}</div>` : ''}
                                </div>
                                <span style="font-size: 0.75rem; color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px;">Selecionar</span>
                            `;

                            div.onclick = () => {
                                this.selectClient(client.id, client.name, null, client.credit_limit);
                                document.getElementById('clientModal').style.display = 'none';
                                document.getElementById('new_client_name').value = '';
                                results.style.display = 'none';
                            };

                            results.appendChild(div);
                        });
                    } else {
                        results.style.display = 'none';
                    }

                    // Bloqueia salvar se tiver nome igual
                    if (exactMatch) {
                        if (btnSave) {
                            btnSave.disabled = true;
                            btnSave.style.opacity = '0.5';
                            btnSave.innerText = "Já Existe";
                        }
                    } else {
                        if (btnSave) {
                            btnSave.disabled = false;
                            btnSave.style.opacity = '1';
                            btnSave.innerText = "Salvar";
                        }
                    }
                });
        }, 300);
    };

    // ==========================================
    // SALVAR NOVO CLIENTE
    // ==========================================
    PDVTables.saveClient = function () {
        const name = document.getElementById('new_client_name').value;
        const phone = document.getElementById('new_client_phone').value;

        if (!name.trim()) return alert('Digite o nome do cliente');

        fetch('clientes/salvar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name,
                phone,
                csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }),
            credentials: 'include'
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('clientModal').style.display = 'none';

                    // Seleciona o cliente recém criado
                    this.selectClient(data.client.id, data.client.name);

                    // Tratamento especial para Retirada (se estiver no modal)
                    const retiradaAlert = document.getElementById('retirada-client-alert');
                    if (retiradaAlert && retiradaAlert.style.display !== 'none') {
                        retiradaAlert.style.background = '#dcfce7';
                        retiradaAlert.style.borderColor = '#22c55e';
                        retiradaAlert.innerHTML = `
                         <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i data-lucide="check-circle" size="18" style="color: #16a34a;"></i>
                                <span style="font-weight: 700; color: #166534; font-size: 0.9rem;">Cliente: ${data.client.name}</span>
                            </div>
                            <button onclick="PDVTables.clearClient()" style="background: none; border: none; color: #166534; cursor: pointer; font-size: 1.2rem; font-weight: bold; padding: 0 5px;">&times;</button>
                        </div>
                    `;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        if (window.updateCheckoutUI) window.updateCheckoutUI();
                    }

                    // Limpa form
                    document.getElementById('new_client_name').value = '';
                    document.getElementById('new_client_phone').value = '';
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(err => alert('Erro ao salvar: ' + err.message));
    };

})();


/* ========== pdv/order-actions.js ========== */
/**
 * PDV ORDER ACTIONS - Ações de Pedido/Mesa/Comanda
 * 
 * Funções para deletar itens e cancelar pedidos.
 * Dependências: BASE_URL (global)
 */

const PDVOrderActions = {

    /**
     * Deleta item já salvo da mesa/comanda
     * @param {number} itemId - ID do order_item
     * @param {number} orderId - ID do pedido
     */
    deleteSavedItem: function (itemId, orderId) {
        if (!confirm('Remover este item do pedido?')) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(BASE_URL + '/admin/loja/venda/remover-item', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ item_id: itemId, order_id: orderId })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Recarrega seção atual via SPA
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.reloadCurrentSection();
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert('Erro: ' + (data.message || 'Não foi possível remover o item'));
                }
            })
            .catch(err => {
                alert('Erro de conexão: ' + err.message);
            });
    },

    /**
     * Cancela todo o pedido da mesa
     * @param {number} tableId - ID da mesa
     * @param {number} orderId - ID do pedido
     */
    cancelTableOrder: function (tableId, orderId) {
        if (!confirm('ATENÇÃO: Isso cancelará TODO o pedido desta mesa.\n\nOs itens voltarão ao estoque.\n\nDeseja continuar?')) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(BASE_URL + '/admin/loja/mesa/cancelar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ table_id: tableId, order_id: orderId })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Pedido cancelado com sucesso!');
                    // Navega para mesas via SPA
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.navigateTo('mesas', true, true);
                    } else {
                        window.location.href = BASE_URL + '/admin/loja/mesas';
                    }
                } else {
                    alert('Erro: ' + (data.message || 'Não foi possível cancelar o pedido'));
                }
            })
            .catch(err => {
                alert('Erro de conexão: ' + err.message);
            });
    }
};

// Expõe globalmente
window.PDVOrderActions = PDVOrderActions;

// Aliases globais para compatibilidade com onclick no HTML
window.deleteSavedItem = (itemId, orderId) => PDVOrderActions.deleteSavedItem(itemId, orderId);
window.cancelTableOrder = (tableId, orderId) => PDVOrderActions.cancelTableOrder(tableId, orderId);


/* ========== pdv/ficha.js ========== */
/**
 * PDV FICHA - Modal de Ficha do Cliente/Mesa
 * 
 * Funções para exibir, fechar e imprimir a ficha de consumo.
 * Dependências: Nenhuma
 */

const PDVFicha = {

    /**
     * Abre o modal de ficha do cliente/mesa
     */
    open: function () {
        const modal = document.getElementById('fichaModal');
        if (modal) {
            modal.style.display = 'flex';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    },

    /**
     * Fecha o modal de ficha
     */
    close: function () {
        const modal = document.getElementById('fichaModal');
        if (modal) modal.style.display = 'none';
    },

    /**
     * Imprime a ficha do cliente/mesa
     */
    print: function () {
        const content = document.getElementById('fichaContent');
        if (!content) return;

        const printWindow = window.open('', '_blank', 'width=400,height=600');

        // Clone content and remove buttons
        const clone = content.cloneNode(true);
        clone.querySelectorAll('button').forEach(btn => btn.remove());

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Ficha do Cliente</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: Arial, sans-serif; 
                        padding: 20px; 
                        max-width: 320px; 
                        margin: 0 auto;
                    }
                    .header { text-align: center; margin-bottom: 15px; border-bottom: 2px dashed #000; padding-bottom: 10px; }
                    .header h1 { font-size: 18px; margin-bottom: 5px; }
                    .header p { font-size: 12px; color: #666; }
                    .item { padding: 8px 0; border-bottom: 1px solid #ddd; }
                    .item-name { font-weight: bold; font-size: 14px; }
                    .item-extras { font-size: 12px; color: #666; padding-left: 10px; }
                    .item-price { text-align: right; font-weight: bold; font-size: 14px; }
                    .total { margin-top: 15px; padding-top: 15px; border-top: 2px solid #000; text-align: right; }
                    .total-label { font-size: 16px; font-weight: bold; }
                    .total-value { font-size: 24px; font-weight: bold; }
                    @media print {
                        body { padding: 5px; }
                    }
                </style>
            </head>
            <body>
                ${clone.innerHTML}
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.onload = function () {
            printWindow.print();
        };
    }
};

// Expõe globalmente para uso no HTML
window.PDVFicha = PDVFicha;

// Aliases globais para compatibilidade com onclick no HTML
window.openFichaModal = () => PDVFicha.open();
window.closeFichaModal = () => PDVFicha.close();
window.printFicha = () => PDVFicha.print();


/* ========== pdv/checkout/helpers.js ========== */
/**
 * PDV CHECKOUT - Helpers
 * Funções utilitárias de formatação
 * 
 * Dependências: Nenhuma
 */

const CheckoutHelpers = {

    /**
     * Formata input de valor monetário (máscara BRL)
     * @param {HTMLInputElement} input 
     */
    formatMoneyInput: function (input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') { input.value = ''; return; }
        value = (parseInt(value) / 100).toFixed(2).replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        input.value = value;
    },

    /**
     * Formata número para moeda BRL
     * @param {number} val 
     * @returns {string}
     */
    formatCurrency: function (val) {
        return parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    },

    /**
     * Traduz método de pagamento para label exibível
     * @param {string} method 
     * @returns {string}
     */
    formatMethodLabel: function (method) {
        const map = { 'dinheiro': 'Dinheiro', 'pix': 'Pix', 'credito': 'Cartão Crédito', 'debito': 'Cartão Débito' };
        return map[method] || method;
    },

    /**
     * Obtém IDs de contexto do DOM (mesa, cliente, pedido)
     * Centraliza a lógica repetitiva de obter e validar IDs
     * @returns {Object} { tableId, clientId, orderId, hasTable, hasClient }
     */
    getContextIds: function () {
        const tableIdRaw = document.getElementById('current_table_id')?.value;
        const clientIdRaw = document.getElementById('current_client_id')?.value;
        const orderIdRaw = document.getElementById('current_order_id')?.value;

        const hasTable = !!(tableIdRaw && tableIdRaw !== '' && tableIdRaw !== '0');
        const hasClient = !!(clientIdRaw && clientIdRaw !== '' && clientIdRaw !== '0');

        return {
            tableId: hasTable ? parseInt(tableIdRaw) : null,
            clientId: hasClient ? parseInt(clientIdRaw) : null,
            orderId: orderIdRaw ? parseInt(orderIdRaw) : null,
            hasTable: hasTable,
            hasClient: hasClient
        };
    },

    /**
     * Verifica se é um ID válido (não vazio, não zero)
     * @param {string|number} id 
     * @returns {boolean}
     */
    isValidId: function (id) {
        return !!(id && id !== '' && id !== '0' && id !== 0);
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutHelpers = CheckoutHelpers;


/* ========== pdv/checkout/state.js ========== */
/**
 * PDV CHECKOUT - State
 * Estado central do checkout
 * 
 * Dependências: Nenhuma
 */

const CheckoutState = {

    // Lista de pagamentos adicionados
    currentPayments: [],

    // Total já pago
    totalPaid: 0,

    // Valor de desconto aplicado
    discountValue: 0,

    // Total armazenado quando o modal abre (cache)
    cachedTotal: 0,

    // Método de pagamento selecionado
    selectedMethod: 'dinheiro',

    // ID para fechar comanda específica
    closingOrderId: null,

    /**
     * Reseta o estado para valores iniciais
     */
    reset: function () {
        this.currentPayments = [];
        this.totalPaid = 0;
        this.discountValue = 0;
        this.cachedTotal = 0;
        this.selectedMethod = 'dinheiro';
        this.closingOrderId = null;
    },

    /**
     * Reseta apenas pagamentos (para reabrir checkout)
     */
    resetPayments: function () {
        this.currentPayments = [];
        this.totalPaid = 0;
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutState = CheckoutState;


/* ========== pdv/checkout/totals.js ========== */
/**
 * PDV CHECKOUT - Totals
 * Cálculos de total, desconto e taxas
 * 
 * Dependências: CheckoutState, CheckoutHelpers, CheckoutUI
 */

const CheckoutTotals = {

    /**
     * Aplica desconto e atualiza UI
     * @param {string} valStr - Valor formatado (ex: "10,00")
     */
    applyDiscount: function (valStr) {
        if (!valStr) {
            CheckoutState.discountValue = 0;
        } else {
            let val = valStr.replace(/\./g, '').replace(',', '.');
            CheckoutState.discountValue = parseFloat(val) || 0;
        }

        // Atualiza UI
        if (typeof CheckoutUI !== 'undefined') {
            CheckoutUI.updateCheckoutUI();
        }

        // Atualiza valor sugerido no input de pagamento
        const finalTotal = this.getFinalTotal();
        const remaining = Math.max(0, finalTotal - CheckoutState.totalPaid);
        const input = document.getElementById('pay-amount');
        if (input) {
            input.value = remaining.toFixed(2).replace('.', ',');
            CheckoutHelpers.formatMoneyInput(input);
        }
    },

    /**
     * Calcula o total final (cache + desconto + taxa entrega)
     * @returns {number}
     */
    getFinalTotal: function () {
        // Usa cachedTotal que já inclui table-initial-total + cart
        let total = CheckoutState.cachedTotal || 0;

        // Se editando pago, desconta o original
        if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder && typeof originalPaidTotal !== 'undefined') {
            const diff = total - originalPaidTotal;
            total = diff > 0 ? diff : 0;
        }

        // Aplica desconto
        total = total - CheckoutState.discountValue;

        // Adiciona taxa de entrega se for Entrega com dados preenchidos
        const orderTypeCards = document.querySelectorAll('.order-type-card.active');
        let isDelivery = false;
        orderTypeCards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('entrega')) isDelivery = true;
        });

        if (isDelivery && typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled) {
            if (typeof PDV_DELIVERY_FEE !== 'undefined') {
                total += PDV_DELIVERY_FEE;
            }
        }

        return total > 0 ? total : 0;
    },

    /**
     * Recalcula o total base (necessário após adicionar item de ajuste)
     */
    refreshBaseTotal: function () {
        let cartTotal = 0;
        if (typeof PDVCart !== 'undefined') {
            cartTotal = PDVCart.calculateTotal();
        }
        let tableInitialTotal = parseFloat(document.getElementById('table-initial-total')?.value || 0);
        CheckoutState.cachedTotal = cartTotal + tableInitialTotal;

        // Se estivermos fechando comanda, cachedTotal pode ter lógica diferente no flow.js, 
        // mas geralmente é item + mesa. 
        // Em 'comanda', initialTotal já vem populado se estiver fechando conta.
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutTotals = CheckoutTotals;


/* ========== pdv/checkout/ui.js ========== */
/**
 * PDV CHECKOUT - UI
 * Atualização de interface (DOM updates)
 * 
 * Dependências: CheckoutState, CheckoutTotals, CheckoutHelpers
 */

const CheckoutUI = {

    /**
     * Atualiza a lista visual de pagamentos
     */
    updatePaymentList: function () {
        const listEl = document.getElementById('payment-list');
        listEl.innerHTML = '';

        if (CheckoutState.currentPayments.length === 0) {
            listEl.style.display = 'block';
            listEl.innerHTML = '<div style="text-align: center; color: #94a3b8; font-size: 0.9rem; padding: 20px 0;">Nenhum pagamento lançado</div>';
            return;
        }

        listEl.style.display = 'block';
        CheckoutState.currentPayments.forEach((pay, index) => {
            const row = document.createElement('div');
            row.style.cssText = "display: flex; justify-content: space-between; padding: 8px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; align-items: center; margin-bottom: 4px; border-radius: 6px;";
            row.innerHTML = `
                <span style="font-weight:600; color:#334155;">${pay.label}</span>
                <div style="display:flex; align-items:center; gap:10px;">
                    <strong>${CheckoutHelpers.formatCurrency(pay.amount)}</strong>
                    <button onclick="PDVCheckout.removePayment(${index})" style="color:#ef4444; border:none; background:#fee2e2; width:24px; height:24px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
                </div>
            `;
            listEl.appendChild(row);
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    /**
     * Atualiza todos os displays do checkout (total, restante, troco, botão)
     */
    updateCheckoutUI: function () {
        const finalTotal = CheckoutTotals.getFinalTotal();
        const discount = CheckoutState.discountValue || 0;
        const subtotal = finalTotal + discount;

        document.getElementById('display-discount').innerText = '- ' + CheckoutHelpers.formatCurrency(discount);
        document.getElementById('display-paid').innerText = CheckoutHelpers.formatCurrency(CheckoutState.totalPaid);
        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(finalTotal);

        // Atualiza input de Edição de Total (apenas se não estiver editando)
        const totalInput = document.getElementById('display-total-edit');
        if (totalInput && totalInput.readOnly) {
            totalInput.value = finalTotal.toFixed(2).replace('.', ',');
            if (CheckoutHelpers.formatMoneyInput) CheckoutHelpers.formatMoneyInput(totalInput);
        }

        const remaining = finalTotal - CheckoutState.totalPaid;
        const btnFinish = document.getElementById('btn-finish-sale');

        // Feature: Atualiza valor a lançar com o restante atualizado
        const payInput = document.getElementById('pay-amount');
        if (payInput) {
            payInput.value = Math.max(0, remaining).toFixed(2).replace('.', ',');
            if (CheckoutHelpers.formatMoneyInput) CheckoutHelpers.formatMoneyInput(payInput);
        }

        document.getElementById('display-remaining').innerText = CheckoutHelpers.formatCurrency(Math.max(0, remaining));

        const changeBox = document.getElementById('change-display-box');
        const changeBoxOld = document.getElementById('change-box'); // Footer antigo

        if (!btnFinish) return;

        // Sempre atualiza o troco (fixo)
        const changeValue = remaining < 0 ? Math.abs(remaining) : 0;
        if (changeBox) {
            document.getElementById('display-change').innerText = CheckoutHelpers.formatCurrency(changeValue);
        }
        // Esconde o antigo
        if (changeBoxOld) changeBoxOld.style.display = 'none';

        // Lógica: Se falta <= 1 centavo, libera
        if (remaining <= 0.01) {
            btnFinish.disabled = false;
            btnFinish.style.background = '#22c55e';
            btnFinish.style.cursor = 'pointer';
        } else {
            // Falta pagar
            btnFinish.disabled = true;
            btnFinish.style.background = '#cbd5e1';
            btnFinish.style.cursor = 'not-allowed';
        }

        // Validação Extra: Retirada sem Cliente
        const keepOpenInput = document.getElementById('keep_open_value');
        const ctx = CheckoutHelpers.getContextIds();

        if (keepOpenInput && keepOpenInput.value === 'true' && !ctx.hasClient && !ctx.hasTable) {
            btnFinish.disabled = true;
            btnFinish.style.background = '#cbd5e1';
            btnFinish.style.cursor = 'not-allowed';
        }

        // Verificação Crediário
        const credContainer = document.getElementById('container-crediario-slot');
        const credInput = document.getElementById('crediario-amount');
        const credBtn = document.getElementById('btn-add-crediario');
        const credLimitInput = document.getElementById('current_client_credit_limit');
        let creditLimit = 0;

        if (credLimitInput && credLimitInput.value) {
            creditLimit = parseFloat(credLimitInput.value);
        }

        // Ler Dívida (Fetch Async preenche isso)
        const credDebtInput = document.getElementById('current_client_debt');
        let creditDebt = 0;
        if (credDebtInput && credDebtInput.value) {
            creditDebt = parseFloat(credDebtInput.value);
        }

        const lblTotal = document.getElementById('cred-limit-total');
        const lblAvail = document.getElementById('cred-limit-available');

        if (credInput && credBtn) {
            if (creditLimit > 0) {
                credInput.disabled = false;
                credBtn.disabled = false;
                if (credContainer) credContainer.style.opacity = '1';
                credBtn.style.opacity = '1';
                credBtn.style.cursor = 'pointer';
                if (credInput.placeholder === "Sem Limite") credInput.placeholder = "0,00";

                // Atualiza Textos
                if (lblTotal) lblTotal.innerText = CheckoutHelpers.formatCurrency(creditLimit);
                if (lblAvail) {
                    const available = creditLimit - creditDebt;
                    lblAvail.innerText = CheckoutHelpers.formatCurrency(available);
                    // Destaque visual
                    lblAvail.style.color = available >= 0 ? '#15803d' : '#dc2626';
                }
            } else {
                credInput.disabled = true;
                credBtn.disabled = true;
                if (credContainer) credContainer.style.opacity = '0.5';
                credBtn.style.opacity = '0.5';
                credBtn.style.cursor = 'not-allowed';
                credInput.value = '';
                credInput.placeholder = "Sem Limite";

                if (lblTotal) lblTotal.innerText = 'R$ 0,00';
                if (lblAvail) {
                    lblAvail.innerText = 'R$ 0,00';
                    lblAvail.style.color = '#9a3412';
                }
            }
        }
    },

    /**
     * Exibe modal de sucesso temporário
     */
    showSuccessModal: function () {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => modal.style.display = 'none', 1500);
        }
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutUI = CheckoutUI;


/* ========== pdv/checkout/payments.js ========== */
/**
 * PDV CHECKOUT - Payments
 * Lógica de adição/remoção de pagamentos
 * 
 * Dependências: CheckoutState, CheckoutTotals, CheckoutUI, CheckoutHelpers
 */

const CheckoutPayments = {

    /**
     * Seleciona método de pagamento e atualiza visual
     * @param {string} method - 'dinheiro' | 'pix' | 'credito' | 'debito'
     */
    setMethod: function (method) {
        CheckoutState.selectedMethod = method;

        // Visual
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderColor = '#cbd5e1';
            btn.style.background = 'white';
            const icon = btn.querySelector('svg');
            if (icon) icon.style.color = 'currentColor';
        });

        const activeBtn = document.getElementById('btn-method-' + method);
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.borderColor = '#2563eb';
            activeBtn.style.background = '#eff6ff';
            const icon = activeBtn.querySelector('svg');
            if (icon) icon.style.color = '#2563eb';
        }

        // Auto-preenchimento
        const finalTotal = CheckoutTotals.getFinalTotal();
        const remaining = finalTotal - CheckoutState.totalPaid;
        const input = document.getElementById('pay-amount');
        if (remaining > 0) {
            input.value = remaining.toFixed(2).replace('.', ',');
        } else {
            input.value = '';
        }
        setTimeout(() => input.focus(), 100);
    },

    /**
     * Adiciona um pagamento à lista
     */
    addPayment: function (forceMethod, forceAmount) {
        let amount = 0;
        let isManual = false;

        if (typeof forceAmount !== 'undefined') {
            amount = parseFloat(forceAmount);
        } else {
            const amountInput = document.getElementById('pay-amount');
            let valStr = amountInput.value.trim();
            if (valStr.includes(',')) valStr = valStr.replace(/\./g, '').replace(',', '.');
            amount = parseFloat(valStr);
            isManual = true;
        }

        const method = forceMethod || CheckoutState.selectedMethod;

        if (!amount || amount <= 0 || isNaN(amount)) {
            alert('Digite um valor válido.');
            return;
        }

        const finalTotal = CheckoutTotals.getFinalTotal();
        const remaining = finalTotal - CheckoutState.totalPaid;

        // Regra de troco: se não for dinheiro, trava no restante
        // Permite 1 centavo de tolerância
        if (method !== 'dinheiro' && amount > remaining + 0.01) {
            amount = remaining;
            if (amount <= 0.01) { alert('Valor restante já pago!'); return; }
        }

        CheckoutState.currentPayments.push({
            method: method,
            amount: amount,
            label: (method === 'crediario' ? 'Crediário' : CheckoutHelpers.formatMethodLabel(method))
        });
        CheckoutState.totalPaid += amount;

        if (isManual) {
            document.getElementById('pay-amount').value = '';
        }

        CheckoutUI.updatePaymentList();
        CheckoutUI.updateCheckoutUI();

        // Foca no botão se terminou
        const newRemaining = finalTotal - CheckoutState.totalPaid;
        if (newRemaining <= 0.01) {
            document.getElementById('btn-finish-sale').focus();
        } else {
            let rest = newRemaining.toFixed(2).replace('.', ',');
            if (isManual) document.getElementById('pay-amount').value = rest;
            if (isManual) document.getElementById('pay-amount').focus();
        }
    },

    addCrediarioPayment: function () {
        const input = document.getElementById('crediario-amount');
        if (!input) return;

        let valStr = input.value.trim();
        if (valStr.includes(',')) valStr = valStr.replace(/\./g, '').replace(',', '.');
        let amount = parseFloat(valStr);

        if (!amount || amount <= 0) {
            alert('Digite um valor para o Crediário.');
            return;
        }

        this.addPayment('crediario', amount);
        input.value = '';
    },

    /**
     * Remove um pagamento da lista
     * @param {number} index 
     */
    removePayment: function (index) {
        const removed = CheckoutState.currentPayments.splice(index, 1)[0];
        CheckoutState.totalPaid -= removed.amount;

        CheckoutUI.updatePaymentList();
        CheckoutUI.updateCheckoutUI();

        // Restaura o valor restante no campo
        const finalTotal = CheckoutTotals.getFinalTotal();
        const remaining = Math.max(0, finalTotal - CheckoutState.totalPaid);
        const input = document.getElementById('pay-amount');
        if (input && remaining > 0) {
            input.value = remaining.toFixed(2).replace('.', ',');
            CheckoutHelpers.formatMoneyInput(input);
            input.focus();
        }
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutPayments = CheckoutPayments;


/* ========== pdv/checkout/services/checkout-service.js ========== */
/**
 * CheckoutService.js
 * Responsável APENAS pela comunicação com a API (Fetch calls)
 */
const CheckoutService = {

    _getCsrf: function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    },

    _headers: function () {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this._getCsrf()
        };
    },

    /**
     * Envia requisição de finalizar venda
     * @param {string} endpoint 
     * @param {object} payload 
     */
    sendSaleRequest: async function (endpoint, payload) {
        const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + endpoint;

        // Fallback: Envia token no corpo para evitar bloqueio de headers
        if (payload && typeof payload === 'object') {
            payload.csrf_token = this._getCsrf();
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: this._headers(),
                credentials: 'same-origin', // Garante envio de cookies
                body: JSON.stringify(payload)
            });
            return await response.json();
        } catch (error) {
            throw new Error('Falha na comunicação: ' + error.message);
        }
    },

    /**
     * Força entrega (Fechar comanda paga)
     */
    closePaidTab: async function (orderId) {
        return this.sendSaleRequest('venda/fechar-comanda', {
            order_id: orderId,
            payments: [],
            keep_open: false
        });
    },

    /**
     * Envia pedido de cliente/mesa (Salvar Comanda)
     */
    saveTabOrder: async function (payload) {
        return this.sendSaleRequest('/admin/loja/venda/finalizar', payload);
    }
};

window.CheckoutService = CheckoutService;


/* ========== pdv/checkout/services/checkout-validator.js ========== */
/**
 * CheckoutValidator.js
 * Responsável por validar se a venda pode prosseguir
 */
const CheckoutValidator = {

    /**
     * Valida carrinho
     */
    validateCart: function (cartItems) {
        if (!cartItems || cartItems.length === 0) {
            alert('Carrinho vazio!');
            return false;
        }
        return true;
    },

    /**
     * Valida seleção de cliente/mesa para Comanda
     */
    validateClientOrTable: function (clientId, tableId) {
        if (!clientId && !tableId) {
            alert('Selecione um cliente ou mesa!');
            return false;
        }
        return true;
    },

    /**
     * Valida dados de entrega
     */
    validateDeliveryData: function (selectedOrderType) {
        if (selectedOrderType === 'delivery') {
            const isFilled = typeof CheckoutEntrega !== 'undefined'
                ? CheckoutEntrega.isDataFilled()
                : (typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled);

            if (!isFilled) {
                alert('Preencha os dados de entrega primeiro!');
                if (typeof openDeliveryPanel === 'function') openDeliveryPanel();
                return false;
            }
        }
        return true;
    }
};

window.CheckoutValidator = CheckoutValidator;


/* ========== pdv/checkout/adjust.js ========== */
/**
 * PDV CHECKOUT - Adjust (Ajuste de Total)
 * Permite definir o valor total final do pedido, criando um item de ajuste
 * (positivo ou negativo) automaticamente.
 * 
 * Dependências: PDVCart, CheckoutTotals, CheckoutUI, CheckoutHelpers
 */

const CheckoutAdjust = {

    isEditing: false,

    /**
     * Alterna modo de edição do Total
     */
    toggleEdit: function () {
        const input = document.getElementById('display-total-edit');
        const btnToggle = document.getElementById('btn-toggle-edit');
        const btnSave = document.getElementById('btn-save-total');

        if (!input) return;

        this.isEditing = !this.isEditing;

        if (this.isEditing) {
            // Habilita edição
            input.readOnly = false;
            input.style.background = 'white';
            input.style.borderColor = '#2563eb';
            input.focus();
            // Garante cursor no final
            if (input.setSelectionRange) {
                input.setSelectionRange(input.value.length, input.value.length);
            }

            if (btnToggle) btnToggle.style.display = 'none';
            if (btnSave) {
                btnSave.style.display = 'flex';
                btnSave.disabled = false;
                btnSave.style.background = '#2563eb';
                btnSave.style.cursor = 'pointer';
            }
        } else {
            // Cancela/Desabilita
            this._resetUI();
        }
    },

    /**
     * Salva o novo total digitado
     */
    saveEdit: function () {
        const input = document.getElementById('display-total-edit');
        if (!input) return;

        let valStr = input.value.trim();
        // Remove pontos de milhar e troca virgula por ponto
        if (valStr.includes(',')) valStr = valStr.replace(/\./g, '').replace(',', '.');

        const newTotal = parseFloat(valStr);

        this.setTotal(newTotal);

        // Após salvar, sai do modo edição e força reset visual
        if (this.isEditing) {
            this.toggleEdit();
        }
    },

    /**
     * Reseta a UI para o estado inicial (readonly)
     */
    _resetUI: function () {
        const input = document.getElementById('display-total-edit');
        const btnToggle = document.getElementById('btn-toggle-edit');
        const btnSave = document.getElementById('btn-save-total');

        if (input) {
            input.readOnly = true;
            input.style.background = '#f1f5f9';
            input.style.borderColor = '#e2e8f0';

            // Restaura valor real atualizado (com delay pequeno para garantir sync)
            setTimeout(() => {
                const currentTotal = CheckoutTotals.getFinalTotal();
                input.value = currentTotal.toFixed(2).replace('.', ',');
                if (typeof CheckoutHelpers !== 'undefined' && CheckoutHelpers.formatMoneyInput) {
                    CheckoutHelpers.formatMoneyInput(input);
                }
            }, 50);
        }

        if (btnToggle) btnToggle.style.display = 'flex';
        if (btnSave) btnSave.style.display = 'none';

        this.isEditing = false;
    },

    /**
     * Define o novo total do pedido criando um item de ajuste.
     * @param {number} newTotal - O valor final desejado pelo usuário
     */
    setTotal: function (newTotal) {
        if (isNaN(newTotal) || newTotal < 0) {
            alert('Por favor, digite um valor válido.');
            return;
        }

        // Remove ajuste anterior se existir para recalcular limpo
        const ADJUST_ITEM_ID = -88888;
        this._removeExistingAdjustment(ADJUST_ITEM_ID);

        // Recalcula diferença após remover antigo ajuste (se havia)
        const cleanTotal = CheckoutTotals.getFinalTotal();
        const finalDifference = newTotal - cleanTotal;

        if (Math.abs(finalDifference) > 0.005) {
            // Adiciona o novo item de ajuste
            const ADJUST_ITEM_NAME = 'Ajuste';

            PDVCart.add(
                ADJUST_ITEM_ID,
                ADJUST_ITEM_NAME,
                finalDifference,
                1,
                [] // sem extras
            );
        }

        // IMPORTANTE: Recalcula cache de totais para refletir adição do item
        if (typeof CheckoutTotals.refreshBaseTotal === 'function') {
            CheckoutTotals.refreshBaseTotal();
        }

        // Atualiza UI Geral
        CheckoutUI.updateCheckoutUI();

        if (document.getElementById('display-total-edit')) {
            const finalVal = CheckoutTotals.getFinalTotal();
            document.getElementById('display-total-edit').value = finalVal.toFixed(2).replace('.', ',');
        }
    },

    /**
     * Remove explicitamente o ajuste (chamado ao fechar modal)
     */
    removeAdjustment: function () {
        this._removeExistingAdjustment(-88888);
    },

    /**
     * Remove item de ajuste anterior do carrinho se existir
     */
    _removeExistingAdjustment: function (adjustId) {
        if (typeof PDVCart !== 'undefined' && PDVCart.items) {
            const index = PDVCart.items.findIndex(i => i.id === adjustId);
            if (index > -1) {
                const item = PDVCart.items[index];
                if (item.cartItemId) {
                    PDVCart.remove(item.cartItemId);
                } else {
                    PDVCart.items.splice(index, 1);
                    PDVCart.updateUI();
                }
                // Após remover, precisamos refrescar o total cached também
                if (typeof CheckoutTotals.refreshBaseTotal === 'function') {
                    CheckoutTotals.refreshBaseTotal();
                }
            }
        }
    }

};

window.CheckoutAdjust = CheckoutAdjust;


/* ========== pdv/checkout/submit.js ========== */
/**
 * PDV CHECKOUT - Submit (Refatorado)
 * Controlador de envio de pedidos (Orchestrator)
 * 
 * Dependências: CheckoutService, CheckoutValidator, CheckoutState, PDVState, PDVCart
 */

const CheckoutSubmit = {

    /**
     * 1. FINALIZAR VENDA (Pagamento Realizado)
     */
    submitSale: async function () {
        // 1. Obter contexto via helper centralizado
        const ctx = CheckoutHelpers.getContextIds();
        const keepOpen = document.getElementById('keep_open_value')?.value === 'true';

        // 2. Obter Carrinho
        const cartItems = this._getCartItems();

        // 3. Preparar Payload Base
        let endpoint = '/admin/loja/venda/finalizar';
        const hasClientOrTable = ctx.hasClient || ctx.hasTable;
        const selectedOrderType = this._determineOrderType(hasClientOrTable);

        // 3. Montar dados
        const payload = {
            cart: cartItems,
            table_id: ctx.tableId,
            client_id: ctx.clientId,
            order_id: ctx.orderId,
            payments: CheckoutState.currentPayments,
            discount: CheckoutState.discountValue,
            keep_open: keepOpen,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: this._calculateIsPaidStatus(CheckoutState.currentPayments),
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0
        };

        // 4. Dados de Entrega
        if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
            payload.delivery_data = getDeliveryData();
        }

        // 5. Vincular entrega à mesa ou comanda (independente de pagar agora ou depois)
        if (selectedOrderType === 'delivery') {
            if (ctx.hasTable) {
                payload.link_to_table = true;
                payload.table_id = ctx.tableId;
            } else if (ctx.hasClient) {
                payload.link_to_comanda = true;
                payload.table_id = null;
                payload.link_to_table = false;
            }
        }

        // 5. Ajuste de Endpoint baseado no Estado
        const { modo, fechandoConta } = PDVState.getState();
        let isPaidLoop = false;
        let isMesaClose = false;

        if (window.isPaidOrderInclusion && typeof editingPaidOrderId !== 'undefined') {
            payload.order_id = editingPaidOrderId;
            payload.save_account = true;
            isPaidLoop = true;
        } else if (modo === 'mesa' && fechandoConta) {
            endpoint = '/admin/loja/mesa/fechar';
            isMesaClose = true;
        } else if (modo === 'comanda' && fechandoConta) {
            endpoint = '/admin/loja/venda/fechar-comanda';
            payload.order_id = CheckoutState.closingOrderId;
        }

        // 6. Enviar via Service
        try {
            const data = await CheckoutService.sendSaleRequest(endpoint, payload);
            this._handleSuccess(data, isPaidLoop, isMesaClose);
        } catch (err) {
            alert(err.message);
        }
    },


    /**
     * 2. FORÇAR ENTREGA (Pedido já pago)
     */
    forceDelivery: async function (orderId) {
        if (!orderId) return;

        try {
            const data = await CheckoutService.sendSaleRequest('/admin/loja/pedidos/entregar', {
                order_id: parseInt(orderId)
            });

            if (data.success) {
                CheckoutUI.showSuccessModal();
                PDVCart.clear();
                // Navega para mesas via SPA após sucesso
                setTimeout(() => {
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.navigateTo('mesas', true, true);
                    } else {
                        window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                    }
                }, 1000);
            } else {
                alert('Erro: ' + (data.message || 'Falha ao entregar pedido.'));
            }
        } catch (err) {
            alert('Erro: ' + err.message);
        }
    },

    /**
     * 3. SALVAR COMANDA (Botão Laranja)
     */
    saveClientOrder: async function () {
        // Obter contexto via helper centralizado
        const ctx = CheckoutHelpers.getContextIds();

        // Validações
        if (!CheckoutValidator.validateCart(PDVCart.items)) return;
        if (!CheckoutValidator.validateClientOrTable(ctx.clientId, ctx.tableId)) return;

        // Determina o tipo de pedido selecionado pelo usuário
        const selectedOrderType = this._determineOrderType(ctx.hasClient || ctx.hasTable);

        // Atualiza Estado
        PDVState.set({
            modo: ctx.hasTable ? 'mesa' : 'comanda',
            clienteId: ctx.clientId,
            mesaId: ctx.tableId
        });

        // Envia
        const payload = {
            cart: PDVCart.items,
            client_id: ctx.clientId,
            table_id: ctx.tableId,
            order_id: ctx.orderId,
            save_account: true,
            order_type: selectedOrderType  // Usa o tipo selecionado (pickup, delivery, local, etc)
        };

        // Se for entrega, adiciona dados de entrega
        if (selectedOrderType === 'delivery' && typeof getDeliveryData === 'function') {
            payload.delivery_data = getDeliveryData();
            payload.delivery_fee = (typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0;
        }

        try {
            const data = await CheckoutService.saveTabOrder(payload);
            // Após salvar, volta para a mesma mesa (não perde contexto)
            this._handleSaveSuccess(data, ctx.tableId, ctx.orderId);
        } catch (err) {
            alert(err.message);
        }
    },

    /**
     * 4. SALVAR PEDIDO (Retirada/Delivery) - Pagar Depois
     */
    savePickupOrder: async function () {
        // Determina tipo baseado no card ATIVO (não no deliveryDataFilled)
        const rawType = this._determineOrderType(false);
        const selectedOrderType = rawType === 'delivery' ? 'delivery' : 'pickup';

        // Validações
        if (!CheckoutValidator.validateDeliveryData(selectedOrderType)) return;

        const cartItems = this._getCartItems();
        if (!CheckoutValidator.validateCart(cartItems)) return;

        // Obter contexto via helper centralizado
        const ctx = CheckoutHelpers.getContextIds();

        // Montar Payload
        const payload = {
            cart: cartItems,
            table_id: ctx.tableId,
            client_id: ctx.clientId,
            order_id: ctx.orderId,
            payments: [],
            discount: CheckoutState.discountValue || 0,
            delivery_fee: (selectedOrderType === 'delivery' && typeof PDV_DELIVERY_FEE !== 'undefined') ? PDV_DELIVERY_FEE : 0,
            keep_open: false,
            finalize_now: true,
            order_type: selectedOrderType,
            is_paid: 0,
            payment_method_expected: CheckoutState.selectedMethod || 'dinheiro'
        };

        // Vincular entrega à mesa ou comanda
        if (selectedOrderType === 'delivery') {
            if (ctx.hasTable) {
                payload.link_to_table = true;
                payload.table_id = ctx.tableId;
            } else if (ctx.hasClient) {
                payload.link_to_comanda = true;
                payload.table_id = null;
                payload.link_to_table = false;
            }

            // Adiciona dados de entrega
            const deliveryData = typeof CheckoutEntrega !== 'undefined' ? CheckoutEntrega.getData() : getDeliveryData();
            if (deliveryData) payload.delivery_data = deliveryData;
        }

        // Enviar
        try {
            const data = await CheckoutService.saveTabOrder(payload);

            // Se tem mesa ou cliente, manter no contexto
            if (ctx.hasTable || ctx.hasClient) {
                this._handleSaveSuccess(data, ctx.tableId, ctx.orderId);
            } else {
                // Sem mesa/cliente, comportamento padrão
                this._handleSuccess(data, false, true);
            }
        } catch (err) {
            alert(err.message);
        }
    },

    // --- Helpers Privados ---

    _getCartItems: function () {
        // IMPORTANTE: PDVCart.items tem prioridade porque window.cart pode ficar desatualizado
        // (window.cart é uma referência que fica stale quando this.items = [] substitui o array)
        if (typeof PDVCart !== 'undefined' && PDVCart.items) return PDVCart.items;
        if (typeof cart !== 'undefined' && Array.isArray(cart)) return cart;
        return [];
    },

    /**
     * Calcula is_paid baseado nos métodos de pagamento.
     * - Se tem qualquer pagamento "real" (dinheiro, pix, cartão) = is_paid 1
     * - Se é APENAS crediário = is_paid 0
     * - A dívida do crediário é calculada separadamente pelo backend (order_payments)
     */
    _calculateIsPaidStatus: function (payments) {
        if (!payments || payments.length === 0) return 0;

        // Verifica se tem algum pagamento "real" (não crediário)
        const hasRealPayment = payments.some(p => p.method !== 'crediario');

        // Se tem pagamento real, marca como pago
        // A parte do crediário será contabilizada como dívida pelo backend
        return hasRealPayment ? 1 : 0;
    },

    _determineOrderType: function (hasClientOrTable) {
        // 1. Primeiro verifica o hidden input (fonte principal)
        const selectedInput = document.getElementById('selected_order_type');
        if (selectedInput && selectedInput.value) {
            const val = selectedInput.value.toLowerCase();
            if (val === 'retirada') return 'pickup';
            if (val === 'entrega') return 'delivery';
            if (val === 'local') return hasClientOrTable ? 'local' : 'balcao';
        }

        // 2. Fallback: verifica os cards ativos
        const cards = document.querySelectorAll('.order-toggle-btn.active');
        let type = 'balcao';

        cards.forEach(card => {
            const label = card.innerText.toLowerCase().trim();
            if (label.includes('retirada')) type = 'pickup';
            else if (label.includes('entrega')) type = 'delivery';
            else if (label.includes('local') && hasClientOrTable) type = 'local';
        });

        return type;
    },

    _handleSuccess: function (data, isPaidLoop = false, isMesaClose = false) {
        if (data.success) {
            CheckoutUI.showSuccessModal();
            PDVCart.clear();

            // [FIX] Invalidar cache do SPA para garantir que Balcão e Mesas recarreguem zerados
            if (typeof AdminSPA !== 'undefined') {
                AdminSPA.invalidateCache('mesas');
                AdminSPA.invalidateCache('balcao');
                AdminSPA.invalidateCache('pdv');
            }

            setTimeout(() => {
                document.getElementById('checkoutModal').style.display = 'none';

                if (isPaidLoop || isMesaClose) {
                    // Navega para mesas via SPA
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.navigateTo('mesas', true, true);
                    } else {
                        window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesas';
                    }
                } else {
                    // Recarrega seção atual via SPA
                    if (typeof AdminSPA !== 'undefined') {
                        // Se estamos no balcão, navigateTo ('balcao', true, true) forçará reload
                        AdminSPA.reloadCurrentSection();
                    } else {
                        window.location.reload();
                    }
                }
            }, 1000);
        } else {
            alert('Erro: ' + data.message);
        }
    },

    /**
     * Handler de sucesso para SALVAR comanda (permanece no Balcão)
     */
    _handleSaveSuccess: function (data, tableId, orderId) {
        if (data.success) {
            CheckoutUI.showSuccessModal();
            PDVCart.clear();

            setTimeout(() => {
                document.getElementById('checkoutModal').style.display = 'none';

                // [ALTERADO] Permanece no Balcão após salvar (não navega para mesa/comanda)
                if (typeof AdminSPA !== 'undefined') {
                    AdminSPA.navigateTo('balcao', true, true); // Recarrega balcão limpo
                } else {
                    window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/pdv';
                }
            }, 1000);
        } else {
            alert('Erro: ' + data.message);
        }
    }
};

// Exports
window.CheckoutSubmit = CheckoutSubmit;
window.savePickupOrder = () => CheckoutSubmit.savePickupOrder();


/* ========== pdv/checkout/orderType.js ========== */
/**
 * PDV CHECKOUT - Order Type
 * Seleção de tipo de pedido (Local/Retirada/Entrega)
 * 
 * Dependências: CheckoutUI, CheckoutHelpers
 */

const CheckoutOrderType = {

    /**
     * Seleciona tipo de pedido e atualiza visual/alertas
     * @param {string} type - 'local' | 'retirada' | 'entrega'
     * @param {HTMLElement} element - Card clicado (pode ser null)
     */
    /**
     * Seleciona tipo de pedido e atualiza visual/alertas
     * @param {string} type - 'local' | 'retirada' | 'entrega'
     * @param {HTMLElement} element - Card clicado (pode ser null)
     * @param {boolean} skipModal - Se true, não força abertura de modal (usado no sync)
     */
    selectOrderType: function (type, element, skipModal = false) {
        // Remove toast se existir (dismiss ao clicar em qualquer opção)
        const existingToast = document.getElementById('pdv-toast');
        if (existingToast) existingToast.remove();

        // 0. Validação imediata: Retirada requer cliente ou mesa
        if (type === 'retirada') {
            const ctx = typeof CheckoutHelpers !== 'undefined'
                ? CheckoutHelpers.getContextIds()
                : this._getBasicContext();

            if (!ctx.hasClient && !ctx.hasTable) {
                // Mostra toast sutil
                this._showToast('⚠️ Vincule um cliente ou mesa primeiro');
                return; // Não prossegue
            }
        }

        // [NOVO] Verifica se JÁ está ativo ANTES de alterar classes
        // Se element não foi passado (programático), busca pelo seletor
        let targetEl = element;
        if (!targetEl) targetEl = document.querySelector(`.order-toggle-btn[data-type="${type}"]`);

        const isAlreadyActive = targetEl && targetEl.classList.contains('active');

        // 1. Se mudando de entrega para outro, limpa dados primeiro
        if (type !== 'entrega') {
            if (typeof CheckoutEntrega !== 'undefined' && CheckoutEntrega.isDataFilled()) {
                CheckoutEntrega.clearData();
            }
            this._closeDeliveryIfOpen();
        }

        // 2. Ativa o card visual
        element = this._activateCard(type, element);

        // 3. Esconde alertas
        this._hideAllAlerts();

        // 3. Processa tipo específico
        const keepOpenInput = document.getElementById('keep_open_value');

        if (type === 'retirada') {
            if (keepOpenInput) keepOpenInput.value = 'true';
            this._handleRetirada();
        } else if (type === 'entrega') {
            if (keepOpenInput) keepOpenInput.value = 'false';
            // Se for re-clique (já estava ativo), força abertura do modal, exceto se skipModal=true
            const forceOpen = isAlreadyActive && !skipModal;
            this._handleEntrega(forceOpen);
        } else {
            // Local
            if (keepOpenInput) keepOpenInput.value = 'false';
        }

        // 4. Atualiza botão "Pagar Depois"
        this._updateSavePickupButton(type);

        // 5. Finaliza
        if (typeof lucide !== 'undefined') lucide.createIcons();
        CheckoutUI.updateCheckoutUI();
    },

    /**
     * Fallback para obter contexto básico se CheckoutHelpers não disponível
     */
    _getBasicContext: function () {
        return {
            hasClient: !!document.getElementById('current_client_id')?.value,
            hasTable: !!document.getElementById('current_table_id')?.value
        };
    },

    /**
     * Mostra um toast sutil que desaparece automaticamente
     */
    _showToast: function (message, duration = 2500) {
        // Remove toast anterior se existir
        const existing = document.getElementById('pdv-toast');
        if (existing) existing.remove();

        // Cria toast
        const toast = document.createElement('div');
        toast.id = 'pdv-toast';
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: #fef3c7;
            color: #92400e;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 99999;
            opacity: 1;
            transition: opacity 0.3s ease;
        `;
        toast.innerText = message;
        document.body.appendChild(toast);

        // Auto-dismiss com fade
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    // ==========================================
    // SUB-FUNÇÕES PRIVADAS
    // ==========================================

    /**
     * Ativa visualmente o botão do tipo selecionado (com estilos inline)
     */
    _activateCard: function (type, element) {
        // Atualiza hidden input com o tipo selecionado
        const selectedTypeInput = document.getElementById('selected_order_type');
        if (selectedTypeInput) selectedTypeInput.value = type;

        // Cores por tipo - Azul padrão, Verde para confirmados
        const colors = {
            local: { border: '#2563eb', bg: '#eff6ff', text: '#2563eb' },
            retirada: { border: '#2563eb', bg: '#eff6ff', text: '#2563eb' },
            retirada_ok: { border: '#16a34a', bg: '#dcfce7', text: '#16a34a' }, // Verde quando válido
            entrega: { border: '#2563eb', bg: '#eff6ff', text: '#2563eb' },
            entrega_ok: { border: '#16a34a', bg: '#dcfce7', text: '#16a34a' }  // Verde quando preenchido
        };
        const inactive = { border: '#cbd5e1', bg: 'white', text: '#1e293b' };

        // Reset todos os toggle buttons para inativo e remove checkmarks
        document.querySelectorAll('.order-toggle-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderColor = inactive.border;
            btn.style.background = inactive.bg;
            btn.style.color = inactive.text;
            // Remove checkmark se existir
            const check = btn.querySelector('.btn-checkmark');
            if (check) check.remove();
        });

        // Se element não foi passado, busca pelo data-type
        if (!element) {
            element = document.querySelector(`.order-toggle-btn[data-type="${type}"]`);
        }

        // Determina se Retirada deve ficar verde (tem cliente/mesa)
        let useGreen = false;
        if (type === 'retirada') {
            const ctx = typeof CheckoutHelpers !== 'undefined'
                ? CheckoutHelpers.getContextIds()
                : this._getBasicContext();
            useGreen = ctx.hasClient || ctx.hasTable;
        }

        // Ativa o selecionado com cores específicas
        if (element) {
            element.classList.add('active');
            const colorKey = useGreen ? 'retirada_ok' : type;
            const c = colors[colorKey] || colors.local;
            element.style.borderColor = c.border;
            element.style.background = c.bg;
            element.style.color = c.text;

            // Adiciona checkmark se verde
            if (useGreen) {
                const checkmark = document.createElement('span');
                checkmark.className = 'btn-checkmark';
                checkmark.innerHTML = '✓';
                checkmark.style.cssText = 'position:absolute; top:-6px; right:-6px; background:#16a34a; color:white; width:18px; height:18px; border-radius:50%; font-size:11px; display:flex; align-items:center; justify-content:center; font-weight:bold;';
                element.style.position = 'relative';
                element.appendChild(checkmark);
            }
        }

        return element;
    },

    /**
     * Esconde todos os alertas de tipo de pedido
     */
    _hideAllAlerts: function () {
        const alertBoxRetirada = document.getElementById('retirada-client-alert');
        const alertBoxEntrega = document.getElementById('entrega-alert');
        if (alertBoxRetirada) alertBoxRetirada.style.display = 'none';
        if (alertBoxEntrega) alertBoxEntrega.style.display = 'none';
    },

    /**
     * Fecha painel de entrega e reseta flag
     */
    _closeDeliveryIfOpen: function () {
        if (typeof CheckoutEntrega !== 'undefined') {
            CheckoutEntrega.closePanel();
            CheckoutEntrega.dataFilled = false;
        }
    },

    /**
     * Processa lógica específica de Retirada
     */
    _handleRetirada: function () {
        const ctx = CheckoutHelpers.getContextIds();
        const displayName = this._getDisplayName(ctx);

        const alertBox = document.getElementById('retirada-client-alert');
        const clientSelectedBox = document.getElementById('retirada-client-selected');
        const noClientBox = document.getElementById('retirada-no-client');

        if (alertBox) alertBox.style.display = 'block';

        // Aceita cliente OU mesa para liberar Retirada
        if ((ctx.hasClient || ctx.hasTable) && displayName) {
            if (clientSelectedBox) {
                clientSelectedBox.style.display = 'block';
                document.getElementById('retirada-client-name').innerText = displayName;
            }
            if (noClientBox) noClientBox.style.display = 'none';
        } else {
            if (clientSelectedBox) clientSelectedBox.style.display = 'none';
            if (noClientBox) noClientBox.style.display = 'block';
        }
    },

    /**
     * Processa lógica específica de Entrega
     * Abre modal de entrega automaticamente se dados não preenchidos
     * @param {boolean} forceOpen - Se true, abre o modal incondicionalmente (ex: click do usuário)
     */
    _handleEntrega: function (forceOpen = false) {
        // Verifica se dados já foram preenchidos
        const isFilled = typeof CheckoutEntrega !== 'undefined' && CheckoutEntrega.isDataFilled();

        // Abre se NÃO estiver preenchido, OU se forçado (usuário clicou para editar)
        if (!isFilled || forceOpen) {
            // Abre modal de entrega automaticamente
            if (typeof CheckoutEntrega !== 'undefined') {
                CheckoutEntrega.openPanel();
            } else if (typeof openDeliveryPanel === 'function') {
                openDeliveryPanel();
            }
        }
    },

    /**
     * Atualiza estado do botão "Pagar Depois"
     */
    _updateSavePickupButton: function (type) {
        const btnSavePickup = document.getElementById('btn-save-pickup');
        if (!btnSavePickup) return;

        if (type === 'retirada' || type === 'entrega') {
            btnSavePickup.style.display = 'flex';

            const ctx = CheckoutHelpers.getContextIds();
            let canEnable = false;

            if (type === 'retirada') {
                canEnable = ctx.hasClient || ctx.hasTable;
            } else if (type === 'entrega') {
                const isFilled = typeof deliveryDataFilled !== 'undefined' && deliveryDataFilled;
                canEnable = ctx.hasClient || ctx.hasTable || isFilled;
            }

            btnSavePickup.disabled = !canEnable;
            btnSavePickup.style.opacity = canEnable ? '1' : '0.5';
            btnSavePickup.style.cursor = canEnable ? 'pointer' : 'not-allowed';
        } else {
            btnSavePickup.style.display = 'none';
        }
    },

    /**
     * Obtém o nome para exibição (cliente ou mesa)
     */
    _getDisplayName: function (ctx) {
        // Tenta pegar o nome de várias fontes
        let displayName = document.getElementById('current_client_name')?.value;

        if (!displayName) {
            displayName = document.getElementById('current_table_name')?.value;
        }

        // Se tem mesa com número, usa "Mesa X"
        if (!displayName && ctx.hasTable) {
            const tableNumber = document.getElementById('current_table_number')?.value;
            if (tableNumber) displayName = 'Mesa ' + tableNumber;
        }

        if (!displayName) {
            const selectedName = document.getElementById('selected-client-name')?.innerText;
            if (selectedName && selectedName !== 'Nome' && selectedName.trim() !== '') {
                displayName = selectedName;
            }
        }

        return displayName || '';
    }

};

// Expõe globalmente para uso pelos outros módulos
window.CheckoutOrderType = CheckoutOrderType;


/* ========== pdv/checkout/retirada.js ========== */
/**
 * PDV CHECKOUT - Retirada
 * Funções auxiliares para cliente de retirada
 * 
 * Dependências: CheckoutUI
 */

/**
 * Abre seletor de cliente na barra lateral
 */
window.openClientSelector = function () {
    const selectedArea = document.getElementById('selected-client-area');
    const searchArea = document.getElementById('client-search-area');
    const searchInput = document.getElementById('client-search');

    // Limpa cliente atual se houver
    document.getElementById('current_client_id').value = '';
    if (document.getElementById('current_client_name')) {
        document.getElementById('current_client_name').value = '';
    }

    // Mostra a área de busca
    if (selectedArea) selectedArea.style.display = 'none';
    if (searchArea) searchArea.style.display = 'flex';
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
        // Dispara o evento para mostrar as mesas/opções
        searchInput.dispatchEvent(new Event('focus'));
    }

    // Alerta visual
    alert('Selecione um cliente na barra lateral à direita');
};

/**
 * Limpa o cliente selecionado para retirada
 */
window.clearRetiradaClient = function () {
    // Limpa o cliente selecionado
    document.getElementById('current_client_id').value = '';
    if (document.getElementById('current_client_name')) {
        document.getElementById('current_client_name').value = '';
    }

    // Limpa a mesa selecionada também (evita inconsistência)
    const tableIdInput = document.getElementById('current_table_id');
    if (tableIdInput) {
        tableIdInput.value = '';
    }

    // Limpa a barra lateral visualmente (sem abrir menu de opções)
    const selectedArea = document.getElementById('selected-client-area');
    const searchArea = document.getElementById('client-search-area');
    const searchInput = document.getElementById('client-search');

    if (selectedArea) selectedArea.style.display = 'none';
    if (searchArea) searchArea.style.display = 'flex';
    if (searchInput) {
        searchInput.value = '';
        // NÃO dar focus aqui - evita abrir automaticamente as opções
    }

    // Mostra o aviso de "Vincule um cliente"
    const clientSelectedBox = document.getElementById('retirada-client-selected');
    const noClientBox = document.getElementById('retirada-no-client');

    if (clientSelectedBox) clientSelectedBox.style.display = 'none';
    if (noClientBox) noClientBox.style.display = 'block';

    // Esconde botão "Salvar" na sidebar (volta ao modo balcão)
    const btnSave = document.getElementById('btn-save-command');
    if (btnSave) btnSave.style.display = 'none';

    // Reseta estado do PDV para balcão
    if (typeof PDVState !== 'undefined') {
        PDVState.set({ modo: 'balcao', mesaId: null, clienteId: null });
    }

    CheckoutUI.updateCheckoutUI();
};

/**
 * Reseta o alerta de retirada quando cliente é removido
 * (Chamado por PDVTables.clearClient)
 * 
 * NOTA: Esta função foi desativada pois a UI de retirada agora é gerenciada
 * pelo orderType.js através do selectOrderType('retirada')
 */
window.handleRetiradaValidation = function () {
    // Função desativada - a UI é gerenciada pelo tables-cliente.js
    // Não devemos forçar 'retirada' aqui pois isso impede o reset para 'local'
    // console.log('Retirada validation skipped to allow reset');
};


/* ========== pdv/checkout/entrega.js ========== */
/**
 * PDV CHECKOUT - Entrega
 * Módulo de dados de entrega (encapsulado)
 * 
 * Dependências: CheckoutUI, CheckoutOrderType, CheckoutTotals
 */

const CheckoutEntrega = {

    // Constante: IDs dos campos de entrega
    FIELD_IDS: [
        'delivery_name', 'delivery_address', 'delivery_number',
        'delivery_neighborhood', 'delivery_phone',
        'delivery_complement', 'delivery_observation'
    ],

    // Estado interno (não mais global)
    dataFilled: false,

    /**
     * Abre o painel de entrega
     */
    openPanel: function () {
        const panel = document.getElementById('delivery-panel');
        if (!panel) return;

        // Só auto-preenche nome se tiver CLIENTE selecionado (não mesa)
        // Mesa não deve preencher o nome no formulário de entrega
        const clientId = document.getElementById('current_client_id')?.value;

        if (clientId && clientId !== '' && clientId !== '0') {
            const clientName = document.getElementById('current_client_name')?.value || '';

            if (clientName && clientName.trim()) {
                document.getElementById('delivery_name').value = clientName.replace('🔹 ', '').split(' (')[0].trim();
            }
        }

        // Mostra o painel
        panel.style.display = 'flex';

        // Foca no primeiro campo vazio
        const nameInput = document.getElementById('delivery_name');
        const addressInput = document.getElementById('delivery_address');
        if (nameInput && !nameInput.value) {
            nameInput.focus();
        } else if (addressInput) {
            addressInput.focus();
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    /**
     * Fecha o painel de entrega
     */
    closePanel: function () {
        const panel = document.getElementById('delivery-panel');
        if (panel) panel.style.display = 'none';
    },

    /**
     * Confirma dados de entrega e atualiza total com taxa
     */
    confirmData: function () {
        // Valida campos obrigatórios
        const name = document.getElementById('delivery_name').value.trim();
        const address = document.getElementById('delivery_address').value.trim();
        const neighborhood = document.getElementById('delivery_neighborhood').value.trim();

        if (!name) {
            alert('Digite o nome do cliente!');
            document.getElementById('delivery_name').focus();
            return;
        }
        if (!address) {
            alert('Digite o endereço!');
            document.getElementById('delivery_address').focus();
            return;
        }
        if (!neighborhood) {
            alert('Digite o bairro!');
            document.getElementById('delivery_neighborhood').focus();
            return;
        }

        // Marca como preenchido
        this.dataFilled = true;

        // Fecha o painel
        this.closePanel();

        // ====== FEEDBACK VISUAL 1: Toast verde ======
        this._showToast('✓ Dados de entrega confirmados!');

        // ====== FEEDBACK VISUAL 2: Card "Entrega" fica verde ======
        this._setCardGreen();

        // ====== FEEDBACK VISUAL 3: Badge com check no card ======
        this._addCheckBadge();

        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Re-executa selectOrderType para manter estado
        // Não chamamos selectOrderType aqui porque já deixamos o card verde

        // Atualiza o TOTAL exibido com a taxa de entrega
        if (typeof CheckoutTotals !== 'undefined') {
            let newTotal = CheckoutTotals.getFinalTotal();

            const totalDisplay = document.getElementById('checkout-total-display');
            if (totalDisplay) {
                totalDisplay.innerText = 'R$ ' + newTotal.toFixed(2).replace('.', ',');
            }

            // Atualiza o Input "Valor a Lançar"
            const payInput = document.getElementById('pay-amount');
            const paidDisplay = document.getElementById('display-paid');

            if (payInput) {
                let paidValue = 0;
                if (paidDisplay) {
                    const raw = paidDisplay.innerText.replace(/[^\d,]/g, '').replace(',', '.');
                    paidValue = parseFloat(raw) || 0;
                }

                if (paidValue < 0.01) {
                    payInput.value = newTotal.toFixed(2).replace('.', ',');
                    payInput.dispatchEvent(new Event('input'));
                }
            }
        }

        // Atualiza UI do checkout
        if (typeof CheckoutUI !== 'undefined') {
            CheckoutUI.updateCheckoutUI();
        }
    },

    /**
     * Mostra toast de confirmação verde
     */
    _showToast: function (message) {
        // Remove toast anterior se existir
        const existing = document.getElementById('delivery-toast');
        if (existing) existing.remove();

        // Cria toast
        const toast = document.createElement('div');
        toast.id = 'delivery-toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #059669;
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
            z-index: 9999;
            animation: slideUp 0.3s ease;
        `;
        toast.textContent = message;

        // Adiciona animação CSS se não existir
        if (!document.getElementById('toast-animation-style')) {
            const style = document.createElement('style');
            style.id = 'toast-animation-style';
            style.textContent = `
                @keyframes slideUp {
                    from { opacity: 0; transform: translateX(-50%) translateY(20px); }
                    to { opacity: 1; transform: translateX(-50%) translateY(0); }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(toast);

        // Remove após 3 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    /**
     * Muda card de entrega para verde
     */
    _setCardGreen: function () {
        const card = document.querySelector('.order-toggle-btn[data-type="entrega"]');
        if (card) {
            card.style.borderColor = '#059669';
            card.style.background = '#ecfdf5';
            card.style.color = '#059669';
        }
    },

    /**
     * Adiciona badge de check no card de entrega
     */
    _addCheckBadge: function () {
        const card = document.querySelector('.order-toggle-btn[data-type="entrega"]');
        if (!card) return;

        // Remove badge anterior se existir
        const existingBadge = card.querySelector('.delivery-check-badge');
        if (existingBadge) existingBadge.remove();

        // Cria badge
        const badge = document.createElement('span');
        badge.className = 'delivery-check-badge';
        badge.style.cssText = `
            position: absolute;
            top: -6px;
            right: -6px;
            width: 20px;
            height: 20px;
            background: #059669;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.4);
        `;
        badge.textContent = '✓';

        // Card precisa ser position relative
        card.style.position = 'relative';
        card.appendChild(badge);
    },

    /**
     * Retorna objeto com dados de entrega preenchidos
     * @returns {Object|null}
     */
    getData: function () {
        if (!this.dataFilled) return null;

        return {
            name: document.getElementById('delivery_name')?.value || '',
            address: document.getElementById('delivery_address')?.value || '',
            number: document.getElementById('delivery_number')?.value || '',
            neighborhood: document.getElementById('delivery_neighborhood')?.value || '',
            phone: document.getElementById('delivery_phone')?.value || '',
            complement: document.getElementById('delivery_complement')?.value || '',
            observation: document.getElementById('delivery_observation')?.value || ''
        };
    },

    /**
     * Limpa dados de entrega
     */
    clearData: function () {
        this.dataFilled = false;
        this._clearFields();
        this._clearVisualState();

        // Atualiza alertas
        const dadosOk = document.getElementById('entrega-dados-ok');
        const dadosPendente = document.getElementById('entrega-dados-pendente');

        if (dadosOk) dadosOk.style.display = 'none';
        if (dadosPendente) dadosPendente.style.display = 'block';

        if (typeof lucide !== 'undefined') lucide.createIcons();
        if (typeof CheckoutUI !== 'undefined') CheckoutUI.updateCheckoutUI();
    },

    /**
     * Reset ao fechar checkout
     */
    resetOnClose: function () {
        this.dataFilled = false;
        this.closePanel();
        this._clearFields();
        this._clearVisualState();
    },

    /**
     * Verifica se dados estão preenchidos
     */
    isDataFilled: function () {
        return this.dataFilled;
    },

    /**
     * Helper: Limpa todos os campos de entrega
     */
    _clearFields: function () {
        this.FIELD_IDS.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    },

    /**
     * Helper: Limpa estado visual (badge e cor verde)
     */
    _clearVisualState: function () {
        const card = document.querySelector('.order-toggle-btn[data-type="entrega"]');
        if (!card) return;

        // Remove badge
        const badge = card.querySelector('.delivery-check-badge');
        if (badge) badge.remove();

        // Reseta cores para inativo (branco com texto preto)
        card.style.borderColor = '#cbd5e1';
        card.style.background = 'white';
        card.style.color = '#1e293b';
    }

};

// Expõe globalmente
window.CheckoutEntrega = CheckoutEntrega;

// Aliases de compatibilidade (HTML usa esses)
window.openDeliveryPanel = () => CheckoutEntrega.openPanel();
window.closeDeliveryPanel = () => CheckoutEntrega.closePanel();
window.confirmDeliveryData = () => CheckoutEntrega.confirmData();
window.getDeliveryData = () => CheckoutEntrega.getData();
window.clearDeliveryData = () => CheckoutEntrega.clearData();
window._resetDeliveryOnClose = () => CheckoutEntrega.resetOnClose();

// Para compatibilidade com código legado que checa deliveryDataFilled
Object.defineProperty(window, 'deliveryDataFilled', {
    get: function () { return CheckoutEntrega.dataFilled; },
    set: function (val) { CheckoutEntrega.dataFilled = val; }
});


/* ========== pdv/checkout/flow.js ========== */
/**
 * PDV CHECKOUT - Flow
 * Orquestração de fluxos de venda (Mesa, Comanda, Balcão)
 * 
 * Dependências: CheckoutState, CheckoutHelpers, CheckoutUI, CheckoutPayments, CheckoutOrderType, PDVCart, PDVState
 */

const CheckoutFlow = {

    /**
     * Ponto de entrada principal para finalizar venda
     * Detecta contexto (Mesa, Comanda, Balcão, Edição de Pago) e direciona
     */
    finalizeSale: function () {
        const ctx = CheckoutHelpers.getContextIds();

        // VERIFICAÇÃO ESPECIAL: Edição de Pedido Pago
        if (typeof isEditingPaidOrder !== 'undefined' && isEditingPaidOrder) {
            this.handlePaidOrderInclusion();
            return;
        }

        // MESA
        if (ctx.hasTable) {
            if (PDVCart.items.length === 0) { alert('Carrinho vazio!'); return; }
            this.openCheckoutModal();
            return;
        }

        // BALCÃO
        const stateBalcao = PDVState.getState();
        if (!ctx.hasTable && stateBalcao.modo !== 'retirada') {
            PDVState.set({ modo: 'balcao' });
        }

        if (PDVCart.items.length === 0) { alert('Carrinho vazio!'); return; }

        this.openCheckoutModal();
    },

    /**
     * Fluxo de inclusão em pedido já pago
     */
    handlePaidOrderInclusion: function () {
        const cartTotal = PDVCart.calculateTotal();

        if (PDVCart.items.length > 0 && cartTotal > 0.01) {
            CheckoutState.resetPayments();
            document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(cartTotal);
            document.getElementById('checkoutModal').style.display = 'flex';
            CheckoutPayments.setMethod('dinheiro');
            CheckoutUI.updateCheckoutUI();
            window.isPaidOrderInclusion = true;
        } else {
            alert('Carrinho vazio! Adicione novos itens para cobrar.');
        }
    },

    /**
     * Fechar conta de Mesa
     */
    fecharContaMesa: function (mesaId) {
        PDVState.set({ modo: 'mesa', mesaId: mesaId, fechandoConta: true });
        const state = PDVState.getState();

        if (state.status === 'editando_pago') {
            alert('Mesa não permite editar pedido pago.');
            return;
        }

        CheckoutState.resetPayments();

        const tableTotalStr = document.getElementById('table-initial-total').value;
        const tableTotal = parseFloat(tableTotalStr) || 0;

        // CORREÇÃO: Atualiza cachedTotal para que getFinalTotal() retorne o valor correto
        CheckoutState.cachedTotal = tableTotal;

        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(tableTotal);
        document.getElementById('checkoutModal').style.display = 'flex';
        CheckoutPayments.setMethod('dinheiro');
        CheckoutUI.updateCheckoutUI();

        // Preenche o input com o valor a pagar
        const payInput = document.getElementById('pay-amount');
        if (payInput) {
            payInput.value = tableTotal.toFixed(2).replace('.', ',');
            CheckoutHelpers.formatMoneyInput(payInput);
        }
    },

    /**
     * Fechar Comanda (Cliente)
     */
    fecharComanda: function (orderId) {
        const isPaid = document.getElementById('current_order_is_paid') ? document.getElementById('current_order_is_paid').value == '1' : false;

        PDVState.set({
            modo: 'comanda',
            pedidoId: orderId ? parseInt(orderId) : null,
            fechandoConta: true
        });

        if (isPaid) {
            if (!confirm('Este pedido já está PAGO. Deseja entregá-lo e finalizar?')) return;
            CheckoutSubmit.forceDelivery(orderId);
            return;
        }

        CheckoutState.closingOrderId = orderId;
        CheckoutState.resetPayments();

        const totalStr = document.getElementById('table-initial-total').value;
        const initialTotal = parseFloat(totalStr) || 0;
        const cartTotal = (typeof PDVCart !== 'undefined') ? PDVCart.calculateTotal() : 0;

        CheckoutState.cachedTotal = initialTotal + cartTotal;

        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(CheckoutState.cachedTotal);

        // Seleciona "Local" por padrão
        CheckoutOrderType.selectOrderType('local');

        document.getElementById('checkoutModal').style.display = 'flex';
        CheckoutPayments.setMethod('dinheiro');
        CheckoutUI.updateCheckoutUI();
    },

    /**
     * Abre modal de checkout (fluxo padrão)
     */
    openCheckoutModal: function () {
        CheckoutState.reset();

        // Calcula o total
        let cartTotal = 0;
        if (typeof calculateTotal === 'function') {
            cartTotal = calculateTotal();
        } else if (typeof PDVCart !== 'undefined') {
            cartTotal = PDVCart.calculateTotal();
        }
        let tableInitialTotal = parseFloat(document.getElementById('table-initial-total')?.value || 0);
        CheckoutState.cachedTotal = cartTotal + tableInitialTotal;

        // Reset Inputs
        const discInput = document.getElementById('discount-amount');
        if (discInput) discInput.value = '';

        CheckoutUI.updatePaymentList();

        document.getElementById('checkout-total-display').innerText = CheckoutHelpers.formatCurrency(CheckoutState.cachedTotal);
        document.getElementById('checkoutModal').style.display = 'flex';
        CheckoutPayments.setMethod('dinheiro');

        // Sincroniza com o tipo de pedido selecionado no header
        const selectedType = document.getElementById('selected_order_type')?.value || 'local';
        // [FIX] Passa true para não reabrir modals (sync apenas)
        CheckoutOrderType.selectOrderType(selectedType, null, true);

        CheckoutUI.updateCheckoutUI();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    /**
     * Fecha modal de checkout e limpa estado
     */
    closeCheckout: function () {
        document.getElementById('checkoutModal').style.display = 'none';
        CheckoutState.resetPayments();

        // Feature: Remove ajuste ao cancelar/fechar
        if (typeof CheckoutAdjust !== 'undefined') {
            CheckoutAdjust.removeAdjustment();
            if (typeof CheckoutAdjust._resetUI === 'function') CheckoutAdjust._resetUI();
        }

        // Limpa visual
        const alertBox = document.getElementById('retirada-client-alert');
        if (alertBox) alertBox.style.display = 'none';

        // Reset dados de entrega
        if (typeof CheckoutEntrega !== 'undefined') {
            CheckoutEntrega.resetOnClose();
        } else if (typeof _resetDeliveryOnClose === 'function') {
            _resetDeliveryOnClose();
        }
    }

};

// Expõe globalmente
window.CheckoutFlow = CheckoutFlow;


/* ========== pdv/checkout/index.js ========== */
/**
 * PDV CHECKOUT - Index (Fachada)
 * Orquestrador principal que monta window.PDVCheckout
 * 
 * Dependências: Todos os módulos de checkout devem estar carregados antes
 * - CheckoutHelpers
 * - CheckoutState
 * - CheckoutTotals
 * - CheckoutUI
 * - CheckoutPayments
 * - CheckoutSubmit
 * - CheckoutOrderType
 * - CheckoutFlow
 * - CheckoutEntrega
 */

const PDVCheckout = {

    // ==========================================
    // ESTADO (delegado para CheckoutState)
    // ==========================================

    get currentPayments() { return CheckoutState.currentPayments; },
    set currentPayments(val) { CheckoutState.currentPayments = val; },

    get totalPaid() { return CheckoutState.totalPaid; },
    set totalPaid(val) { CheckoutState.totalPaid = val; },

    get discountValue() { return CheckoutState.discountValue; },
    set discountValue(val) { CheckoutState.discountValue = val; },

    get cachedTotal() { return CheckoutState.cachedTotal; },
    set cachedTotal(val) { CheckoutState.cachedTotal = val; },

    get selectedMethod() { return CheckoutState.selectedMethod; },
    set selectedMethod(val) { CheckoutState.selectedMethod = val; },

    get closingOrderId() { return CheckoutState.closingOrderId; },
    set closingOrderId(val) { CheckoutState.closingOrderId = val; },

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================

    init: function () {
        this.bindEvents();
    },

    bindEvents: function () {
        // Input de pagamento
        const payInput = document.getElementById('pay-amount');
        if (payInput) {
            payInput.addEventListener('input', function () { CheckoutHelpers.formatMoneyInput(this); });
            payInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    CheckoutPayments.addPayment();
                }
            });
        }

        // Input Desconto
        const discInput = document.getElementById('discount-amount');
        if (discInput) {
            discInput.addEventListener('input', function () {
                CheckoutHelpers.formatMoneyInput(this);
                CheckoutTotals.applyDiscount(this.value);
            });
        }
    },

    // ==========================================
    // HELPERS (delegados)
    // ==========================================

    formatMoneyInput: (input) => CheckoutHelpers.formatMoneyInput(input),
    formatCurrency: (val) => CheckoutHelpers.formatCurrency(val),
    formatMethodLabel: (method) => CheckoutHelpers.formatMethodLabel(method),

    // ==========================================
    // DESCONTO / TOTAIS (delegados)
    // ==========================================

    applyDiscount: (valStr) => CheckoutTotals.applyDiscount(valStr),
    getFinalTotal: () => CheckoutTotals.getFinalTotal(),

    // ==========================================
    // UI (delegados)
    // ==========================================

    updatePaymentList: () => CheckoutUI.updatePaymentList(),
    updateCheckoutUI: () => CheckoutUI.updateCheckoutUI(),
    showSuccessModal: () => CheckoutUI.showSuccessModal(),

    // ==========================================
    // PAGAMENTOS (delegados)
    // ==========================================

    setMethod: (method) => CheckoutPayments.setMethod(method),
    addPayment: (m, a) => CheckoutPayments.addPayment(m, a),
    addCrediarioPayment: () => CheckoutPayments.addCrediarioPayment(),
    removePayment: (index) => CheckoutPayments.removePayment(index),

    // ==========================================
    // TIPO DE PEDIDO (delegado)
    // ==========================================

    selectOrderType: (type, element) => CheckoutOrderType.selectOrderType(type, element),

    // ==========================================
    // SUBMIT (delegados)
    // ==========================================

    submitSale: () => CheckoutSubmit.submitSale(),
    saveClientOrder: () => CheckoutSubmit.saveClientOrder(),
    savePickupOrder: () => CheckoutSubmit.savePickupOrder(),
    forceDelivery: (orderId) => CheckoutSubmit.forceDelivery(orderId),

    // ==========================================
    // FLUXO (delegado para CheckoutFlow)
    // ==========================================

    finalizeSale: () => CheckoutFlow.finalizeSale(),
    fecharContaMesa: (mesaId) => CheckoutFlow.fecharContaMesa(mesaId),
    fecharComanda: (orderId) => CheckoutFlow.fecharComanda(orderId),
    openCheckoutModal: () => CheckoutFlow.openCheckoutModal(),
    closeCheckout: () => CheckoutFlow.closeCheckout(),
    handlePaidOrderInclusion: () => CheckoutFlow.handlePaidOrderInclusion()

};

// ==========================================
// EXPÕE GLOBALMENTE
// ==========================================

window.PDVCheckout = PDVCheckout;

// ==========================================
// ALIASES DE COMPATIBILIDADE (HTML usa esses)
// ==========================================

window.finalizeSale = () => {
    PDVCheckout.finalizeSale();
};
window.fecharContaMesa = (id) => PDVCheckout.fecharContaMesa(id);
window.fecharComanda = (mid) => PDVCheckout.fecharComanda(mid);
window.includePaidOrderItems = () => PDVCheckout.finalizeSale();
window.saveClientOrder = () => PDVCheckout.saveClientOrder();
window.submitSale = () => PDVCheckout.submitSale();
window.setMethod = (m) => PDVCheckout.setMethod(m);
window.addPayment = (m, a) => PDVCheckout.addPayment(m, a);
window.addCrediarioPayment = () => PDVCheckout.addCrediarioPayment();
window.removePayment = (i) => PDVCheckout.removePayment(i);
window.closeCheckout = () => PDVCheckout.closeCheckout();
window.selectOrderType = (t, e) => PDVCheckout.selectOrderType(t, e);


/* ========== pdv/pdv-events.js ========== */
/**
 * PDV-EVENTS.JS - Gerenciador de Eventos (Delegation)
 * 
 * Centraliza os listeners de eventos do PDV para remover 'onclick' do HTML.
 * Padrão: data-action="nomeAcao" e data-payload="{json}"
 * 
 * [REFACTOR SPA] Agora suporta init() idempotente com removeEventListener.
 */

const PDVEvents = {
    // Armazena referência para desvincular eventos
    clickHandler: null,
    keydownHandler: null,

    init: function () {
        this.bindGlobalClicks();
        this.bindKeyboardShortcuts();
    },

    bindGlobalClicks: function () {
        // 1. Remove listener anterior se existir (Limpeza)
        if (this.clickHandler) {
            document.removeEventListener('click', this.clickHandler);
        }

        // 2. Cria nova referência vinculada (Bind)
        this.clickHandler = (e) => this.handleDocumentClick(e);

        // 3. Adiciona
        document.addEventListener('click', this.clickHandler);
    },

    bindKeyboardShortcuts: function () {
        if (this.keydownHandler) {
            document.removeEventListener('keydown', this.keydownHandler);
        }

        this.keydownHandler = (e) => this.handleDocumentKeydown(e);
        document.addEventListener('keydown', this.keydownHandler);
    },

    // ===========================================
    // ROUTERS (Event Routing)
    // ===========================================

    handleDocumentClick: function (e) {
        // PRODUCTS GRID
        const productCard = e.target.closest('.js-add-product');
        if (productCard) {
            this.handleAddProduct(productCard);
            return;
        }

        // GENERIC ACTIONS (data-action)
        const actionBtn = e.target.closest('[data-action]');
        if (actionBtn) {
            const action = actionBtn.dataset.action;
            this.handleAction(action, actionBtn);
        }
    },

    handleDocumentKeydown: function (e) {
        // F2 is already handled in pdv-search.js
        if (e.key === 'Escape') {
            if (window.closeExtrasModal) window.closeExtrasModal();
            if (window.closeFichaModal) window.closeFichaModal();
        }
    },

    // ===========================================
    // HANDLERS
    // ===========================================

    handleAddProduct: function (el) {
        // Proteção contra duplicação de clique rápido
        if (el.classList.contains('processing-click')) return;
        el.classList.add('processing-click');
        setTimeout(() => el.classList.remove('processing-click'), 300);

        const id = el.dataset.id;
        const name = el.dataset.name;
        const price = parseFloat(el.dataset.price);
        const hasExtras = el.dataset.hasExtras === 'true';

        // Animação visual
        el.classList.add('clicked');
        setTimeout(() => el.classList.remove('clicked'), 150);

        if (window.PDVCart) {
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

            if (window.playBeep) window.playBeep();
        } else {
            console.error('PDVCart module not loaded');
        }
    },

    handleAction: function (action, el) {
        switch (action) {
            // CART ACTIONS
            case 'cart-undo':
                if (window.PDVCart) PDVCart.undoClear();
                break;
            case 'cart-clear':
                if (window.PDVCart) PDVCart.clear();
                break;
            case 'cart-remove-item':
                if (window.PDVCart) PDVCart.remove(el.dataset.id);
                break;

            // EXTRAS MODAL
            case 'extras-close':
                if (window.PDVExtras) PDVExtras.close();
                break;
            case 'extras-confirm':
                if (window.PDVExtras) PDVExtras.confirm();
                break;
            case 'extras-increase':
                if (window.PDVExtras) PDVExtras.increaseQty();
                break;
            case 'extras-decrease':
                if (window.PDVExtras) PDVExtras.decreaseQty();
                break;

            // TABLE/CLIENT ACTIONS
            case 'ficha-open':
                if (window.openFichaModal) window.openFichaModal();
                break;
            case 'ficha-close':
                if (window.closeFichaModal) window.closeFichaModal();
                break;
            case 'ficha-print':
                if (window.printFicha) window.printFicha();
                break;

            // ITEM SAVED ACTIONS (Tables)
            case 'saved-item-delete':
                if (window.deleteSavedItem) window.deleteSavedItem(el.dataset.id, el.dataset.orderId);
                break;
            case 'table-cancel':
                if (window.cancelTableOrder) window.cancelTableOrder(el.dataset.tableId, el.dataset.orderId);
                break;

            // ORDER FLOW
            case 'order-save': // Salvar Comanda
                if (window.saveClientOrder) window.saveClientOrder();
                else console.error('saveClientOrder not found');
                break;
            case 'order-include-paid': // Incluir no Pago
                if (window.includePaidOrderItems) window.includePaidOrderItems();
                break;
            case 'order-finalize-quick': // Balcão Finalizar
                if (window.finalizeSale) window.finalizeSale();
                else console.error('finalizeSale not found');
                break;
            case 'order-close-table': // Fechar Mesa
                if (window.fecharContaMesa) window.fecharContaMesa(el.dataset.tableId);
                break;
            case 'order-close-command': // Fechar Comanda
                if (window.fecharComanda) window.fecharComanda(el.dataset.orderId);
                break;

            // CLIENT MODAL
            case 'client-new':
                const m = document.getElementById('clientModal');
                if (m) {
                    document.body.appendChild(m);
                    m.style.display = 'flex';
                    m.style.zIndex = '9999';
                    setTimeout(() => document.getElementById('new_client_name')?.focus(), 50);
                }
                break;
            case 'client-clear':
                if (window.clearClient) window.clearClient();
                break;

            default:
                console.warn('[PDV-EVENTS] Unknown action:', action);
        }
    }
};

// ==========================================
// EXPORTAR GLOBALMENTE
// ==========================================
window.PDVEvents = PDVEvents;


/* ========== pdv/pdv-search.js ========== */
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

    keydownHandler: null,

    bindEvents: function () {
        // Eventos de Chips (Categorias)
        if (this.chips) {
            this.chips.forEach(chip => {
                // removeEventListener não é necessário pois elementos são substituídos no SPA
                chip.addEventListener('click', (e) => {
                    this.chips.forEach(c => c.classList.remove('active'));
                    e.currentTarget.classList.add('active');

                    this.selectedCategory = e.currentTarget.dataset.category;
                    this.filterProducts();
                });
            });
        }

        // Eventos de Busca (Input)
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value.toLowerCase().trim();
                this.filterProducts();
            });

            // Atalho F2 (Global)
            if (this.keydownHandler) {
                document.removeEventListener('keydown', this.keydownHandler);
            }

            this.keydownHandler = (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    if (this.searchInput) this.searchInput.focus();
                }
            };
            document.addEventListener('keydown', this.keydownHandler);
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


/* ========== pdv.js ========== */
/**
 * PDV MAIN - Ponto de Entrada
 * Orquestra os módulos: State, Cart, Tables, Checkout.
 */
(function () {
    'use strict';

    window.PDV = window.PDV || {};

    Object.assign(window.PDV, {
        init: function () {
            // 1. LER CONFIGURAÇÃO (SPA)
            const configEl = document.getElementById('pdv-config');
            let config = {};
            if (configEl) {
                try {
                    config = JSON.parse(configEl.dataset.config);
                    // Define globais legado se necessário para compatibilidade
                    if (config.baseUrl) window.BASE_URL = config.baseUrl;
                    if (config.deliveryFee) window.PDV_DELIVERY_FEE = config.deliveryFee;
                    if (config.tableId) window.PDV_TABLE_ID = config.tableId;
                } catch (e) {
                    console.error('[PDV] Invalid config JSON', e);
                }
            }

            // Limpa URL
            const url = new URL(window.location.href);
            if (url.searchParams.has('order_id') || url.searchParams.has('mesa_id')) {
                const cleanUrl = url.origin + url.pathname + '#pdv'; // Mantém hash se necessário
                // window.history.replaceState({}, document.title, cleanUrl); // AdminSPA cuida da URL
            }

            // 2. INICIALIZA ESTADO (PDVState)
            const tableIdInput = document.getElementById('current_table_id');
            const clientIdInput = document.getElementById('current_client_id');
            const orderIdInput = document.getElementById('current_order_id');

            const tableId = tableIdInput ? tableIdInput.value : null;
            const clientId = clientIdInput ? clientIdInput.value : null;
            const orderId = orderIdInput ? orderIdInput.value : null;

            // Detecta modo baseado config ou inputs
            let modo = 'balcao';
            let status = 'aberto';

            if (config.isEditingPaidOrder) {
                modo = 'retirada';
                status = 'editando_pago';
            } else if (tableId) {
                modo = 'mesa';
            } else if (orderId) {
                modo = 'comanda';
            }

            if (window.PDVState) {
                PDVState.set({
                    modo: modo,
                    mesaId: tableId ? parseInt(tableId) : null,
                    clienteId: clientId ? parseInt(clientId) : null,
                    pedidoId: orderId ? parseInt(orderId) : null
                });
                PDVState.initStatus(status);
            }

            // 3. INICIALIZA CARRINHO (PDVCart)
            if (window.PDVCart) {
                // Recupera carrinho da config
                const recoveredCart = config.recoveredCart || [];

                // Mapeia formato do PHP para formato do JS
                const items = recoveredCart.map(item => ({
                    id: parseInt(item.id),
                    name: item.name,
                    price: parseFloat(item.price),
                    quantity: parseInt(item.quantity),
                    extras: item.extras || []
                }));

                PDVCart.items = []; // Reseta antes de setar
                PDVCart.setItems(items);

                // [MIGRATION] Recupera itens do balcão se houver migração pendente
                if (typeof PDVCart.recoverFromMigration === 'function') {
                    PDVCart.recoverFromMigration();
                }

                PDVCart.updateUI();
            }

            // 4. INICIALIZA MÓDULOS DE UI
            if (window.PDVTables && typeof PDVTables.init === 'function') PDVTables.init();
            if (window.PDVCheckout && typeof PDVCheckout.init === 'function') PDVCheckout.init();

            // Inicializa Eventos (agora com proteção contra duplicação)
            if (window.PDVEvents && typeof PDVEvents.init === 'function') PDVEvents.init();

            // 5. VISUAL INICIAL
            const btn = document.getElementById('btn-finalizar');
            if (parseInt(tableId) > 0 && btn) {
                btn.innerText = "Salvar";
                btn.style.backgroundColor = "#d97706";
                btn.disabled = false;
            }

            // 6. FILTRO DE CATEGORIAS E BUSCA
            if (window.PDVSearch && typeof PDVSearch.init === 'function') {
                PDVSearch.init();
            }

            // 7. ÍCONES (Lucide)
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    });

    // ============================================
    // HELPERS GLOBAIS (Compatibilidade)
    // ============================================
    window.formatCurrency = function (value) {
        return parseFloat(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    };

    // Auto-init apenas se não estiver no SPA (fallback legado)
    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('spa-content')) {
            PDV.init();
        }
    });

})();

