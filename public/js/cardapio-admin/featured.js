/**
 * ============================================
 * CARDÁPIO ADMIN - Featured (Orquestrador)
 * 
 * Arquivo principal que registra o namespace Destaques.
 * Os módulos são carregados separadamente:
 * - featured-edit.js (Modo edição)
 * - featured-dragdrop.js (Drag and Drop)
 * - featured-tabs.js (Abas/Highlight)
 * - featured-categories.js (Ordenação categorias)
 * ============================================
 */

(function (CardapioAdmin) {
    'use strict';

    // Cria namespace Destaques se não existir
    CardapioAdmin.Destaques = CardapioAdmin.Destaques || {
        draggedItem: null
    };

    console.log('CardapioAdmin Featured JS Loaded');

})(window.CardapioAdmin = window.CardapioAdmin || {});
