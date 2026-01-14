/**
 * CLIENTES.JS - Gestão de Clientes (Admin)
 * Refatorado para Módulo: ClientManager
 */

(function () {
    'use strict';

    const ClientManager = {
        // ==========================================
        // ESTADO
        // ==========================================
        state: {
            currentType: 'PF', // PF ou PJ
            isDuplicate: false // Controle de duplicidade
        },

        // ==========================================
        // MÁSCARAS
        // ==========================================
        masks: {
            phone: (v) => {
                v = v.replace(/\D/g, "");
                v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
                v = v.replace(/(\d)(\d{4})$/, "$1-$2");
                return v.substring(0, 15);
            },
            cpf: (v) => {
                v = v.replace(/\D/g, "");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                return v.substring(0, 14);
            },
            cnpj: (v) => {
                v = v.replace(/\D/g, "");
                v = v.replace(/^(\d{2})(\d)/, "$1.$2");
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
                v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
                v = v.replace(/(\d{4})(\d)/, "$1-$2");
                return v.substring(0, 18);
            },
            zip: (v) => {
                v = v.replace(/\D/g, "");
                v = v.replace(/^(\d{5})(\d)/, "$1-$2");
                return v.substring(0, 9);
            },
            currency: (v) => {
                v = v.replace(/\D/g, "");
                if (v === "") return "";
                v = (parseInt(v) / 100).toFixed(2) + "";
                v = v.replace(".", ",");
                v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
                return "R$ " + v;
            },
            titleCase: (v) => {
                if (!v) return "";
                return v.toLowerCase().replace(/(?:^|\s|["'([{])+\S/g, match => match.toUpperCase());
            }
        },

        // ==========================================
        // INICIALIZAÇÃO
        // ==========================================
        init: function () {
            this.bindEvents();

        },

        bindEvents: function () {
            // Helper para aplicar máscara
            const addMask = (id, fn) => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', e => e.target.value = fn(e.target.value));
            };

            addMask('cli_phone', this.masks.phone);
            addMask('cli_zip', this.masks.zip);

            // Nome: Capitalização e Duplicidade
            const nameInput = document.getElementById('cli_name');
            if (nameInput) {
                // Máscara Title Case (Ao digitar)
                nameInput.addEventListener('input', e => {
                    const start = e.target.selectionStart;
                    const oldVal = e.target.value;
                    const newVal = this.masks.titleCase(oldVal);

                    if (oldVal !== newVal) {
                        e.target.value = newVal;
                        e.target.setSelectionRange(start, start);
                    }

                    // Reseta erro visual
                    if (ClientManager.state.isDuplicate) {
                        ClientManager.state.isDuplicate = false;
                        nameInput.style.borderColor = '#cbd5e1';
                        const errSpan = document.getElementById('cli-name-error');
                        if (errSpan) errSpan.style.display = 'none';
                    }
                });

                // Checar Duplicidade (Ao sair)
                nameInput.addEventListener('blur', async e => {
                    const val = e.target.value.trim();
                    if (val.length > 2) {
                        try {
                            // Tenta inferir endpoint correto
                            const baseUrl = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '';
                            // Ajuste para rota comum de busca: /admin/clientes/buscar
                            let url = `${baseUrl}/admin/clientes/buscar?q=${encodeURIComponent(val)}`;

                            // Correção de barra duplicada se houver
                            url = url.replace('//admin', '/admin');

                            const r = await fetch(url);
                            const data = await r.json();

                            // Verifica correspondência exata
                            const exists = Array.isArray(data) && data.some(c => c.name.toLowerCase() === val.toLowerCase());

                            if (exists) {
                                ClientManager.state.isDuplicate = true;
                                nameInput.style.borderColor = '#ef4444'; // Vermelho

                                let errSpan = document.getElementById('cli-name-error');
                                if (!errSpan) {
                                    errSpan = document.createElement('span');
                                    errSpan.id = 'cli-name-error';
                                    errSpan.style.color = '#ef4444';
                                    errSpan.style.fontSize = '0.75rem';
                                    errSpan.style.fontWeight = '700';
                                    errSpan.style.display = 'block';
                                    errSpan.style.marginTop = '4px';
                                    nameInput.parentNode.appendChild(errSpan);
                                }
                                errSpan.innerText = 'Este cliente já está cadastrado.';
                                errSpan.style.display = 'block';
                            } else {
                                // Se não existir, garante sucesso visual (ou remove erro)
                                ClientManager.state.isDuplicate = false;
                                nameInput.style.borderColor = '#cbd5e1';
                                const errSpan = document.getElementById('cli-name-error');
                                if (errSpan) errSpan.style.display = 'none';
                            }
                        } catch (err) { console.error('Erro ao verificar duplicidade:', err); }
                    }
                });
            }

            // CPF/CNPJ Dinâmico
            const docInput = document.getElementById('cli_doc');
            if (docInput) {
                docInput.addEventListener('input', e => {
                    e.target.value = this.state.currentType === 'PF'
                        ? this.masks.cpf(e.target.value)
                        : this.masks.cnpj(e.target.value);
                });
            }

            // Moeda (Crediário)
            const limitInput = document.getElementById('cli_limit');
            if (limitInput) {
                limitInput.addEventListener('input', e => {
                    e.target.value = this.masks.currency(e.target.value);
                });
            }

            // Dia Vencimento (1-31)
            const dueInput = document.getElementById('cli_due');
            if (dueInput) {
                dueInput.addEventListener('input', e => {
                    let val = parseInt(e.target.value);
                    if (val > 31) e.target.value = 31;
                    if (val < 1) e.target.value = '';
                });
            }
        },

        // ==========================================
        // UI - INTERFACE
        // ==========================================
        ui: {
            openModal: function (type) {
                const modal = document.getElementById('superClientModal');
                if (modal) {
                    this.resetForm(); // Limpa ao abrir para garantir
                    modal.style.display = 'flex';
                    // Configura visual
                    this.setTypeVisual(type);
                    document.getElementById('cli_name').focus();
                }
            },

            closeModal: function () {
                const modal = document.getElementById('superClientModal');
                if (modal) modal.style.display = 'none';
                this.resetForm();
            },

            resetForm: function () {
                const ids = [
                    'cli_name', 'cli_doc', 'cli_phone', 'cli_zip',
                    'cli_addr', 'cli_num', 'cli_bairro', 'cli_city',
                    'cli_limit', 'cli_due'
                ];
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
            },

            setTypeVisual: function (type) {
                ClientManager.state.currentType = type;

                const lblName = document.getElementById('lbl-name');
                const lblDoc = document.getElementById('lbl-doc');
                const subtitle = document.getElementById('modal-subtitle');
                const headerDados = document.getElementById('header-dados');

                // Garante que elementos existem antes de alterar
                if (lblName) {
                    lblName.innerHTML = type === 'PF'
                        ? 'Nome Completo <span style="color: #ef4444">*</span>'
                        : 'Razão Social <span style="color: #ef4444">*</span>';
                }

                if (lblDoc) {
                    lblDoc.innerHTML = type === 'PF' ? 'CPF (Opcional)' : 'CNPJ (Opcional)';
                }

                if (subtitle) {
                    subtitle.innerText = type === 'PF'
                        ? 'Preencha os dados do cliente'
                        : 'Preencha os dados da empresa';
                }

                if (headerDados) {
                    headerDados.innerHTML = type === 'PF'
                        ? '<i data-lucide="user" size="16"></i> DADOS PESSOAIS'
                        : '<i data-lucide="building-2" size="16"></i> DADOS DA EMPRESA';

                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }
        },

        // ==========================================
        // API - SERVER
        // ==========================================
        api: {
            save: function () {
                // Limpeza de campos
                let limitVal = document.getElementById('cli_limit').value;
                limitVal = limitVal.replace('R$ ', '').replace(/\./g, '').replace(',', '.');

                // Construção do Payload
                const payload = {
                    type: ClientManager.state.currentType,
                    name: document.getElementById('cli_name').value,
                    document: document.getElementById('cli_doc').value,
                    phone: document.getElementById('cli_phone').value,
                    zip_code: document.getElementById('cli_zip').value,
                    neighborhood: document.getElementById('cli_bairro').value,
                    address: document.getElementById('cli_addr').value,
                    address_number: document.getElementById('cli_num').value,
                    city: document.getElementById('cli_city').value,
                    credit_limit: parseFloat(limitVal) || 0,
                    due_day: document.getElementById('cli_due').value
                };

                // Validação Básica
                if (!payload.name) {
                    alert('Por favor, preencha o Nome/Razão Social.');
                    return;
                }

                // Check Duplicidade (Bloqueio Final)
                if (ClientManager.state.isDuplicate) {
                    alert('Este nome já existe cadastrado no sistema. Utilize outro nome ou busque o cliente existente.');
                    document.getElementById('cli_name').focus();
                    return;
                }

                // Determinar BASE_URL
                const baseUrl = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/cardapio-saas/public';

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`${baseUrl}/admin/loja/clientes/salvar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            alert((ClientManager.state.currentType === 'PF' ? 'Cliente' : 'Empresa') + ' cadastrado com sucesso!');
                            document.getElementById('superClientModal').style.display = 'none';
                            window.location.reload();
                        } else {
                            alert('Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
                        }
                    })
                    .catch(err => {
                        console.error('[ClientManager] API Error:', err);
                        alert('Erro de Conexão. Detalhes no console.');
                    });
            }
        }
    };

    // ==========================================
    // EXPORTAR GLOBALMENTE
    // ==========================================
    window.ClientManager = ClientManager;

    // Aliases para compatibilidade com onclick PHP
    window.openNewClientModal = (type) => ClientManager.ui.openModal(type);
    window.closeSuperClientModal = () => ClientManager.ui.closeModal();
    window.saveSuperClient = () => ClientManager.api.save();

    // Inicialização
    document.addEventListener('DOMContentLoaded', () => ClientManager.init());

})();
