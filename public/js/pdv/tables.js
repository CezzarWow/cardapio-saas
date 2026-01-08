/**
 * TABLES.JS - Orquestrador de Gestão de Mesas e Clientes
 * 
 * Este arquivo inicializa o objeto PDVTables e expõe as funções globais.
 * As implementações estão em arquivos separados:
 * - tables-mesa.js (Lógica de Mesas)
 * - tables-cliente.js (Lógica de Clientes)
 * - tables-client-modal.js (Modal de Novo Cliente)
 * 
 * Dependências: PDVState
 * ORDEM DE CARREGAMENTO:
 * 1. tables.js (este arquivo) - cria o objeto base
 * 2. tables-mesa.js - estende com funções de mesa
 * 3. tables-cliente.js - estende com funções de cliente
 * 4. tables-client-modal.js - estende com modal de cliente
 */

const PDVTables = {
    searchTimeout: null,

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================
    init: function () {
        this.bindEvents();
    },

    // ==========================================
    // BIND DE EVENTOS
    // ==========================================
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
    }
};

// ==========================================
// EXPOR GLOBALMENTE
// ==========================================
window.PDVTables = PDVTables;

// ==========================================
// COMPATIBILIDADE (Aliases Globais)
// ==========================================
window.fetchTables = () => PDVTables.fetchTables();
window.selectTable = (t) => PDVTables.selectTable(t);
window.selectClient = (id, n) => PDVTables.selectClient(id, n);
window.clearClient = () => PDVTables.clearClient();
window.saveClient = () => PDVTables.saveClient();
window.renderClientResults = (d) => PDVTables.renderClientResults(d);
window.renderTableResults = (d) => PDVTables.renderTableResults(d);
window.openClientModal = () => document.getElementById('clientModal').style.display = 'flex';
