<?php
/**
 * ============================================
 * PARTIAL: Aba WhatsApp
 * ============================================
 */
?>

<!-- Card Status -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="power"></i>
        <h3 class="cardapio-admin-card-title">Status da Loja</h3>
    </div>

    <div class="cardapio-admin-toggle-row">
        <span class="cardapio-admin-toggle-label">Loja Aberta</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="is_open" id="is_open" value="1"
                   <?= ($config['is_open'] ?? 1) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <div class="cardapio-admin-form-group" style="margin-top: 1rem;">
        <label class="cardapio-admin-label" for="closed_message">Mensagem quando fechado</label>
        <input type="text" 
               class="cardapio-admin-input" 
               id="closed_message" 
               name="closed_message" 
               placeholder="Estamos fechados no momento"
               value="<?= htmlspecialchars($config['closed_message'] ?? 'Estamos fechados no momento') ?>">
    </div>
</div>

<!-- Card WhatsApp -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="message-circle"></i>
        <h3 class="cardapio-admin-card-title">Configurações do WhatsApp</h3>
    </div>

    <!-- Toggle Habilitar -->
    <div class="cardapio-admin-toggle-row">
        <span class="cardapio-admin-toggle-label">Habilitar recebimento por WhatsApp</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled" value="1"
                   <?= ($config['whatsapp_enabled'] ?? 0) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <!-- Campos do WhatsApp (condicionais) -->
    <div id="whatsapp-fields" style="margin-top: 1.5rem; <?= ($config['whatsapp_enabled'] ?? 0) ? '' : 'display: none;' ?>">
        
        <div class="cardapio-admin-form-group">
            <label class="cardapio-admin-label" for="whatsapp_number">Número do WhatsApp (recebedor)</label>
            
            <!-- Input + Botões na mesma linha -->
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" 
                       class="cardapio-admin-input" 
                       id="whatsapp_number" 
                       name="whatsapp_number" 
                       placeholder="(11) 9 9999-9999" 
                       maxlength="20"
                       disabled 
                       style="width: 240px; background-color: #f8fafc;"
                       oninput="CardapioAdmin.maskPhone(this)"
                       value="<?= htmlspecialchars($config['whatsapp_number'] ?? '') ?>">
                
                <button type="button" 
                        class="cardapio-admin-btn" 
                        id="btn_edit_wa"
                        style="background: #e2e8f0; color: #475569; padding: 10px 16px;"
                        onclick="CardapioAdmin.startWaEdit()">
                    <i data-lucide="pencil" size="16"></i>
                    Editar
                </button>
                
                <button type="button" 
                        class="cardapio-admin-btn" 
                        id="btn_apply_wa"
                        style="background: #22c55e; color: white; padding: 10px 16px; display: none;"
                        onclick="CardapioAdmin.applyWaEdit()">
                    <i data-lucide="check" size="16"></i>
                    Aplicar
                </button>
            </div>
            
            <p class="cardapio-admin-hint" style="margin-top: 6px;">Use apenas números, a máscara é automática.</p>
        </div>

        <div class="cardapio-admin-form-group">
            <label class="cardapio-admin-label">Mensagens Automáticas</label>
            <p class="cardapio-admin-hint" style="margin-bottom: 10px;">
                Estas mensagens serão enviadas em ordem. Você pode usar {RESUMO_DO_PEDIDO} para inserir o pedido.
            </p>
            
            <div id="whatsapp-messages-container">
                <?php 
                $msgs = json_decode($config['whatsapp_message'] ?? '[]', true);
                if (!is_array($msgs) || empty($msgs)) {
                    // Fallback para valor antigo ou padrão
                    $oldMsg = $config['whatsapp_message'] ?? '';
                    $msgs = !empty($oldMsg) && $oldMsg !== '[]' ? [$oldMsg] : ['Olá! Obrigado pelo pedido.', '{RESUMO_DO_PEDIDO}'];
                }
                
                foreach ($msgs as $index => $msg): 
                ?>
                <div class="cardapio-admin-message-row">
                    <textarea class="cardapio-admin-input cardapio-admin-textarea" 
                              name="whatsapp_messages[]" 
                              rows="2"
                              placeholder="Digite sua mensagem..."><?= htmlspecialchars($msg) ?></textarea>
                    <button type="button" class="cardapio-admin-btn" style="background: #fee2e2; color: #ef4444; height: fit-content;" onclick="this.parentElement.remove()">
                        <i data-lucide="trash-2" size="16"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="cardapio-admin-btn" 
                    style="margin-top: 8px; border: 1px dashed #cbd5e1; background: #f8fafc; width: 100%; justify-content: center;"
                    onclick="CardapioAdmin.addWhatsappMessage()">
                <i data-lucide="plus" size="16"></i>
                Adicionar Nova Mensagem
            </button>
        </div>

    </div>
</div>
