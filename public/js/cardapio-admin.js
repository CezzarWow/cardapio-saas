/**
 * ============================================
 * CARDÁPIO ADMIN - JavaScript (v1.2)
 * Arquivo: public/js/cardapio-admin.js
 * 
 * REGRA: Namespace window.CardapioAdmin
 * ============================================
 */

window.CardapioAdmin = {

    /**
     * Inicializa todo o módulo
     */
    init() {
        this.initTabs();
        this.initToggles();
        this.initValidation();
        this.initLoader();

        // [ETAPA 5] Aplicar máscara inicial no telefone (se já tiver valor do banco)
        const waInput = document.getElementById('whatsapp_number');
        if (waInput && waInput.value) {
            this.maskPhone(waInput);
        }

        console.log('✅ CardapioAdmin v1.2 inicializado');
    },

    /**
     * Sistema de abas com persistência via Hash
     */
    initTabs() {
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
    },

    /**
     * Loader no botão salvar
     */
    initLoader() {
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
            document.querySelectorAll('.delivery-field').forEach(f => f.disabled = false);

            // Mostra loader
            const originalText = btnSave.innerHTML;
            btnSave.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
            btnSave.disabled = true;
            btnSave.style.opacity = '0.7';
            btnSave.style.cursor = 'wait';

            // Re-renderiza ícones para o loader aparecer
            if (window.lucide) lucide.createIcons();
        });
    },

    /**
     * Toggles condicionais (mostra/esconde + habilita/desabilita campos)
     */
    initToggles() {
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
    },

    /**
     * Configura toggle com seção dependente
     */
    setupToggleSection(toggleId, sectionId, fieldIds, disabledMsgId = null) {
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
    },

    /**
     * Validação HTML5 + feedback visual
     */
    initValidation() {
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
    },

    /**
     * Adiciona atributos de validação a um campo
     */
    addValidation(fieldId, attrs) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        Object.keys(attrs).forEach(attr => {
            field.setAttribute(attr, attrs[attr]);
        });
    },

    /**
     * Validação antes de submit
     */
    validateForm() {
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
    },

    /**
     * Destaca campo com erro
     */
    highlightError(field) {
        if (!field) return;
        field.style.borderColor = '#ef4444';
        field.focus();

        setTimeout(() => {
            field.style.borderColor = '';
        }, 3000);
    },

    /**
     * [ETAPA 2] Toggle de linha de horário
     */
    toggleHourRow(dayNum) {
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
    },

    /**
     * [ETAPA 5] Máscara de Telefone BR (9 ou 10 dígitos)
     */
    maskPhone(input) {
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
    },

    /**
     * [ETAPA 5] Adicionar nova mensagem de WhatsApp
     */
    addWhatsappMessage() {
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
    },

    /**
     * [ETAPA 5] Inicia edição do WhatsApp
     */
    startWaEdit() {
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
    },

    /**
     * [ETAPA 5] Aplica (trava) as alterações do WhatsApp
     */
    applyWaEdit() {
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
    },

    /**
     * [ETAPA 5] Inicia edição dos campos Delivery
     */
    startDeliveryEdit() {
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
    },

    /**
     * [ETAPA 5] Aplica (trava) as alterações de Delivery
     */
    applyDeliveryEdit() {
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
    }
};

// Auto-inicializa quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.CardapioAdmin.init();
});
