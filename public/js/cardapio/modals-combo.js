/**
 * MODALS-COMBO.JS (Adapter)
 * 
 * Mantém a compatibilidade com chamadas legadas (CardapioModals.*)
 * delegando para o novo ComboController.js
 */

(function () {
    'use strict';

    // Inicializa o Controller
    if (typeof ComboController !== 'undefined') {
        ComboController.init();
    } else {
        console.error('ComboController module not loaded!');
        return;
    }

    // Mapeamento (Adapter Pattern)
    CardapioModals.openCombo = (id) => ComboController.open(id);
    CardapioModals.closeCombo = () => ComboController.close();
    CardapioModals.increaseComboQty = () => ComboController.increaseQty();
    CardapioModals.decreaseComboQty = () => ComboController.decreaseQty();
    CardapioModals.addComboToCart = () => ComboController.addToCart();

    // Propriedades Legadas (Getters para manter compatibilidade de leitura se necessário)
    Object.defineProperty(CardapioModals, 'currentCombo', {
        get: () => ComboController.currentCombo
    });

})();
