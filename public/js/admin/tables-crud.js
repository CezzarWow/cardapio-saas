/**
 * TABLES-CRUD.JS - CRUD de Mesas
 * Módulo: TablesAdmin.Crud
 * 
 * Dependência: tables-helpers.js (carregar antes)
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Crud = {

        openNewModal: function () {
            const modal = document.getElementById('newTableModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            setTimeout(() => document.getElementById('new_table_number').focus(), 50);
        },

        save: function () {
            const number = document.getElementById('new_table_number').value;
            if (!number) return;

            fetch(TablesHelpers.getBaseUrl() + '/admin/loja/mesas/salvar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': TablesHelpers.getCsrf()
                },
                body: JSON.stringify({ number: number })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (window.AdminSPA) window.AdminSPA.reloadCurrentSection();
                        else window.location.reload();
                    }
                    else alert(data.message || 'Erro ao salvar mesa');
                })
                .catch(err => alert('Erro de conexão ao salvar mesa'));
        },

        openRemoveModal: function () {
            const modal = document.getElementById('removeTableModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            setTimeout(() => document.getElementById('remove_table_number').focus(), 50);
        },

        remove: function () {
            const number = document.getElementById('remove_table_number').value;
            if (!number) return;

            if (!confirm(`Tem certeza que deseja excluir a MESA ${number}?`)) return;

            const url = TablesHelpers.getBaseUrl() + '/admin/loja/mesas/deletar';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': TablesHelpers.getCsrf()
                },
                body: JSON.stringify({ number: number, force: false })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (window.AdminSPA) window.AdminSPA.reloadCurrentSection();
                        else window.location.reload();
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
                    'X-CSRF-TOKEN': TablesHelpers.getCsrf()
                },
                body: JSON.stringify({ number: number, force: true })
            })
                .then(r2 => r2.json())
                .then(d2 => {
                    if (d2.success) {
                        if (window.AdminSPA) window.AdminSPA.reloadCurrentSection();
                        else window.location.reload();
                    }
                    else alert('Erro ao excluir (forçado): ' + d2.message);
                })
                .catch(err => alert('Erro de conexão (forçado)'));
        },

        abrir: function (id, numero) {
            // Usa navegação SPA quando disponível
            // AdminSPA automaticamente destaca 'mesas' quando há mesa_id
            if (typeof AdminSPA !== 'undefined') {
                AdminSPA.navigateTo('balcao', true, true, {
                    mesa_id: id,
                    mesa_numero: numero
                });
            } else {
                window.location.href = TablesHelpers.getBaseUrl() + '/admin/loja/pdv?mesa_id=' + id + '&mesa_numero=' + numero;
            }
        }
    };


})();
