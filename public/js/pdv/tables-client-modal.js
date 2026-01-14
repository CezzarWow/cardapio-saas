/**
 * TABLES-CLIENT-MODAL.JS - Modal de Novo Cliente
 * Dependências: PDVTables (tables.js), PDVState
 * 
 * Este módulo estende PDVTables com as funções do modal de cliente.
 */

(function () {
    'use strict';

    // ==========================================
    // ESTADO DO MODAL
    // ==========================================
    PDVTables.modalSearchTimeout = null;

    // ==========================================
    // BUSCAR CLIENTE NO MODAL
    // ==========================================
    PDVTables.searchClientInModal = function (term) {
        clearTimeout(this.modalSearchTimeout);
        const results = document.getElementById('modal-client-results');
        const btnSave = document.getElementById('btn-save-new-client');

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

                            const hasCrediario = client.credit_limit && parseFloat(client.credit_limit) > 0;
                            const badge = hasCrediario ? '<span style="background: #ea580c; color: white; font-size: 0.65rem; padding: 2px 4px; border-radius: 4px; font-weight: 800; margin-left: 6px;">CREDIÁRIO</span>' : '';

                            const div = document.createElement('div');
                            div.style.cssText = "padding: 8px 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center;";
                            div.innerHTML = `
                                <div>
                                    <div style="font-weight:600; font-size:0.85rem; display: flex; align-items: center;">${client.name} ${badge}</div>
                                    ${client.phone ? `<div style="font-size:0.75rem; color:#64748b;">${client.phone}</div>` : ''}
                                </div>
                                <span style="font-size: 0.75rem; color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px;">Selecionar</span>
                            `;

                            div.onclick = () => {
                                this.selectClient(client.id, client.name, null, client.credit_limit);
                                document.getElementById('clientModal').style.display = 'none';
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
    };

    // ==========================================
    // SALVAR NOVO CLIENTE
    // ==========================================
    PDVTables.saveClient = function () {
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
    };

})();
