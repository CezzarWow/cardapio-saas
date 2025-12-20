// Variável global que guarda os itens
let cart = [];

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

    // Atualiza o Total formatado em Reais
    totalElement.innerText = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
