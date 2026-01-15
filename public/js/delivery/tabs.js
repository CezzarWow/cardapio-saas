/**
 * ============================================
 * DELIVERY JS — Tabs (Filtros instantâneos)
 * Mostra/esconde colunas sem reload
 * ============================================
 */

const DeliveryTabs = {

    currentFilter: 'todos',

    /**
     * Filtra por status (instantâneo)
     */
    filter: function (status) {
        this.currentFilter = status;

        const columns = document.querySelectorAll('.delivery-column');
        const buttons = document.querySelectorAll('.delivery-filter-btn');

        // Atualiza botões
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.status === status) {
                btn.classList.add('active');
            }
        });

        // Mostra/esconde colunas
        columns.forEach(col => {
            if (status === 'todos') {
                // Se 'todos', mostra tudo MENOS cancelado
                if (col.classList.contains('delivery-column--cancelado')) {
                    col.style.display = 'none';
                } else {
                    col.style.display = 'flex';
                }
            } else {
                // Se status específico (incluindo 'cancelado'), mostra só ele
                if (col.classList.contains('delivery-column--' + status)) {
                    col.style.display = 'flex';
                } else {
                    col.style.display = 'none';
                }
            }
        });

        // Atualiza contador
        this.updateCounter();
    },

    /**
     * Atualiza contador de pedidos visíveis
     */
    updateCounter: function () {
        const visibleCards = document.querySelectorAll('.delivery-column[style*="flex"] .delivery-card-compact');
        const counter = document.getElementById('delivery-count');
        if (counter) counter.textContent = visibleCards.length;
    }
};

// Expõe globalmente
window.DeliveryTabs = DeliveryTabs;
