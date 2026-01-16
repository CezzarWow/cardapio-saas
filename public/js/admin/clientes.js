/**
 * CLIENTES.JS - Gestão de Clientes (Orquestrador)
 * 
 * Módulo principal de gerenciamento de clientes.
 * Coordena máscaras de input, validação e comunicação com API.
 * 
 * Dependências: 
 *   - InputMasks (shared/masks.js)
 *   - ClientValidator (admin/client-validator.js)
 * 
 * Exporta: 
 *   - window.ClientManager
 *   - window.openNewClientModal(type)
 *   - window.closeSuperClientModal()
 *   - window.saveSuperClient()
 */

(function () {
    'use strict';

    var ClientManager = {
        // ==========================================
        // ESTADO
        // ==========================================
        state: {
            currentType: 'PF' // PF ou PJ
        },

        // ==========================================
        // INICIALIZAÇÃO
        // ==========================================
        init: function () {
            this._bindMasks();
            this._bindNameValidation();
            this._bindLimitInput();
        },

        // ==========================================
        // BINDINGS PRIVADOS
        // ==========================================

        /**
         * Aplica máscaras aos inputs
         */
        _bindMasks: function () {
            var self = this;

            // Telefone e CEP
            InputMasks.applyTo('cli_phone', InputMasks.phone);
            InputMasks.applyTo('cli_zip', InputMasks.zip);

            // CPF/CNPJ dinâmico baseado no tipo
            var docInput = document.getElementById('cli_doc');
            if (docInput) {
                docInput.addEventListener('input', function (e) {
                    var maskFn = self.state.currentType === 'PF' ? InputMasks.cpf : InputMasks.cnpj;
                    e.target.value = maskFn(e.target.value);
                });
            }
        },

        /**
         * Configura validação do nome (Title Case + Duplicidade)
         */
        _bindNameValidation: function () {
            var nameInput = document.getElementById('cli_name');
            if (!nameInput) return;

            // Input: Aplica Title Case e limpa erros
            nameInput.addEventListener('input', function (e) {
                // Preserva posição do cursor
                var start = e.target.selectionStart;
                var oldVal = e.target.value;
                var newVal = InputMasks.titleCase(oldVal);

                if (oldVal !== newVal) {
                    e.target.value = newVal;
                    e.target.setSelectionRange(start, start);
                }

                // Limpa erro visual ao digitar
                ClientValidator.clearError(nameInput);
            });

            // Blur: Verifica duplicidade
            nameInput.addEventListener('blur', async function (e) {
                var name = e.target.value.trim();
                var isDuplicate = await ClientValidator.checkDuplicate(name);

                if (isDuplicate) {
                    ClientValidator.showError(nameInput, 'Este cliente já está cadastrado.');
                }
            });
        },

        /**
         * Configura input de limite de crédito e dia de vencimento
         */
        _bindLimitInput: function () {
            // Limite de crédito
            InputMasks.applyTo('cli_limit', InputMasks.currency);

            // Dia de vencimento (1-31)
            var dueInput = document.getElementById('cli_due');
            if (dueInput) {
                dueInput.addEventListener('input', function (e) {
                    var val = parseInt(e.target.value);
                    if (val > 31) e.target.value = 31;
                    if (val < 1) e.target.value = '';
                });
            }
        },

        // ==========================================
        // UI - MODAL
        // ==========================================

        /**
         * Abre o modal de cadastro
         * @param {string} type - 'PF' ou 'PJ'
         */
        openModal: function (type) {
            var modal = document.getElementById('superClientModal');
            if (!modal) return;

            this.resetForm();
            modal.style.display = 'flex';
            this.setType(type);
            document.getElementById('cli_name').focus();
        },

        /**
         * Fecha o modal de cadastro
         */
        closeModal: function () {
            var modal = document.getElementById('superClientModal');
            if (modal) modal.style.display = 'none';
            this.resetForm();
        },

        /**
         * Limpa todos os campos do formulário
         */
        resetForm: function () {
            var ids = [
                'cli_name', 'cli_doc', 'cli_phone', 'cli_zip',
                'cli_addr', 'cli_num', 'cli_bairro', 'cli_city',
                'cli_limit', 'cli_due'
            ];

            ids.forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.value = '';
            });

            // Limpa erros visuais
            var nameInput = document.getElementById('cli_name');
            if (nameInput) ClientValidator.clearError(nameInput);
        },

        /**
         * Configura o tipo (PF ou PJ) e atualiza labels
         * @param {string} type - 'PF' ou 'PJ'
         */
        setType: function (type) {
            this.state.currentType = type;
            var isPF = type === 'PF';

            // Labels
            var lblName = document.getElementById('lbl-name');
            var lblDoc = document.getElementById('lbl-doc');
            var subtitle = document.getElementById('modal-subtitle');
            var headerDados = document.getElementById('header-dados');

            if (lblName) {
                lblName.innerHTML = isPF
                    ? 'Nome Completo <span style="color:#ef4444">*</span>'
                    : 'Razão Social <span style="color:#ef4444">*</span>';
            }

            if (lblDoc) {
                lblDoc.innerHTML = isPF ? 'CPF (Opcional)' : 'CNPJ (Opcional)';
            }

            if (subtitle) {
                subtitle.innerText = isPF
                    ? 'Preencha os dados do cliente'
                    : 'Preencha os dados da empresa';
            }

            if (headerDados) {
                headerDados.innerHTML = isPF
                    ? '<i data-lucide="user" size="16"></i> DADOS PESSOAIS'
                    : '<i data-lucide="building-2" size="16"></i> DADOS DA EMPRESA';

                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        },

        // ==========================================
        // API - SERVIDOR
        // ==========================================

        /**
         * Salva o cliente no servidor
         */
        save: function () {
            var self = this;

            // Coleta dados
            var name = document.getElementById('cli_name').value.trim();
            var limitEl = document.getElementById('cli_limit');
            var limitVal = limitEl ? limitEl.value.replace('R$ ', '').replace(/\./g, '').replace(',', '.') : '0';

            // Validação básica
            if (!name) {
                alert('Por favor, preencha o Nome/Razão Social.');
                return;
            }

            // Validação de duplicidade
            if (ClientValidator.isDuplicate()) {
                alert('Este nome já existe cadastrado no sistema. Utilize outro nome ou busque o cliente existente.');
                document.getElementById('cli_name').focus();
                return;
            }

            // Monta payload
            var payload = {
                type: this.state.currentType,
                name: name,
                document: document.getElementById('cli_doc')?.value || '',
                phone: document.getElementById('cli_phone')?.value || '',
                zip_code: document.getElementById('cli_zip')?.value || '',
                neighborhood: document.getElementById('cli_bairro')?.value || '',
                address: document.getElementById('cli_addr')?.value || '',
                address_number: document.getElementById('cli_num')?.value || '',
                city: document.getElementById('cli_city')?.value || '',
                credit_limit: parseFloat(limitVal) || 0,
                due_day: document.getElementById('cli_due')?.value || ''
            };

            // Envia
            var baseUrl = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '';
            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(baseUrl + '/admin/loja/clientes/salvar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf || ''
                },
                body: JSON.stringify(payload)
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        var tipo = self.state.currentType === 'PF' ? 'Cliente' : 'Empresa';
                        alert(tipo + ' cadastrado com sucesso!');
                        document.getElementById('superClientModal').style.display = 'none';
                        // Recarrega seção via SPA
                        if (typeof AdminSPA !== 'undefined') {
                            AdminSPA.reloadCurrentSection();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(function (err) {
                    console.error('[ClientManager] Erro de conexão:', err);
                    alert('Erro de Conexão. Verifique o console para detalhes.');
                });
        }
    };

    // ==========================================
    // EXPORTAR GLOBALMENTE
    // ==========================================
    window.ClientManager = ClientManager;

    // Aliases para compatibilidade com onclick PHP
    window.openNewClientModal = function (type) { ClientManager.openModal(type); };
    window.closeSuperClientModal = function () { ClientManager.closeModal(); };
    window.saveSuperClient = function () { ClientManager.save(); };

    // Inicialização
    document.addEventListener('DOMContentLoaded', function () {
        ClientManager.init();
    });

})();
