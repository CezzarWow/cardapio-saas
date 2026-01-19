/* cardapio-bundle - Generated 2026-01-19T12:16:54.876Z */


/* ========== cardapio-admin/utils.js ========== */
/**
 * ============================================
 * CARDÁPIO ADMIN - Utils
 * Funções utilitárias globais
 * ============================================
 */

// Função de máscara monetária (ex: 5000 -> 50,00)
window.formatCurrency = function (input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    value = (parseInt(value) / 100).toFixed(2) + '';
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = value;
};


/* ========== cardapio-admin/pix.js ========== */
/**
 * ============================================
 * CARDÁPIO ADMIN - PIX / Máscaras
 * Funções de máscara para PIX, CPF, CNPJ, telefone
 * ============================================
 */

(function (CardapioAdmin) {

    /**
     * [ETAPA 5] Máscara de Telefone BR (9 ou 10 dígitos)
     */
    CardapioAdmin.maskPhone = function (input) {
        let value = input.value.replace(/\D/g, '');

        // Limita a 11 dígitos
        if (value.length > 11) value = value.slice(0, 11);

        // Aplica a máscara visualmente
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{1})(\d{4})(\d{4}).*/, '($1) $2 $3-$4');
        } else if (value.length > 5) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
        } else {
            value = value.replace(/^(\d*)/, '($1');
        }

        input.value = value;
    };

    /**
     * [NOVO] Máscaras Dinâmicas para PIX
     */
    CardapioAdmin.initPixMask = function () {
        const pixKey = document.getElementById('pix_key');
        const pixType = document.getElementById('pix_key_type');

        if (!pixKey || !pixType) return;

        const applyMask = () => {
            const type = pixType.value;
            let value = pixKey.value;

            if (type === 'cpf') value = this.maskCPF(value);
            else if (type === 'cnpj') value = this.maskCNPJ(value);
            else if (type === 'telefone') value = this.maskPhoneValue(value);

            pixKey.value = value;
        };

        pixKey.addEventListener('input', applyMask);

        pixType.addEventListener('change', () => {
            pixKey.value = ''; // Limpa ao mudar o tipo
            pixKey.focus();
        });
    };

    CardapioAdmin.maskCPF = function (v) {
        v = v.replace(/\D/g, "");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        return v.substring(0, 14);
    };

    CardapioAdmin.maskCNPJ = function (v) {
        v = v.replace(/\D/g, "");
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        return v.substring(0, 18);
    };

    CardapioAdmin.maskPhoneValue = function (v) {
        v = v.replace(/\D/g, "");
        if (v.length > 11) v = v.slice(0, 11);

        if (v.length > 10) {
            return v.replace(/^(\d{2})(\d{1})(\d{4})(\d{4}).*/, '($1) $2 $3-$4');
        } else if (v.length > 5) {
            return v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (v.length > 2) {
            return v.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
        } else {
            return v.replace(/^(\d*)/, '($1');
        }
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/whatsapp.js ========== */
/**
 * ============================================
 * CARDÁPIO ADMIN - WhatsApp
 * Funções relacionadas a mensagens WhatsApp
 * ============================================
 */

(function (CardapioAdmin) {

    /**
     * [ETAPA 5] Adicionar nova mensagem de WhatsApp (versão 1 - sem parâmetro)
     */
    CardapioAdmin.addWhatsappMessage = function () {
        const container = document.getElementById('whatsapp-messages-container');
        if (!container) return;

        const div = document.createElement('div');
        div.className = 'cardapio-admin-message-row';

        div.innerHTML = `
            <textarea class="cardapio-admin-input cardapio-admin-textarea" 
                      name="whatsapp_messages[]" 
                      rows="2"
                      placeholder="Digite sua mensagem..."></textarea>
            <button type="button" class="cardapio-admin-btn" 
                    style="background: #fee2e2; color: #ef4444; height: fit-content;" 
                    onclick="this.parentElement.remove()">
                <i data-lucide="trash-2"></i>
            </button>
        `;

        container.appendChild(div);

        if (window.lucide) lucide.createIcons();
    };

    /**
     * [ETAPA 5] Inicia edição do WhatsApp
     */
    CardapioAdmin.startWaEdit = function () {
        const input = document.getElementById('whatsapp_number');
        const btnEdit = document.getElementById('btn_edit_wa');
        const btnApply = document.getElementById('btn_apply_wa');

        if (!input) return;

        // Habilitar campo
        input.disabled = false;
        input.style.backgroundColor = 'white';
        input.focus();

        // Mostrar botão Aplicar, esconder Editar
        if (btnEdit) btnEdit.style.display = 'none';
        if (btnApply) btnApply.style.display = 'inline-flex';

        if (window.lucide) lucide.createIcons();
    };

    /**
     * [ETAPA 5] Aplica (trava) as alterações do WhatsApp
     */
    CardapioAdmin.applyWaEdit = function () {
        const input = document.getElementById('whatsapp_number');
        const btnEdit = document.getElementById('btn_edit_wa');
        const btnApply = document.getElementById('btn_apply_wa');

        if (!input) return;

        // Travar campo
        input.disabled = true;
        input.style.backgroundColor = '#f8fafc';

        // Mostrar botão Editar, esconder Aplicar
        if (btnEdit) btnEdit.style.display = 'inline-flex';
        if (btnApply) btnApply.style.display = 'none';

        if (window.lucide) lucide.createIcons();
    };

    /**
     * [NOVO] Adiciona mensagem do WhatsApp na lista específica (versão 2 - com parâmetro type)
     * @param {string} type 'before' ou 'after'
     */
    CardapioAdmin.addWhatsappMessage = function (type) {
        const container = document.getElementById(`whatsapp-list-${type}`);
        if (!container) return;

        const div = document.createElement('div');
        div.className = 'cardapio-admin-message-row';
        div.style.cssText = 'gap: 6px; margin-bottom: 6px; display: flex; align-items: center; width: 100%;';

        div.innerHTML = `
            <textarea class="cardapio-admin-input cardapio-admin-textarea" 
                      name="whatsapp_data[${type}][]" 
                      rows="2"
                      style="padding: 6px 10px; font-size: 0.85rem; background-color: #f8fafc; border: 1px solid #cbd5e1; width: 100%; min-height: 48px; resize: none;"
                      placeholder="Nova mensagem..."></textarea>
            <button type="button" class="cardapio-admin-btn" 
                    style="background: #fee2e2; color: #ef4444; padding: 0; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" 
                    onclick="this.parentElement.remove()">
                <i data-lucide="trash-2" size="14"></i>
            </button>
        `;

        container.appendChild(div);
        if (window.lucide) lucide.createIcons();

        // Foca no novo input
        const textarea = div.querySelector('textarea');
        if (textarea) textarea.focus();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/forms.js ========== */
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



})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/forms-tabs.js ========== */
/**
 * FORMS-TABS.JS - Sistema de Abas
 * 
 * Gerencia sistema de abas com persistência via Hash na URL.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

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

        // Verifica se o hash corresponde a uma aba existente
        const hasTab = document.querySelector(`.cardapio-admin-tab-btn[data-tab="${currentHash}"]`);

        if (currentHash && hasTab) {
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

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/forms-toggles.js ========== */
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


/* ========== cardapio-admin/forms-validation.js ========== */
/**
 * FORMS-VALIDATION.JS - Validação de Formulário
 * 
 * Gerencia validação HTML5 + feedback visual.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Validação HTML5 + feedback visual
     */
    CardapioAdmin.initValidation = function () {
        const form = document.querySelector('form');
        if (!form) return;

        // Adiciona validação nos campos críticos
        this.addValidation('whatsapp_number', {
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

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/forms-delivery.js ========== */
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


/* ========== cardapio-admin/forms-cards.js ========== */
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


/* ========== cardapio-admin/combos.js ========== */
/**
 * ============================================
 * CARDÁPIO ADMIN - Combos (Orquestrador)
 * 
 * Arquivo principal que registra o namespace.
 * Os módulos são carregados separadamente:
 * - combos-save.js (Salvamento AJAX)
 * - combos-edit.js (Edição/Cancel)
 * - combos-helpers.js (Clear/Summary/Toggle)
 * ============================================
 */

(function (CardapioAdmin) {
    'use strict';



})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/combos-save.js ========== */
/**
 * COMBOS-SAVE.JS - Salvamento de Combos via AJAX
 * 
 * Gerencia salvamento de combos (criar/atualizar).
 * Parte do módulo CardapioAdmin.
 * Refatorado para usar BASE_URL
 */

(function (CardapioAdmin) {
    'use strict';

    // Helper URL
    const getBaseUrl = () => typeof BASE_URL !== 'undefined' ? BASE_URL : '/cardapio-saas/public';

    /**
     * Salva Combo via AJAX (simula form submission)
     */
    CardapioAdmin.saveCombo = function () {
        const btn = document.querySelector('#comboFormContainer .cardapio-admin-btn-primary');
        if (!btn) return;

        const originalText = btn.innerHTML;

        // 1. Coleta dados básicos
        const nameInput = document.getElementById('combo_name');
        const priceInput = document.getElementById('combo_price');
        const descInput = document.getElementById('combo_description');
        const validityType = document.getElementById('combo_validity_type');
        const validUntilInput = document.getElementById('combo_valid_until');
        const imageInput = document.getElementById('combo_image');

        if (!nameInput || !priceInput) return;

        const name = nameInput.value.trim();
        const price = priceInput.value.trim();
        const desc = descInput ? descInput.value.trim() : '';
        let validUntil = validUntilInput ? validUntilInput.value : '';

        // Validação simples
        if (!name) {
            alert('Por favor, informe o nome do combo.');
            nameInput.focus();
            return;
        }
        if (!price) {
            alert('Por favor, informe o preço promocional.');
            priceInput.focus();
            return;
        }

        // Validação de data
        if (validityType) {
            if (validityType.value === 'today') {
                const today = new Date();
                validUntil = today.toISOString().split('T')[0];
            } else if (validityType.value === 'always') {
                validUntil = '';
            } else if (validityType.value === 'date' && !validUntil) {
                alert('Por favor, selecione a data de validade.');
                return;
            }
        }

        // 2. Coleta produtos selecionados (Com Quantidade)
        const selectedProducts = [];
        const allowAdditionals = {};

        document.querySelectorAll('.combo-product-qty').forEach(input => {
            const qty = parseInt(input.value) || 0;
            const pid = input.getAttribute('data-id');

            if (qty > 0) {
                for (let i = 0; i < qty; i++) {
                    selectedProducts.push(pid);
                }

                const toggle = document.querySelector(`.combo-additional-toggle[data-prod-id="${pid}"]`);
                if (toggle && toggle.checked) {
                    allowAdditionals[pid] = 1;
                }
            }
        });

        if (selectedProducts.length === 0) {
            alert('Selecione pelo menos um produto para o combo.');
            return;
        }

        // 3. Monta FormData
        const formData = new FormData();
        formData.append('name', name);
        formData.append('price', price);
        formData.append('description', desc);
        formData.append('display_order', 0);
        formData.append('is_active', 1);
        if (validUntil) formData.append('valid_until', validUntil);

        if (imageInput && imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }

        selectedProducts.forEach(pid => {
            formData.append('products[]', pid);
            if (allowAdditionals[pid]) {
                formData.append(`allow_additionals[${pid}]`, 1);
            }
        });

        // 4. Detecta modo edição e define URL
        const comboIdInput = document.getElementById('combo_id');
        const isEditMode = comboIdInput && comboIdInput.value && comboIdInput.value !== '';

        if (isEditMode) {
            formData.append('id', comboIdInput.value);
        }

        // URL robusta usando BASE_URL
        const endpoint = isEditMode
            ? '/admin/cardapio/combo/atualizar'
            : '/admin/cardapio/combo/salvar';

        const url = getBaseUrl() + endpoint;

        // 5. Envia via AJAX
        btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
        btn.disabled = true;

        // CSRF Token
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrf) {
            // FormData não aceita headers diretos no objeto, 
            // mas fetch aceita
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf
            },
            body: formData
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.reload();
                } else {
                    return response.text().then(text => { throw new Error(text || 'Erro desconhecido'); });
                }
            })
            .catch(error => {
                console.error('Erro ao salvar combo:', error);
                alert('Erro ao salvar combo. Verifique o console.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/combos-edit.js ========== */
/**
 * COMBOS-EDIT.JS - Edição de Combos
 * 
 * Gerencia carregamento e cancelamento de edição de combos.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Carrega dados de um combo existente para edição in-place
     * @param {number} comboId ID do combo a editar
     */
    CardapioAdmin.loadComboForEdit = function (comboId) {
        const mainForm = document.getElementById('formCardapio');
        let baseUrl = mainForm ? mainForm.action.replace('/salvar', '/combo/editar') : 'admin/loja/cardapio/combo/editar';
        baseUrl += `?id=${comboId}&json=1`;

        const formContainer = document.getElementById('comboFormContainer');
        if (formContainer) {
            formContainer.style.opacity = '0.6';
            formContainer.style.pointerEvents = 'none';
        }

        fetch(baseUrl)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erro ao carregar combo: ' + (data.error || 'Desconhecido'));
                    return;
                }

                const combo = data.combo;
                const items = data.items;

                // 1. Preencher campos do formulário
                document.getElementById('combo_id').value = combo.id;
                document.getElementById('combo_name').value = combo.name || '';
                document.getElementById('combo_description').value = combo.description || '';

                const priceInput = document.getElementById('combo_price');
                if (priceInput && combo.price) {
                    priceInput.value = parseFloat(combo.price).toFixed(2).replace('.', ',');
                }

                // Validade
                const validityType = document.getElementById('combo_validity_type');
                const validUntilInput = document.getElementById('combo_valid_until');
                if (validityType && validUntilInput) {
                    if (!combo.valid_until) {
                        validityType.value = 'always';
                        validUntilInput.style.display = 'none';
                    } else {
                        const today = new Date().toISOString().split('T')[0];
                        if (combo.valid_until === today) {
                            validityType.value = 'today';
                            validUntilInput.style.display = 'none';
                        } else {
                            validityType.value = 'date';
                            validUntilInput.value = combo.valid_until;
                            validUntilInput.style.display = 'block';
                        }
                    }
                }

                // 2. Limpar quantidades anteriores
                document.querySelectorAll('.combo-product-qty').forEach(input => {
                    input.value = 0;
                    const pid = input.getAttribute('data-id');
                    const display = document.getElementById('display_qty_' + pid);
                    const card = document.getElementById('card_prod_' + pid);
                    if (display) display.textContent = '0';
                    if (card) card.classList.remove('selected');
                });

                // 3. Preencher quantidades dos itens do combo
                items.forEach(item => {
                    const qtyInput = document.getElementById('qty_prod_' + item.id);
                    const display = document.getElementById('display_qty_' + item.id);
                    const card = document.getElementById('card_prod_' + item.id);
                    const toggle = document.querySelector(`.combo-additional-toggle[data-prod-id="${item.id}"]`);

                    if (qtyInput) qtyInput.value = item.qty;
                    if (display) display.textContent = item.qty;
                    if (card && item.qty > 0) card.classList.add('selected');
                    if (toggle) toggle.checked = item.allow_additionals == 1;
                });

                // 4. Atualizar preço original calculado
                if (typeof calculateComboOriginalPrice === 'function') {
                    calculateComboOriginalPrice();
                }

                // 5. Atualizar lista resumo
                this.updateComboItemsSummary(items);

                // 6. Atualizar UI do formulário para modo edição
                const title = document.getElementById('comboFormTitle');
                const subtitle = document.getElementById('comboFormSubtitle');
                const btnSaveText = document.getElementById('btnSaveComboText');
                const btnCancel = document.getElementById('btnCancelCombo');
                const summary = document.getElementById('comboItemsSummary');

                if (title) title.textContent = '✏️ Editar Combo: ' + combo.name;
                if (subtitle) subtitle.textContent = 'Modifique os dados e clique em Salvar Alterações.';
                if (btnSaveText) btnSaveText.textContent = 'Salvar Alterações';
                if (btnCancel) btnCancel.style.display = 'inline-flex';
                if (summary) summary.style.display = 'block';

                // 7. Scroll suave até o formulário
                formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(error => {
                console.error('Erro ao carregar combo:', error);
                alert('Erro de conexão ao carregar combo.');
            })
            .finally(() => {
                if (formContainer) {
                    formContainer.style.opacity = '1';
                    formContainer.style.pointerEvents = 'auto';
                }
            });
    };

    /**
     * Cancela edição e volta ao modo criação
     */
    CardapioAdmin.cancelComboEdit = function () {
        // 1. Limpar ID do combo
        const comboIdInput = document.getElementById('combo_id');
        if (comboIdInput) comboIdInput.value = '';

        // 2. Limpar campos do formulário
        document.getElementById('combo_name').value = '';
        document.getElementById('combo_description').value = '';
        document.getElementById('combo_price').value = '';
        document.getElementById('combo_original_price').value = '';

        const validityType = document.getElementById('combo_validity_type');
        const validUntilInput = document.getElementById('combo_valid_until');
        if (validityType) validityType.value = 'always';
        if (validUntilInput) {
            validUntilInput.value = '';
            validUntilInput.style.display = 'none';
        }

        // 3. Limpar quantidades
        this.clearComboItems();

        // 4. Restaurar UI para modo criação
        const title = document.getElementById('comboFormTitle');
        const subtitle = document.getElementById('comboFormSubtitle');
        const btnSaveText = document.getElementById('btnSaveComboText');
        const btnCancel = document.getElementById('btnCancelCombo');
        const summary = document.getElementById('comboItemsSummary');

        if (title) title.textContent = 'Criar Novo Combo';
        if (subtitle) subtitle.textContent = 'Configure sua oferta especial em uma única tela.';
        if (btnSaveText) btnSaveText.textContent = 'Salvar Promoção';
        if (btnCancel) btnCancel.style.display = 'none';
        if (summary) summary.style.display = 'none';

        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/combos-helpers.js ========== */
/**
 * COMBOS-HELPERS.JS - Funções Auxiliares de Combos
 * 
 * Gerencia funções auxiliares: clear, summary, toggle.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Limpa todos os itens selecionados do combo
     */
    CardapioAdmin.clearComboItems = function () {
        document.querySelectorAll('.combo-product-qty').forEach(input => {
            input.value = 0;
            const pid = input.getAttribute('data-id');
            const display = document.getElementById('display_qty_' + pid);
            const card = document.getElementById('card_prod_' + pid);
            if (display) display.textContent = '0';
            if (card) card.classList.remove('selected');
        });

        // Atualizar preço original
        if (typeof calculateComboOriginalPrice === 'function') {
            calculateComboOriginalPrice();
        }

        // Limpar lista resumo
        const list = document.getElementById('comboItemsList');
        if (list) list.innerHTML = '';
    };

    /**
     * Atualiza a lista visual de itens do combo
     * @param {Array} items Lista de itens com {id, name, qty}
     */
    CardapioAdmin.updateComboItemsSummary = function (items) {
        const list = document.getElementById('comboItemsList');
        if (!list) return;

        list.innerHTML = '';

        items.forEach(item => {
            if (item.qty > 0) {
                const li = document.createElement('li');
                li.style.cssText = 'background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 0.85rem; font-weight: 500;';
                li.textContent = `${item.qty}x ${item.name}`;
                list.appendChild(li);
            }
        });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});

// ==========================================
// FUNÇÃO GLOBAL - Toggle de Combo
// ==========================================
window.toggleComboActive = function (id, isActive) {
    fetch('admin/loja/cardapio/combo/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            active: isActive
        })
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Erro ao atualizar status: ' + (data.error || 'Desconhecido'));
                const checkbox = document.querySelector(`input[onchange*="${id}"]`);
                if (checkbox) checkbox.checked = !isActive;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro de conexão');
            const checkbox = document.querySelector(`input[onchange*="${id}"]`);
            if (checkbox) checkbox.checked = !isActive;
        });
};


/* ========== cardapio-admin/combos-ui.js ========== */
/**
 * COMBOS-UI.JS - Interface de Combos
 * 
 * Funções de interface para a aba de promoções/combos.
 * Extraído de _tab_promocoes.php
 */

// ==========================================
// FUNÇÕES DE INTERFACE
// ==========================================

/**
 * Toggle de visibilidade do campo de data de validade
 */
function toggleValidityDate() {
    const select = document.getElementById('combo_validity_type');
    const input = document.getElementById('combo_valid_until');

    if (select && input) {
        if (select.value === 'date') {
            input.style.display = 'block';
        } else {
            input.style.display = 'none';
        }
    }
}

/**
 * Alterna entre abas de categorias de produtos
 */
function toggleComboTab(btn, tabId) {
    // Remove active
    document.querySelectorAll('.combo-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.combo-tab-content').forEach(c => c.style.display = 'none');

    // Create active
    btn.classList.add('active');
    const content = document.getElementById(tabId);
    if (content) content.style.display = 'grid';
}

/**
 * Atualiza quantidade de um produto no combo
 */
function updateComboQty(id, change) {
    const qtyInput = document.getElementById('qty_prod_' + id);
    const display = document.getElementById('display_qty_' + id);
    const card = document.getElementById('card_prod_' + id);

    if (!qtyInput || !display) return;

    let current = parseInt(qtyInput.value) || 0;
    let newValue = current + change;

    if (newValue < 0) newValue = 0;

    qtyInput.value = newValue;
    display.textContent = newValue;

    // Visual update
    if (newValue > 0) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }

    calculateComboOriginalPrice();
}

/**
 * Calcula e exibe o preço original somando os produtos selecionados
 */
function calculateComboOriginalPrice() {
    let total = 0;

    document.querySelectorAll('.combo-product-qty').forEach(input => {
        const qty = parseInt(input.value) || 0;
        const price = parseFloat(input.getAttribute('data-price') || 0);
        total += qty * price;
    });

    // Format Currency Br
    const formatter = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('combo_original_price').value = formatter.format(total);
}

/**
 * Preview de imagem do combo antes de upload
 */
function previewComboImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('combo_image_preview').innerHTML = `
                <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
            `;
        }
        reader.readAsDataURL(input.files[0]);
    }
}




/* ========== cardapio-admin/featured.js ========== */
/**
 * ============================================
 * CARDÁPIO ADMIN - Featured (Orquestrador)
 * 
 * Arquivo principal que registra o namespace Destaques.
 * Os módulos são carregados separadamente:
 * - featured-edit.js (Modo edição)
 * - featured-dragdrop.js (Drag and Drop)
 * - featured-tabs.js (Abas/Highlight)
 * - featured-categories.js (Ordenação categorias)
 * ============================================
 */

(function (CardapioAdmin) {
    'use strict';

    // Cria namespace Destaques se não existir
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {
        draggedItem: null
    };



})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/featured-edit.js ========== */
/**
 * FEATURED-EDIT.JS - Modo de Edição de Destaques
 * 
 * Gerencia ativação/cancelamento/save do modo edição.
 * Parte do namespace CardapioAdmin.Destaques.
 */

(function (CardapioAdmin) {
    'use strict';

    // Garante namespace
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {};

    /**
     * Ativa modo de edição para produtos
     */
    CardapioAdmin.Destaques.enableEditMode = function () {
        const container = document.querySelector('.cardapio-admin-destaques-content-wrapper');
        const editBtn = document.querySelector('.cardapio-admin-btn-edit');
        const saveGroup = document.querySelector('.cardapio-admin-save-group');
        const viewHint = document.querySelector('.view-hint');
        const editHint = document.querySelector('.edit-hint');

        if (container) {
            container.classList.remove('disabled-overlay');
            container.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(card => {
                card.setAttribute('draggable', 'true');
                const handle = card.querySelector('.cardapio-admin-destaques-drag-handle');
                if (handle) handle.style.display = 'block';
            });
        }

        if (editBtn) editBtn.style.display = 'none';
        if (saveGroup) saveGroup.style.display = 'flex';
        if (viewHint) viewHint.style.display = 'none';
        if (editHint) editHint.style.display = 'inline';

        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    /**
     * Cancela edição
     */
    CardapioAdmin.Destaques.cancelEditMode = function () {
        window.location.reload();
    };

    /**
     * Salva alterações de destaques (ordem e seleção)
     */
    CardapioAdmin.Destaques.saveDestaques = function () {
        const form = document.getElementById('formCardapio');
        if (form) form.submit();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/featured-dragdrop.js ========== */
/**
 * FEATURED-DRAGDROP.JS - Drag and Drop de Destaques
 * 
 * Gerencia drag and drop de cards de produtos.
 * Parte do namespace CardapioAdmin.Destaques.
 */

(function (CardapioAdmin) {
    'use strict';

    // Garante namespace
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {};
    CardapioAdmin.Destaques.draggedItem = null;

    CardapioAdmin.Destaques.dragStart = function (e) {
        CardapioAdmin.Destaques.draggedItem = e.currentTarget;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
        setTimeout(() => {
            CardapioAdmin.Destaques.draggedItem.classList.add('dragging');
        }, 0);
    };

    CardapioAdmin.Destaques.dragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        const target = e.currentTarget;
        const draggedItem = CardapioAdmin.Destaques.draggedItem;

        if (target && draggedItem && target !== draggedItem && target.classList.contains('cardapio-admin-destaques-product-card')) {
            document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
            target.classList.add('drag-over');
        }
    };

    CardapioAdmin.Destaques.drop = function (e) {
        e.stopPropagation();
        e.preventDefault();

        const target = e.currentTarget;
        const draggedItem = CardapioAdmin.Destaques.draggedItem;

        if (draggedItem && target && draggedItem !== target) {
            const sourceArea = draggedItem.closest('.cardapio-admin-destaques-products-grid');
            const targetArea = target.closest('.cardapio-admin-destaques-products-grid');

            if (sourceArea === targetArea) {
                const allCards = Array.from(targetArea.querySelectorAll('.cardapio-admin-destaques-product-card'));
                const draggedIndex = allCards.indexOf(draggedItem);
                const targetIndex = allCards.indexOf(target);

                if (draggedIndex < targetIndex) {
                    targetArea.insertBefore(draggedItem, target.nextSibling);
                } else {
                    targetArea.insertBefore(draggedItem, target);
                }

                CardapioAdmin.Destaques.updateProductOrder(targetArea);
            }
        }

        document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
        return false;
    };

    CardapioAdmin.Destaques.dragEnd = function (e) {
        const draggedItem = CardapioAdmin.Destaques.draggedItem;
        if (draggedItem) {
            draggedItem.classList.remove('dragging');
        }
        document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
        CardapioAdmin.Destaques.draggedItem = null;
    };

    CardapioAdmin.Destaques.updateProductOrder = function (container) {
        const cards = container.querySelectorAll('.cardapio-admin-destaques-product-card');

        cards.forEach((card, index) => {
            const productId = card.dataset.productId;
            const newValue = index;

            const inputs = document.querySelectorAll(`[data-order-input="${productId}"]`);
            inputs.forEach(input => {
                input.value = newValue;
            });
        });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/featured-tabs.js ========== */
/**
 * FEATURED-TABS.JS - Abas e Highlight de Destaques
 * 
 * Gerencia troca de abas e toggle de highlight em produtos.
 * Parte do namespace CardapioAdmin.Destaques.
 */

(function (CardapioAdmin) {
    'use strict';

    // Garante namespace
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {};

    /**
     * Troca entre abas de categorias
     */
    CardapioAdmin.Destaques.switchTab = function (categoryName) {
        const tabs = document.querySelectorAll('.cardapio-admin-destaques-tab-btn');
        tabs.forEach(tab => {
            if (tab.dataset.categoryTab === categoryName) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });

        const contents = document.querySelectorAll('.cardapio-admin-destaques-tab-content');
        contents.forEach(content => {
            if (content.dataset.categoryContent === categoryName) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    /**
     * Adiciona/remove produto dos destaques
     */
    CardapioAdmin.Destaques.toggleHighlight = function (productId) {
        const cards = document.querySelectorAll(`[data-product-id="${productId}"]`);
        const inputs = document.querySelectorAll(`[data-featured-input="${productId}"]`);

        if (inputs.length === 0) return;

        const isCurrentlyFeatured = inputs[0].checked;

        inputs.forEach(input => {
            input.checked = !isCurrentlyFeatured;
        });

        cards.forEach(card => {
            const btn = card.querySelector('.cardapio-admin-destaques-highlight-btn');
            const star = card.querySelector('.cardapio-admin-destaques-star');

            if (!isCurrentlyFeatured) {
                card.classList.add('featured');
                if (btn) {
                    btn.classList.add('active');
                    btn.innerHTML = '<i data-lucide="x" style="width: 16px; height: 16px;"></i> Remover';
                }
                if (!star) {
                    const info = card.querySelector('.cardapio-admin-destaques-product-info');
                    if (info) {
                        const newStar = document.createElement('span');
                        newStar.className = 'cardapio-admin-destaques-star';
                        newStar.textContent = '⭐';
                        info.insertBefore(newStar, info.firstChild);
                    }
                }
            } else {
                card.classList.remove('featured');
                if (btn) {
                    btn.classList.remove('active');
                    btn.innerHTML = '<i data-lucide="star" style="width: 16px; height: 16px;"></i> Destacar';
                }
                if (star) star.remove();
            }
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
        this.refreshFeaturedTab();
    };

    /**
     * Atualiza a aba Destaques
     */
    CardapioAdmin.Destaques.refreshFeaturedTab = function () {
        const featuredContent = document.querySelector('[data-category-content="featured"]');
        if (!featuredContent) return;

        let grid = featuredContent.querySelector('.cardapio-admin-destaques-products-grid');
        const emptyMsg = featuredContent.querySelector('.cardapio-admin-destaques-empty');

        if (!grid) {
            grid = document.createElement('div');
            grid.className = 'cardapio-admin-destaques-products-grid';
            grid.dataset.sortableArea = 'featured';
            featuredContent.insertBefore(grid, emptyMsg);
        }

        const allFeaturedInputs = document.querySelectorAll('[data-featured-input]');

        allFeaturedInputs.forEach(input => {
            const productId = input.dataset.featuredInput;
            const isChecked = input.checked;
            const existsInFeaturedTab = grid.querySelector(`[data-product-id="${productId}"]`);

            if (isChecked && !existsInFeaturedTab) {
                const sourceCard = document.querySelector(`[data-category-content]:not([data-category-content="featured"]) [data-product-id="${productId}"]`);
                if (sourceCard) {
                    const clonedCard = sourceCard.cloneNode(true);
                    const btn = clonedCard.querySelector('.cardapio-admin-destaques-highlight-btn');
                    if (btn) {
                        btn.classList.add('active');
                        btn.innerHTML = '<i data-lucide="x" style="width: 16px; height: 16px;"></i> Remover';
                    }
                    const info = clonedCard.querySelector('.cardapio-admin-destaques-product-info');
                    if (info && !info.querySelector('.cardapio-admin-destaques-star')) {
                        const star = document.createElement('span');
                        star.className = 'cardapio-admin-destaques-star';
                        star.textContent = '⭐';
                        info.insertBefore(star, info.firstChild);
                    }
                    clonedCard.querySelectorAll('input').forEach(inp => inp.remove());
                    grid.appendChild(clonedCard);
                }
            } else if (!isChecked && existsInFeaturedTab) {
                existsInFeaturedTab.remove();
            }
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();

        const remainingCards = grid.querySelectorAll('.cardapio-admin-destaques-product-card').length;
        if (remainingCards === 0) {
            grid.style.display = 'none';
            if (emptyMsg) {
                emptyMsg.style.display = 'block';
            } else {
                grid.insertAdjacentHTML('afterend', '<p class="cardapio-admin-destaques-empty">Nenhum produto em destaque. Use as outras abas para adicionar.</p>');
            }
        } else {
            grid.style.display = 'grid';
            if (emptyMsg) emptyMsg.style.display = 'none';
        }
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/featured-categories.js ========== */
/**
 * FEATURED-CATEGORIES.JS - Ordenação de Categorias
 * 
 * Gerencia ordenação de categorias na aba Destaques.
 * Parte do namespace CardapioAdmin.Destaques.
 */

(function (CardapioAdmin) {
    'use strict';

    // Garante namespace
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {};

    /**
     * Move categoria para cima ou para baixo
     */
    CardapioAdmin.Destaques.moveCategory = function (categoryId, direction) {
        const list = document.getElementById('categoryList');
        if (!list) return;

        const currentRow = list.querySelector(`[data-category-id="${categoryId}"]`);
        if (!currentRow) return;

        const rows = Array.from(list.querySelectorAll('.cardapio-admin-destaques-category-row'));
        const currentIndex = rows.indexOf(currentRow);

        if (direction === 'up' && currentIndex > 0) {
            const prevRow = rows[currentIndex - 1];
            list.insertBefore(currentRow, prevRow);
        } else if (direction === 'down' && currentIndex < rows.length - 1) {
            const nextRow = rows[currentIndex + 1];
            list.insertBefore(nextRow, currentRow);
        }

        this.updateCategoryOrder();
    };

    /**
     * Atualiza os inputs hidden com a nova ordem
     */
    CardapioAdmin.Destaques.updateCategoryOrder = function () {
        const list = document.getElementById('categoryList');
        if (!list) return;

        const rows = Array.from(list.querySelectorAll('.cardapio-admin-destaques-category-row'));

        rows.forEach((row, index) => {
            const input = row.querySelector('[data-order-input]');
            if (input) input.value = index;

            const btnUp = row.querySelector('.cardapio-admin-destaques-arrow-btn:first-child');
            const btnDown = row.querySelector('.cardapio-admin-destaques-arrow-btn:last-child');

            if (btnUp) btnUp.disabled = (index === 0);
            if (btnDown) btnDown.disabled = (index === rows.length - 1);
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});


/* ========== cardapio-admin/index.js ========== */
/**
 * ============================================
 * CARDÁPIO ADMIN - Bootstrap (v2.0 Modular)
 * Arquivo: public/js/cardapio-admin/index.js
 * 
 * REGRA: Este arquivo apenas inicializa.
 * Toda lógica está em módulos separados.
 * ============================================
 */

// Garante que o objeto existe (outros módulos já podem ter criado)
window.CardapioAdmin = window.CardapioAdmin || {};

/**
 * Inicializa todo o módulo
 */
window.CardapioAdmin.init = function () {
    this.initTabs();
    this.initToggles();
    this.initValidation();
    this.initLoader();
    this.initPixMask();

    // [ETAPA 5] Aplicar máscara inicial no telefone (se já tiver valor do banco)
    const waInput = document.getElementById('whatsapp_number');
    if (waInput && waInput.value) {
        this.maskPhone(waInput);
    }
};



