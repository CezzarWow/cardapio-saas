// ============================================
// PDV-UI.JS - Interface do Carrinho e Clientes
// ============================================

// --- ATUALIZAÇÃO VISUAL DO CARRINHO ---

function updateCartUI() {
    const cartContainer = document.getElementById('cart-items-area');
    const emptyState = document.getElementById('cart-empty-state');
    const totalElement = document.getElementById('cart-total');
    const btnFinalizar = document.getElementById('btn-finalizar');

    if (!cartContainer) return;

    cartContainer.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        cartContainer.style.display = 'none';
        if (emptyState) emptyState.style.display = 'flex';
        if (btnFinalizar) btnFinalizar.disabled = true;
    } else {
        cartContainer.style.display = 'block';
        if (emptyState) emptyState.style.display = 'none';
        if (btnFinalizar) btnFinalizar.disabled = false;

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            const itemHTML = `
                <div style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.9rem; color: #1f2937;">${item.name}</div>
                        <div style="font-size: 0.8rem; color: #6b7280;">
                            ${item.quantity}x R$ ${item.price.toFixed(2).replace('.', ',')}
                        </div>
                    </div>
                    <div style="display: flex; gap: 5px; align-items: center;">
                         <button onclick="removeFromCart(${item.id})" style="background: #fee2e2; color: #991b1b; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">-</button>
                         <button onclick="addToCart(${item.id}, '${item.name}', ${item.price})" style="background: #dcfce7; color: #166534; border: none; width: 24px; height: 24px; border-radius: 6px; cursor: pointer; font-weight:bold;">+</button>
                    </div>
                </div>
            `;
            cartContainer.innerHTML += itemHTML;
        });
    }

    // Atualiza totais
    let tableInitialValue = document.getElementById('table-initial-total')?.value || "0";
    const tableInitialTotal = parseFloat(tableInitialValue);
    const grandTotal = total + tableInitialTotal;

    if (totalElement) {
        totalElement.innerText = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    const grandTotalElement = document.getElementById('grand-total');
    if (grandTotalElement) {
        grandTotalElement.innerText = grandTotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
}

// --- BUSCA E SELEÇÃO DE CLIENTES/MESAS ---

const clientSearchInput = document.getElementById('client-search');
const clientResults = document.getElementById('client-results');
let searchTimeout = null;

/* LOGICA MOVIDA PARA tables.js (EVITAR DUPLICIDADE)
if (clientSearchInput && clientResults) {
    clientSearchInput.addEventListener('input', function () {
        const term = this.value.trim();
        clearTimeout(searchTimeout);

        if (term.length < 2) {
            clientResults.innerHTML = '';
            clientResults.style.display = 'none';
            return;
        }

        // Verifica se é número (mesa) ou texto (cliente)
        const isNumber = /^\d+$/.test(term);

        if (isNumber) {
            fetchTables();
        } else {
            searchTimeout = setTimeout(() => {
                fetch('clientes/buscar?q=' + term)
                    .then(r => r.json())
                    .then(data => renderClientResults(data));
            }, 300);
        }
    });
}
*/

function fetchTables() {
    fetch('mesas/buscar')
        .then(r => r.json())
        .then(data => renderTableResults(data));
}

function renderTableResults(tables) {
    if (!clientResults) return;

    clientResults.style.display = 'block';
    clientResults.innerHTML = '';

    if (tables.length === 0) {
        clientResults.innerHTML = '<div style="padding: 12px; color: #6b7280; text-align: center;">Nenhuma mesa encontrada</div>';
        return;
    }

    tables.forEach(table => {
        const status = table.status === 'ocupada' ? '🔴' : '🟢';
        const div = document.createElement('div');
        div.innerHTML = `${status} Mesa ${table.number}`;
        div.style.cssText = 'padding: 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9;';
        div.onmouseover = () => div.style.background = '#f8fafc';
        div.onmouseout = () => div.style.background = 'white';
        div.onclick = () => selectTable(table);
        clientResults.appendChild(div);
    });
}

function renderClientResults(clients) {
    if (!clientResults) return;

    clientResults.style.display = 'block';
    clientResults.innerHTML = '';

    if (clients.length === 0) {
        clientResults.innerHTML = '<div style="padding: 12px; color: #6b7280; text-align: center;">Nenhum cliente encontrado</div>';
        return;
    }

    clients.forEach(client => {
        const div = document.createElement('div');
        div.innerHTML = `<strong>${client.name}</strong><br><small style="color:#64748b">${client.phone || ''}</small>`;
        div.style.cssText = 'padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9;';
        div.onclick = () => selectClient(client.id, client.name);
        div.onmouseover = () => div.style.background = '#f8fafc';
        div.onmouseout = () => div.style.background = 'white';
        clientResults.appendChild(div);
    });
}

function selectTable(table) {
    document.getElementById('current_table_id').value = table.id;
    document.getElementById('current_client_id').value = '';

    if (clientSearchInput) {
        clientSearchInput.value = '';
        clientSearchInput.placeholder = `Mesa ${table.number} selecionada`;
    }
    if (clientResults) {
        clientResults.style.display = 'none';
    }

    const btn = document.getElementById('btn-finalizar');
    if (btn) {
        btn.innerText = 'Salvar';
        btn.style.backgroundColor = '#d97706';
    }
}

function selectClient(id, name) {
    document.getElementById('current_client_id').value = id;
    document.getElementById('current_table_id').value = '';

    // Armazena o nome do cliente também
    let clientNameInput = document.getElementById('current_client_name');
    if (!clientNameInput) {
        // Cria o input hidden se não existir
        clientNameInput = document.createElement('input');
        clientNameInput.type = 'hidden';
        clientNameInput.id = 'current_client_name';
        document.body.appendChild(clientNameInput);
    }
    clientNameInput.value = name;

    const displayEl = document.getElementById('selected-client-display');
    const nameEl = document.getElementById('selected-client-name');
    const searchBox = document.getElementById('client-search-box');

    if (displayEl) displayEl.style.display = 'flex';
    if (nameEl) nameEl.innerText = name;
    if (searchBox) searchBox.style.display = 'none';
    if (clientResults) clientResults.style.display = 'none';

    // Atualiza a view de Retirada se estiver visível
    const retiradaAlert = document.getElementById('retirada-client-alert');
    if (retiradaAlert && retiradaAlert.style.display !== 'none') {
        const clientSelectedBox = document.getElementById('retirada-client-selected');
        const noClientBox = document.getElementById('retirada-no-client');
        const clientNameDisplay = document.getElementById('retirada-client-name');

        if (clientSelectedBox) {
            clientSelectedBox.style.display = 'block';
            if (clientNameDisplay) clientNameDisplay.innerText = name;
        }
        if (noClientBox) noClientBox.style.display = 'none';

        if (typeof lucide !== 'undefined') lucide.createIcons();
        if (typeof PDVCheckout !== 'undefined') PDVCheckout.updateCheckoutUI();
    }
}

function clearClient() {
    document.getElementById('current_client_id').value = '';
    document.getElementById('current_table_id').value = '';

    const displayEl = document.getElementById('selected-client-display');
    const searchBox = document.getElementById('client-search-box');

    if (displayEl) displayEl.style.display = 'none';
    if (searchBox) searchBox.style.display = 'block';
    if (clientSearchInput) {
        clientSearchInput.value = '';
        clientSearchInput.placeholder = 'Buscar cliente ou mesa...';
    }
}

// --- MODAL NOVO CLIENTE ---

function openClientModal() {
    document.getElementById('newClientModal').style.display = 'flex';
}

function saveClient() {
    const name = document.getElementById('new-client-name').value.trim();
    const phone = document.getElementById('new-client-phone').value.trim();

    if (!name) {
        alert('Nome é obrigatório!');
        return;
    }

    fetch('clientes/salvar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, phone })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                selectClient(data.client_id, name);
                document.getElementById('newClientModal').style.display = 'none';
                document.getElementById('new-client-name').value = '';
                document.getElementById('new-client-phone').value = '';
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(err => alert('Erro de conexão.'));
}

console.log('[PDV] UI carregado ✓');
