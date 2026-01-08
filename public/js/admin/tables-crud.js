/**
 * TABLES-CRUD.JS - CRUD de Mesas
 * Módulo: TablesAdmin.Crud
 */

(function () {
    'use strict';

    // Garante namespace
    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Crud = {

        openNewModal: function () {
            document.getElementById('newTableModal').style.display = 'flex';
            document.getElementById('new_table_number').focus();
        },

        save: function () {
            const number = document.getElementById('new_table_number').value;
            if (!number) return;

            fetch('mesas/salvar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ number: number })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else alert(data.message);
                });
        },

        openRemoveModal: function () {
            document.getElementById('removeTableModal').style.display = 'flex';
            document.getElementById('remove_table_number').focus();
        },

        remove: function () {
            const number = document.getElementById('remove_table_number').value;
            if (!number) return;

            if (!confirm(`Tem certeza que deseja excluir a MESA ${number}?`)) return;

            fetch('mesas/deletar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ number: number, force: false })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                    else if (data.occupied) {
                        if (confirm(`ATENÇÃO: A Mesa ${number} está OCUPADA!\n\nExcluir agora pode causar erros nos pedidos.\nDeseja forçar a exclusão mesmo assim?`)) {
                            fetch('mesas/deletar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ number: number, force: true })
                            })
                                .then(r2 => r2.json())
                                .then(d2 => {
                                    if (d2.success) window.location.reload();
                                    else alert('Erro ao excluir: ' + d2.message);
                                });
                        }
                    } else {
                        alert(data.message);
                    }
                });
        },

        abrir: function (id, numero) {
            window.location.href = 'pdv?mesa_id=' + id + '&mesa_numero=' + numero;
        }
    };

    console.log('[TablesAdmin.Crud] Módulo carregado');
})();
