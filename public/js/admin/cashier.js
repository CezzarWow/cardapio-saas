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

                modal.classList.add('active');
                // Focus no input de valor
                setTimeout(() => {
                    const valInput = document.getElementById('movValue');
                    if (valInput) valInput.focus();
                }, 50);
            },

            close: function () {
                const modal = document.getElementById('modalMovimento');
                if (modal) modal.classList.remove('active');
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

                modal.classList.add('active');
                list.innerHTML = '<p style="text-align:center; padding: 20px 0;">Carregando...</p>';

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
                if (modal) modal.classList.remove('active');
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
    window.CashierSPA = {
        init: () => CashierAdmin.init(),
        openModal: (type) => CashierAdmin.Movimento.open(type),
        openOrderDetails: (id, total, date) => CashierAdmin.OrderDetails.open(id, total, date),

        // Verifica pendências antes de fechar o caixa
        tryCloseCashier: async function () {
            try {
                const response = await fetch(getBaseUrl() + '/admin/loja/caixa/verificar-pendencias');
                const data = await response.json();

                if (!data.success && data.pendencias && data.pendencias.length > 0) {
                    // Mostrar modal de pendências
                    this._showPendenciasModal(data.pendencias);
                } else {
                    // Nenhuma pendência, mostrar modal de confirmação
                    this._showConfirmModal();
                }
            } catch (err) {
                console.error('Erro ao verificar pendências:', err);
                alert('Erro ao verificar pendências. Tente novamente.');
            }
        },

        _showConfirmModal: function () {
            const modal = document.getElementById('modalConfirmarFechamento');
            if (modal) {
                modal.classList.add('active');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        },

        closeConfirmModal: function () {
            const modal = document.getElementById('modalConfirmarFechamento');
            if (modal) modal.classList.remove('active');
        },

        finalizeClose: function () {
            window.location.href = getBaseUrl() + '/admin/loja/caixa/fechar';
        },

        _showPendenciasModal: function (pendencias) {
            const modal = document.getElementById('modalPendencias');
            const list = document.getElementById('pendenciasList');
            if (!modal || !list) return;

            // Mapear ícones por tipo
            const icones = {
                'mesas': 'utensils',
                'delivery': 'truck',
                'clientes': 'users'
            };

            const cores = {
                'mesas': { bg: '#fef3c7', icon: '#d97706', text: '#92400e' },
                'delivery': { bg: '#dbeafe', icon: '#2563eb', text: '#1e40af' },
                'clientes': { bg: '#f3e8ff', icon: '#9333ea', text: '#6b21a8' }
            };

            let html = '';
            pendencias.forEach(p => {
                const icone = icones[p.tipo] || 'alert-circle';

                html += `
                    <div class="pendencia-item">
                        <div class="pendencia-icon">
                            <i data-lucide="${icone}"></i>
                        </div>
                        <div class="pendencia-info">
                            <span class="pendencia-count">${p.quantidade}</span>
                            <span class="pendencia-msg">${p.mensagem}</span>
                        </div>
                    </div>
                `;
            });

            list.innerHTML = html;
            modal.classList.add('active');

            // Recria ícones Lucide no conteúdo novo
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    };

    // Aliases (legado)
    window.openModal = (type) => CashierAdmin.Movimento.open(type);
    window.openOrderDetails = (id, total, date) => CashierAdmin.OrderDetails.open(id, total, date);

    // Auto-init apenas fora do SPA (fallback)
    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('spa-content')) {
            CashierAdmin.init();
        }
    });

})();
