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
        this.initPixMask();

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
    },

    /**
     * [NOVO] Salva configurações automaticamente
     */
    saveSettings() {
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
    },

    /**
     * [NOVO] Adiciona mensagem do WhatsApp na lista específica
     * @param {string} type 'before' ou 'after'
     */
    addWhatsappMessage(type) {
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
    },



    /**
     * [NOVO] Toggle Editar/Aplicar/Cancelar para cards
     */
    toggleCardEdit(cardName, action = 'toggle') {
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
    },

    /**
     * Cancela edição de um card (reverte e trava)
     */
    cancelCardEdit(cardName) {
        // Recarrega a página para reverter os valores (simples e seguro)
        // Alternativa: armazenar valores originais e restaurar
        this.toggleCardEdit(cardName, 'cancel');
    },

    /**
     * [NOVO] Máscaras Dinâmicas para PIX
     */
    initPixMask() {
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
    },

    maskCPF(v) {
        v = v.replace(/\D/g, "");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        return v.substring(0, 14);
    },

    maskCNPJ(v) {
        v = v.replace(/\D/g, "");
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        return v.substring(0, 18);
    },

    maskPhoneValue(v) {
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
    },

    // [DESTAQUES] Namespace para aba Destaques
    Destaques: {
        draggedItem: null,

        /**
         * Ativa modo de edição para produtos
         */
        enableEditMode: function () {
            const container = document.querySelector('.cardapio-admin-destaques-content-wrapper');
            const editBtn = document.querySelector('.cardapio-admin-btn-edit');
            const saveGroup = document.querySelector('.cardapio-admin-save-group');
            const viewHint = document.querySelector('.view-hint');
            const editHint = document.querySelector('.edit-hint');

            if (container) {
                container.classList.remove('disabled-overlay');
                // Habilita drag and drop
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

            // Re-inicializa ícones pois alteramos visibilidade
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        /**
         * Cancela edição
         */
        cancelEditMode: function () {
            // Recarrega a página para desfazer alterações visuais
            window.location.reload();
        },

        /**
         * Salva alterações de destaques (ordem e seleção)
         */
        saveDestaques: function () {
            // Submete o formulário principal
            const form = document.getElementById('formCardapio');
            if (form) form.submit();
        },

        /* --- Drag and Drop Logic --- */

        draggedItem: null,

        dragStart: function (e) {
            CardapioAdmin.Destaques.draggedItem = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', ''); // Necessário para Firefox
            setTimeout(() => {
                CardapioAdmin.Destaques.draggedItem.classList.add('dragging');
            }, 0);
        },

        dragOver: function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const target = e.currentTarget;
            const draggedItem = CardapioAdmin.Destaques.draggedItem;

            if (target && draggedItem && target !== draggedItem && target.classList.contains('cardapio-admin-destaques-product-card')) {
                // Remove indicador de outros
                document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
                target.classList.add('drag-over');
            }
        },

        drop: function (e) {
            e.stopPropagation();
            e.preventDefault();

            const target = e.currentTarget;
            const draggedItem = CardapioAdmin.Destaques.draggedItem;

            if (draggedItem && target && draggedItem !== target) {
                // Verifica se estão na mesma área (aba)
                const sourceArea = draggedItem.closest('.cardapio-admin-destaques-products-grid');
                const targetArea = target.closest('.cardapio-admin-destaques-products-grid');

                if (sourceArea === targetArea) {
                    // Determina a posição relativa
                    const allCards = Array.from(targetArea.querySelectorAll('.cardapio-admin-destaques-product-card'));
                    const draggedIndex = allCards.indexOf(draggedItem);
                    const targetIndex = allCards.indexOf(target);

                    if (draggedIndex < targetIndex) {
                        // Movendo para baixo - insere depois do target
                        targetArea.insertBefore(draggedItem, target.nextSibling);
                    } else {
                        // Movendo para cima - insere antes do target
                        targetArea.insertBefore(draggedItem, target);
                    }

                    // Atualiza input hidden de ordem
                    CardapioAdmin.Destaques.updateProductOrder(targetArea);
                }
            }

            // Limpa estados
            document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
            return false;
        },

        dragEnd: function (e) {
            const draggedItem = CardapioAdmin.Destaques.draggedItem;
            if (draggedItem) {
                draggedItem.classList.remove('dragging');
            }
            document.querySelectorAll('.cardapio-admin-destaques-product-card').forEach(c => c.classList.remove('drag-over'));
            CardapioAdmin.Destaques.draggedItem = null;
        },

        updateProductOrder: function (container) {
            const cards = container.querySelectorAll('.cardapio-admin-destaques-product-card');

            // Se estamos na aba Destaques (ou qualquer aba), a ordem visual aqui define o valor 'display_order'
            // Mas cuidado: se for aba Destaques, estamos definindo ordem entre eles.
            // Se for aba Categoria, estamos definindo ordem total.

            // Para resolver o problema de "não salva", vamos garantir que ao mover, TODOS os inputs deste produto recebam o novo valor.
            // Mas o valor do índice depende do contexto.

            // ESTRATÉGIA:
            // Vamos assumir que se o usuário está ordenando, ele quer essa prioridade.
            // Vamos atualizar apenas o input deste card por enquanto, mas vamos garantir que no SUBMIT, inputs duplicados não atrapalhem.
            // OU melhor: Vamos forçar que o input da aba Categoria receba o valor se mexermos na aba Destaque?

            // Se mexermos na aba Destaques, os itens ganham indices 0, 1, 2...
            // Se aplicarmos isso nos inputs da aba Categoria, esses produtos vão pro topo das categorias. Isso é bom.

            cards.forEach((card, index) => {
                const productId = card.dataset.productId;
                const newValue = index;

                // Atualiza TODOS os inputs referentes a este produto em qualquer aba
                // Input de ordem
                const inputs = document.querySelectorAll(`[data-order-input="${productId}"]`);
                inputs.forEach(input => {
                    input.value = newValue;
                });
            });
        },

        /* --- Tab Logic --- */

        /**
         * Troca entre abas de categorias
         * @param {string} categoryName Nome da categoria ou 'featured'
         */
        switchTab: function (categoryName) {
            // Atualiza botões das abas
            const tabs = document.querySelectorAll('.cardapio-admin-destaques-tab-btn');
            tabs.forEach(tab => {
                if (tab.dataset.categoryTab === categoryName) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            // Atualiza conteúdo das abas
            const contents = document.querySelectorAll('.cardapio-admin-destaques-tab-content');
            contents.forEach(content => {
                if (content.dataset.categoryContent === categoryName) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });

            // Re-inicializa ícones Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        },

        /**
         * Adiciona/remove produto dos destaques
         * @param {number} productId ID do produto
         */
        toggleHighlight: function (productId) {
            // Busca todos os cards e inputs deste produto
            const cards = document.querySelectorAll(`[data-product-id="${productId}"]`);
            // Buscar TODOS os inputs para este produto
            const inputs = document.querySelectorAll(`[data-featured-input="${productId}"]`);

            if (inputs.length === 0) return;

            // Verifica estado atual pelo primeiro input
            const isCurrentlyFeatured = inputs[0].checked;

            // Inverte estado em TODOS os inputs
            inputs.forEach(input => {
                input.checked = !isCurrentlyFeatured;
            });

            // Atualiza todos os cards deste produto
            cards.forEach(card => {
                const btn = card.querySelector('.cardapio-admin-destaques-highlight-btn');
                const star = card.querySelector('.cardapio-admin-destaques-star');

                if (!isCurrentlyFeatured) {
                    // Adicionando ao destaque
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
                    // Removendo do destaque
                    card.classList.remove('featured');
                    if (btn) {
                        btn.classList.remove('active');
                        btn.innerHTML = '<i data-lucide="star" style="width: 16px; height: 16px;"></i> Destacar';
                    }
                    if (star) star.remove();
                }
            });

            // Re-inicializa ícones Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Atualiza aba Destaques
            this.refreshFeaturedTab();
        },

        /**
         * Atualiza a aba Destaques - adiciona novos e remove os desmarcados
         */
        refreshFeaturedTab: function () {
            const featuredContent = document.querySelector('[data-category-content="featured"]');
            if (!featuredContent) return;

            let grid = featuredContent.querySelector('.cardapio-admin-destaques-products-grid');
            const emptyMsg = featuredContent.querySelector('.cardapio-admin-destaques-empty');

            // Cria o grid se não existir
            if (!grid) {
                grid = document.createElement('div');
                grid.className = 'cardapio-admin-destaques-products-grid';
                grid.dataset.sortableArea = 'featured';
                featuredContent.insertBefore(grid, emptyMsg);
            }

            // Busca todos os inputs featured marcados
            const allFeaturedInputs = document.querySelectorAll('[data-featured-input]');

            allFeaturedInputs.forEach(input => {
                const productId = input.dataset.featuredInput;
                const isChecked = input.checked;
                const existsInFeaturedTab = grid.querySelector(`[data-product-id="${productId}"]`);

                if (isChecked && !existsInFeaturedTab) {
                    // Produto foi destacado - adicionar à aba Destaques
                    const sourceCard = document.querySelector(`[data-category-content]:not([data-category-content="featured"]) [data-product-id="${productId}"]`);
                    if (sourceCard) {
                        const clonedCard = sourceCard.cloneNode(true);
                        // Ajusta o botão para mostrar "Remover"
                        const btn = clonedCard.querySelector('.cardapio-admin-destaques-highlight-btn');
                        if (btn) {
                            btn.classList.add('active');
                            btn.innerHTML = '<i data-lucide="x" style="width: 16px; height: 16px;"></i> Remover';
                        }
                        // Adiciona estrela se não tiver
                        const info = clonedCard.querySelector('.cardapio-admin-destaques-product-info');
                        if (info && !info.querySelector('.cardapio-admin-destaques-star')) {
                            const star = document.createElement('span');
                            star.className = 'cardapio-admin-destaques-star';
                            star.textContent = '⭐';
                            info.insertBefore(star, info.firstChild);
                        }
                        // Remove inputs do clone (evita duplicatas)
                        clonedCard.querySelectorAll('input').forEach(inp => inp.remove());
                        grid.appendChild(clonedCard);
                    }
                } else if (!isChecked && existsInFeaturedTab) {
                    // Produto foi removido do destaque - remover da aba Destaques
                    existsInFeaturedTab.remove();
                }
            });

            // Re-inicializa ícones Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Mostra/esconde mensagem vazia
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
        },

        /**
         * Move categoria para cima ou para baixo
         * @param {number} categoryId ID da categoria
         * @param {string} direction 'up' ou 'down'
         */
        moveCategory: function (categoryId, direction) {
            const list = document.getElementById('categoryList');
            if (!list) return;

            const currentRow = list.querySelector(`[data-category-id="${categoryId}"]`);
            if (!currentRow) return;

            const rows = Array.from(list.querySelectorAll('.cardapio-admin-destaques-category-row'));
            const currentIndex = rows.indexOf(currentRow);

            if (direction === 'up' && currentIndex > 0) {
                // Troca com o anterior
                const prevRow = rows[currentIndex - 1];
                list.insertBefore(currentRow, prevRow);
            } else if (direction === 'down' && currentIndex < rows.length - 1) {
                // Troca com o próximo
                const nextRow = rows[currentIndex + 1];
                list.insertBefore(nextRow, currentRow);
            }

            // Atualiza inputs hidden e botões
            this.updateCategoryOrder();
        },

        /**
         * Atualiza os inputs hidden com a nova ordem
         * e desabilita/habilita setas conforme posição
         */
        updateCategoryOrder: function () {
            const list = document.getElementById('categoryList');
            if (!list) return;

            const rows = Array.from(list.querySelectorAll('.cardapio-admin-destaques-category-row'));

            rows.forEach((row, index) => {
                // Atualiza input hidden
                const input = row.querySelector('[data-order-input]');
                if (input) input.value = index;

                // Atualiza botões
                const btnUp = row.querySelector('.cardapio-admin-destaques-arrow-btn:first-child');
                const btnDown = row.querySelector('.cardapio-admin-destaques-arrow-btn:last-child');

                if (btnUp) btnUp.disabled = (index === 0);
                if (btnDown) btnDown.disabled = (index === rows.length - 1);
            });

            // Re-inicializa ícones Lucide nos botões
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    },

    /**
     * [NOVO] Salva Combo via AJAX (simula form submission)
     */
    saveCombo: function () {
        const btn = document.querySelector('#comboFormContainer .cardapio-admin-btn-primary');
        if (!btn) return;

        const originalText = btn.innerHTML;

        // 1. Coleta dados básicos
        const nameInput = document.getElementById('combo_name');
        const priceInput = document.getElementById('combo_price');
        const originalPrice = document.getElementById('combo_original_price');
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
                // Adiciona o ID N vezes
                for (let i = 0; i < qty; i++) {
                    selectedProducts.push(pid);
                }

                // Verifica toggle de adicionais
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
        formData.append('display_order', 0); // Padrão
        formData.append('is_active', 1);
        if (validUntil) formData.append('valid_until', validUntil);

        if (imageInput && imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }

        // Produtos (array)
        selectedProducts.forEach(pid => {
            formData.append('products[]', pid);
            // Allow Additionals é enviado apenas uma vez por PID (backend map logic)
            if (allowAdditionals[pid]) {
                formData.append(`allow_additionals[${pid}]`, 1);
            }
        });

        // 4. Envia via AJAX (Fetch)
        btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Salvando...';
        btn.disabled = true;

        // Detecta URL base do form principal e ajusta para combo/salvar
        const mainForm = document.getElementById('formCardapio');
        let baseUrl = mainForm ? mainForm.action.replace('/salvar', '/combo/salvar') : window.location.href;

        // Fallback se URL falhar
        if (!baseUrl.includes('combo/salvar')) {
            // Tenta construir relativo
            baseUrl = 'cardapio/combo/salvar';
        }

        fetch(baseUrl, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Erro ao salvar combo. Tente novamente.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro de conexão ao salvar combo.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }

};

// Auto-inicializa quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.CardapioAdmin.init();
});
