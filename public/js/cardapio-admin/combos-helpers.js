/**
 * COMBOS-HELPERS.JS - Funções Auxiliares de Combos
 * 
 * Gerencia funções auxiliares: clear, summary, toggle.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Limpa todos os itens selecionados do combo
     */
    CardapioAdmin.clearComboItems = function () {
        document.querySelectorAll('.combo-product-qty').forEach(input => {
            input.value = 0;
            const pid = input.getAttribute('data-id');
            const display = document.getElementById('display_qty_' + pid);
            const card = document.getElementById('card_prod_' + pid);
            if (display) display.textContent = '0';
            if (card) card.classList.remove('selected');
        });

        // Atualizar preço original
        if (typeof calculateComboOriginalPrice === 'function') {
            calculateComboOriginalPrice();
        }

        // Limpar lista resumo
        const list = document.getElementById('comboItemsList');
        if (list) list.innerHTML = '';
    };

    /**
     * Atualiza a lista visual de itens do combo
     * @param {Array} items Lista de itens com {id, name, qty}
     */
    CardapioAdmin.updateComboItemsSummary = function (items) {
        const list = document.getElementById('comboItemsList');
        if (!list) return;

        list.innerHTML = '';

        items.forEach(item => {
            if (item.qty > 0) {
                const li = document.createElement('li');
                li.style.cssText = 'background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 0.85rem; font-weight: 500;';
                li.textContent = `${item.qty}x ${item.name}`;
                list.appendChild(li);
            }
        });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});

// ==========================================
// FUNÇÃO GLOBAL - Toggle de Combo
// ==========================================
window.toggleComboActive = function (id, isActive) {
    fetch('admin/loja/cardapio/combo/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            active: isActive
        })
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Erro ao atualizar status: ' + (data.error || 'Desconhecido'));
                const checkbox = document.querySelector(`input[onchange*="${id}"]`);
                if (checkbox) checkbox.checked = !isActive;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro de conexão');
            const checkbox = document.querySelector(`input[onchange*="${id}"]`);
            if (checkbox) checkbox.checked = !isActive;
        });
};
