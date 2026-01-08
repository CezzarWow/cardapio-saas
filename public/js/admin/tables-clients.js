/**
 * TABLES-CLIENTS.JS - Modal de Clientes
 * Módulo: TablesAdmin.Clients
 */

(function () {
    'use strict';

    // Garante namespace
    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Clients = {

        openModal: function (startType) {
            const modal = document.getElementById('superClientModal');
            if (!modal) {
                alert('🚧 Super Modal em construção!');
                return;
            }

            modal.style.display = 'flex';

            if (typeof setType === 'function') {
                setType(startType);
            }

            const nameInput = document.getElementById('cli_name');
            if (nameInput) nameInput.focus();
        }
    };

    console.log('[TablesAdmin.Clients] Módulo carregado');
})();
