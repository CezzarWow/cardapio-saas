/**
 * TABLES-DOSSIER.JS - Dossiê do Cliente
 * Módulo: TablesAdmin.Dossier
 */

(function () {
    'use strict';

    // Garante namespace
    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Dossier = {

        open: function (clientId) {
            const modal = document.getElementById('dossierModal');
            if (!modal) return;

            modal.style.display = 'flex';
            document.getElementById('dos_name').innerText = 'Buscando dados...';
            document.getElementById('dos_info').innerText = '...';
            document.getElementById('dos_history_list').innerHTML = '<p style="color:#94a3b8; text-align:center">Carregando...</p>';

            const btnOrder = document.getElementById('btn-dossier-order');
            btnOrder.onclick = function () {
                window.location.href = BASE_URL + '/admin/loja/pdv?client_id=' + clientId;
            };

            fetch(BASE_URL + '/admin/loja/clientes/detalhes?id=' + clientId)
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

            document.getElementById('dos_name').innerText = cli.name;
            const docLabel = cli.type === 'PJ' ? 'CNPJ' : 'CPF';
            document.getElementById('dos_info').innerText = `${docLabel}: ${cli.document || 'Não informado'} • Tel: ${cli.phone || '--'}`;

            const debt = parseFloat(cli.current_debt || 0);
            const limit = parseFloat(cli.credit_limit || 0);

            document.getElementById('dos_debt').innerText = 'R$ ' + debt.toFixed(2).replace('.', ',');
            document.getElementById('dos_limit').innerText = 'R$ ' + limit.toFixed(2).replace('.', ',');

            this._renderHistory(data.history);

            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        _renderHistory: function (history) {
            const list = document.getElementById('dos_history_list');
            list.innerHTML = '';

            if (!history || history.length === 0) {
                list.innerHTML = '<div style="text-align:center; padding:20px; color:#cbd5e1;">Nenhuma movimentação registrada.</div>';
                return;
            }

            history.forEach(item => {
                const isPay = item.type === 'pagamento';
                const color = isPay ? '#16a34a' : '#ef4444';
                const sign = isPay ? '+' : '-';

                list.innerHTML += `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <div style="font-weight: 600; color: #334155; font-size: 0.9rem;">${item.description || item.type.toUpperCase()}</div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">${new Date(item.created_at).toLocaleDateString('pt-BR')}</div>
                        </div>
                        <div style="font-weight: 700; color: ${color}; font-size: 0.95rem;">
                            ${sign} R$ ${parseFloat(item.amount).toFixed(2).replace('.', ',')}
                        </div>
                    </div>
                `;
            });
        }
    };

    console.log('[TablesAdmin.Dossier] Módulo carregado');
})();
