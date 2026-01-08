/**
 * FORMS-HOURS.JS - Toggle de Horários
 * 
 * Gerencia toggle de linhas de horário de funcionamento.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Toggle de linha de horário
     */
    CardapioAdmin.toggleHourRow = function (dayNum) {
        const checkbox = document.getElementById('hour_day_' + dayNum);
        const fields = document.getElementById('hour_fields_' + dayNum);
        const closedLabel = document.getElementById('hour_closed_' + dayNum);
        const openInput = document.getElementById('hour_open_' + dayNum);
        const closeInput = document.getElementById('hour_close_' + dayNum);

        if (!checkbox) return;

        const isOpen = checkbox.checked;

        if (fields) {
            fields.style.opacity = isOpen ? '1' : '0.4';
        }
        if (openInput) {
            openInput.disabled = !isOpen;
        }
        if (closeInput) {
            closeInput.disabled = !isOpen;
        }
        if (closedLabel) {
            closedLabel.style.display = isOpen ? 'none' : 'inline';
        }
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
