/**
 * TABLES-CLIENTE.JS - Lógica de Clientes
 * Dependências: PDVTables (tables.js), PDVState
 * 
 * Este módulo estende PDVTables com as funções de clientes.
 */

(function () {
    'use strict';

    // ==========================================
    // RENDERIZAR RESULTADOS DE CLIENTES
    // ==========================================
    PDVTables.renderClientResults = function (clients) {
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
    };

    // ==========================================
    // SELECIONAR CLIENTE
    // ==========================================
    PDVTables.selectClient = function (id, name) {
        // Atualiza Estado
        PDVState.set({ modo: 'balcao', clienteId: id, mesaId: null });

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

        // Mostrar botão Salvar Comanda
        const btnSave = document.getElementById('btn-save-command');
        if (btnSave) btnSave.style.display = 'flex';
    };

    // ==========================================
    // LIMPAR CLIENTE/MESA
    // ==========================================
    PDVTables.clearClient = function () {
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
    };

    // ==========================================
    // VALIDAÇÃO DE RETIRADA
    // ==========================================
    PDVTables.handleRetiradaValidation = function () {
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
                               oninput="searchClientForRetirada(this.value)">
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
            // Bloqueia botão de finalizar
            if (window.updateCheckoutUI) window.updateCheckoutUI();
        }
    };

})();
