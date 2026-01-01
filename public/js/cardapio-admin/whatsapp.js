/**
 * ============================================
 * CARDÁPIO ADMIN - WhatsApp
 * Funções relacionadas a mensagens WhatsApp
 * ============================================
 */

(function (CardapioAdmin) {

    /**
     * [ETAPA 5] Adicionar nova mensagem de WhatsApp (versão 1 - sem parâmetro)
     */
    CardapioAdmin.addWhatsappMessage = function () {
        const container = document.getElementById('whatsapp-messages-container');
        if (!container) return;

        const div = document.createElement('div');
        div.className = 'cardapio-admin-message-row';

        div.innerHTML = `
            <textarea class="cardapio-admin-input cardapio-admin-textarea" 
                      name="whatsapp_messages[]" 
                      rows="2"
                      placeholder="Digite sua mensagem..."></textarea>
            <button type="button" class="cardapio-admin-btn" 
                    style="background: #fee2e2; color: #ef4444; height: fit-content;" 
                    onclick="this.parentElement.remove()">
                <i data-lucide="trash-2"></i>
            </button>
        `;

        container.appendChild(div);

        if (window.lucide) lucide.createIcons();
    };

    /**
     * [ETAPA 5] Inicia edição do WhatsApp
     */
    CardapioAdmin.startWaEdit = function () {
        const input = document.getElementById('whatsapp_number');
        const btnEdit = document.getElementById('btn_edit_wa');
        const btnApply = document.getElementById('btn_apply_wa');

        if (!input) return;

        // Habilitar campo
        input.disabled = false;
        input.style.backgroundColor = 'white';
        input.focus();

        // Mostrar botão Aplicar, esconder Editar
        if (btnEdit) btnEdit.style.display = 'none';
        if (btnApply) btnApply.style.display = 'inline-flex';

        if (window.lucide) lucide.createIcons();
    };

    /**
     * [ETAPA 5] Aplica (trava) as alterações do WhatsApp
     */
    CardapioAdmin.applyWaEdit = function () {
        const input = document.getElementById('whatsapp_number');
        const btnEdit = document.getElementById('btn_edit_wa');
        const btnApply = document.getElementById('btn_apply_wa');

        if (!input) return;

        // Travar campo
        input.disabled = true;
        input.style.backgroundColor = '#f8fafc';

        // Mostrar botão Editar, esconder Aplicar
        if (btnEdit) btnEdit.style.display = 'inline-flex';
        if (btnApply) btnApply.style.display = 'none';

        if (window.lucide) lucide.createIcons();
    };

    /**
     * [NOVO] Adiciona mensagem do WhatsApp na lista específica (versão 2 - com parâmetro type)
     * @param {string} type 'before' ou 'after'
     */
    CardapioAdmin.addWhatsappMessage = function (type) {
        const container = document.getElementById(`whatsapp-list-${type}`);
        if (!container) return;

        const div = document.createElement('div');
        div.className = 'cardapio-admin-message-row';
        div.style.cssText = 'gap: 6px; margin-bottom: 6px; display: flex; align-items: center; width: 100%;';

        div.innerHTML = `
            <textarea class="cardapio-admin-input cardapio-admin-textarea" 
                      name="whatsapp_data[${type}][]" 
                      rows="2"
                      style="padding: 6px 10px; font-size: 0.85rem; background-color: #f8fafc; border: 1px solid #cbd5e1; width: 100%; min-height: 48px; resize: none;"
                      placeholder="Nova mensagem..."></textarea>
            <button type="button" class="cardapio-admin-btn" 
                    style="background: #fee2e2; color: #ef4444; padding: 0; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" 
                    onclick="this.parentElement.remove()">
                <i data-lucide="trash-2" size="14"></i>
            </button>
        `;

        container.appendChild(div);
        if (window.lucide) lucide.createIcons();

        // Foca no novo input
        const textarea = div.querySelector('textarea');
        if (textarea) textarea.focus();
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
