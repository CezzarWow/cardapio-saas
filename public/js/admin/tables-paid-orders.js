/**
 * TABLES-PAID-ORDERS.JS - Pedidos Pagos (Retirada)
 * Módulo: TablesAdmin.PaidOrders
 */

(function () {
    'use strict';

    // Garante namespace
    window.TablesAdmin = window.TablesAdmin || {};

    // Estado privado
    let currentPaidOrderId = null;
    let currentPaidClientId = null;

    TablesAdmin.PaidOrders = {

        showOptions: function (orderId, clientName, total, clientId) {
            currentPaidOrderId = orderId;
            currentPaidClientId = clientId;

            document.getElementById('paid-order-client-name').innerText = clientName;
            document.getElementById('paid-order-total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
            document.getElementById('paidOrderModal').style.display = 'flex';

            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        closeModal: function () {
            document.getElementById('paidOrderModal').style.display = 'none';
            currentPaidOrderId = null;
        },

        deliver: function () {
            if (!currentPaidOrderId) return;

            fetch(BASE_URL + '/admin/loja/pedidos/entregar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: currentPaidOrderId })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.closeModal();
                        location.reload();
                    } else {
                        alert('Erro: ' + (data.message || 'Falha ao entregar'));
                    }
                })
                .catch(err => alert('Erro: ' + err.message));
        },

        edit: function () {
            if (!currentPaidOrderId) return;
            window.location.href = BASE_URL + '/admin/loja/pdv?order_id=' + currentPaidOrderId + '&edit_paid=1';
        },

        // Getters para acesso externo se necessário
        getCurrentOrderId: () => currentPaidOrderId,
        getCurrentClientId: () => currentPaidClientId
    };

    console.log('[TablesAdmin.PaidOrders] Módulo carregado');
})();
