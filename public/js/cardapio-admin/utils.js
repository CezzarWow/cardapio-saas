/**
 * ============================================
 * CARDÁPIO ADMIN - Utils
 * Funções utilitárias globais
 * ============================================
 */

// Função de máscara monetária (ex: 5000 -> 50,00)
window.formatCurrency = function (input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    value = (parseInt(value) / 100).toFixed(2) + '';
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = value;
};
