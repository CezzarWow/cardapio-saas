/**
 * CART-UI.JS - Interface do Carrinho
 * Dependências: PDVCart (cart.js + cart-core.js)
 * 
 * Este módulo estende PDVCart com as funções de renderização.
 */

(function () {
    'use strict';

    // ==========================================
    // UPDATE UI - Renderiza o carrinho na tela
    // ==========================================
    PDVCart.updateUI = function () {
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
    };

    // ==========================================
    // FORMAT MONEY - Helper de formatação
    // ==========================================
    PDVCart.formatMoney = function (value, withSymbol = false) {
        const formatted = value.toFixed(2).replace('.', ',');
        return withSymbol ? `R$ ${formatted}` : formatted;
    };

})();
