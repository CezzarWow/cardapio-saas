/**
 * FORMS-TOGGLES.JS - Toggles Condicionais
 * 
 * Gerencia toggles que mostram/escondem e habilitam/desabilitam campos.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Toggles condicionais (mostra/esconde + habilita/desabilita campos)
     */
    CardapioAdmin.initToggles = function () {
        // WhatsApp
        this.setupToggleSection('whatsapp_enabled', 'whatsapp-fields', [
            'whatsapp_number',
            'whatsapp_message'
        ]);

        // Delivery
        this.setupToggleSection('delivery_enabled', 'delivery-fields', [
            'delivery_fee',
            'min_order_value',
            'delivery_time_min',
            'delivery_time_max'
        ]);

        // PIX
        this.setupToggleSection('accept_pix', 'pix-fields', [
            'pix_key',
            'pix_key_type'
        ], 'pix-disabled-msg');
    };

    /**
     * Configura toggle com seção dependente
     */
    CardapioAdmin.setupToggleSection = function (toggleId, sectionId, fieldIds, disabledMsgId = null) {
        const toggle = document.getElementById(toggleId);
        const section = document.getElementById(sectionId);

        if (!toggle || !section) return;

        const updateState = () => {
            const isEnabled = toggle.checked;

            // Mostra/esconde seção
            section.style.display = isEnabled ? 'block' : 'none';

            // Habilita/desabilita campos
            fieldIds.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.disabled = !isEnabled;
                    field.style.opacity = isEnabled ? '1' : '0.5';
                }
            });

            // Mensagem de desabilitado (opcional)
            if (disabledMsgId) {
                const msg = document.getElementById(disabledMsgId);
                if (msg) {
                    msg.style.display = isEnabled ? 'none' : 'block';
                }
            }
        };

        toggle.addEventListener('change', updateState);
        updateState(); // Estado inicial
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
