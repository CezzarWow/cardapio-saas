<?php
/**
 * ============================================
 * PARTIAL: Aba Operação (Consolidada)
 * Status + Pagamentos + WhatsApp
 * ============================================
 */
?>

<!-- Status da Loja + Pagamentos (lado a lado, compacto) -->
<div class="cardapio-admin-grid cardapio-admin-grid-2">
    
    <!-- Card Status -->
    <div class="cardapio-admin-card" style="padding: 16px;">
        <div class="cardapio-admin-card-header" style="margin-bottom: 12px; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="power"></i>
                <h3 class="cardapio-admin-card-title">Status</h3>
            </div>
            <div style="display: flex; gap: 6px;">
                <button type="button" class="cardapio-admin-btn" id="btn_edit_status"
                        style="background: #e2e8f0; color: #475569; padding: 6px 12px; font-size: 0.8rem;"
                        onclick="CardapioAdmin.toggleCardEdit('status')">
                    <i data-lucide="pencil" size="14"></i> Editar
                </button>
                <button type="button" class="cardapio-admin-btn" id="btn_cancel_status"
                        style="background: #fee2e2; color: #ef4444; padding: 6px 12px; font-size: 0.8rem; display: none;"
                        onclick="CardapioAdmin.cancelCardEdit('status')">
                    <i data-lucide="x" size="14"></i> Cancelar
                </button>
            </div>
        </div>
        
        <div class="cardapio-admin-toggle-row status-field" style="padding: 8px 0; border: none; opacity: 0.7; pointer-events: none;">
            <span class="cardapio-admin-toggle-label">Loja Aberta</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="is_open" id="is_open" value="1" disabled
                       <?= ($config['is_open'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
        <div class="cardapio-admin-form-group status-field" style="margin-top: 8px; opacity: 0.7; pointer-events: none;">
            <label class="cardapio-admin-label" for="closed_message" style="font-size: 0.85rem;">Mensagem (fechado)</label>
            <input type="text" 
                   class="cardapio-admin-input" 
                   id="closed_message" 
                   name="closed_message" 
                   disabled
                   style="padding: 8px 10px; font-size: 0.9rem; background-color: #f8fafc;"
                   placeholder="Estamos fechados"
                   value="<?= htmlspecialchars($config['closed_message'] ?? 'Estamos fechados no momento') ?>">
        </div>
    </div>

    <!-- Card Pagamentos -->
    <div class="cardapio-admin-card" style="padding: 16px;">
        <div class="cardapio-admin-card-header" style="margin-bottom: 12px; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="wallet"></i>
                <h3 class="cardapio-admin-card-title">Pagamentos</h3>
            </div>
            <div style="display: flex; gap: 6px;">
                <button type="button" class="cardapio-admin-btn" id="btn_edit_pagamentos"
                        style="background: #e2e8f0; color: #475569; padding: 6px 12px; font-size: 0.8rem;"
                        onclick="CardapioAdmin.toggleCardEdit('pagamentos')">
                    <i data-lucide="pencil" size="14"></i> Editar
                </button>
                <button type="button" class="cardapio-admin-btn" id="btn_cancel_pagamentos"
                        style="background: #fee2e2; color: #ef4444; padding: 6px 12px; font-size: 0.8rem; display: none;"
                        onclick="CardapioAdmin.cancelCardEdit('pagamentos')">
                    <i data-lucide="x" size="14"></i> Cancelar
                </button>
            </div>
        </div>
        
        <div class="cardapio-admin-toggle-row pagamentos-field" style="padding: 6px 0; border-bottom: 1px solid #f1f5f9; opacity: 0.7; pointer-events: none;">
            <span class="cardapio-admin-toggle-label" style="font-size: 0.9rem;">💵 Dinheiro</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="accept_cash" id="accept_cash" value="1" disabled
                       <?= ($config['accept_cash'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
        <div class="cardapio-admin-toggle-row pagamentos-field" style="padding: 6px 0; border-bottom: 1px solid #f1f5f9; opacity: 0.7; pointer-events: none;">
            <span class="cardapio-admin-toggle-label" style="font-size: 0.9rem;">💳 Cartão</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="accept_card" id="accept_card" value="1" disabled
                       <?= ($config['accept_card'] ?? ($config['accept_credit'] ?? 1)) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
        <div class="cardapio-admin-toggle-row pagamentos-field" style="padding: 6px 0; border: none; opacity: 0.7; pointer-events: none;">
            <span class="cardapio-admin-toggle-label" style="font-size: 0.9rem;">💠 PIX</span>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" name="accept_pix" id="accept_pix" value="1" disabled
                       onchange="document.getElementById('pix-key-fields').style.display = this.checked ? 'block' : 'none'"
                       <?= ($config['accept_pix'] ?? 1) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
        
        <!-- Chave PIX (condicional) -->
        <div id="pix-key-fields" class="pagamentos-field" style="margin-top: 10px; opacity: 0.7; pointer-events: none; <?= ($config['accept_pix'] ?? 1) ? '' : 'display: none;' ?>">
            <div style="display: flex; gap: 8px;">
                <div style="flex: 1;">
                    <label class="cardapio-admin-label" for="pix_key" style="font-size: 0.8rem; margin-bottom: 4px;">Chave PIX</label>
                    <input type="text" class="cardapio-admin-input" id="pix_key" name="pix_key" disabled
                           style="padding: 6px 10px; font-size: 0.85rem; background-color: #f8fafc;"
                           placeholder="Sua chave PIX"
                           value="<?= htmlspecialchars($config['pix_key'] ?? '') ?>">
                </div>
                <div style="width: 110px;">
                    <label class="cardapio-admin-label" for="pix_key_type" style="font-size: 0.8rem; margin-bottom: 4px;">Tipo</label>
                    <select class="cardapio-admin-input" id="pix_key_type" name="pix_key_type" disabled style="padding: 6px 8px; font-size: 0.85rem; background-color: #f8fafc;">
                        <option value="telefone" <?= ($config['pix_key_type'] ?? '') == 'telefone' ? 'selected' : '' ?>>Telefone</option>
                        <option value="cpf" <?= ($config['pix_key_type'] ?? '') == 'cpf' ? 'selected' : '' ?>>CPF</option>
                        <option value="cnpj" <?= ($config['pix_key_type'] ?? '') == 'cnpj' ? 'selected' : '' ?>>CNPJ</option>
                        <option value="email" <?= ($config['pix_key_type'] ?? '') == 'email' ? 'selected' : '' ?>>E-mail</option>
                        <option value="aleatoria" <?= ($config['pix_key_type'] ?? '') == 'aleatoria' ? 'selected' : '' ?>>Aleatória</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Card WhatsApp (compacto) -->
<div class="cardapio-admin-card" style="padding: 16px;">
    <div class="cardapio-admin-card-header" style="margin-bottom: 12px; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="message-circle"></i>
            <h3 class="cardapio-admin-card-title">WhatsApp</h3>
        </div>
        <div style="display: flex; gap: 6px;">
            <button type="button" class="cardapio-admin-btn" id="btn_edit_whatsapp"
                    style="background: #e2e8f0; color: #475569; padding: 6px 12px; font-size: 0.8rem;"
                    onclick="CardapioAdmin.toggleCardEdit('whatsapp')">
                <i data-lucide="pencil" size="14"></i> Editar
            </button>
            <button type="button" class="cardapio-admin-btn" id="btn_cancel_whatsapp"
                    style="background: #fee2e2; color: #ef4444; padding: 6px 12px; font-size: 0.8rem; display: none;"
                    onclick="CardapioAdmin.cancelCardEdit('whatsapp')">
                <i data-lucide="x" size="14"></i> Cancelar
            </button>
        </div>
    </div>

    <!-- Toggle Habilitar -->
    <div class="cardapio-admin-toggle-row whatsapp-field" style="padding: 6px 0; border: none; opacity: 0.7; pointer-events: none;">
        <span class="cardapio-admin-toggle-label">Habilitar WhatsApp</span>
        <label class="cardapio-admin-toggle">
            <input type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled" value="1" disabled
                   <?= ($config['whatsapp_enabled'] ?? 0) ? 'checked' : '' ?>>
            <span class="cardapio-admin-toggle-slider"></span>
        </label>
    </div>

    <!-- Número WhatsApp -->
    <div class="cardapio-admin-form-group whatsapp-field" style="margin-top: 10px; opacity: 0.7; pointer-events: none;">
        <label class="cardapio-admin-label" for="whatsapp_number" style="font-size: 0.8rem; margin-bottom: 4px;">Número WhatsApp</label>
        <input type="text" 
               class="cardapio-admin-input" 
               id="whatsapp_number" 
               name="whatsapp_number" 
               placeholder="(11) 9 9999-9999" 
               maxlength="20"
               disabled 
               style="width: 200px; padding: 6px 10px; font-size: 0.9rem; background-color: #f8fafc;"
               oninput="CardapioAdmin.maskPhone(this)"
               value="<?= htmlspecialchars($config['whatsapp_number'] ?? '') ?>">
    </div>

    <!-- Mensagens Automáticas (Grid Lado a Lado) -->
    <div style="margin-top: 10px;">
        <label class="cardapio-admin-label" style="font-size: 0.8rem; margin-bottom: 6px;">Mensagens Automáticas</label>
        
        <?php 
        // Recuperar mensagens
        $json = $config['whatsapp_message'] ?? '[]';
        $data = json_decode($json, true);

        $beforeList = [];
        $afterList = [];

        if (isset($data['before']) || isset($data['after'])) {
             // Formato Novo
             $beforeList = $data['before'] ?? [];
             $afterList = $data['after'] ?? [];
        } else if (is_array($data)) {
             // Formato Legado (posicional)
             if (count($data) >= 1) $beforeList[] = $data[0];
             if (count($data) >= 2) $afterList[] = $data[1];
        }

        // Defaults se vazio
        if (empty($beforeList)) $beforeList[] = 'Olá! Gostaria de fazer um pedido:';
        if (empty($afterList)) $afterList[] = 'Aguardo a confirmação.';
        ?>

        <div class="cardapio-admin-grid cardapio-admin-grid-2" style="gap: 16px;">
            <!-- Coluna 1: Antes do Pedido -->
            <div class="whatsapp-field" style="opacity: 0.7; pointer-events: none;">
                <label class="cardapio-admin-label" style="font-size: 0.75rem; color: #64748b; margin-bottom: 8px;">Mensagens Iniciais (Antes)</label>
                
                <div id="whatsapp-list-before">
                    <?php foreach ($beforeList as $msg): ?>
                    <div class="cardapio-admin-message-row" style="gap: 6px; margin-bottom: 6px; display: flex; align-items: center;">
                        <textarea class="cardapio-admin-input cardapio-admin-textarea" 
                                  name="whatsapp_data[before][]" 
                                  rows="2"
                                  disabled
                                  style="padding: 6px 10px; font-size: 0.85rem; background-color: #f8fafc; border: 1px solid #cbd5e1; width: 100%; min-height: 48px; resize: none;"
                                  placeholder="Nova mensagem..."><?= htmlspecialchars($msg) ?></textarea>
                        <button type="button" class="cardapio-admin-btn" 
                                style="background: #fee2e2; color: #ef4444; padding: 0; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" 
                                onclick="this.parentElement.remove()">
                            <i data-lucide="trash-2" size="14"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; gap: 6px;">
                    <button type="button" class="cardapio-admin-btn" 
                            style="margin-top: 2px; border: 1px dashed #cbd5e1; background: #f8fafc; color: #64748b; width: 100%; justify-content: center; padding: 8px; font-size: 0.8rem; border-radius: 6px;"
                            onclick="CardapioAdmin.addWhatsappMessage('before')">
                        <i data-lucide="plus" size="14"></i> Adicionar Mensagem
                    </button>
                    <!-- Espaçador para alinhar com a lixeira acima -->
                    <div style="width: 32px; flex-shrink: 0;"></div>
                </div>
            </div>

            <!-- Coluna 2: Depois do Pedido -->
            <div class="whatsapp-field" style="opacity: 0.7; pointer-events: none;">
                <label class="cardapio-admin-label" style="font-size: 0.75rem; color: #64748b; margin-bottom: 8px;">Mensagens Finais (Depois)</label>
                
                <div id="whatsapp-list-after">
                    <?php foreach ($afterList as $msg): ?>
                    <div class="cardapio-admin-message-row" style="gap: 6px; margin-bottom: 6px; display: flex; align-items: center;">
                        <textarea class="cardapio-admin-input cardapio-admin-textarea" 
                                  name="whatsapp_data[after][]" 
                                  rows="2"
                                  disabled
                                  style="padding: 6px 10px; font-size: 0.85rem; background-color: #f8fafc; border: 1px solid #cbd5e1; width: 100%; min-height: 48px; resize: none;"
                                  placeholder="Nova mensagem..."><?= htmlspecialchars($msg) ?></textarea>
                        <button type="button" class="cardapio-admin-btn" 
                                style="background: #fee2e2; color: #ef4444; padding: 0; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" 
                                onclick="this.parentElement.remove()">
                            <i data-lucide="trash-2" size="14"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; gap: 6px;">
                    <button type="button" class="cardapio-admin-btn" 
                            style="margin-top: 2px; border: 1px dashed #cbd5e1; background: #f8fafc; color: #64748b; width: 100%; justify-content: center; padding: 8px; font-size: 0.8rem; border-radius: 6px;"
                            onclick="CardapioAdmin.addWhatsappMessage('after')">
                        <i data-lucide="plus" size="14"></i> Adicionar Mensagem
                    </button>
                    <!-- Espaçador para alinhar com a lixeira acima -->
                    <div style="width: 32px; flex-shrink: 0;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
