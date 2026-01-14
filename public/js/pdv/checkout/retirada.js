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
 * 
 * NOTA: Esta função foi desativada pois a UI de retirada agora é gerenciada
 * pelo orderType.js através do selectOrderType('retirada')
 */
window.handleRetiradaValidation = function () {
    // Função desativada - a UI é gerenciada pelo orderType.js
    // Apenas chama o select novamente para atualizar o estado
    if (typeof CheckoutOrderType !== 'undefined') {
        CheckoutOrderType.selectOrderType('retirada');
    }
};
