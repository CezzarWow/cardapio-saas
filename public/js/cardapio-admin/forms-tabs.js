/**
 * FORMS-TABS.JS - Sistema de Abas
 * 
 * Gerencia sistema de abas com persistência via Hash na URL.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Sistema de abas com persistência via Hash
     */
    CardapioAdmin.initTabs = function () {
        const tabBtns = document.querySelectorAll('.cardapio-admin-tab-btn');
        const tabContents = document.querySelectorAll('.cardapio-admin-tab-content');

        if (!tabBtns.length) return;

        // Função para ativar aba
        const activateTab = (tabId) => {
            // Remove active
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            // Adiciona active no botão
            const btn = document.querySelector(`.cardapio-admin-tab-btn[data-tab="${tabId}"]`);
            if (btn) btn.classList.add('active');

            // Adiciona active no conteúdo
            const content = document.getElementById(`tab-${tabId}`);
            if (content) content.classList.add('active');
        };

        // 1. Checar Hash na URL ao carregar
        const currentHash = window.location.hash.replace('#', '');
        if (currentHash) {
            activateTab(currentHash);
        }

        // 2. Click Listener
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.dataset.tab;
                activateTab(targetTab);

                // Atualiza URL sem recarregar
                history.replaceState(null, null, `#${targetTab}`);
            });
        });
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
