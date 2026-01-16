/**
 * TABLES-PAID-ORDERS.JS - Pedidos Pagos (Retirada)
 * Módulo: TablesAdmin.PaidOrders
 * 
 * Dependência: tables-helpers.js (carregar antes)
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

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
            if (totalEl) totalEl.innerText = TablesHelpers.formatCurrency(total);
            if (modal) {
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        closeModal: function () {
            const modal = document.getElementById('paidOrderModal');
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
            currentPaidOrderId = null;
        },

        deliver: function () {
            if (!currentPaidOrderId) return;

            fetch(TablesHelpers.getBaseUrl() + '/admin/loja/pedidos/entregar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': TablesHelpers.getCsrf()
                },
                body: JSON.stringify({ order_id: currentPaidOrderId })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.closeModal();
                        // Recarrega seção via SPA
                        if (typeof AdminSPA !== 'undefined') {
                            AdminSPA.reloadCurrentSection();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('Erro: ' + (data.message || 'Falha ao entregar'));
                    }
                })
                .catch(err => alert('Erro na conexão: ' + err.message));
        },

        edit: function () {
            if (!currentPaidOrderId) return;
            // Navega para PDV com edit_paid via SPA
            if (typeof AdminSPA !== 'undefined') {
                AdminSPA.navigateTo('balcao', true, true, {
                    order_id: currentPaidOrderId,
                    edit_paid: 1
                });
            } else {
                window.location.href = TablesHelpers.getBaseUrl() + '/admin/loja/pdv?order_id=' + currentPaidOrderId + '&edit_paid=1';
            }
        },

        getCurrentOrderId: () => currentPaidOrderId,
        getCurrentClientId: () => currentPaidClientId
    };

})();
