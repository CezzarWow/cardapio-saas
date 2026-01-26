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
            results.innerHTML = '<div class="client-results-empty">Nenhum cliente encontrado</div>';
            results.style.display = 'block';
            return;
        }

        results.style.display = 'block';

        const header = document.createElement('div');
        header.innerHTML = '<small class="client-results-header">CLIENTES ENCONTRADOS</small>';
        results.appendChild(header);

        clients.forEach(client => {
            const div = document.createElement('div');
            const hasOpenOrder = client.has_open_order;

            // Usar classes CSS
            div.className = hasOpenOrder ? 'client-item client-item--open' : 'client-item';

            let tag = '';
            if (hasOpenOrder) {
                tag = `<span class="client-badge">OCUPADO</span>`;
            }

            const hasCrediario = client.credit_limit && parseFloat(client.credit_limit) > 0;
            const badge = hasCrediario ? '<span style="background: #ea580c; color: white; font-size: 0.6rem; padding: 2px 4px; border-radius: 4px; font-weight: 800; margin-left: 6px;">CREDIÁRIO</span>' : '';

            div.innerHTML = `
                <div class="client-avatar">
                    <span>${client.name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="client-info">
                    <div class="client-name" style="display:flex; align-items:center;">${client.name} ${badge}</div>
                    ${client.phone ? `<div class="client-phone">${client.phone}</div>` : ''}
                </div>
                ${tag}
            `;

            div.onclick = () => {
                if (hasOpenOrder && client.open_order_id) {
                    // Se clicar no card de um cliente que JÁ TEM pedido:
                    // Pergunta se quer abrir o pedido existente
                    if (confirm(`O cliente ${client.name} possui um pedido aberto (#${client.open_order_id}). Deseja abri-lo?`)) {
                        if (typeof AdminSPA !== 'undefined') {
                            AdminSPA.navigateTo('balcao', true, true, { order_id: client.open_order_id });
                        } else {
                            window.location.href = `${BASE_URL}/admin/loja/pdv?order_id=${client.open_order_id}`;
                        }
                        return;
                    }
                }
                // Se não tem pedido ou cancelou a abertura, seleciona apenas para novo pedido
                this.selectClient(client.id, client.name, client.open_order_id, client.credit_limit);
            };
            results.appendChild(div);
        });
    };

    // ==========================================
    // SELECIONAR CLIENTE
    // ==========================================
    PDVTables.selectClient = function (id, name, openOrderId = null, creditLimit = 0) {
        // [ALTERADO] Não navega mais automaticamente para comanda
        // A navegação para comanda só ocorre via grid na aba Mesas
        // Aqui apenas vinculamos o cliente ao pedido atual (para Balcão)

        // Atualiza Estado
        PDVState.set({ modo: 'balcao', clienteId: id, mesaId: null });

        // Atualiza inputs hidden
        document.getElementById('current_client_id').value = id;
        document.getElementById('current_table_id').value = '';

        // [FIX] Cria/Atualiza order_id hidden se necessário
        let orderIdInput = document.getElementById('current_order_id');
        if (!orderIdInput) {
            orderIdInput = document.createElement('input');
            orderIdInput.type = 'hidden';
            orderIdInput.id = 'current_order_id';
            document.body.appendChild(orderIdInput);
        }
        orderIdInput.value = openOrderId || '';

        // Armazena o nome e crédito do cliente
        let clientNameInput = document.getElementById('current_client_name');
        if (!clientNameInput) {
            clientNameInput = document.createElement('input');
            clientNameInput.type = 'hidden';
            clientNameInput.id = 'current_client_name';
            document.body.appendChild(clientNameInput);
        }
        clientNameInput.value = name;

        let clientCreditInput = document.getElementById('current_client_credit_limit');
        if (!clientCreditInput) {
            clientCreditInput = document.createElement('input');
            clientCreditInput.type = 'hidden';
            clientCreditInput.id = 'current_client_credit_limit';
            document.body.appendChild(clientCreditInput);
        }
        clientCreditInput.value = creditLimit || 0;

        const hasCrediario = creditLimit && parseFloat(creditLimit) > 0;
        const badge = hasCrediario ? '<span style="background: #ea580c; color: white; font-size: 0.65rem; padding: 2px 4px; border-radius: 4px; font-weight: 800; margin-left: 6px;">CREDIÁRIO</span>' : '';

        // Tag de comanda aberta e link "Ver Comanda" dentro do card
        const openTag = openOrderId ? '<span style="color:#ef4444; font-size:0.8rem; margin-left:5px;">(COMANDA ABERTA)</span>' : '';
        const verComandaLink = openOrderId
            ? `<a href="#" id="client-ver-comanda" style="color:#2563eb; font-size:0.75rem; margin-left:8px; text-decoration:underline;">Ver Comanda</a>`
            : '';

        document.getElementById('selected-client-name').innerHTML = `${name} ${badge} ${openTag} ${verComandaLink}`;

        // Bind evento no link (se existir)
        if (openOrderId) {
            const linkEl = document.getElementById('client-ver-comanda');
            if (linkEl) {
                linkEl.onclick = (e) => {
                    e.preventDefault();
                    if (typeof AdminSPA !== 'undefined') {
                        // Passa order_id como query params para o Balcão
                        AdminSPA.navigateTo('balcao', true, true, { order_id: openOrderId });
                    } else {
                        const baseUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : '');
                        window.location.href = `${baseUrl}/admin/loja/pdv?order_id=${openOrderId}`;
                    }
                };
            }
        }

        const selectedArea = document.getElementById('selected-client-area');
        const searchArea = document.getElementById('client-search-area');
        const resultsArea = document.getElementById('client-results');

        if (selectedArea) selectedArea.style.display = 'flex';
        if (searchArea) searchArea.style.display = 'none';
        if (resultsArea) resultsArea.style.display = 'none';

        // Atualiza a view de Retirada se estiver visível
        this._updateRetiradaView(name);

        // Botões
        this._updateButtons(true);

        // Fetch Async para Dados Atualizados (Dívida/Limite) Do Cliente
        const baseUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : '');
        fetch(`${baseUrl}/admin/loja/clientes/detalhes?id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.client) {

                    // Atualiza Credit Limit Hidden (garante dado fresco)
                    let credInp = document.getElementById('current_client_credit_limit');
                    if (credInp) credInp.value = data.client.credit_limit || 0;

                    // Cria/Atualiza Debt Hidden
                    let debtInp = document.getElementById('current_client_debt');
                    if (!debtInp) {
                        debtInp = document.createElement('input');
                        debtInp.type = 'hidden';
                        debtInp.id = 'current_client_debt';
                        document.body.appendChild(debtInp);
                    }
                    debtInp.value = data.client.current_debt || 0;

                    // Atualiza UI Checkout se necessário (para mostrar Labels)
                    if (typeof CheckoutUI !== 'undefined' && typeof CheckoutUI.updateCheckoutUI === 'function') {
                        CheckoutUI.updateCheckoutUI();
                    }
                }
            })
            .catch(e => console.error('Erro buscando detalhes do cliente:', e));
    };

    // ==========================================
    // LIMPAR CLIENTE/MESA
    // ==========================================
    PDVTables.clearClient = function () {
        // Atualiza Estado
        PDVState.set({ clienteId: null, mesaId: null });

        // Limpa inputs hidden
        document.getElementById('current_client_id').value = '';
        document.getElementById('current_table_id').value = '';

        const orderIdInp = document.getElementById('current_order_id');
        if (orderIdInp) orderIdInp.value = '';
        const credInp = document.getElementById('current_client_credit_limit');
        if (credInp) credInp.value = '';
        const debtInp = document.getElementById('current_client_debt');
        if (debtInp) debtInp.value = '';

        // Visual - usando style.display para compatibilidade
        const selectedArea = document.getElementById('selected-client-area');
        const searchArea = document.getElementById('client-search-area');
        const searchInput = document.getElementById('client-search');

        if (selectedArea) selectedArea.style.display = 'none';
        if (searchArea) searchArea.style.display = 'flex';
        if (searchInput) {
            searchInput.value = '';
            // Não faz focus automático para evitar abrir o dropdown de mesas
        }

        // Botões
        this._updateButtons(false);

        // Se estava em Retirada, volta automaticamente para Local
        const selectedType = document.getElementById('selected_order_type')?.value;
        if (selectedType === 'retirada') {
            // Volta para Local automaticamente
            if (typeof selectOrderType === 'function') {
                selectOrderType('local', null);
            } else if (typeof CheckoutOrderType !== 'undefined') {
                CheckoutOrderType.selectOrderType('local', null);
            }
        }

        // Lógica Específica de Retirada (função global em retirada.js)
        if (typeof handleRetiradaValidation === 'function') {
            handleRetiradaValidation();
        }
    };

    // ==========================================
    // HELPERS PRIVADOS
    // ==========================================

    /**
     * Atualiza a view de Retirada quando cliente é selecionado
     */
    PDVTables._updateRetiradaView = function (name) {
        const retiradaAlert = document.getElementById('retirada-client-alert');
        if (!retiradaAlert || retiradaAlert.classList.contains('u-hidden')) return;

        const clientSelectedBox = document.getElementById('retirada-client-selected');
        const noClientBox = document.getElementById('retirada-no-client');
        const clientNameDisplay = document.getElementById('retirada-client-name');

        if (clientSelectedBox) {
            clientSelectedBox.classList.remove('u-hidden');
            if (clientNameDisplay) clientNameDisplay.innerText = name;
        }
        if (noClientBox) noClientBox.classList.add('u-hidden');

        if (typeof lucide !== 'undefined') lucide.createIcons();
        if (typeof PDVCheckout !== 'undefined') PDVCheckout.updateCheckoutUI();
    };

    /**
     * Atualiza estado dos botões Finalizar e Salvar
     */
    PDVTables._updateButtons = function (clientSelected) {
        const btnFinalizar = document.getElementById('btn-finalizar');
        const btnSave = document.getElementById('btn-save-command');

        if (btnFinalizar) {
            btnFinalizar.innerText = 'Finalizar';
            btnFinalizar.style.backgroundColor = '';
        }

        if (btnSave) {
            // Usa display flex para garantir visibilidade sobrepondo estilo inline do PHP
            btnSave.style.display = clientSelected ? 'flex' : 'none';
        }
    };

})();

