/**
 * UTILS.JS - Funções Utilitárias e Helpers
 * Módulo independente para formatação e máscaras.
 */

const Utils = {
    // Formata número para moeda BRL (R$ 1.234,56)
    formatCurrency: function (value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    },

    // Máscara de Moeda para Inputs (ex: 50,00) - Limite R$ 200
    formatMoneyInput: function (input, maxValue = 200) {
        let value = input.value.replace(/\D/g, ''); // Remove não-números
        if (value === '') {
            input.value = '';
            return;
        }

        let numericValue = parseInt(value) / 100;

        // Bloqueia se exceder o limite (remove último dígito)
        if (numericValue > maxValue) {
            value = value.slice(0, -1);
            if (value === '') {
                input.value = '';
                return;
            }
            numericValue = parseInt(value) / 100;
        }

        value = numericValue.toFixed(2);
        value = value.replace('.', ','); // Troca ponto por vírgula
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.'); // Milhar
        input.value = 'R$ ' + value;
    },

    // Inicializa ícones Lucide (se disponível)
    initIcons: function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
};

// Disponibiliza globalmente
window.Utils = Utils;

// Atalho global para compatibilidade
window.formatCurrency = Utils.formatCurrency;

// ==========================================
// DETECÇÃO DE NAVEGADOR SAMSUNG INTERNET
// ==========================================
// Bug conhecido: Samsung ignora padding-bottom para cálculo de scroll
// quando existe elemento position:fixed sobreposto.
// Solução: Usar elemento real (spacer) com altura física.
(function () {
    const isSamsung = /SamsungBrowser/i.test(navigator.userAgent);

    if (isSamsung) {
        document.body.classList.add('samsung-browser');
        console.log('[Utils] Samsung Internet detectado - aplicando ajustes de compatibilidade');

        // Aguarda DOM carregar para dimensionar spacers
        document.addEventListener('DOMContentLoaded', function () {
            const spacers = document.querySelectorAll('.modal-scroll-spacer');

            spacers.forEach(function (spacer) {
                // Encontra o footer/botão fixo mais próximo
                const modal = spacer.closest('.cardapio-modal-content');
                if (!modal) return;

                // Calcula altura necessária (altura do botão + margem de segurança)
                const fixedBtn = modal.querySelector('.cardapio-floating-cart-btn, .send-order-btn');
                const btnHeight = fixedBtn ? fixedBtn.offsetHeight : 70;

                // Define altura do spacer (altura do botão + 60px de folga)
                spacer.style.height = (btnHeight + 60) + 'px';
                console.log('[Utils] Spacer dimensionado para Samsung:', btnHeight + 60, 'px');
            });
        });
    }
})();
