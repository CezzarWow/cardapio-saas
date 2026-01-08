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

    const TablesAdmin = {

        // ==========================================
        // DELEGAÇÃO - CRUD DE MESAS
        // ==========================================
        openNewTableModal: () => TablesAdmin.Crud.openNewModal(),
        saveTable: () => TablesAdmin.Crud.save(),
        openRemoveTableModal: () => TablesAdmin.Crud.openRemoveModal(),
        removeTable: () => TablesAdmin.Crud.remove(),
        abrirMesa: (id, numero) => TablesAdmin.Crud.abrir(id, numero),

        // ==========================================
        // DELEGAÇÃO - CLIENTES (via Wrapper)
        // ==========================================
        openNewClientModal: (startType) => TablesAdmin.Clients.openModal(startType),

        // ==========================================
        // DELEGAÇÃO - PEDIDOS PAGOS
        // ==========================================
        showPaidOrderOptions: (orderId, clientName, total, clientId) =>
            TablesAdmin.PaidOrders.showOptions(orderId, clientName, total, clientId),
        closePaidOrderModal: () => TablesAdmin.PaidOrders.closeModal(),
        deliverOrder: () => TablesAdmin.PaidOrders.deliver(),
        editPaidOrder: () => TablesAdmin.PaidOrders.edit(),

        // ==========================================
        // DELEGAÇÃO - DOSSIÊ
        // ==========================================
        openDossier: (clientId) => TablesAdmin.Dossier.open(clientId)
    };

    // ==========================================
    // EXPORTAR GLOBALMENTE
    // ==========================================
    window.TablesAdmin = TablesAdmin;

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
