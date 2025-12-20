// Variável global que guarda os itens
let cart = [];

// Verifica se estamos numa mesa assim que carrega
document.addEventListener('DOMContentLoaded', () => {
    const tableIdInput = document.getElementById('current_table_id');
    const tableId = tableIdInput ? tableIdInput.value : null;
    const btn = document.getElementById('btn-finalizar');

    if (tableId) {
        btn.innerText = "Salvar na Mesa"; // Muda o texto visualmente
        btn.style.backgroundColor = "#d97706"; // Um laranja para diferenciar
    }

    // Atualiza a UI inicial (para mostrar o TOTAL da mesa se houver)
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

function finalizeSale() {
    const btn = document.getElementById('btn-finalizar');
    const tableIdInput = document.getElementById('current_table_id');
    const tableId = tableIdInput ? tableIdInput.value : null; // Pega o ID da mesa, se houver

    btn.disabled = true;
    btn.innerText = "Processando...";

    // Prepara o pacote para enviar
    const payload = {
        cart: cart,
        table_id: tableId ? parseInt(tableId) : null // Manda o ID se existir
    };

    fetch('venda/finalizar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (tableId) {

                    window.location.reload(); // Recarrega para atualizar a lista "Já na Mesa"
                } else {
                    alert('Venda realizada! 💰✅');
                    cart = [];
                    updateCartUI();
                }
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error(error);
            alert('Erro de conexão.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = tableId ? "Salvar na Mesa" : "Finalizar Venda";
        });
}

// Função para fechar conta da mesa e liberar
function fecharContaMesa(mesaId) {
    if (!confirm('Tem certeza que deseja fechar a conta e liberar a mesa?')) return;

    fetch('mesa/fechar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ table_id: mesaId })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {

                window.location.href = 'mesas'; // Volta para o mapa
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro de conexão ao fechar conta.');
        });
}

