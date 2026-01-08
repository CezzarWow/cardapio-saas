/**
 * PRICE-MASK.JS - Máscara de Preço Estilo Calculadora
 * 
 * Componente reutilizável para entrada de valores monetários.
 * Digitação direita para esquerda, formato: 0,00
 * Usado em: stock/edit.php, stock/create.php, additionals
 */

(function () {
    'use strict';

    // ==========================================
    // INICIALIZAÇÃO AUTOMÁTICA
    // ==========================================
    function initPriceMask(input) {
        if (!input) return;

        // Converte valor inicial para centavos
        let initialValue = input.value.replace(/\D/g, '');
        let cents = parseInt(initialValue) || 0;

        // Formata centavos para exibição
        function formatCents(c) {
            const str = String(c).padStart(3, '0');
            const integer = str.slice(0, -2) || '0';
            const decimal = str.slice(-2);
            return integer + ',' + decimal;
        }

        // Exibe valor formatado
        input.value = formatCents(cents);

        // Ao focar, seleciona tudo
        input.addEventListener('focus', function () {
            this.select();
        });

        // Ao clicar, também seleciona tudo
        input.addEventListener('click', function () {
            this.select();
        });

        // Controla a digitação
        input.addEventListener('keydown', function (e) {
            // Permite: backspace, delete, tab, enter, escape
            if ([8, 46, 9, 13, 27].includes(e.keyCode)) {
                if (e.keyCode === 8 || e.keyCode === 46) {
                    e.preventDefault();
                    cents = Math.floor(cents / 10);
                    this.value = formatCents(cents);
                }
                return;
            }

            // Bloqueia tudo que não for número
            if (e.key < '0' || e.key > '9') {
                e.preventDefault();
                return;
            }

            e.preventDefault();

            if (cents > 999999) return;

            cents = cents * 10 + parseInt(e.key);
            this.value = formatCents(cents);
        });

        // Move cursor pro final sempre
        input.addEventListener('input', function () {
            const len = this.value.length;
            this.setSelectionRange(len, len);
        });
    }

    // ==========================================
    // INICIALIZA AO CARREGAR
    // ==========================================
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializa input padrão por ID
        const priceInput = document.getElementById('priceInput');
        if (priceInput) initPriceMask(priceInput);

        // Inicializa inputs com data attribute
        document.querySelectorAll('[data-price-mask]').forEach(initPriceMask);
    });

    // Expõe para uso manual
    window.initPriceMask = initPriceMask;

})();
