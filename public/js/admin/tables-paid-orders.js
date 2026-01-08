/**
 * TABLES-PAID-ORDERS.JS - Pedidos Pagos (Retirada)
 * Módulo: TablesAdmin.PaidOrders
 * Refatorado para usar BASE_URL
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    // Helper URL
    const getBaseUrl = () => typeof BASE_URL !== 'undefined' ? BASE_URL : '/cardapio-saas/public';

    let currentPaidOrderId = null;
    let currentPaidClientId = null;

    TablesAdmin.PaidOrders = {

        showOptions: function (orderId, clientName, total, clientId) {
            currentPaidOrderId = orderId;
            currentPaidClientId = clientId;

            const nameEl = document.getElementById('paid-order-client-name');
            const totalEl = document.getElementById('paid-order-total');
            const modal = document.getElementById('paidOrderModal');

            if (nameEl) nameEl.innerText = clientName;
            if (totalEl) totalEl.innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
            if (modal) modal.style.display = 'flex';

            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        closeModal: function () {
            const modal = document.getElementById('paidOrderModal');
            if (modal) modal.style.display = 'none';
            currentPaidOrderId = null;
        },

        deliver: function () {
            if (!currentPaidOrderId) return;

            fetch(getBaseUrl() + '/admin/loja/pedidos/entregar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: currentPaidOrderId })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.closeModal();
                        window.location.reload();
                    } else {
                        alert('Erro: ' + (data.message || 'Falha ao entregar'));
                    }
                })
                .catch(err => alert('Erro na conexão: ' + err.message));
        },

        edit: function () {
            if (!currentPaidOrderId) return;
            window.location.href = getBaseUrl() + '/admin/loja/pdv?order_id=' + currentPaidOrderId + '&edit_paid=1';
        },

        getCurrentOrderId: () => currentPaidOrderId,
        getCurrentClientId: () => currentPaidClientId
    };

    console.log('[TablesAdmin.PaidOrders] Módulo carregado');
})();
