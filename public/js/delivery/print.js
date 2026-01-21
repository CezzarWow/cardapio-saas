/**
 * PRINT.JS - Orquestrador de Impressão Delivery
 * Namespace: DeliveryPrint
 * 
 * Dependências (carregar ANTES deste arquivo):
 * - print-helpers.js
 * - print-generators.js
 * - print-modal.js
 * - print-actions.js
 */

const DeliveryPrint = window.DeliveryPrint || {};

// ==========================================
// DELEGAÇÃO PARA MÓDULOS
// ==========================================

// Modal Control
DeliveryPrint.openModal = (orderId, type) => DeliveryPrint.Modal.open(orderId, type);
DeliveryPrint.closeModal = () => DeliveryPrint.Modal.close();
DeliveryPrint.showDeliverySlip = () => DeliveryPrint.Modal.showDeliverySlip();
DeliveryPrint.showKitchenSlip = () => DeliveryPrint.Modal.showKitchenSlip();

// Actions
DeliveryPrint.print = () => window.DeliveryPrint.Actions.print();
DeliveryPrint.printComplete = (orderData) => window.DeliveryPrint.Actions.printComplete(orderData);
DeliveryPrint.printDirect = (orderId, type) => window.DeliveryPrint.Actions.printDirect(orderId, type);
DeliveryPrint.printFromModal = () => window.DeliveryPrint.Actions.printFromModal();

// Generators (acesso direto para uso externo)
DeliveryPrint.generateSlipHTML = (order, items, title) =>
    DeliveryPrint.Generators.generateSlipHTML(order, items, title);
DeliveryPrint.generateKitchenSlipHTML = (order, items) =>
    DeliveryPrint.Generators.generateKitchenSlipHTML(order, items);

// Helpers (acesso direto para uso externo)
DeliveryPrint.extractOrderData = (order) => DeliveryPrint.Helpers.extractOrderData(order);
DeliveryPrint.generateItemsHTML = (items, showPrice) => DeliveryPrint.Helpers.generateItemsHTML(items, showPrice);

// ==========================================
// EXPÕE GLOBALMENTE
// ==========================================

window.DeliveryPrint = DeliveryPrint;


