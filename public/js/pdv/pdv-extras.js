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
                    <button onclick="PDVExtras.confirm()" style="background: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; border: none;">Adicionar sem extras</button>
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
