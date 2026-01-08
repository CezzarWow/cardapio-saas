/**
 * ADDITIONALS-UI.JS - Interface e Helpers
 * Dependências: additionals.js
 * 
 * Este módulo gerencia:
 * - Abas de visualização (grupos/itens)
 * - Busca/filtro
 * - Helpers (formatação, toggle)
 */

(function () {
    'use strict';

    // ==========================================
    // ESTADO
    // ==========================================
    window.currentAdditionalView = 'groups';

    // ==========================================
    // ALTERNAR VISUALIZAÇÃO (ABAS)
    // ==========================================
    window.setAdditionalView = function (view, btn) {
        currentAdditionalView = view;
        const viewGroups = document.getElementById('view-groups');
        const viewItems = document.getElementById('view-items');
        const buttons = document.querySelectorAll('.stock-view-btn');

        // Atualizar classe ativa dos botões
        buttons.forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        // Alternar containers
        if (view === 'groups') {
            viewGroups.style.display = 'block';
            viewItems.style.display = 'none';
        } else {
            viewGroups.style.display = 'none';
            viewItems.style.display = 'block';
        }

        // Reaplicar busca se houver
        const searchVal = document.getElementById('searchInput').value;
        handleSearch(searchVal);
    };

    // ==========================================
    // BUSCA/FILTRO
    // ==========================================
    window.handleSearch = function (query) {
        const q = query.toLowerCase().trim();

        if (currentAdditionalView === 'groups') {
            // Filtrar Grupos
            const cards = document.querySelectorAll('.group-card');
            cards.forEach(card => {
                const name = card.dataset.name || '';
                card.style.display = name.includes(q) ? 'block' : 'none';
            });
        } else {
            // Filtrar Itens
            const items = document.querySelectorAll('.item-card-row');
            items.forEach(item => {
                const name = item.dataset.name || '';
                item.style.display = name.includes(q) ? 'flex' : 'none';
            });
        }
    };

    // ==========================================
    // HELPERS
    // ==========================================

    // Máscara de Moeda (Direita para Esquerda)
    window.formatCurrency = function (input) {
        let value = input.value.replace(/\D/g, ""); // Remove não dígitos
        value = (Number(value) / 100).toLocaleString("pt-BR", { minimumFractionDigits: 2 });
        input.value = value;
    };

    // Toggle Item Grátis
    window.toggleItemFree = function (checkbox) {
        const input = document.getElementById('itemPriceInput');
        if (checkbox.checked) {
            input.value = '0,00';
            input.disabled = true;
            input.style.background = '#f3f4f6';
        } else {
            input.value = '';
            input.disabled = false;
            input.style.background = 'white';
            input.focus();
        }
    };

})();
