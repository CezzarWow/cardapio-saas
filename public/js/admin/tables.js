/**
 * TABLES.JS - Orquestrador de Mesas e Comandas
 * Namespace: TablesAdmin
 * 
 * Dependências (carregar ANTES):
 * - tables-crud.js
 * - tables-clients.js
 * - tables-paid-orders.js
 * - tables-dossier.js
 * - clientes.js (ClientManager)
 */

(function () {
    'use strict';

    // IMPORTANTE: Estender o objeto existente ao invés de sobrescrever
    window.TablesAdmin = window.TablesAdmin || {};

    Object.assign(window.TablesAdmin, {

        // ==========================================
        // DELEGAÇÃO - CRUD DE MESAS
        // ==========================================
        openNewTableModal: () => window.TablesAdmin.Crud.openNewModal(),
        saveTable: () => window.TablesAdmin.Crud.save(),
        openRemoveTableModal: () => window.TablesAdmin.Crud.openRemoveModal(),
        removeTable: () => window.TablesAdmin.Crud.remove(),
        abrirMesa: (id, numero) => window.TablesAdmin.Crud.abrir(id, numero),

        // ==========================================
        // DELEGAÇÃO - CLIENTES (via Wrapper)
        // ==========================================
        openNewClientModal: (startType) => window.TablesAdmin.Clients.openModal(startType),

        // ==========================================
        // DELEGAÇÃO - PEDIDOS PAGOS
        // ==========================================
        showPaidOrderOptions: (orderId, clientName, total, clientId) =>
            window.TablesAdmin.PaidOrders.showOptions(orderId, clientName, total, clientId),
        closePaidOrderModal: () => window.TablesAdmin.PaidOrders.closeModal(),
        deliverOrder: () => window.TablesAdmin.PaidOrders.deliver(),
        editPaidOrder: () => window.TablesAdmin.PaidOrders.edit(),

        // ==========================================
        // DELEGAÇÃO - DOSSIÊ
        // ==========================================
        openDossier: (clientId) => window.TablesAdmin.Dossier.open(clientId)
    });

    // ==========================================
    // ALIASES DE COMPATIBILIDADE (HTML onclicks)
    // ==========================================
    window.openNewTableModal = () => TablesAdmin.openNewTableModal();
    window.saveTable = () => TablesAdmin.saveTable();
    window.openRemoveTableModal = () => TablesAdmin.openRemoveTableModal();
    window.removeTable = () => TablesAdmin.removeTable();
    window.abrirMesa = (id, numero) => TablesAdmin.abrirMesa(id, numero);

    // Alias para Clientes (Prioriza ClientManager direto se possível, fallback pro TablesAdmin)
    window.openNewClientModal = (type) => {
        if (window.ClientManager) window.ClientManager.ui.openModal(type);
        else TablesAdmin.openNewClientModal(type);
    };

    window.showPaidOrderOptions = (orderId, clientName, total, clientId) =>
        TablesAdmin.showPaidOrderOptions(orderId, clientName, total, clientId);

    window.closePaidOrderModal = () => TablesAdmin.closePaidOrderModal();
    window.deliverOrder = () => TablesAdmin.deliverOrder();
    window.editPaidOrder = () => TablesAdmin.editPaidOrder();
    window.openDossier = (id) => TablesAdmin.openDossier(id);

    console.log('[TablesAdmin] Orquestrador carregado e vinculado.');

})();
