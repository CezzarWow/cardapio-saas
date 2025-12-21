// Variável global que guarda os itens
let cart = [];

// Verifica se estamos numa mesa assim que carrega
document.addEventListener('DOMContentLoaded', () => {
    const tableIdInput = document.getElementById('current_table_id');
    const tableId = tableIdInput ? tableIdInput.value : null;
    const btn = document.getElementById('btn-finalizar');

    // --- NOVO: Carrega carrinho recuperado (Edição) ---
    if (typeof recoveredCart !== 'undefined' && recoveredCart.length > 0) {
        // Converte os tipos para garantir numéricos
        cart = recoveredCart.map(item => ({
            id: parseInt(item.id),
            name: item.name,
            price: parseFloat(item.price),
            quantity: parseInt(item.quantity)
        }));
        alert('Pedido carregado para edição! ✏️');
    }
    // --------------------------------------------------

    if (tableId) {
        btn.innerText = "Salvar";
        btn.style.backgroundColor = "#d97706";
    }

    updateCartUI();
});

// Função chamada quando clica no produto
function addToCart(id, name, price) {
    // 1. Verifica se o produto já está no carrinho
    const existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        // Se já existe, só aumenta a quantidade
        existingItem.quantity++;
    } else {
        // Se não existe, adiciona um novo
        cart.push({
            id: id,
            name: name,
            price: parseFloat(price),
            quantity: 1
        });
    }

    // 2. Atualiza a tela
    updateCartUI();
}

// Função para remover ou diminuir item
function removeFromCart(id) {
    const itemIndex = cart.findIndex(item => item.id === id);

    if (itemIndex > -1) {
        const item = cart[itemIndex];
        if (item.quantity > 1) {
            item.quantity--;
        } else {
            cart.splice(itemIndex, 1);
        }
    }

    updateCartUI();
}

// A Mágica: Desenha o carrinho na tela
function updateCartUI() {
    const cartContainer = document.getElementById('cart-items-area'); // Área da lista
    const emptyState = document.getElementById('cart-empty-state');   // "Carrinho Vazio"
    const totalElement = document.getElementById('cart-total');       // Texto do Valor Total
    const btnFinalizar = document.getElementById('btn-finalizar');    // Botão Finalizar

    // Limpa o HTML atual da lista para redesenhar
    cartContainer.innerHTML = '';

    let total = 0;

    if (cart.length === 0) {
        // Se vazio: Mostra o desenho de vazio e esconde a lista
        cartContainer.style.display = 'none';
        emptyState.style.display = 'flex';
        btnFinalizar.disabled = true;
    } else {
        // Se tem itens: Esconde o vazio e mostra a lista
        cartContainer.style.display = 'block';
        emptyState.style.display = 'none';
        btnFinalizar.disabled = false;

        // Desenha cada item
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            // HTML do Item no Carrinho
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

    // --- LÓGICA DE TOTAL UNIFICADO ---

    // --- LÓGICA DE TOTAIS SEPARADOS ---

    // 1. Pega o valor que JÁ ESTÁ na mesa
    let tableInitialValue = document.getElementById('table-initial-total')?.value || "0";
    const tableInitialTotal = parseFloat(tableInitialValue);

    // 2. Calcula o Grand Total (Mesa + Carrinho)
    const grandTotal = total + tableInitialTotal;

    // 3. Atualiza "Adicionar" (Só o Carrinho)
    totalElement.innerText = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    // 4. Atualiza "TOTAL" (Tudo Junto)
    const grandTotalElement = document.getElementById('grand-total');
    if (grandTotalElement) {
        grandTotalElement.innerText = grandTotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
}

// --- LÓGICA DE CHECKOUT 2.0 (INTELIGENTE) ---
let currentPayments = [];
let totalPaid = 0;
let selectedMethod = 'dinheiro'; // Padrão
let isClosingTable = false; // Flag para saber se é fechamento de mesa
let isClosingCommand = false; // Flag para fechamento de comanda
let closingOrderId = null; // ID da comanda sendo fechada

function finalizeSale() {
    // SE ESTIVER EM MESA (ADICIONAR ITENS) -> SALVA DIRETO SEM PAGAMENTO
    const tableId = document.getElementById('current_table_id').value;
    if (tableId) {
        if (cart.length === 0) { alert('Carrinho vazio!'); return; }
        isClosingTable = false;
        submitSale(); // Salva direto
        return;
    }

    // SE FOR BALCÃO -> ABRE PAGAMENTO
    if (cart.length === 0) { alert('Carrinho vazio!'); return; }

    isClosingTable = false;
    currentPayments = [];
    totalPaid = 0;

    const total = calculateTotal();
    document.getElementById('checkout-total-display').innerText = formatCurrency(total);

    document.getElementById('checkoutModal').style.display = 'flex';
    setMethod('dinheiro');
    updateCheckoutUI();
}

function calculateTotal() {
    let total = 0;
    cart.forEach(item => {
        total += item.price * item.quantity;
    });
    return total;
}

// function formatCurrency removed (duplicate)

// FORMATADOR DE MOEDA (Input: 5050 -> 50,50) - Estilo centavos
function formatMoneyInput(input) {
    let value = input.value.replace(/\D/g, ''); // Remove tudo que não é número
    if (value === '') {
        input.value = '';
        return;
    }
    value = (parseInt(value) / 100).toFixed(2); // Divide por 100
    value = value.replace('.', ','); // Ponto vira vírgula
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.'); // Adiciona pontos de milhar
    input.value = value;
}

// Event listener para input de pagamento
document.addEventListener('DOMContentLoaded', () => {
    const payInput = document.getElementById('pay-amount');
    if (payInput) {
        payInput.type = 'text';
        payInput.addEventListener('input', function () { formatMoneyInput(this); });
        payInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addPayment();
            }
        });
    }
});

// Função para fechar conta da mesa e liberar
function fecharContaMesa(mesaId) {
    // REMOVIDO CONFIRM POR SOLICITAÇÃO DO USUÁRIO

    // PREPARA O CHECKOUT PARA MESA
    isClosingTable = true;
    currentPayments = [];
    totalPaid = 0;

    // Total da mesa (vindo do input hidden)
    const tableTotalStr = document.getElementById('table-initial-total').value;
    const tableTotal = parseFloat(tableTotalStr);

    document.getElementById('checkout-total-display').innerText = formatCurrency(tableTotal);

    // Abre modal
    document.getElementById('checkoutModal').style.display = 'flex';
    setMethod('dinheiro');
    // Força o total total da mesa como base para cálculos (Hack: sobrescreve calculateTotal temporariamente ou usa logica ajustada)
    // Melhor abordagem: Ajustar updateCheckoutUI para usar tableTotal se isClosingTable for true
    updateCheckoutUI();
}

// --- LÓGICA DE CLIENTES ---

// --- LÓGICA DE CLIENTES E MESAS UNIFICADA ---

const clientSearchInput = document.getElementById('client-search');
const clientResults = document.getElementById('client-results');
let searchTimeout = null;

if (clientSearchInput) {
    // 1. FOCUS: MOSTRAR MESAS (Sem digitar nada)
    clientSearchInput.addEventListener('focus', function () {
        if (this.value.trim() === '') {
            fetchTables();
        }
    });

    // 2. INPUT: MOSTRAR CLIENTES (Digitando)
    clientSearchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const term = this.value;

        if (term.length < 2) {
            if (term.length === 0) fetchTables(); // Voltou a ficar vazio -> Mesas
            else clientResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch('clientes/buscar?q=' + term)
                .then(r => r.json())
                .then(data => {
                    renderClientResults(data);
                });
        }, 300);
    });
}

function fetchTables() {
    fetch('mesas/buscar') // Rota nova
        .then(r => r.json())
        .then(data => {
            renderTableResults(data);
        });
}

function renderTableResults(tables) {
    clientResults.innerHTML = '';

    // Grid Container
    const grid = document.createElement('div');
    grid.style.cssText = "display: flex; flex-wrap: wrap; gap: 8px; padding: 10px;";

    if (tables.length > 0) {
        clientResults.style.display = 'block';

        // Header
        const header = document.createElement('div');
        header.innerHTML = '<small style="color:#64748b; font-weight:700; padding:10px 15px 5px; display:block; font-size:0.75rem;">MESAS DISPONÍVEIS</small>';
        clientResults.appendChild(header);

        tables.forEach(table => {
            const isOccupied = table.status === 'ocupada';
            const bg = isOccupied ? '#fef2f2' : '#f0fdf4';
            const border = isOccupied ? '#ef4444' : '#22c55e';
            const text = isOccupied ? '#991b1b' : '#166534';

            const card = document.createElement('div');
            card.className = 'table-card-item'; // Classe para facilitar css externo se quiser
            card.style.cssText = `
                width: 60px; height: 60px; 
                background: ${bg}; 
                border: 2px solid ${border}; 
                border-radius: 12px; 
                display: flex; flex-direction: column; 
                align-items: center; justify-content: center; 
                cursor: pointer; transition: transform 0.1s;
                position: relative;
            `;

            // Número da Mesa
            card.innerHTML = `
                <span style="font-weight:800; font-size:1.1rem; color:${text};">${table.number}</span>
                ${isOccupied ? '<span style="font-size:0.6rem; color:#dc2626; font-weight:bold;">OCP</span>' : ''}
            `;

            // Hover simple
            card.onmouseover = () => card.style.transform = 'scale(1.05)';
            card.onmouseout = () => card.style.transform = 'scale(1)';
            card.onclick = () => selectTable(table);

            grid.appendChild(card);
        });

        clientResults.appendChild(grid);
    } else {
        clientResults.style.display = 'none';
    }
}

function renderClientResults(clients) {
    clientResults.innerHTML = '';
    if (clients.length > 0) {
        clientResults.style.display = 'block';

        const header = document.createElement('div');
        header.innerHTML = '<small style="color:#64748b; font-weight:700; padding:10px 15px; display:block; font-size:0.75rem; border-bottom:1px solid #f1f5f9;">CLIENTES ENCONTRADOS</small>';
        clientResults.appendChild(header);

        clients.forEach(client => {
            const div = document.createElement('div');
            div.style.cssText = "padding: 10px 15px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; align-items: center; gap: 10px;";

            div.innerHTML = `
                <div style="background:#f1f5f9; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <span style="font-weight:bold; color:#64748b;">${client.name.charAt(0).toUpperCase()}</span>
                </div>
                <div>
                    <div style="font-weight:600; font-size:0.9rem; color:#1e293b;">${client.name}</div>
                    ${client.phone ? `<div style="font-size:0.8rem; color:#64748b;">${client.phone}</div>` : ''}
                </div>
            `;

            div.onclick = () => selectClient(client.id, client.name);
            div.onmouseover = () => div.style.background = '#f8fafc';
            div.onmouseout = () => div.style.background = 'white';
            clientResults.appendChild(div);
        });
    } else {
        clientResults.innerHTML = '<div style="padding:15px; color:#64748b; text-align:center;">Nenhum cliente encontrado</div>';
        clientResults.style.display = 'block';
    }
}

// 3. SELECIONAR MESA (Contexto de Mesa)
function selectTable(table) {
    if (table.status === 'ocupada') {
        alert(`🚧 ATENÇÃO: A Mesa ${table.number} já está ocupada!\nVocê está adicionando itens ao pedido existente.`);
    }

    // Set Hidden Inputs
    document.getElementById('current_table_id').value = table.id;
    // Opcional: Limpar cliente se trocar pra mesa?
    document.getElementById('current_client_id').value = '';

    // Visual Update
    document.getElementById('selected-client-name').innerHTML = `🔹 Mesa ${table.number} <small>(${table.status})</small>`;

    document.getElementById('selected-client-area').style.display = 'flex';
    document.getElementById('client-search-area').style.display = 'none';
    document.getElementById('client-results').style.display = 'none';

    // Muda Botão para "Salvar"
    const btn = document.getElementById('btn-finalizar');
    btn.innerText = "Salvar";
    btn.style.backgroundColor = "#d97706"; // Laranja
    btn.disabled = false;
}

// 4. SELECIONAR CLIENTE (Contexto de Balcão)
// 4. SELECIONAR CLIENTE (Contexto de Balcão)
function selectClient(id, name) {
    document.getElementById('current_client_id').value = id;
    document.getElementById('current_table_id').value = ''; // Limpa mesa

    document.getElementById('selected-client-name').innerText = name;

    // Visual Update
    document.getElementById('selected-client-area').style.display = 'flex';
    document.getElementById('client-search-area').style.display = 'none';
    document.getElementById('client-results').style.display = 'none';

    // Muda Botão para Finalizar e Exibe Salvar Comanda
    const btn = document.getElementById('btn-finalizar');
    btn.innerText = "Finalizar";
    btn.style.backgroundColor = "";

    const btnSave = document.getElementById('btn-save-command');
    if (btnSave) btnSave.style.display = 'flex';
}

// 5. Limpar Seleção (X)
// 5. Limpar Seleção (X)
function clearClient() {
    document.getElementById('current_client_id').value = '';
    document.getElementById('current_table_id').value = '';

    // Volta o input
    document.getElementById('selected-client-area').style.display = 'none';
    document.getElementById('client-search-area').style.display = 'flex';
    document.getElementById('client-search').value = '';
    document.getElementById('client-search').focus();

    // Volta botão normal e esconde Salvar Comanda
    const btn = document.getElementById('btn-finalizar');
    const btnSave = document.getElementById('btn-save-command');

    btn.innerText = "Finalizar";
    btn.style.backgroundColor = ""; // Default

    if (btnSave) btnSave.style.display = 'none';
}

// 6. Modal Novo Cliente
function openClientModal() {
    document.getElementById('clientModal').style.display = 'flex';
    document.getElementById('new_client_name').focus();
}

function saveClient() {
    const name = document.getElementById('new_client_name').value;
    const phone = document.getElementById('new_client_phone').value;

    fetch('clientes/salvar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, phone })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('clientModal').style.display = 'none';
                // Já seleciona automático
                selectClient(data.client.id, data.client.name);
                // Limpa form
                document.getElementById('new_client_name').value = '';
                document.getElementById('new_client_phone').value = '';
            } else {
                alert('Erro: ' + data.message);
            }
        });
}

// ... Pay Methods (Mantido igual) ...
// Função para clicar nos botões de pagamento
function setMethod(method) {
    selectedMethod = method;

    // Visual dos botões (Borda azul no selecionado)
    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.style.borderColor = '#e2e8f0';
        btn.style.background = 'white';
        btn.style.color = '#475569';
    });

    const activeBtn = document.getElementById('btn-' + method);
    if (activeBtn) {
        activeBtn.style.borderColor = '#2563eb';
        activeBtn.style.background = '#eff6ff';
        activeBtn.style.color = '#2563eb';
    }

    // AUTO-PREENCHIMENTO INTELIGENTE
    const finalTotal = getFinalTotal();
    const remaining = finalTotal - totalPaid;

    const input = document.getElementById('pay-amount');
    if (remaining > 0) {
        // Formata para o input mask style (ex: 50,00)
        let val = remaining.toFixed(2).replace('.', ',');
        input.value = val;
    } else {
        input.value = '';
    }

    input.focus();
}

// Helper para obter o total correto (Mesa ou Carrinho)
function getFinalTotal() {
    if (isClosingTable || isClosingCommand) {
        const tableTotalStr = document.getElementById('table-initial-total').value;
        return parseFloat(tableTotalStr);
    }
    return calculateTotal();
}

function addPayment() {
    const amountInput = document.getElementById('pay-amount');

    // Parse do valor com máscara (1.234,56 -> 1234.56)
    let rawValue = amountInput.value.replace(/\./g, '').replace(',', '.');
    let amount = parseFloat(rawValue);

    if (!amount || amount <= 0) return;

    const finalTotal = getFinalTotal();
    const remaining = finalTotal - totalPaid;

    // REGRA DE TROCO:
    if (selectedMethod !== 'dinheiro' && amount > remaining) {
        amount = remaining;
        if (amount <= 0.01) {
            alert('Valor restante já foi pago!');
            return;
        }
    }

    // Adiciona na lista
    currentPayments.push({ method: selectedMethod, amount: amount });

    // Limpa input
    amountInput.value = '';

    // Recalcula tudo
    updateCheckoutUI();

    // Foca
    const newTotalPaid = currentPayments.reduce((acc, p) => acc + p.amount, 0);
    if (newTotalPaid >= finalTotal - 0.01) {
        document.getElementById('btn-finish-sale').focus();
    } else {
        // Preenche o restante formatado
        let rest = (finalTotal - newTotalPaid).toFixed(2).replace('.', ',');
        document.getElementById('pay-amount').value = rest;
        amountInput.focus();
    }
}

function removePayment(index) {
    currentPayments.splice(index, 1);
    updateCheckoutUI();
}

function updateCheckoutUI() {
    const finalTotal = getFinalTotal();
    totalPaid = currentPayments.reduce((acc, p) => acc + p.amount, 0);

    // Arredondamento para evitar bugs de 0.0000001
    const remaining = Math.round((finalTotal - totalPaid) * 100) / 100;

    // 1. Atualiza Lista Visual
    const list = document.getElementById('payment-list');
    list.innerHTML = '';

    if (currentPayments.length > 0) {
        list.style.display = 'block';
        currentPayments.forEach((p, index) => {
            const div = document.createElement('div');
            div.style.cssText = "display: flex; justify-content: space-between; padding: 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; align-items: center; margin-bottom: 5px; border-radius: 6px;";

            const methodName = {
                'dinheiro': '💵 Dinheiro',
                'pix': '💠 Pix',
                'credito': '💳 Crédito',
                'debito': '💳 Débito'
            }[p.method] || p.method;

            div.innerHTML = `
                <span style="font-weight:600; color:#334155;">${methodName}</span>
                <div style="display:flex; align-items:center; gap:10px;">
                    <strong>R$ ${p.amount.toFixed(2)}</strong>
                    <button onclick="removePayment(${index})" style="color:#ef4444; border:none; background:#fee2e2; width:24px; height:24px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
                </div>
            `;
            list.appendChild(div);
        });
    } else {
        list.style.display = 'none';
    }

    // 2. Atualiza Restante e Botão Concluir
    const remainingEl = document.getElementById('checkout-remaining');
    const changeBox = document.getElementById('change-box');
    const changeEl = document.getElementById('checkout-change');
    const btnFinish = document.getElementById('btn-finish-sale');
    const remainingBox = document.getElementById('remaining-box');

    // Lógica do Botão Concluir (Margem de erro de 1 centavo)
    if (remaining > 0.01) {
        // FALTA PAGAR
        remainingBox.style.display = 'block';
        changeBox.style.display = 'none';

        remainingEl.innerText = 'R$ ' + remaining.toFixed(2).replace('.', ',');

        btnFinish.disabled = true;
        btnFinish.style.background = '#cbd5e1';
        btnFinish.style.cursor = 'not-allowed';
    } else {
        // PAGO (OU TEM TROCO)
        btnFinish.disabled = false;
        btnFinish.style.background = '#16a34a'; // Verde Sucesso
        btnFinish.style.cursor = 'pointer';

        if (remaining < -0.01) {
            // TEM TROCO
            remainingBox.style.display = 'none';
            changeBox.style.display = 'flex';
            changeEl.innerText = 'R$ ' + Math.abs(remaining).toFixed(2).replace('.', ',');
        } else {
            // CONTA EXATA
            remainingBox.style.display = 'block';
            remainingEl.innerText = 'R$ 0,00';
            remainingEl.style.color = '#166534';
            changeBox.style.display = 'none';
        }
    }
}

function closeCheckout() {
    document.getElementById('checkoutModal').style.display = 'none';
}

// --- SALVAR COMANDA (Cliente) ---
function saveClientOrder() {
    if (cart.length === 0) return alert('O carrinho está vazio!');
    const clientId = document.getElementById('current_client_id').value;
    const orderId = document.getElementById('current_order_id') ? document.getElementById('current_order_id').value : null;

    if (!clientId) return alert('Nenhum cliente selecionado!');

    fetch('venda/finalizar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cart: cart,
            client_id: clientId,
            order_id: orderId, // Envia ID para atualizar
            save_account: true // FLAG IMPORTANTE
        })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Sucesso!
                const modal = document.getElementById('successModal');
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.style.display = 'none';
                    window.location.reload();
                }, 1000);
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(err => alert('Erro de conexão.'));
}

// Função para fechar conta da mesa e liberar
function fecharContaMesa(mesaId) {
    // REMOVIDO CONFIRM POR SOLICITAÇÃO DO USUÁRIO
    // if (!confirm('Tem certeza que deseja fechar a conta da mesa?')) return;

    // PREPARA O CHECKOUT PARA MESA
    isClosingTable = true;
    currentPayments = [];
    totalPaid = 0;

    // Pega total da mesa (input hidden na view)
    const totalStr = document.getElementById('table-initial-total').value;
    const total = parseFloat(totalStr);

    document.getElementById('checkout-total-display').innerText = formatCurrency(total);
    document.getElementById('checkoutModal').style.display = 'flex';
    setMethod('dinheiro');
    updateCheckoutUI();
}

// FORMATA MOEDA
function formatCurrency(value) {
    return parseFloat(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

// ATUALIZA UI DO CHECKOUT (CALCULA RESTANTE/TROCO)
function updateCheckoutUI() {
    let total = 0;

    if (isClosingTable || isClosingCommand) {
        const val = document.getElementById('table-initial-total').value;
        total = parseFloat(val) || 0.00; // Fallback to 0 if NaN/Empty
    } else {
        total = cart.reduce((acc, item) => acc + (parseFloat(item.price) * parseFloat(item.quantity)), 0);
    }

    const remaining = total - totalPaid;
    const btnFinish = document.getElementById('btn-finish-sale');

    // Atualiza textos
    document.getElementById('checkout-remaining').innerText = formatCurrency(Math.max(0, remaining));
    document.getElementById('checkout-total-display').innerText = formatCurrency(total);

    // Lógica de Troco (com tolerância para floats)
    const changeBox = document.getElementById('change-box');
    const epsilon = 0.009; // Tolerância para arredondamento

    if (remaining < -epsilon) {
        // Tem troco (pagou mais que o total)
        changeBox.style.display = 'block';
        document.getElementById('checkout-change').innerText = formatCurrency(Math.abs(remaining));

        // Pode finalizar
        btnFinish.disabled = false;
        btnFinish.style.background = '#22c55e'; // Verde
        btnFinish.style.cursor = 'pointer';
    } else if (Math.abs(remaining) <= epsilon) {
        // Pagou exato (ou diferença insignificante)
        changeBox.style.display = 'none';
        btnFinish.disabled = false;
        btnFinish.style.background = '#22c55e';
        btnFinish.style.cursor = 'pointer';
    } else {
        // Falta pagar
        changeBox.style.display = 'none';
        btnFinish.disabled = true;
        btnFinish.style.background = '#cbd5e1';
        btnFinish.style.cursor = 'not-allowed';
    }

    // Atualiza input com o restante (sugestão)
    const payInput = document.getElementById('pay-amount');
    if (remaining > 0) {
        payInput.value = remaining.toFixed(2);
    } else {
        payInput.value = '';
    }
}

// Função para FECHAR COMANDA (Sem Mesa)
function fecharComanda(orderId) {
    const isPaid = document.getElementById('current_order_is_paid') ? document.getElementById('current_order_is_paid').value == '1' : false;

    if (isPaid) {
        // JÁ PAGO -> FINALIZAR / ENTREGAR
        if (!confirm('Este pedido já está PAGO. Deseja entregá-lo e finalizar?')) return;

        fetch('venda/fechar-comanda', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_id: orderId,
                payments: [], // Sem pagamentos extras
                keep_open: false // Finaliza de vez
            })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Pedido entregue com sucesso! ✅');
                    window.location.href = BASE_URL + '/admin/loja/mesas';
                } else {
                    alert('Erro: ' + data.message);
                }
            });
        return;
    }

    // AINDA NÃO PAGO -> CHECKOUT
    isClosingCommand = true;
    closingOrderId = orderId;

    currentPayments = [];
    totalPaid = 0;

    // Total da comanda (mesmo input hidden da mesa, pois a view usa o mesmo layout)
    const totalStr = document.getElementById('table-initial-total').value;
    const total = parseFloat(totalStr);

    document.getElementById('checkout-total-display').innerText = formatCurrency(total);

    // Reset Order Type to Local (Default)
    const cards = document.querySelectorAll('.order-type-card');
    if (cards.length > 0) selectOrderType('local', cards[0]);

    document.getElementById('checkoutModal').style.display = 'flex';
    setMethod('dinheiro');
    updateCheckoutUI();
}

// --- ENVIA A VENDA PRO PHP ---
// Função para limpar carrinho (Lixeira)
function clearCart() {
    if (cart.length === 0) return;

    // Se estiver em mesa, só limpa o carrinho local (novos itens), não mexe nos salvos
    // Se quiser "Cancelar Pedido da Mesa", seria outra função

    if (confirm('Deseja esvaziar o carrinho atual?')) {
        cart = [];
        updateCartUI();
    }
}

// Função para remover item SALVO da mesa
function deleteSavedItem(itemId, orderId) {
    // REMOVIDO CONFIRM POR SOLICITAÇÃO DO USUÁRIO
    // if (!confirm('Tem certeza?')) return;

    fetch((typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/venda/remover-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: itemId, order_id: orderId })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSuccessModal('Item removido!');
                // Recarrega mantendo a URL atual (que já pode ter ?mesa_id=X)
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert('Erro ao remover: ' + data.message);
            }
        })
        .catch(err => alert('Erro de conexão: ' + err.message));
}

// MASCARA DE TELEFONE (Novo Cliente)
const phoneInput = document.getElementById('new_client_phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function (e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
        e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
    });
}

// FECHAR BUSCA AO CLICAR FORA
document.addEventListener('click', function (event) {
    const searchArea = document.getElementById('client-search-area');
    const results = document.getElementById('client-results');

    if (searchArea && results && results.style.display === 'block') {
        if (!searchArea.contains(event.target)) {
            results.style.display = 'none';
        }
    }
});

// Função para CANCELAR PEDIDO DA MESA (Apagar tudo)
function cancelTableOrder(tableId, orderId) {
    if (!confirm('ATENÇÃO: Deseja CANCELAR todo o pedido desta mesa?\n\nIsso apagará todos os itens salvos e liberará a mesa.\nO estoque será devolvido.')) return;

    fetch((typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/mesa/cancelar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ table_id: tableId, order_id: orderId })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSuccessModal('Pedido Cancelado!');
                setTimeout(() => window.location.href = 'mesas', 1000); // Volta para o mapa de mesas
            } else {
                alert('Erro ao cancelar: ' + data.message);
            }
        })
        .catch(err => alert('Erro de conexão logic: ' + err.message));
}

// Modal de Sucesso (Visual)
function showSuccessModal(msg = 'Sucesso!') {
    const modal = document.getElementById('successModal');
    if (!modal) return;

    // Opcional: mudar texto
    // modal.querySelector('h2').innerText = msg;

    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 1500);
}

// --- ENVIA A VENDA PRO PHP ---
// --- CONTROLE DE PAGAMENTOS ---
// selectedMethod já declarado lá em cima


function setMethod(method) {
    selectedMethod = method;

    // Atualiza botões visuais
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.borderColor = '#cbd5e1';
        btn.style.background = 'white';
        // Reset icon color
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

    // Foca no input de valor
    setTimeout(() => document.getElementById('pay-amount').focus(), 100);
}

function addPayment() {
    const amountInput = document.getElementById('pay-amount');
    let valStr = amountInput.value.trim();

    // Detecta formato brasileiro: se tem vírgula, assume pt-BR
    // Ex: "1.234,56" -> 1234.56 | "67,00" -> 67.00 | "67" -> 67
    if (valStr.includes(',')) {
        // Formato brasileiro: remove pontos de milhar, troca vírgula por ponto
        valStr = valStr.replace(/\./g, '').replace(',', '.');
    }
    // Se não tem vírgula, assume que é número simples (67 ou 67.00)

    let amount = parseFloat(valStr);

    if (!amount || amount <= 0 || isNaN(amount)) {
        alert('Digite um valor válido.');
        return;
    }

    // Adiciona ao array
    currentPayments.push({
        method: selectedMethod,
        amount: amount,
        label: formatMethodLabel(selectedMethod)
    });

    // Atualiza Total Pago
    totalPaid += amount;

    // Limpa input
    amountInput.value = '';

    // Atualiza UI
    updatePaymentList();
    updateCheckoutUI();
}

function removePayment(index) {
    // Remove do array
    const removed = currentPayments.splice(index, 1)[0];
    totalPaid -= removed.amount;

    updatePaymentList();
    updateCheckoutUI();
}

function updatePaymentList() {
    const listEl = document.getElementById('payment-list');
    listEl.innerHTML = '';

    if (currentPayments.length === 0) {
        listEl.style.display = 'none';
        return;
    }

    listEl.style.display = 'block';

    currentPayments.forEach((pay, index) => {
        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.justifyContent = 'space-between';
        row.style.alignItems = 'center';
        row.style.padding = '8px 0';
        row.style.borderBottom = '1px solid #e2e8f0';

        row.innerHTML = `
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-weight:600; font-size:0.9rem; color:#1e293b;">${pay.label}</span>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-weight:700; color:#1e293b;">${formatCurrency(pay.amount)}</span>
                <button onclick="removePayment(${index})" style="background:none; border:none; cursor:pointer; color:#ef4444;">
                    <i data-lucide="trash-2" size="16"></i>
                </button>
            </div>
        `;
        listEl.appendChild(row);
    });

    // Re-render icons
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function formatMethodLabel(method) {
    const map = {
        'dinheiro': 'Dinheiro',
        'pix': 'Pix',
        'credito': 'Cartão Crédito',
        'debito': 'Cartão Débito'
    };
    return map[method] || method;
}

function closeCheckout() {
    document.getElementById('checkoutModal').style.display = 'none';
    currentPayments = [];
    totalPaid = 0;
}

// SELEÇÃO DE TIPO DE PEDIDO (NOVO MODAL)
function selectOrderType(type, element) {
    if (element.classList.contains('disabled')) return;

    // Reset visual
    document.querySelectorAll('.order-type-card').forEach(el => {
        el.className = 'order-type-card';
        el.style.border = '1px solid #cbd5e1';
        el.style.background = 'white';
        // Reset icon color
        const icon = el.querySelector('svg');
        if (icon) icon.style.color = '#64748b';
    });

    // Set Active
    element.className = 'order-type-card active';
    element.style.border = '2px solid #2563eb';
    element.style.background = '#eff6ff';
    const icon = element.querySelector('svg');
    if (icon) icon.style.color = '#2563eb';

    // Set Logic
    const keepOpenInput = document.getElementById('keep_open_value');
    if (type === 'retirada') {
        keepOpenInput.value = 'true';
    } else {
        keepOpenInput.value = 'false';
    }
}

// --- ENVIA A VENDA PRO PHP ---
function submitSale() {
    const tableId = document.getElementById('current_table_id').value;
    const clientId = document.getElementById('current_client_id').value;
    const searchInput = document.getElementById('client-search');
    // Checa se existe para evitar erro em modo Mesa (onde ele não existe)
    if (searchInput) {
        const searchVal = searchInput.value.trim();
        // VALIDAÇÃO: SE TEM TEXTO NA BUSCA MAS NÃO SELECIONOU ID
        if (searchVal !== '' && !tableId && !clientId) {
            alert('⚠️ ATENÇÃO: Você digitou algo no campo de busca/mesa mas não selecionou nenhum resultado.\n\nPor favor, clique numa das opções da lista para confirmar a Mesa ou Cliente, ou limpe o campo.');
            searchInput.focus();
            return;
        }
    }

    // DEFINIR URL (Venda normal ou Fechamento de mesa?)
    // Usa BASE_URL global definida no dashboard.php
    let endpoint = '/admin/loja/venda/finalizar';

    // Ler valor do card selecionado
    const keepOpenStr = document.getElementById('keep_open_value') ? document.getElementById('keep_open_value').value : 'false';
    const keepOpen = keepOpenStr === 'true';

    const payload = {
        cart: cart,
        table_id: tableId ? parseInt(tableId) : null,
        client_id: clientId ? parseInt(clientId) : null,
        payments: currentPayments,
        keep_open: keepOpen
    };

    if (isClosingTable) {
        endpoint = '/admin/loja/mesa/fechar';
    } else if (isClosingCommand) {
        endpoint = '/admin/loja/venda/fechar-comanda'; // Nova Rota
        // Adiciona Order ID no payload
        payload.order_id = closingOrderId;
    }

    const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + endpoint;

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(r => r.text()) // Use text() primeiro para debug de erros PHP (HTML)
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Erro ao parsear resposta:', text);
                throw new Error('Resposta inválida do servidor: ' + text.substring(0, 50) + '...');
            }
        })
        .then(data => {
            if (data.success) {
                showSuccessModal();

                cart = [];
                currentPayments = [];
                updateCartUI();
                closeCheckout();

                // Delay para mostrar o modal antes de recarregar
                setTimeout(() => {
                    // Se estava em MESA ou COMANDA -> Recarrega ou Vai para Mesas
                    if (tableId || isClosingCommand) {
                        // Se for Retirada (keep_open), vai para Mesas para ver o pedido "Pago"
                        // Se for normal, também vai para Mesas (liberou a mesa/comanda)
                        window.location.href = BASE_URL + '/admin/loja/mesas';
                    } else {
                        // Se é balcão puro, só limpa
                        if (typeof clearClient === 'function') clearClient();
                    }
                }, 1500);

            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro ao processar venda: ' + error.message);
        });
}
