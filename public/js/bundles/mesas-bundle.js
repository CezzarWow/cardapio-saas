/* mesas-bundle - Generated 2026-01-26T20:40:27.313Z */


/* ========== shared/masks.js ========== */
/**
 * MASKS.JS - Máscaras de Input Reutilizáveis
 * 
 * Este módulo fornece funções de formatação para inputs de formulários.
 * Pode ser usado em qualquer parte do sistema.
 * 
 * Dependências: Nenhuma
 * Exporta: window.InputMasks
 */

(function () {
    'use strict';

    const InputMasks = {
        /**
         * Máscara de telefone brasileiro
         * Formato: (XX) XXXXX-XXXX
         */
        phone: function (v) {
            v = v.replace(/\D/g, "");
            v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
            v = v.replace(/(\d)(\d{4})$/, "$1-$2");
            return v.substring(0, 15);
        },

        /**
         * Máscara de CPF
         * Formato: XXX.XXX.XXX-XX
         */
        cpf: function (v) {
            v = v.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            return v.substring(0, 14);
        },

        /**
         * Máscara de CNPJ
         * Formato: XX.XXX.XXX/XXXX-XX
         */
        cnpj: function (v) {
            v = v.replace(/\D/g, "");
            v = v.replace(/^(\d{2})(\d)/, "$1.$2");
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
            v = v.replace(/(\d{4})(\d)/, "$1-$2");
            return v.substring(0, 18);
        },

        /**
         * Máscara de CEP
         * Formato: XXXXX-XXX
         */
        zip: function (v) {
            v = v.replace(/\D/g, "");
            v = v.replace(/^(\d{5})(\d)/, "$1-$2");
            return v.substring(0, 9);
        },

        /**
         * Máscara de moeda brasileira
         * Formato: R$ X.XXX,XX
         */
        currency: function (v) {
            v = v.replace(/\D/g, "");
            if (v === "") return "";
            v = (parseInt(v) / 100).toFixed(2) + "";
            v = v.replace(".", ",");
            v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            return "R$ " + v;
        },

        /**
         * Converte texto para Title Case
         * Primeira letra de cada palavra em maiúscula
         */
        titleCase: function (v) {
            if (!v) return "";
            return v.toLowerCase().replace(/(?:^|\s|["'([{])+\S/g, function (match) {
                return match.toUpperCase();
            });
        },

        /**
         * Helper: Aplica máscara a um elemento pelo ID
         * @param {string} elementId - ID do elemento
         * @param {function} maskFn - Função de máscara a aplicar
         */
        applyTo: function (elementId, maskFn) {
            var el = document.getElementById(elementId);
            if (el) {
                el.addEventListener('input', function (e) {
                    e.target.value = maskFn(e.target.value);
                });
            }
        }
    };

    // Exporta globalmente
    window.InputMasks = InputMasks;

})();


/* ========== admin/client-validator.js ========== */
/**
 * CLIENT-VALIDATOR.JS - Validações de Cliente
 * 
 * Este módulo fornece validação de duplicidade de nomes de clientes
 * e gerenciamento de feedback visual de erros.
 * 
 * Dependências: Nenhuma
 * Exporta: window.ClientValidator
 */

(function () {
    'use strict';

    const ClientValidator = {
        /**
         * Estado de validação
         */
        state: {
            isDuplicate: false
        },

        /**
         * Verifica se um nome de cliente já existe no sistema
         * @param {string} name - Nome a verificar
         * @returns {Promise<boolean>} - true se duplicado
         */
        checkDuplicate: async function (name) {
            if (!name || name.length < 3) {
                this.state.isDuplicate = false;
                return false;
            }

            try {
                var baseUrl = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '';
                var url = baseUrl + '/admin/loja/clientes/buscar?q=' + encodeURIComponent(name);

                var response = await fetch(url);
                var data = await response.json();

                // Verifica correspondência exata (case insensitive)
                this.state.isDuplicate = Array.isArray(data) && data.some(function (client) {
                    return client.name.toLowerCase() === name.toLowerCase();
                });

                return this.state.isDuplicate;
            } catch (error) {
                console.error('[ClientValidator] Erro ao verificar duplicidade:', error);
                return false;
            }
        },

        /**
         * Exibe mensagem de erro abaixo do input
         * @param {HTMLElement} input - Elemento input
         * @param {string} message - Mensagem de erro
         */
        showError: function (input, message) {
            if (!input) return;

            // Borda vermelha
            input.style.borderColor = '#ef4444';

            // Span de erro
            var span = document.getElementById('cli-name-error');
            if (!span) {
                span = document.createElement('span');
                span.id = 'cli-name-error';
                span.style.cssText = 'color:#ef4444;font-size:0.75rem;font-weight:700;display:block;margin-top:4px';
                input.parentNode.appendChild(span);
            }
            span.innerText = message;
            span.style.display = 'block';
        },

        /**
         * Remove mensagem de erro
         * @param {HTMLElement} input - Elemento input
         */
        clearError: function (input) {
            if (!input) return;

            // Restaura borda
            input.style.borderColor = '#cbd5e1';

            // Esconde span
            var span = document.getElementById('cli-name-error');
            if (span) {
                span.style.display = 'none';
            }

            // Reseta estado
            this.state.isDuplicate = false;
        },

        /**
         * Retorna se está em estado de duplicidade
         * @returns {boolean}
         */
        isDuplicate: function () {
            return this.state.isDuplicate;
        }
    };

    // Exporta globalmente
    window.ClientValidator = ClientValidator;

})();


/* ========== admin/clientes.js ========== */
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


/* ========== admin/tables-helpers.js ========== */
/**
 * ============================================
 * TABLES JS — Constants & Helpers
 * Constantes e funções compartilhadas entre módulos de mesas
 * ============================================
 */

window.TablesHelpers = {

    /**
     * Retorna a BASE_URL segura
     */
    getBaseUrl: function () {
        return typeof BASE_URL !== 'undefined' ? BASE_URL : '/cardapio-saas/public';
    },

    /**
     * Retorna o token CSRF
     */
    getCsrf: function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    },

    /**
     * Formata valor como moeda BRL
     */
    formatCurrency: function (val) {
        return 'R$ ' + parseFloat(val || 0).toFixed(2).replace('.', ',');
    }
};

// Expõe globalmente
window.TablesHelpers = TablesHelpers;


/* ========== admin/tables-crud.js ========== */
/**
 * TABLES-CRUD.JS - CRUD de Mesas
 * Módulo: TablesAdmin.Crud
 * 
 * Dependência: tables-helpers.js (carregar antes)
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Crud = {

        openNewModal: function () {
            const modal = document.getElementById('newTableModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            setTimeout(() => document.getElementById('new_table_number').focus(), 50);
        },

        save: function () {
            const number = document.getElementById('new_table_number').value;
            if (!number) return;

            fetch(TablesHelpers.getBaseUrl() + '/admin/loja/mesas/salvar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': TablesHelpers.getCsrf()
                },
                body: JSON.stringify({ number: number })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (window.AdminSPA) window.AdminSPA.reloadCurrentSection();
                        else window.location.reload();
                    }
                    else alert(data.message || 'Erro ao salvar mesa');
                })
                .catch(err => alert('Erro de conexão ao salvar mesa'));
        },

        openRemoveModal: function () {
            const modal = document.getElementById('removeTableModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            setTimeout(() => document.getElementById('remove_table_number').focus(), 50);
        },

        remove: function () {
            const number = document.getElementById('remove_table_number').value;
            if (!number) return;

            if (!confirm(`Tem certeza que deseja excluir a MESA ${number}?`)) return;

            const url = TablesHelpers.getBaseUrl() + '/admin/loja/mesas/deletar';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': TablesHelpers.getCsrf()
                },
                body: JSON.stringify({ number: number, force: false })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (window.AdminSPA) window.AdminSPA.reloadCurrentSection();
                        else window.location.reload();
                    }
                    else if (data.occupied) {
                        if (confirm(`ATENÇÃO: A Mesa ${number} está OCUPADA!\n\nExcluir agora pode causar erros nos pedidos.\nDeseja forçar a exclusão mesmo assim?`)) {
                            this._forceRemove(number, url);
                        }
                    } else {
                        alert(data.message || 'Erro ao excluir mesa');
                    }
                })
                .catch(err => alert('Erro de conexão ao excluir mesa'));
        },

        _forceRemove: function (number, url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': TablesHelpers.getCsrf()
                },
                body: JSON.stringify({ number: number, force: true })
            })
                .then(r2 => r2.json())
                .then(d2 => {
                    if (d2.success) {
                        if (window.AdminSPA) window.AdminSPA.reloadCurrentSection();
                        else window.location.reload();
                    }
                    else alert('Erro ao excluir (forçado): ' + d2.message);
                })
                .catch(err => alert('Erro de conexão (forçado)'));
        },

        abrir: function (id, numero) {
            // Usa navegação SPA quando disponível
            // AdminSPA automaticamente destaca 'mesas' quando há mesa_id
            if (typeof AdminSPA !== 'undefined') {
                AdminSPA.navigateTo('balcao', true, true, {
                    mesa_id: id,
                    mesa_numero: numero
                });
            } else {
                window.location.href = TablesHelpers.getBaseUrl() + '/admin/loja/pdv?mesa_id=' + id + '&mesa_numero=' + numero;
            }
        }
    };


})();


/* ========== admin/tables-clients.js ========== */
/**
 * TABLES-CLIENTS.JS - Modal de Clientes
 * Módulo: TablesAdmin.Clients
 * Refatorado: Delega para ClientManager (clientes.js)
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Clients = {

        openModal: function (startType) {
            // Verifica se o ClientManager existe (carregado via clientes.js)
            if (window.ClientManager) {
                window.ClientManager.openModal(startType);
            } else {
                console.error('ClientManager não encontrado. Verifique se clientes.js foi carregado.');
                alert('Erro: Módulo de Clientes não carregado.');
            }
        }
    };

})();


/* ========== admin/tables-paid-orders.js ========== */
/**
 * TABLES-PAID-ORDERS.JS - Pedidos Pagos (Retirada)
 * Módulo: TablesAdmin.PaidOrders
 * 
 * Dependência: tables-helpers.js (carregar antes)
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    let currentPaidOrderId = null;
    let currentPaidClientId = null;

    TablesAdmin.PaidOrders = {

        showOptions: function (orderId, clientName, total, clientId) {
            currentPaidOrderId = orderId;
            currentPaidClientId = clientId;

            const nameEl = document.getElementById('paid-order-client-name');
            const totalEl = document.getElementById('paid-order-total');
            const modal = document.getElementById('paidOrderModal');

            if (nameEl) nameEl.innerText = clientName;
            if (totalEl) totalEl.innerText = TablesHelpers.formatCurrency(total);
            if (modal) {
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        closeModal: function () {
            const modal = document.getElementById('paidOrderModal');
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
            currentPaidOrderId = null;
        },

        deliver: function () {
            if (!currentPaidOrderId) return;

            fetch(TablesHelpers.getBaseUrl() + '/admin/loja/pedidos/entregar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': TablesHelpers.getCsrf()
                },
                body: JSON.stringify({ order_id: currentPaidOrderId })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.closeModal();
                        // Recarrega seção via SPA
                        if (typeof AdminSPA !== 'undefined') {
                            AdminSPA.reloadCurrentSection();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('Erro: ' + (data.message || 'Falha ao entregar'));
                    }
                })
                .catch(err => alert('Erro na conexão: ' + err.message));
        },

        edit: function () {
            if (!currentPaidOrderId) return;
            // Navega para PDV com edit_paid via SPA
            if (typeof AdminSPA !== 'undefined') {
                AdminSPA.navigateTo('balcao', true, true, {
                    order_id: currentPaidOrderId,
                    edit_paid: 1
                });
            } else {
                window.location.href = TablesHelpers.getBaseUrl() + '/admin/loja/pdv?order_id=' + currentPaidOrderId + '&edit_paid=1';
            }
        },

        getCurrentOrderId: () => currentPaidOrderId,
        getCurrentClientId: () => currentPaidClientId
    };

})();


/* ========== admin/tables-dossier.js ========== */
/**
 * TABLES-DOSSIER.JS - Dossiê do Cliente
 * Módulo: TablesAdmin.Dossier
 * 
 * Dependência: tables-helpers.js (carregar antes)
 */

(function () {
    'use strict';

    window.TablesAdmin = window.TablesAdmin || {};

    TablesAdmin.Dossier = {

        open: function (clientId) {
            const modal = document.getElementById('dossierModal');
            if (!modal) return;

            // Reset UI
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.getElementById('dos_name').innerText = 'Buscando dados...';
            document.getElementById('dos_info').innerText = '...';
            document.getElementById('dos_history_list').innerHTML = '<p style="color:#94a3b8; text-align:center">Carregando...</p>';

            // Setup Botão Novo Pedido
            const btnOrder = document.getElementById('btn-dossier-order');
            if (btnOrder) {
                btnOrder.onclick = () => {
                    if (typeof AdminSPA !== 'undefined') {
                        AdminSPA.navigateTo('balcao', true, true, { client_id: clientId });
                    } else {
                        window.location.href = TablesHelpers.getBaseUrl() + '/admin/loja/pdv?client_id=' + clientId;
                    }
                };
            }

            // Fetch Dados
            fetch(TablesHelpers.getBaseUrl() + '/admin/loja/clientes/detalhes?id=' + clientId)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this._renderDossier(data);
                    } else {
                        alert('Erro: ' + data.message);
                        modal.style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro ao buscar detalhes.');
                    modal.style.display = 'none';
                });
        },

        _renderDossier: function (data) {
            const cli = data.client;

            // Info Básica
            document.getElementById('dos_name').innerText = cli.name;
            const docLabel = cli.type === 'PJ' ? 'CNPJ' : 'CPF';
            const docValue = cli.document || 'Não informado';
            const phoneValue = cli.phone || '--';
            document.getElementById('dos_info').innerText = `${docLabel}: ${docValue} • Tel: ${phoneValue}`;

            // Financeiro
            const debt = parseFloat(cli.current_debt || 0);
            const limit = parseFloat(cli.credit_limit || 0);
            document.getElementById('dos_debt').innerText = TablesHelpers.formatCurrency(debt);
            document.getElementById('dos_limit').innerText = TablesHelpers.formatCurrency(limit);

            // Histórico
            this._renderHistory(data.history);

            // Ícones
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        _renderHistory: function (history) {
            const list = document.getElementById('dos_history_list');
            list.innerHTML = '';

            if (!history || history.length === 0) {
                list.innerHTML = '<div style="text-align:center; padding:20px; color:#cbd5e1;">Nenhuma movimentação registrada.</div>';
                return;
            }

            const html = history.map(item => this._createHistoryItemHtml(item)).join('');
            list.innerHTML = html;
        },

        _createHistoryItemHtml: function (item) {
            const isPay = item.type === 'pagamento';
            const color = isPay ? '#16a34a' : '#ef4444';
            const sign = isPay ? '+' : '-';
            const dateStr = new Date(item.created_at).toLocaleDateString('pt-BR');
            const amountStr = TablesHelpers.formatCurrency(item.amount).replace('R$ ', '');

            return `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <div style="font-weight: 600; color: #334155; font-size: 0.9rem;">${item.description || item.type.toUpperCase()}</div>
                        <div style="font-size: 0.75rem; color: #94a3b8;">${dateStr}</div>
                    </div>
                    <div style="font-weight: 700; color: ${color}; font-size: 0.95rem;">
                        ${sign} R$ ${amountStr}
                    </div>
                </div>
            `;
        }
    };

})();


/* ========== admin/tables.js ========== */
/**
 * TABLES.JS - Orquestrador de Mesas e Comandas
 * Namespace: TablesAdmin
 * 
 * Dependências (carregar ANTES):
 * - tables-crud.js
 * - tables-clients.js
 * - tables-paid-orders.js
 * - tables-dossier.js
 * - clientes.js (ClientManager)
 */

(function () {
    'use strict';

    // IMPORTANTE: Estender o objeto existente ao invés de sobrescrever
    window.TablesAdmin = window.TablesAdmin || {};

    Object.assign(window.TablesAdmin, {

        // ==========================================
        // DELEGAÇÃO - CRUD DE MESAS
        // ==========================================
        openNewTableModal: () => window.TablesAdmin.Crud.openNewModal(),
        saveTable: () => window.TablesAdmin.Crud.save(),
        openRemoveTableModal: () => window.TablesAdmin.Crud.openRemoveModal(),
        removeTable: () => window.TablesAdmin.Crud.remove(),
        abrirMesa: (id, numero) => window.TablesAdmin.Crud.abrir(id, numero),

        // ==========================================
        // DELEGAÇÃO - CLIENTES (via Wrapper)
        // ==========================================
        openNewClientModal: (startType) => window.TablesAdmin.Clients.openModal(startType),

        // ==========================================
        // DELEGAÇÃO - PEDIDOS PAGOS
        // ==========================================
        showPaidOrderOptions: (orderId, clientName, total, clientId) =>
            window.TablesAdmin.PaidOrders.showOptions(orderId, clientName, total, clientId),
        closePaidOrderModal: () => window.TablesAdmin.PaidOrders.closeModal(),
        deliverOrder: () => window.TablesAdmin.PaidOrders.deliver(),
        editPaidOrder: () => window.TablesAdmin.PaidOrders.edit(),

        // ==========================================
        // DELEGAÇÃO - DOSSIÊ
        // ==========================================
        openDossier: (clientId) => window.TablesAdmin.Dossier.open(clientId)
    });

    // ==========================================
    // ALIASES DE COMPATIBILIDADE (HTML onclicks)
    // ==========================================
    window.openNewTableModal = () => TablesAdmin.openNewTableModal();
    window.saveTable = () => TablesAdmin.saveTable();
    window.openRemoveTableModal = () => TablesAdmin.openRemoveTableModal();
    window.removeTable = () => TablesAdmin.removeTable();
    window.abrirMesa = (id, numero) => TablesAdmin.abrirMesa(id, numero);

    // Alias para Clientes (Prioriza ClientManager direto se possível, fallback pro TablesAdmin)
    window.openNewClientModal = (type) => {
        if (window.ClientManager) window.ClientManager.openModal(type);
        else TablesAdmin.openNewClientModal(type);
    };

    window.showPaidOrderOptions = (orderId, clientName, total, clientId) =>
        TablesAdmin.showPaidOrderOptions(orderId, clientName, total, clientId);

    window.closePaidOrderModal = () => TablesAdmin.closePaidOrderModal();
    window.deliverOrder = () => TablesAdmin.deliverOrder();
    window.editPaidOrder = () => TablesAdmin.editPaidOrder();
    window.openDossier = (id) => TablesAdmin.openDossier(id);



})();

