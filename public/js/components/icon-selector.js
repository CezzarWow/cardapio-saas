/**
 * ICON-SELECTOR.JS - Seletor de Ícone
 * 
 * Componente reutilizável para seleção de ícones em produtos.
 * Usado em: stock/edit.php, stock/create.php
 */

(function () {
    'use strict';

    // ==========================================
    // TOGGLE GRID DE ÍCONES
    // ==========================================
    window.toggleIconGrid = function () {
        const grid = document.getElementById('iconGrid');
        const chevron = document.getElementById('iconChevron');

        if (!grid) return;

        if (grid.style.display === 'none' || grid.style.display === '') {
            grid.style.display = 'grid';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            grid.style.display = 'none';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    };

    // ==========================================
    // SELECIONAR ÍCONE
    // ==========================================
    window.selectIcon = function (iconName) {
        const hiddenInput = document.getElementById('selectedIcon');
        const display = document.getElementById('selectedIconDisplay');

        if (hiddenInput) hiddenInput.value = iconName;
        if (display) display.textContent = iconName;

        // Remove seleção anterior visual
        document.querySelectorAll('.icon-option').forEach(opt => {
            opt.style.borderColor = '#e5e7eb';
            opt.style.background = 'white';
        });

        // Marca selecionado visual
        const selected = document.querySelector(`.icon-option[data-icon="${iconName}"]`);
        if (selected) {
            selected.style.borderColor = '#2563eb';
            selected.style.background = '#eff6ff';
        }

        // Fecha o grid após selecionar
        setTimeout(() => {
            toggleIconGrid();
        }, 150);
    };

    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

})();
