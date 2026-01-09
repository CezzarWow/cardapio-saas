/**
 * TABLES-CRUD.JS - CRUD de Mesas
 * Módulo: TablesAdmin.Crud
 * Refatorado para usar BASE_URL
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    // Helper para URL Segura
    const getBaseUrl = () => typeof BASE_URL !== 'undefined' ? BASE_URL : '/cardapio-saas/public';
    // Helper para CSRF
    const getCsrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    TablesAdmin.Crud = {

        openNewModal: function () {
            document.getElementById('newTableModal').style.display = 'flex';
            setTimeout(() => document.getElementById('new_table_number').focus(), 50);
        },

        save: function () {
            const number = document.getElementById('new_table_number').value;
            if (!number) return;

            fetch(getBaseUrl() + '/admin/loja/mesas/salvar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ number: number })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else alert(data.message || 'Erro ao salvar mesa');
                })
                .catch(err => alert('Erro de conexão ao salvar mesa'));
        },

        openRemoveModal: function () {
            document.getElementById('removeTableModal').style.display = 'flex';
            setTimeout(() => document.getElementById('remove_table_number').focus(), 50);
        },

        remove: function () {
            const number = document.getElementById('remove_table_number').value;
            if (!number) return;

            if (!confirm(`Tem certeza que deseja excluir a MESA ${number}?`)) return;

            const url = getBaseUrl() + '/admin/loja/mesas/deletar';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ number: number, force: false })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                    else if (data.occupied) {
                        if (confirm(`ATENÇÃO: A Mesa ${number} está OCUPADA!\n\nExcluir agora pode causar erros nos pedidos.\nDeseja forçar a exclusão mesmo assim?`)) {
                            this._forceRemove(number, url);
                        }
                    } else {
                        alert(data.message || 'Erro ao excluir mesa');
                    }
                })
                .catch(err => alert('Erro de conexão ao excluir mesa'));
        },

        _forceRemove: function (number, url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ number: number, force: true })
            })
                .then(r2 => r2.json())
                .then(d2 => {
                    if (d2.success) window.location.reload();
                    else alert('Erro ao excluir (forçado): ' + d2.message);
                })
                .catch(err => alert('Erro de conexão (forçado)'));
        },

        abrir: function (id, numero) {
            window.location.href = getBaseUrl() + '/admin/loja/pdv?mesa_id=' + id + '&mesa_numero=' + numero;
        }
    };

    console.log('[TablesAdmin.Crud] Módulo carregado');
})();
