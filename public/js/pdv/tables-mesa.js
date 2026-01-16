/**
 * TABLES-MESA.JS - Lógica de Mesas
 * Dependências: PDVTables (tables.js), PDVState
 * 
 * Este módulo estende PDVTables com as funções de mesas.
 */

(function () {
    'use strict';

    // ==========================================
    // BUSCAR MESAS
    // ==========================================
    PDVTables.fetchTables = function () {
        fetch('mesas/buscar')
            .then(r => r.json())
            .then(data => this.renderTableResults(data));
    };

    // ==========================================
    // RENDERIZAR RESULTADOS DE MESAS
    // ==========================================
    PDVTables.renderTableResults = function (tables) {
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
    };

    // ==========================================
    // SELECIONAR MESA
    // ==========================================
    PDVTables.selectTable = function (table) {
        // Se mesa está ocupada e tem order_id, carrega via SPA navigation
        if (table.status === 'ocupada' && table.current_order_id) {
            // [MIGRATION] Salva carrinho atual antes de navegar
            if (typeof PDVCart !== 'undefined') PDVCart.saveForMigration();

            // Usa navegação SPA com query params
            // AdminSPA automaticamente destaca 'mesas' quando há mesa_id/order_id
            if (typeof AdminSPA !== 'undefined') {
                AdminSPA.navigateTo('balcao', true, true, {
                    order_id: table.current_order_id,
                    mesa_id: table.id,
                    mesa_numero: table.number
                });
            } else {
                // Fallback para redirect (fora do SPA)
                window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/admin/loja/pdv?order_id=' + table.current_order_id;
            }
            return;
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
    };

})();
