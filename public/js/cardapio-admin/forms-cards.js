/**
 * FORMS-CARDS.JS - Edição de Cards
 * 
 * Gerencia toggle Editar/Aplicar/Cancelar para cards.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Salva configurações automaticamente
     */
    CardapioAdmin.saveSettings = function () {
        const form = document.querySelector('form');
        const btnSave = document.querySelector('.cardapio-admin-btn-save');

        if (form && btnSave) {
            // Feedback visual no botão Salvar principal
            const originalText = btnSave.innerHTML;
            btnSave.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
            btnSave.disabled = true;

            // Dispara validação e submit
            if (this.validateForm()) {
                // Habilitar campos críticos
                document.querySelectorAll('input:disabled, select:disabled, textarea:disabled').forEach(f => f.disabled = false);
                form.submit();
            } else {
                // Se falhar validação, restaura botão
                btnSave.innerHTML = originalText;
                btnSave.disabled = false;
                if (window.lucide) lucide.createIcons();
            }
        }
    };

    /**
     * Toggle Editar/Aplicar/Cancelar para cards
     */
    CardapioAdmin.toggleCardEdit = function (cardName, action = 'toggle') {
        const fields = document.querySelectorAll(`.${cardName}-field`);
        const btnEdit = document.getElementById(`btn_edit_${cardName}`);
        const btnCancel = document.getElementById(`btn_cancel_${cardName}`);

        if (!fields.length || !btnEdit) return;

        // Verificar estado atual
        const firstField = fields[0];
        const isLocked = firstField.style.pointerEvents === 'none';

        // Ação: cancelar = forçar bloqueio
        const shouldUnlock = (action === 'toggle') ? isLocked : false;

        if (shouldUnlock) {
            // DESBLOQUEAR (Editar)
            fields.forEach(f => {
                f.style.opacity = '1';
                f.style.pointerEvents = 'auto';
                f.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = false;
                    input.style.backgroundColor = 'white';
                });
            });

            // Mudar botão para "Aplicar" (verde)
            btnEdit.innerHTML = '<i data-lucide="check" size="14"></i> Aplicar';
            btnEdit.style.background = '#22c55e';
            btnEdit.style.color = 'white';

            // Mostrar botão Cancelar
            if (btnCancel) btnCancel.style.display = 'inline-flex';
        } else {
            // BLOQUEAR (Aplicar ou Cancelar)

            // Se for APLICAR (não cancel), salva antes de travar visualmente
            if (action !== 'cancel') {
                btnEdit.innerHTML = '<i data-lucide="loader-2" size="14" class="spin"></i> Salvando...';
                this.saveSettings();
                return; // O submit vai recarregar a página
            }

            fields.forEach(f => {
                f.style.opacity = '0.7';
                f.style.pointerEvents = 'none';
                f.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = true;
                    input.style.backgroundColor = '#f8fafc';
                });
            });

            // Mudar botão para "Editar" (cinza)
            btnEdit.innerHTML = '<i data-lucide="pencil" size="14"></i> Editar';
            btnEdit.style.background = '#e2e8f0';
            btnEdit.style.color = '#475569';

            // Esconder botão Cancelar
            if (btnCancel) btnCancel.style.display = 'none';
        }

        if (window.lucide) lucide.createIcons();
    };

    /**
     * Cancela edição de um card (reverte e trava)
     */
    CardapioAdmin.cancelCardEdit = function (cardName) {
        this.toggleCardEdit(cardName, 'cancel');
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
