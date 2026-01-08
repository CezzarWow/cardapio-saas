/**
 * PDV TABLES - Gestão de Mesas e Clientes
 * Dependências: PDVState, PDVCart (opcional)
 */

const PDVTables = {
    searchTimeout: null,

    init: function () {
this.bindEvents();
    },

    bindEvents: function () {
        const input = document.getElementById('client-search');
        if (!input) return;

        // 0. Click Outside: Fecha dropdown ao clicar fora
        document.addEventListener('click', (e) => {
            const results = document.getElementById('client-results');
            if (input && results && !input.contains(e.target) && !results.contains(e.target)) {
                results.style.display = 'none';
            }
        });

        // 1. Focus: Mostra mesas (sem digitar)
        input.addEventListener('focus', () => {
            if (input.value.trim() === '') {
                this.fetchTables();
            }
        });

        // 2. Input: Busca Clientes
        input.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            const term = e.target.value;

            if (term.length < 2) {
                if (term.length === 0) this.fetchTables(); // Voltou a vazio -> mesas
                else document.getElementById('client-results').style.display = 'none';
                return;
            }

            this.searchTimeout = setTimeout(() => {
                fetch('clientes/buscar?q=' + term)
                    .then(r => r.json())
                    .then(data => this.renderClientResults(data));
            }, 300);
        });
    },

    // ==========================================
    // MESAS
    // ==========================================
    fetchTables: function () {
        fetch('mesas/buscar')
            .then(r => r.json())
            .then(data => this.renderTableResults(data));
    },

    renderTableResults: function (tables) {
        const results = document.getElementById('client-results');
        results.innerHTML = '';

        if (!tables.length) {
            results.style.display = 'none';
            return;
        }

        results.style.display = 'block';

        // Header com botão fechar
        const header = document.createElement('div');
        header.style.cssText = "display: flex; justify-content: space-between; align-items: center; padding: 10px 15px 5px;";
        header.innerHTML = `
            <small style="color:#64748b; font-weight:700; font-size:0.75rem;">MESAS DISPONÍVEIS</small>
            <button onclick="document.getElementById('client-results').style.display='none'" 
                    style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1.2rem; font-weight: bold; padding: 0; line-height: 1;"
                    title="Fechar">&times;</button>
        `;
        results.appendChild(header);

        // Grid (5 por linha, alinhado)
        const grid = document.createElement('div');
        grid.style.cssText = "display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; padding: 10px;";

        tables.forEach(table => {
            const isOccupied = table.status === 'ocupada';
            const bg = isOccupied ? '#fef2f2' : '#f0fdf4';
            const border = isOccupied ? '#ef4444' : '#22c55e';
            const text = isOccupied ? '#991b1b' : '#166534';

            const card = document.createElement('div');
            card.className = 'table-card-item';
            card.style.cssText = `
                width: 50px; height: 50px; 
                background: ${bg}; 
                border: 2px solid ${border}; 
                border-radius: 10px; 
                display: flex; flex-direction: column; 
                align-items: center; justify-content: center; 
                cursor: pointer; transition: transform 0.1s;
                position: relative;
            `;

            card.innerHTML = `
                <span style="font-weight:800; font-size:1.1rem; color:${text};">${table.number}</span>
                ${isOccupied ? '<span style="font-size:0.6rem; color:#dc2626; font-weight:bold;">OCP</span>' : ''}
            `;

            // Events
            card.onmouseover = () => card.style.transform = 'scale(1.05)';
            card.onmouseout = () => card.style.transform = 'scale(1)';
            card.onclick = () => this.selectTable(table);

            grid.appendChild(card);
        });

        results.appendChild(grid);
    },

    selectTable: function (table) {
        if (table.status === 'ocupada') {
            alert(`🚧 ATENÇÃO: A Mesa ${table.number} já está ocupada!\nVocê está adicionando itens ao pedido existente.`);
        }

        // Atualiza Estado
        PDVState.set({ modo: 'mesa', mesaId: table.id, clienteId: null });

        // Atualiza UI inputs hidden
        document.getElementById('current_table_id').value = table.id;
        document.getElementById('current_client_id').value = '';

        // [FIX] Armazena o nome/número da mesa para funcionar com Retirada
        let tableNameInput = document.getElementById('current_table_name');
        if (!tableNameInput) {
            tableNameInput = document.createElement('input');
            tableNameInput.type = 'hidden';
            tableNameInput.id = 'current_table_name';
            document.body.appendChild(tableNameInput);
        }
        tableNameInput.value = `Mesa ${table.number}`;

        // Visual
        document.getElementById('selected-client-name').innerHTML = `🔹 Mesa ${table.number} <small>(${table.status})</small>`;
        document.getElementById('selected-client-area').style.display = 'flex';
        document.getElementById('client-search-area').style.display = 'none';
        document.getElementById('client-results').style.display = 'none';

        // Mostra botão Salvar (laranja) e mantém Finalizar (azul)
        const btnFinalizar = document.getElementById('btn-finalizar');
        if (btnFinalizar) {
            btnFinalizar.innerText = "Finalizar";
            btnFinalizar.style.backgroundColor = "";
        }

        const btnSave = document.getElementById('btn-save-command');
        if (btnSave) btnSave.style.display = 'flex';

        // Atualiza a view de Retirada se estiver visível (igual cliente)
        const retiradaAlert = document.getElementById('retirada-client-alert');
        if (retiradaAlert && retiradaAlert.style.display !== 'none') {
            const clientSelectedBox = document.getElementById('retirada-client-selected');
            const noClientBox = document.getElementById('retirada-no-client');
            const clientNameDisplay = document.getElementById('retirada-client-name');

            if (clientSelectedBox) {
                clientSelectedBox.style.display = 'block';
                if (clientNameDisplay) clientNameDisplay.innerText = `Mesa ${table.number}`;
            }
            if (noClientBox) noClientBox.style.display = 'none';

            if (typeof lucide !== 'undefined') lucide.createIcons();
            if (typeof PDVCheckout !== 'undefined') PDVCheckout.updateCheckoutUI();
        }
    },

    // ==========================================
    // CLIENTES
    // ==========================================
    renderClientResults: function (clients) {
        const results = document.getElementById('client-results');
        results.innerHTML = '';

        if (!clients.length) {
            results.innerHTML = '<div style="padding:15px; color:#64748b; text-align:center;">Nenhum cliente encontrado</div>';
            results.style.display = 'block';
            return;
        }

        results.style.display = 'block';

        const header = document.createElement('div');
        header.innerHTML = '<small style="color:#64748b; font-weight:700; padding:10px 15px; display:block; font-size:0.75rem; border-bottom:1px solid #f1f5f9;">CLIENTES ENCONTRADOS</small>';
        results.appendChild(header);

        clients.forEach(client => {
            const div = document.createElement('div');
            div.style.cssText = "padding: 10px 15px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; align-items: center; gap: 10px;";

            div.innerHTML = `
                <div style="background:#f1f5f9; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <span style="font-weight:bold; color:#64748b;">${client.name.charAt(0).toUpperCase()}</span>
                </div>
                <div>
                    <div style="font-weight:600; font-size:0.9rem; color:#1e293b;">${client.name}</div>
                    ${client.phone ? `<div style="font-size:0.8rem; color:#64748b;">${client.phone}</div>` : ''}
                </div>
            `;

            div.onclick = () => this.selectClient(client.id, client.name);
            div.onmouseover = () => div.style.background = '#f8fafc';
            div.onmouseout = () => div.style.background = 'white';
            results.appendChild(div);
        });
    },

    selectClient: function (id, name) {
        // Atualiza Estado
        PDVState.set({ modo: 'balcao', clienteId: id, mesaId: null });
        // Nota: modo pode ser 'retirada' se estiver editando pago. 
        // Lógica original apenas setava inputs. 

        document.getElementById('current_client_id').value = id;
        document.getElementById('current_table_id').value = '';

        // Armazena o nome do cliente também
        let clientNameInput = document.getElementById('current_client_name');
        if (!clientNameInput) {
            clientNameInput = document.createElement('input');
            clientNameInput.type = 'hidden';
            clientNameInput.id = 'current_client_name';
            document.body.appendChild(clientNameInput);
        }
        clientNameInput.value = name;

        document.getElementById('selected-client-name').innerText = name;

        // Visual
        document.getElementById('selected-client-area').style.display = 'flex';
        document.getElementById('client-search-area').style.display = 'none';
        document.getElementById('client-results').style.display = 'none';

        // Atualiza a view de Retirada se estiver visível
        const retiradaAlert = document.getElementById('retirada-client-alert');
        if (retiradaAlert && retiradaAlert.style.display !== 'none') {
            const clientSelectedBox = document.getElementById('retirada-client-selected');
            const noClientBox = document.getElementById('retirada-no-client');
            const clientNameDisplay = document.getElementById('retirada-client-name');

            if (clientSelectedBox) {
                clientSelectedBox.style.display = 'block';
                if (clientNameDisplay) clientNameDisplay.innerText = name;
            }
            if (noClientBox) noClientBox.style.display = 'none';

            if (typeof lucide !== 'undefined') lucide.createIcons();
            if (typeof PDVCheckout !== 'undefined') PDVCheckout.updateCheckoutUI();
        }

        // Botões
        const btn = document.getElementById('btn-finalizar');
        if (btn) {
            btn.innerText = "Finalizar";
            btn.style.backgroundColor = "";
        }

        // NÃO mostrar botão Salvar Comanda em modo balcão (só em comanda)
        const btnSave = document.getElementById('btn-save-command');
        if (btnSave) btnSave.style.display = 'flex';
    },

    clearClient: function () {
        // Atualiza Estado
        PDVState.set({ clienteId: null, mesaId: null });

        document.getElementById('current_client_id').value = '';
        document.getElementById('current_table_id').value = '';

        // Visual
        document.getElementById('selected-client-area').style.display = 'none';
        document.getElementById('client-search-area').style.display = 'flex';
        document.getElementById('client-search').value = '';
        document.getElementById('client-search').focus();

        // Botões
        const btn = document.getElementById('btn-finalizar');
        btn.innerText = "Finalizar";
        btn.style.backgroundColor = "";

        const btnSave = document.getElementById('btn-save-command');
        if (btnSave) btnSave.style.display = 'none';

        // Lógica Específica de Retirada (se o modal estiver aberto)
        this.handleRetiradaValidation();
    },

    handleRetiradaValidation: function () {
        const keepOpen = document.getElementById('keep_open_value')?.value === 'true';
        const checkoutModal = document.getElementById('checkoutModal');

        if (keepOpen && checkoutModal && checkoutModal.style.display !== 'none') {
            // Reverte alerta de retirada
            const alertBox = document.getElementById('retirada-client-alert');
            if (alertBox) {
                alertBox.style.background = '#fef3c7';
                alertBox.style.borderColor = '#f59e0b';
                alertBox.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <i data-lucide="alert-triangle" size="18" style="color: #d97706;"></i>
                        <span style="font-weight: 700; color: #92400e; font-size: 0.9rem;">Cliente obrigatório para Retirada</span>
                    </div>
                    <div style="position: relative; margin-bottom: 10px;">
                        <input type="text" id="retirada-client-search" placeholder="Buscar cliente por nome ou telefone..."
                               style="width: 100%; padding: 10px 12px; border: 1px solid #d97706; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;"
                               oninput="searchClientForRetirada(this.value)"> <!-- AINDA NÃO DEFINIDO NO GLOBAL? -->
                        <div id="retirada-client-results" style="display: none; position: absolute; left: 0; right: 0; top: 100%; background: white; border: 1px solid #e5e7eb; border-radius: 6px; max-height: 150px; overflow-y: auto; z-index: 100; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="document.getElementById('clientModal').style.display='flex'" 
                                style="flex: 1; padding: 10px; background: white; color: #d97706; border: 1px solid #d97706; border-radius: 6px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i data-lucide="user-plus" size="16"></i> Cadastrar Novo
                        </button>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
            // Bloqueia botão de finalizar (precisa estar no checkout.js? Ou exposto aqui?)
            if (window.updateCheckoutUI) window.updateCheckoutUI(); // Compatibilidade
        }
    },

    // ==========================================
    // NOVO CLIENTE (MODAL)
    // ==========================================
    modalSearchTimeout: null,

    searchClientInModal: function (term) {
        clearTimeout(this.modalSearchTimeout);
        const results = document.getElementById('modal-client-results');
        const btnSave = document.getElementById('btn-save-new-client'); // Precisa adicionar ID no HTML

        if (term.length < 2) {
            results.style.display = 'none';
            if (btnSave) btnSave.disabled = false;
            if (btnSave) btnSave.style.opacity = '1';
            return;
        }

        this.modalSearchTimeout = setTimeout(() => {
            fetch('clientes/buscar?q=' + term)
                .then(r => r.json())
                .then(data => {
                    results.innerHTML = '';
                    let exactMatch = false;

                    if (data.length > 0) {
                        results.style.display = 'block';

                        data.forEach(client => {
                            // Verifica duplicidade exata (case insensitive)
                            if (client.name.toLowerCase() === term.toLowerCase()) {
                                exactMatch = true;
                            }

                            const div = document.createElement('div');
                            div.style.cssText = "padding: 8px 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center;";
                            div.innerHTML = `
                                <div>
                                    <div style="font-weight:600; font-size:0.85rem;">${client.name}</div>
                                    ${client.phone ? `<div style="font-size:0.75rem; color:#64748b;">${client.phone}</div>` : ''}
                                </div>
                                <span style="font-size: 0.75rem; color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px;">Selecionar</span>
                            `;

                            div.onclick = () => {
                                this.selectClient(client.id, client.name);
                                document.getElementById('clientModal').style.display = 'none';
                                document.body.removeChild(document.getElementById('clientModal')); // Move de volta ou destroi? Melhor esconder e resetar.
                                // Na verdade o modal foi movido pro body, entao ok.
                                document.getElementById('new_client_name').value = '';
                                results.style.display = 'none';
                            };

                            results.appendChild(div);
                        });
                    } else {
                        results.style.display = 'none';
                    }

                    // Bloqueia salvar se tiver nome igual
                    if (exactMatch) {
                        if (btnSave) {
                            btnSave.disabled = true;
                            btnSave.style.opacity = '0.5';
                            btnSave.innerText = "Já Existe";
                        }
                    } else {
                        if (btnSave) {
                            btnSave.disabled = false;
                            btnSave.style.opacity = '1';
                            btnSave.innerText = "Salvar";
                        }
                    }
                });
        }, 300);
    },

    saveClient: function () {
        const name = document.getElementById('new_client_name').value;
        const phone = document.getElementById('new_client_phone').value;

        if (!name.trim()) return alert('Digite o nome do cliente');

        fetch('clientes/salvar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, phone })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('clientModal').style.display = 'none';

                    // Seleciona o cliente recém criado
                    this.selectClient(data.client.id, data.client.name);

                    // Tratamento especial para Retirada (se estiver no modal)
                    const retiradaAlert = document.getElementById('retirada-client-alert');
                    if (retiradaAlert && retiradaAlert.style.display !== 'none') {
                        // Atualiza visual do alerta para Verde (Sucesso)
                        // ... (Simplificado, idealmente reusa selectClient logic)
                        // Mas o handleRetiradaValidation lida com o "clear", 
                        // aqui precisamos lidar com o "success" no contexto de modal aberto.

                        retiradaAlert.style.background = '#dcfce7';
                        retiradaAlert.style.borderColor = '#22c55e';
                        retiradaAlert.innerHTML = `
                         <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i data-lucide="check-circle" size="18" style="color: #16a34a;"></i>
                                <span style="font-weight: 700; color: #166534; font-size: 0.9rem;">Cliente: ${data.client.name}</span>
                            </div>
                            <button onclick="PDVTables.clearClient()" style="background: none; border: none; color: #166534; cursor: pointer; font-size: 1.2rem; font-weight: bold; padding: 0 5px;">&times;</button>
                        </div>
                    `;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        if (window.updateCheckoutUI) window.updateCheckoutUI();
                    }

                    // Limpa form
                    document.getElementById('new_client_name').value = '';
                    document.getElementById('new_client_phone').value = '';
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(err => alert('Erro ao salvar: ' + err.message));
    }
};

// Expor Globalmente
window.PDVTables = PDVTables;

// Compatibilidade
window.fetchTables = () => PDVTables.fetchTables();
window.selectTable = (t) => PDVTables.selectTable(t);
window.selectClient = (id, n) => PDVTables.selectClient(id, n);
window.clearClient = () => PDVTables.clearClient();
window.saveClient = () => PDVTables.saveClient();
window.renderClientResults = (d) => PDVTables.renderClientResults(d);
window.renderTableResults = (d) => PDVTables.renderTableResults(d);
window.openClientModal = () => document.getElementById('clientModal').style.display = 'flex';
