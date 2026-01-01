/**
 * ============================================
 * CARDÁPIO ADMIN - Bootstrap (v2.0 Modular)
 * Arquivo: public/js/cardapio-admin/index.js
 * 
 * REGRA: Este arquivo apenas inicializa.
 * Toda lógica está em módulos separados.
 * ============================================
 */

// Garante que o objeto existe (outros módulos já podem ter criado)
window.CardapioAdmin = window.CardapioAdmin || {};

/**
 * Inicializa todo o módulo
 */
window.CardapioAdmin.init = function () {
    this.initTabs();
    this.initToggles();
    this.initValidation();
    this.initLoader();
    this.initPixMask();

    // [ETAPA 5] Aplicar máscara inicial no telefone (se já tiver valor do banco)
    const waInput = document.getElementById('whatsapp_number');
    if (waInput && waInput.value) {
        this.maskPhone(waInput);
    }

    console.log('✅ CardapioAdmin v2.0 (Modular) inicializado');
};

// Auto-inicializa quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.CardapioAdmin.init();
});
