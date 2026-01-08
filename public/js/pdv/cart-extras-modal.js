/**
 * CART-EXTRAS-MODAL.JS - Modal de Adicionais
 * Dependências: PDVCart (cart.js + cart-core.js)
 * 
 * Este módulo gerencia o modal de seleção de extras/adicionais.
 */

(function () {
    'use strict';

    // ==========================================
    // ESTADO DO MODAL
    // ==========================================
    window.pendingProduct = null;
    window.extrasQty = 1;

    // ==========================================
    // ABRIR MODAL DE EXTRAS
    // ==========================================
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

    // ==========================================
    // FECHAR MODAL
    // ==========================================
    window.closeExtrasModal = function () {
        const modal = document.getElementById('extrasModal');
        if (modal) modal.style.display = 'none';
        pendingProduct = null;
        // Limpa checkboxes
        const content = document.getElementById('extras-modal-content');
        if (content) content.innerHTML = '';
    };

    // ==========================================
    // RENDERIZAR EXTRAS
    // ==========================================
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

    // ==========================================
    // CONFIRMAR EXTRAS
    // ==========================================
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

        // Usa a quantidade selecionada no modal
        PDVCart.add(pendingProduct.id, pendingProduct.name, totalPrice, extrasQty, selectedExtras);
        closeExtrasModal();
    };

    // ==========================================
    // CONTROLE DE QUANTIDADE
    // ==========================================
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

})();
