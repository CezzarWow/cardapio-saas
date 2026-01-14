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
