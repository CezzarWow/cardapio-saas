/**
 * MULTI-SELECT - Componente Genérico
 * Gerencia dropdowns de seleção múltipla com checkboxes
 * 
 * Uso:
 *   - Container deve ter classe: custom-select-container-{type}
 *   - Trigger deve ter classe: select-trigger-{type}
 *   - Lista deve ter classe: options-list-{type}
 *   - Texto deve ter classe: trigger-text-{type}
 */

const MultiSelect = {

    /**
     * Abre/fecha o dropdown
     * @param {HTMLElement} triggerEl - Elemento trigger clicado
     * @param {string} type - Tipo do select (ex: 'groups', 'items', 'cat')
     */
    toggle: function (triggerEl, type) {
        const container = triggerEl.parentElement;
        const list = container.querySelector('.options-list-' + type);

        if (!list) return;

        if (list.style.display === 'block') {
            list.style.display = 'none';
        } else {
            list.style.display = 'block';
        }
    },

    /**
     * Atualiza o texto do trigger com contagem de selecionados
     * @param {string} type - Tipo do select
     * @param {string} singularLabel - Texto singular (ex: 'Item', 'Grupo')
     * @param {string} pluralLabel - Texto plural (ex: 'Itens', 'Grupos')
     * @param {string} emptyText - Texto quando nada selecionado
     */
    updateTriggerText: function (type, singularLabel, pluralLabel, emptyText) {
        const container = document.querySelector('.custom-select-container-' + type);
        if (!container) return;

        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const triggerText = container.querySelector('.trigger-text-' + type);

        if (!triggerText) return;

        if (checkedCount === 0) {
            triggerText.textContent = emptyText;
            triggerText.style.color = '#6b7280';
            triggerText.style.fontWeight = '400';
        } else {
            const label = checkedCount === 1 ? singularLabel : pluralLabel;
            triggerText.textContent = checkedCount + ' ' + label + ' Selecionado(s)';
            triggerText.style.color = '#1f2937';
            triggerText.style.fontWeight = '600';
        }
    },

    /**
     * Reseta todos os checkboxes de um container
     * @param {string} type - Tipo do select
     */
    reset: function (type) {
        const container = document.querySelector('.custom-select-container-' + type);
        if (!container) return;

        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = false);
    },

    /**
     * Fecha dropdown ao clicar fora
     * @param {string} type - Tipo do select
     */
    closeOnClickOutside: function (type) {
        const containerClass = '.custom-select-container-' + type;
        const listClass = '.options-list-' + type;

        document.addEventListener('click', function (e) {
            if (!e.target.closest(containerClass)) {
                const lists = document.querySelectorAll(listClass);
                lists.forEach(l => l.style.display = 'none');
            }
        });
    },

    /**
     * Inicializa listeners de click-outside para múltiplos tipos
     * @param {Array<string>} types - Array de tipos
     */
    initClickOutside: function (types) {
        document.addEventListener('click', function (e) {
            types.forEach(type => {
                if (!e.target.closest('.custom-select-container-' + type)) {
                    const lists = document.querySelectorAll('.options-list-' + type);
                    lists.forEach(l => l.style.display = 'none');
                }
            });
        });
    }
};

// Expõe globalmente
window.MultiSelect = MultiSelect;

// Compatibilidade com marcação legada usada em alguns forms (ex: create.php)
window.toggleSelect = function (triggerEl) {
    try {
        const container = triggerEl.closest('.custom-select-container');
        if (!container) return;
        const list = container.querySelector('.options-list');
        if (!list) return;
        list.style.display = (list.style.display === 'block') ? 'none' : 'block';
    } catch (e) {
        console.warn('toggleSelect error', e);
    }
};

window.updateTriggerText = function (checkboxEl) {
    try {
        const container = checkboxEl.closest('.custom-select-container');
        if (!container) return;
        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const triggerText = container.querySelector('.trigger-text');
        if (!triggerText) return;

        if (checkedCount === 0) {
            triggerText.textContent = 'Selecione...';
            triggerText.style.color = '#6b7280';
            triggerText.style.fontWeight = '400';
        } else {
            triggerText.textContent = checkedCount + ' Selecionado(s)';
            triggerText.style.color = '#1f2937';
            triggerText.style.fontWeight = '600';
        }
    } catch (e) {
        console.warn('updateTriggerText error', e);
    }
};

// Fecha dropdowns legacy ao clicar fora
document.addEventListener('click', function (e) {
    if (!e.target.closest('.custom-select-container')) {
        document.querySelectorAll('.options-list').forEach(l => l.style.display = 'none');
    }
});
