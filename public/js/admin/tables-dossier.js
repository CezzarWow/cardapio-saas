/**
 * TABLES-DOSSIER.JS - Dossiê do Cliente
 * Módulo: TablesAdmin.Dossier
 * 
 * Dependência: tables-helpers.js (carregar antes)
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Dossier = {

        open: function (clientId) {
            const modal = document.getElementById('dossierModal');
            if (!modal) return;

            // Reset UI
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.getElementById('dos_name').innerText = 'Buscando dados...';
            document.getElementById('dos_info').innerText = '...';
            document.getElementById('dos_history_list').innerHTML = '<p style="color:#94a3b8; text-align:center">Carregando...</p>';

            // Setup Botão Novo Pedido
            const btnOrder = document.getElementById('btn-dossier-order');
            if (btnOrder) {
                btnOrder.onclick = () => {
                    window.location.href = TablesHelpers.getBaseUrl() + '/admin/loja/pdv?client_id=' + clientId;
                };
            }

            // Fetch Dados
            fetch(TablesHelpers.getBaseUrl() + '/admin/loja/clientes/detalhes?id=' + clientId)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this._renderDossier(data);
                    } else {
                        alert('Erro: ' + data.message);
                        modal.style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro ao buscar detalhes.');
                    modal.style.display = 'none';
                });
        },

        _renderDossier: function (data) {
            const cli = data.client;

            // Info Básica
            document.getElementById('dos_name').innerText = cli.name;
            const docLabel = cli.type === 'PJ' ? 'CNPJ' : 'CPF';
            const docValue = cli.document || 'Não informado';
            const phoneValue = cli.phone || '--';
            document.getElementById('dos_info').innerText = `${docLabel}: ${docValue} • Tel: ${phoneValue}`;

            // Financeiro
            const debt = parseFloat(cli.current_debt || 0);
            const limit = parseFloat(cli.credit_limit || 0);
            document.getElementById('dos_debt').innerText = TablesHelpers.formatCurrency(debt);
            document.getElementById('dos_limit').innerText = TablesHelpers.formatCurrency(limit);

            // Histórico
            this._renderHistory(data.history);

            // Ícones
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        _renderHistory: function (history) {
            const list = document.getElementById('dos_history_list');
            list.innerHTML = '';

            if (!history || history.length === 0) {
                list.innerHTML = '<div style="text-align:center; padding:20px; color:#cbd5e1;">Nenhuma movimentação registrada.</div>';
                return;
            }

            const html = history.map(item => this._createHistoryItemHtml(item)).join('');
            list.innerHTML = html;
        },

        _createHistoryItemHtml: function (item) {
            const isPay = item.type === 'pagamento';
            const color = isPay ? '#16a34a' : '#ef4444';
            const sign = isPay ? '+' : '-';
            const dateStr = new Date(item.created_at).toLocaleDateString('pt-BR');
            const amountStr = TablesHelpers.formatCurrency(item.amount).replace('R$ ', '');

            return `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <div style="font-weight: 600; color: #334155; font-size: 0.9rem;">${item.description || item.type.toUpperCase()}</div>
                        <div style="font-size: 0.75rem; color: #94a3b8;">${dateStr}</div>
                    </div>
                    <div style="font-weight: 700; color: ${color}; font-size: 0.95rem;">
                        ${sign} R$ ${amountStr}
                    </div>
                </div>
            `;
        }
    };

})();
