/**
 * CASHIER.JS - Gerenciamento do Caixa/Financeiro
 * Namespace: CashierAdmin
 * Refatorado para usar BASE_URL
 */

(function () {
    'use strict';

    // Helper URL
    const getBaseUrl = () => typeof BASE_URL !== 'undefined' ? BASE_URL : '/cardapio-saas/public';

    const CashierAdmin = {

        init: function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            console.log('[CashierAdmin] Initialized');
        },

        // ==========================================
        // MODAL DE MOVIMENTAÇÃO (SANGRIA/SUPRIMENTO)
        // ==========================================

        Movimento: {
            open: function (type) {
                const modal = document.getElementById('modalMovimento');
                if (!modal) return;

                const title = document.getElementById('modalTitle');
                const inputType = document.getElementById('movType');

                inputType.value = type;

                if (type === 'sangria') {
                    title.innerText = "Retirar Valor (Saída)";
                    title.style.color = "#b91c1c";
                } else {
                    title.innerText = "Adicionar Dinheiro (Entrada)";
                    title.style.color = "#1d4ed8";
                }

                modal.style.display = 'flex';
                // Focus no input de valor
                setTimeout(() => {
                    const valInput = document.getElementById('movValue');
                    if (valInput) valInput.focus();
                }, 50);
            },

            close: function () {
                const modal = document.getElementById('modalMovimento');
                if (modal) modal.style.display = 'none';
            }
        },

        // ==========================================
        // MODAL DE DETALHES DO PEDIDO (COMANDA)
        // ==========================================

        OrderDetails: {
            open: function (orderId, total, date) {
                const modal = document.getElementById('orderDetailsModal');
                if (!modal) return;

                const list = document.getElementById('modalItemsList');
                const dateEl = document.getElementById('receiptDate');
                const totalEl = document.getElementById('receiptTotal');

                if (dateEl) dateEl.innerText = 'PEDIDO #' + orderId + ' • ' + date;
                if (totalEl) totalEl.innerText = 'R$ ' + total;

                modal.style.display = 'flex';
                list.innerHTML = '<p style="text-align:center; padding: 20px 0;">Impressora conectando...</p>';

                fetch(getBaseUrl() + '/admin/loja/vendas/itens?id=' + orderId)
                    .then(response => response.json())
                    .then(data => this._renderItems(data, list))
                    .catch(err => {
                        console.error(err);
                        list.innerHTML = '<p style="color:red; text-align:center;">Erro de conexão.</p>';
                    });
            },

            close: function () {
                const modal = document.getElementById('orderDetailsModal');
                if (modal) modal.style.display = 'none';
            },

            _renderItems: function (data, list) {
                if (data.length === 0) {
                    list.innerHTML = '<p style="text-align:center;">Sem itens.</p>';
                    return;
                }

                const rows = data.map(item => {
                    let unitPrice = parseFloat(item.price);
                    let subTotal = unitPrice * item.quantity;
                    return `
                        <tr>
                            <td style="padding: 4px 0; vertical-align: top;">
                                <div style="font-weight:bold;">${item.quantity}x ${item.name}</div>
                                <div style="font-size:0.7rem; color:#666;">Unit: R$ ${unitPrice.toFixed(2).replace('.', ',')}</div>
                            </td>
                            <td style="padding: 4px 0; text-align: right; vertical-align: top; font-weight:bold;">
                                R$ ${subTotal.toFixed(2).replace('.', ',')}
                            </td>
                        </tr>
                    `;
                }).join('');

                const html = `
                    <table style="width: 100%; font-size: 0.85rem; border-collapse: collapse;">
                        ${rows}
                    </table>
                    <div style="margin-top:10px; border-top:1px solid #000; padding-top:5px; font-size:0.7rem; text-align:center;">*** FIM DO COMPROVANTE ***</div>
                `;

                list.innerHTML = html;
            }
        }
    };

    // ==========================================
    // EXPORTAR GLOBALMENTE
    // ==========================================
    window.CashierAdmin = CashierAdmin;

    // Aliases
    window.openModal = (type) => CashierAdmin.Movimento.open(type);
    window.openOrderDetails = (id, total, date) => CashierAdmin.OrderDetails.open(id, total, date);

    // Auto-init
    document.addEventListener('DOMContentLoaded', () => CashierAdmin.init());

})();
