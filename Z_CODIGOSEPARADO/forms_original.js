/**
 * ============================================
 * CARDÁPIO ADMIN - Forms
 * Funções de formulários, validação, toggles, settings
 * ============================================
 */

(function (CardapioAdmin) {

    /**
     * Sistema de abas com persistência via Hash
     */
    CardapioAdmin.initTabs = function () {
        const tabBtns = document.querySelectorAll('.cardapio-admin-tab-btn');
        const tabContents = document.querySelectorAll('.cardapio-admin-tab-content');

        if (!tabBtns.length) return;

        // Função para ativar aba
        const activateTab = (tabId) => {
            // Remove active
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            // Adiciona active no botão
            const btn = document.querySelector(`.cardapio-admin-tab-btn[data-tab="${tabId}"]`);
            if (btn) btn.classList.add('active');

            // Adiciona active no conteúdo
            const content = document.getElementById(`tab-${tabId}`);
            if (content) content.classList.add('active');
        };

        // 1. Checar Hash na URL ao carregar
        const currentHash = window.location.hash.replace('#', '');
        if (currentHash) {
            activateTab(currentHash);
        }

        // 2. Click Listener
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.dataset.tab;
                activateTab(targetTab);

                // Atualiza URL sem recarregar
                history.replaceState(null, null, `#${targetTab}`);
            });
        });
    };

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

            // [CRÍTICO] Habilitar campo WhatsApp para garantir envio no POST
            const waInput = document.getElementById('whatsapp_number');
            if (waInput) waInput.disabled = false;

            // [CRÍTICO] Habilitar campos Delivery para garantir envio no POST
            document.querySelectorAll('.delivery-field input, .delivery-field select').forEach(f => f.disabled = false);

            // [CRÍTICO] Habilitar campos Status e Pagamentos para garantir envio
            document.querySelectorAll('.status-field input, .status-field select').forEach(f => f.disabled = false);
            document.querySelectorAll('.pagamentos-field input, .pagamentos-field select').forEach(f => f.disabled = false);
            document.querySelectorAll('.whatsapp-field input, .whatsapp-field select, .whatsapp-field textarea').forEach(f => f.disabled = false);

            // Mostra loader
            const originalText = btnSave.innerHTML;
            btnSave.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
            btnSave.disabled = true;
            btnSave.style.opacity = '0.7';
            btnSave.style.cursor = 'wait';

            // Re-renderiza ícones para o loader aparecer
            if (window.lucide) lucide.createIcons();
        });
    };

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

    /**
     * Validação HTML5 + feedback visual
     */
    CardapioAdmin.initValidation = function () {
        const form = document.querySelector('form');
        if (!form) return;

        // Adiciona validação nos campos críticos
        this.addValidation('whatsapp_number', {
            // [ETAPA 5] Removido padrão rígido pois agora usamos máscara visual (XX) ...
            // O backend limpa os caracteres.
            placeholder: '(XX) X XXXX-XXXX'
        });

        this.addValidation('delivery_fee', {
            min: '0',
            title: 'Valor não pode ser negativo'
        });

        this.addValidation('min_order_value', {
            min: '0',
            title: 'Valor não pode ser negativo'
        });

        this.addValidation('delivery_time_min', {
            min: '1',
            max: '180',
            title: 'Entre 1 e 180 minutos'
        });

        this.addValidation('delivery_time_max', {
            min: '1',
            max: '300',
            title: 'Entre 1 e 300 minutos'
        });
    };

    /**
     * Adiciona atributos de validação a um campo
     */
    CardapioAdmin.addValidation = function (fieldId, attrs) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        Object.keys(attrs).forEach(attr => {
            field.setAttribute(attr, attrs[attr]);
        });
    };

    /**
     * Validação antes de submit
     */
    CardapioAdmin.validateForm = function () {
        let isValid = true;
        const errors = [];

        // Validar WhatsApp se habilitado
        const whatsappEnabled = document.getElementById('whatsapp_enabled');
        const whatsappNumber = document.getElementById('whatsapp_number');

        if (whatsappEnabled && whatsappEnabled.checked) {
            const cleanNumber = whatsappNumber.value.replace(/\D/g, '');
            if (!whatsappNumber || cleanNumber.length < 10) {
                errors.push('Número do WhatsApp inválido (mínimo 10 dígitos com DDD)');
                this.highlightError(whatsappNumber);
                isValid = false;
            }
        }

        // Validar PIX se habilitado
        const pixEnabled = document.getElementById('accept_pix');
        const pixKey = document.getElementById('pix_key');

        if (pixEnabled && pixEnabled.checked) {
            if (!pixKey || !pixKey.value.trim()) {
                errors.push('Chave PIX é obrigatória quando PIX está habilitado');
                this.highlightError(pixKey);
                isValid = false;
            }
        }

        // Validar tempo min < max
        const timeMin = document.getElementById('delivery_time_min');
        const timeMax = document.getElementById('delivery_time_max');

        if (timeMin && timeMax) {
            if (parseInt(timeMin.value) > parseInt(timeMax.value)) {
                errors.push('Tempo mínimo não pode ser maior que o máximo');
                this.highlightError(timeMin);
                this.highlightError(timeMax);
                isValid = false;
            }
        }

        if (!isValid) {
            alert('Por favor, corrija os seguintes erros:\n\n• ' + errors.join('\n• '));
        }

        return isValid;
    };

    /**
     * Destaca campo com erro
     */
    CardapioAdmin.highlightError = function (field) {
        if (!field) return;
        field.style.borderColor = '#ef4444';
        field.focus();

        setTimeout(() => {
            field.style.borderColor = '';
        }, 3000);
    };

    /**
     * [ETAPA 2] Toggle de linha de horário
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

    /**
     * [ETAPA 5] Inicia edição dos campos Delivery
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
     * [ETAPA 5] Aplica (trava) as alterações de Delivery
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

    /**
     * [NOVO] Salva configurações automaticamente
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
                // Habilitar campos críticos (já feito no submit listener, mas reforçando)
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
     * [NOVO] Toggle Editar/Aplicar/Cancelar para cards
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
        // Recarrega a página para reverter os valores (simples e seguro)
        // Alternativa: armazenar valores originais e restaurar
        this.toggleCardEdit(cardName, 'cancel');
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
