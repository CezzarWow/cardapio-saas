/**
 * TABLES.JS - Orquestrador de Mesas e Comandas
 * Namespace: TablesAdmin
 * 
 * Dependências (carregar ANTES deste arquivo):
 * - tables-crud.js
 * - tables-clients.js
 * - tables-paid-orders.js
 * - tables-dossier.js
 */

const TablesAdmin = {

    // ==========================================
    // DELEGAÇÃO PARA MÓDULOS
    // ==========================================

    // CRUD de Mesas (tables-crud.js)
    openNewTableModal: () => TablesAdmin.Crud.openNewModal(),
    saveTable: () => TablesAdmin.Crud.save(),
    openRemoveTableModal: () => TablesAdmin.Crud.openRemoveModal(),
    removeTable: () => TablesAdmin.Crud.remove(),
    abrirMesa: (id, numero) => TablesAdmin.Crud.abrir(id, numero),

    // Clientes (tables-clients.js)
    openNewClientModal: (startType) => TablesAdmin.Clients.openModal(startType),

    // Pedidos Pagos (tables-paid-orders.js)
    showPaidOrderOptions: (orderId, clientName, total, clientId) =>
        TablesAdmin.PaidOrders.showOptions(orderId, clientName, total, clientId),
    closePaidOrderModal: () => TablesAdmin.PaidOrders.closeModal(),
    deliverOrder: () => TablesAdmin.PaidOrders.deliver(),
    editPaidOrder: () => TablesAdmin.PaidOrders.edit(),

    // Dossiê (tables-dossier.js)
    openDossier: (clientId) => TablesAdmin.Dossier.open(clientId)
};

// ==========================================
// EXPÕE GLOBALMENTE
// ==========================================

window.TablesAdmin = TablesAdmin;

// ==========================================
// ALIASES DE COMPATIBILIDADE (HTML usa esses)
// ==========================================

window.openNewTableModal = () => TablesAdmin.openNewTableModal();
window.saveTable = () => TablesAdmin.saveTable();
window.openRemoveTableModal = () => TablesAdmin.openRemoveTableModal();
window.removeTable = () => TablesAdmin.removeTable();
window.abrirMesa = (id, numero) => TablesAdmin.abrirMesa(id, numero);
window.openNewClientModal = (type) => TablesAdmin.openNewClientModal(type);
window.showPaidOrderOptions = (a, b, c, d) => TablesAdmin.showPaidOrderOptions(a, b, c, d);
window.closePaidOrderModal = () => TablesAdmin.closePaidOrderModal();
window.deliverOrder = () => TablesAdmin.deliverOrder();
window.editPaidOrder = () => TablesAdmin.editPaidOrder();
window.openDossier = (id) => TablesAdmin.openDossier(id);

console.log('[TablesAdmin] Orquestrador carregado');
