/**
 * FORMS-DELIVERY.JS - Edição de Delivery
 * 
 * Gerencia edição/aplicação dos campos de Delivery.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Inicia edição dos campos Delivery
     */
    CardapioAdmin.startDeliveryEdit = function () {
        const fields = document.querySelectorAll('.delivery-field');
        const btnEdit = document.getElementById('btn_edit_delivery');
        const btnApply = document.getElementById('btn_apply_delivery');

        if (!fields.length) return;

        // Habilitar campos
        fields.forEach(f => {
            f.disabled = false;
            f.style.backgroundColor = 'white';
        });
        fields[0].focus();

        // Mostrar botão Aplicar, esconder Editar
        if (btnEdit) btnEdit.style.display = 'none';
        if (btnApply) btnApply.style.display = 'inline-flex';

        if (window.lucide) lucide.createIcons();
    };

    /**
     * Aplica (trava) as alterações de Delivery
     */
    CardapioAdmin.applyDeliveryEdit = function () {
        const fields = document.querySelectorAll('.delivery-field');
        const btnEdit = document.getElementById('btn_edit_delivery');
        const btnApply = document.getElementById('btn_apply_delivery');

        if (!fields.length) return;

        // Travar campos
        fields.forEach(f => {
            f.disabled = true;
            f.style.backgroundColor = '#f8fafc';
        });

        // Mostrar botão Editar, esconder Aplicar
        if (btnEdit) btnEdit.style.display = 'inline-flex';
        if (btnApply) btnApply.style.display = 'none';

        if (window.lucide) lucide.createIcons();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
