/**
 * CASHIER.JS - Gerenciamento do Caixa/Financeiro
 * Namespace: CashierAdmin
 * 
 * Dependências: BASE_URL (definida no PHP antes deste script)
 */

const CashierAdmin = {

    // ==========================================
    // MODAL DE MOVIMENTAÇÃO (SANGRIA/SUPRIMENTO)
    // ==========================================

    Movimento: {
        open: function (type) {
            const modal = document.getElementById('modalMovimento');
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
        },

        close: function () {
            document.getElementById('modalMovimento').style.display = 'none';
        }
    },

    // ==========================================
    // MODAL DE DETALHES DO PEDIDO (COMANDA)
    // ==========================================

    OrderDetails: {
        open: function (orderId, total, date) {
            const modal = document.getElementById('orderDetailsModal');
            const list = document.getElementById('modalItemsList');
            const dateEl = document.getElementById('receiptDate');
            const totalEl = document.getElementById('receiptTotal');

            dateEl.innerText = 'PEDIDO #' + orderId + ' • ' + date;
            totalEl.innerText = 'R$ ' + total;

            modal.style.display = 'flex';
            list.innerHTML = '<p style="text-align:center; padding: 20px 0;">Impressora conectando...</p>';

            fetch(BASE_URL + '/admin/loja/vendas/itens?id=' + orderId)
                .then(response => response.json())
                .then(data => this._renderItems(data, list))
                .catch(err => {
                    console.error(err);
                    list.innerHTML = '<p style="color:red; text-align:center;">Erro de conexão.</p>';
                });
        },

        close: function () {
            document.getElementById('orderDetailsModal').style.display = 'none';
        },

        _renderItems: function (data, list) {
            if (data.length === 0) {
                list.innerHTML = '<p style="text-align:center;">Sem itens.</p>';
                return;
            }

            let html = '<table style="width: 100%; font-size: 0.85rem; border-collapse: collapse;">';

            data.forEach(item => {
                let unitPrice = parseFloat(item.price);
                let subTotal = unitPrice * item.quantity;

                html += `
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
            });

            html += '</table>';
            html += '<div style="margin-top:10px; border-top:1px solid #000; padding-top:5px; font-size:0.7rem; text-align:center;">*** FIM DO COMPROVANTE ***</div>';

            list.innerHTML = html;
        }
    },

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================

    init: function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
};

// ==========================================
// EXPÕE GLOBALMENTE
// ==========================================

window.CashierAdmin = CashierAdmin;

// ==========================================
// ALIASES DE COMPATIBILIDADE (HTML usa esses)
// ==========================================

window.openModal = (type) => CashierAdmin.Movimento.open(type);
window.openOrderDetails = (id, total, date) => CashierAdmin.OrderDetails.open(id, total, date);

// ==========================================
// AUTO-INIT
// ==========================================

document.addEventListener('DOMContentLoaded', () => CashierAdmin.init());

console.log('[CashierAdmin] Módulo carregado');
