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
