/**
 * ============================================
 * CARDÁPIO ADMIN - Forms (Orquestrador)
 * 
 * Arquivo principal que inicializa os módulos de formulário.
 * Os módulos são carregados separadamente:
 * - forms-tabs.js (Sistema de abas)
 * - forms-toggles.js (Toggles condicionais)
 * - forms-validation.js (Validação)
 * - forms-hours.js (Horários)
 * - forms-delivery.js (Delivery)
 * - forms-cards.js (Cards)
 * ============================================
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Loader no botão salvar
     */
    CardapioAdmin.initLoader = function () {
        const form = document.querySelector('form');
        const btnSave = document.querySelector('.cardapio-admin-btn-save');

        if (!form || !btnSave) return;

        form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                return;
            }

            // [CRÍTICO] Habilitar campos para garantir envio no POST
            const waInput = document.getElementById('whatsapp_number');
            if (waInput) waInput.disabled = false;

            document.querySelectorAll('.delivery-field input, .delivery-field select').forEach(f => f.disabled = false);
            document.querySelectorAll('.status-field input, .status-field select').forEach(f => f.disabled = false);
            document.querySelectorAll('.pagamentos-field input, .pagamentos-field select').forEach(f => f.disabled = false);
            document.querySelectorAll('.whatsapp-field input, .whatsapp-field select, .whatsapp-field textarea').forEach(f => f.disabled = false);

            // Mostra loader
            const originalText = btnSave.innerHTML;
            btnSave.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
            btnSave.disabled = true;
            btnSave.style.opacity = '0.7';
            btnSave.style.cursor = 'wait';

            if (window.lucide) lucide.createIcons();
        });
    };

    console.log('CardapioAdmin Forms JS Loaded');

})(window.CardapioAdmin = window.CardapioAdmin || {});
