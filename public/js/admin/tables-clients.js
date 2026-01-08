/**
 * TABLES-CLIENTS.JS - Modal de Clientes
 * Módulo: TablesAdmin.Clients
 * Refatorado: Delega para ClientManager (clientes.js)
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Clients = {

        openModal: function (startType) {
            // Verifica se o ClientManager existe (carregado via clientes.js)
            if (window.ClientManager && window.ClientManager.ui) {
                window.ClientManager.ui.openModal(startType);
            } else {
                console.error('ClientManager não encontrado. Verifique se clientes.js foi carregado.');
                alert('Erro: Módulo de Clientes não carregado.');
            }
        }
    };

    console.log('[TablesAdmin.Clients] Módulo carregado (Wrapper)');
})();
