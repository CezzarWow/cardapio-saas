/**
 * PDV CHECKOUT - Retirada
 * Funções auxiliares para cliente de retirada
 * 
 * Dependências: CheckoutUI
 */

/**
 * Abre seletor de cliente na barra lateral
 */
window.openClientSelector = function () {
    const selectedArea = document.getElementById('selected-client-area');
    const searchArea = document.getElementById('client-search-area');
    const searchInput = document.getElementById('client-search');

    // Limpa cliente atual se houver
    document.getElementById('current_client_id').value = '';
    if (document.getElementById('current_client_name')) {
        document.getElementById('current_client_name').value = '';
    }

    // Mostra a área de busca
    if (selectedArea) selectedArea.style.display = 'none';
    if (searchArea) searchArea.style.display = 'flex';
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
        // Dispara o evento para mostrar as mesas/opções
        searchInput.dispatchEvent(new Event('focus'));
    }

    // Alerta visual
    alert('Selecione um cliente na barra lateral à direita');
};

/**
 * Limpa o cliente selecionado para retirada
 */
window.clearRetiradaClient = function () {
    // Limpa o cliente selecionado
    document.getElementById('current_client_id').value = '';
    if (document.getElementById('current_client_name')) {
        document.getElementById('current_client_name').value = '';
    }

    // Limpa a mesa selecionada também (evita inconsistência)
    const tableIdInput = document.getElementById('current_table_id');
    if (tableIdInput) {
        tableIdInput.value = '';
    }

    // Limpa a barra lateral visualmente (sem abrir menu de opções)
    const selectedArea = document.getElementById('selected-client-area');
    const searchArea = document.getElementById('client-search-area');
    const searchInput = document.getElementById('client-search');

    if (selectedArea) selectedArea.style.display = 'none';
    if (searchArea) searchArea.style.display = 'flex';
    if (searchInput) {
        searchInput.value = '';
        // NÃO dar focus aqui - evita abrir automaticamente as opções
    }

    // Mostra o aviso de "Vincule um cliente"
    const clientSelectedBox = document.getElementById('retirada-client-selected');
    const noClientBox = document.getElementById('retirada-no-client');

    if (clientSelectedBox) clientSelectedBox.style.display = 'none';
    if (noClientBox) noClientBox.style.display = 'block';

    // Esconde botão "Salvar" na sidebar (volta ao modo balcão)
    const btnSave = document.getElementById('btn-save-command');
    if (btnSave) btnSave.style.display = 'none';

    // Reseta estado do PDV para balcão
    if (typeof PDVState !== 'undefined') {
        PDVState.set({ modo: 'balcao', mesaId: null, clienteId: null });
    }

    CheckoutUI.updateCheckoutUI();
};

/**
 * Reseta o alerta de retirada quando cliente é removido
 * (Chamado por PDVTables.clearClient)
 */
window.handleRetiradaValidation = function () {
    const keepOpen = document.getElementById('keep_open_value')?.value === 'true';
    const checkoutModal = document.getElementById('checkoutModal');

    if (keepOpen && checkoutModal && !checkoutModal.classList.contains('u-hidden')) {
        const alertBox = document.getElementById('retirada-client-alert');
        if (alertBox) {
            alertBox.classList.remove('retirada-alert--success');
            alertBox.classList.add('retirada-alert--warning');

            alertBox.innerHTML = `
                <div class="retirada-alert__header">
                    <i data-lucide="alert-triangle" size="18"></i>
                    <span>Cliente obrigatório para Retirada</span>
                </div>
                <div class="retirada-alert__search">
                    <input type="text" id="retirada-client-search" 
                           class="retirada-alert__input"
                           placeholder="Buscar cliente por nome ou telefone..."
                           oninput="searchClientForRetirada(this.value)">
                    <div id="retirada-client-results" class="retirada-alert__results u-hidden"></div>
                </div>
                <div class="retirada-alert__actions">
                    <button type="button" 
                            class="retirada-alert__btn"
                            onclick="document.getElementById('clientModal').classList.remove('u-hidden')">
                        <i data-lucide="user-plus" size="16"></i> Cadastrar Novo
                    </button>
                </div>
            `;

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        if (window.updateCheckoutUI) window.updateCheckoutUI();
    }
};
