<?php
/**
 * ============================================
 * PARTIAL: Aba Promoções (Nova Interface Unificada)
 * ============================================
 */
?>

<!-- Container Principal: Criar/Editar Combo -->
<div class="cardapio-admin-card" id="comboFormContainer" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
    <div class="cardapio-admin-card-header" style="background: linear-gradient(to right, #f8fafc, #fff);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="background: #eff6ff; pading: 8px; border-radius: 6px; color: #3b82f6;">
                <i data-lucide="package-plus" size="24"></i>
            </div>
            <div>
                <h3 class="cardapio-admin-card-title" style="font-size: 1.1rem; color: #1e293b;">Criar Novo Combo</h3>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Configure sua oferta especial em uma única tela.</p>
            </div>
        </div>
    </div>

    <div style="padding: 24px;">
        <!-- ID oculto para edição -->
        <input type="hidden" id="combo_id" value="">

        <!-- Layout Unificado (Grid 4 Colunas) -->
        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 15px; align-items: start; margin-bottom: 20px;">
            
            <!-- 1. Nome -->
            <div style="grid-column: 1;">
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Nome da Oferta <span style="color: red">*</span></label>
                <input type="text" class="cardapio-admin-input" id="combo_name" placeholder="Ex: Combo Família">
            </div>

            <!-- 2. Preço Promo -->
            <div style="grid-column: 2;">
                <label class="cardapio-admin-label" style="margin-bottom: 4px; color: #16a34a;">Preço Promo <span style="color: red">*</span></label>
                <div class="cardapio-input-group" style="display: flex; width: 100%;">
                    <span class="cardapio-input-group-btn" style="padding: 10px 12px; border-radius: 8px 0 0 8px; border-right: 0; display: flex; align-items: center; justify-content: center; background: #e2e8f0; border: 1px solid #d1d5db;">R$</span>
                    <input type="text" class="cardapio-admin-input" id="combo_price" placeholder="0,00" onkeyup="formatCurrency(this)" style="font-weight: 700; color: #16a34a; border-radius: 0 8px 8px 0; width: 100%; flex: 1; border-right: 1px solid #d1d5db;">
                </div>
            </div>

            <!-- 3. Preço Original -->
            <div style="grid-column: 3;">
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Preço Original</label>
                <div class="cardapio-input-group" style="display: flex; width: 100%;">
                    <span class="cardapio-input-group-btn" style="padding: 10px 12px; border-radius: 8px 0 0 8px; border-right: 0; display: flex; align-items: center; justify-content: center; background: #e2e8f0; border: 1px solid #d1d5db;">R$</span>
                    <input type="text" class="cardapio-admin-input" id="combo_original_price" placeholder="0,00" readonly style="background-color: #f1f5f9; color: #64748b; border-radius: 0 8px 8px 0; width: 100%; flex: 1; border-right: 1px solid #d1d5db;">
                </div>
            </div>

            <!-- 4. Foto (Agora na Linha 1, Direita) -->
            <div style="grid-column: 4;">
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Foto</label>
                <div onclick="document.getElementById('combo_image').click()" style="border: 1px dashed #cbd5e1; border-radius: 6px; height: 42px; display: flex; align-items: center; gap: 10px; padding: 0 10px; cursor: pointer; background: #f8fafc; transition: all 0.2s;">
                    <div id="combo_image_preview" style="width: 28px; height: 28px; background: #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;">
                        <i data-lucide="image" size="14" style="color: #94a3b8;"></i>
                    </div>
                    <span style="font-size: 0.8rem; color: #64748b; line-height: 1.2;">Trocar</span>
                    <input type="file" id="combo_image" accept="image/*" style="display: none;" onchange="previewComboImage(this)">
                </div>
            </div>

            <!-- 5. Descrição (Linha 2, Esquerda - Ocupa Nome + Preço Promo) -->
            <div style="grid-column: 1 / span 2; margin-top: 5px;">
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Descrição</label>
                <textarea class="cardapio-admin-input cardapio-admin-textarea" id="combo_description" placeholder="Descreva os itens..." style="height: 42px; min-height: 42px; resize: none;"></textarea>
            </div>

            <!-- 6. Validade (Linha 2, Colunas 3 e 4 - Grid interno alinhado) -->
            <div style="grid-column: 3 / span 2; margin-top: 5px;">
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Validade</label>
                <!-- Grid Interno: 1fr (col3) 1.2fr (col4) -->
                <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 15px; align-items: center;">
                    <select class="cardapio-admin-select" id="combo_validity_type" onchange="toggleValidityDate()" style="width: 100%;">
                        <option value="always">Sempre Ativo</option>
                        <option value="today">Só Hoje</option>
                        <option value="date">Válido até...</option>
                    </select>
                    
                    <!-- Input de Data (lado direito, embaixo da foto) -->
                    <input type="date" class="cardapio-admin-input" id="combo_valid_until" style="display: none; border-color: #2563eb;">
                </div>
            </div>

        </div>

        <hr style="margin: 30px 0; border-color: #e2e8f0;">

        <!-- Seleção de Itens (Layout Abas + Grid) -->
        <h4 style="font-size: 1rem; font-weight: 600; color: #334155; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="layers" size="18"></i>
            Selecione os Itens do Combo
        </h4>

        <div class="combo-selection-container" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff;">
            <?php if (!empty($productsByCategory)): ?>
                
                <!-- Abas de Categorias -->
                <div class="combo-category-tabs" style="display: flex; overflow-x: auto; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 4px;">
                    <?php $i = 0; foreach ($productsByCategory as $categoryName => $products): $active = $i === 0 ? 'active' : ''; ?>
                    <button type="button" 
                            class="combo-tab-btn <?= $active ?>" 
                            onclick="toggleComboTab(this, 'cat_<?= md5($categoryName) ?>')"
                            style="padding: 10px 16px; border: none; background: transparent; color: #64748b; font-weight: 500; cursor: pointer; border-radius: 6px; white-space: nowrap; transition: all 0.2s;">
                        <?= htmlspecialchars($categoryName) ?>
                    </button>
                    <?php $i++; endforeach; ?>
                </div>

                <!-- Conteúdo das Abas (Grid de Produtos) -->
                <div class="combo-category-contents" style="padding: 20px; background: #fff;">
                    <?php $i = 0; foreach ($productsByCategory as $categoryName => $products): $display = $i === 0 ? 'grid' : 'none'; ?>
                    <div id="cat_<?= md5($categoryName) ?>" class="combo-tab-content" style="display: <?= $display ?>; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                        <?php foreach ($products as $product): ?>
                            <div class="combo-product-card" 
                                 id="card_prod_<?= $product['id'] ?>"
                                 style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; position: relative; transition: all 0.2s; background: #fff;">
                                
                                <!-- Input Quantidade Oculto (para leitura fácil) -->
                                <input type="hidden" 
                                       class="combo-product-qty" 
                                       id="qty_prod_<?= $product['id'] ?>"
                                       value="0"
                                       data-id="<?= $product['id'] ?>"
                                       data-price="<?= $product['price'] ?>">

                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                    <span style="font-weight: 600; font-size: 0.9rem; color: #334155; line-height: 1.3; margin-right: 10px;"><?= htmlspecialchars($product['name']) ?></span>
                                </div>
                                
                                <span style="font-size: 0.85rem; color: #64748b; display: block; margin-bottom: 12px;">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>

                                <!-- Controles de Quantidade -->
                                <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-radius: 6px; padding: 4px;">
                                     <button type="button" 
                                             class="btn-qty-minus"
                                             onclick="updateComboQty('<?= $product['id'] ?>', -1)"
                                             style="width: 28px; height: 28px; border: 1px solid #e2e8f0; background: #fff; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b;">
                                         <i data-lucide="minus" size="14"></i>
                                     </button>
                                     <span id="display_qty_<?= $product['id'] ?>" style="font-weight: 600; color: #334155; font-size: 0.9rem;">0</span>
                                     <button type="button" 
                                             class="btn-qty-plus"
                                             onclick="updateComboQty('<?= $product['id'] ?>', 1)"
                                             style="width: 28px; height: 28px; border: 1px solid #e2e8f0; background: #fff; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #3b82f6;">
                                         <i data-lucide="plus" size="14"></i>
                                     </button>
                                </div>

                                <!-- Toggle Adicionais -->
                                <div class="allow-additionals-wrapper" style="border-top: 1px solid #f1f5f9; margin-top: 10px; padding-top: 8px; display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 0.75rem; color: #94a3b8;">Adicionais?</span>
                                    <label class="cardapio-admin-toggle" style="transform: scale(0.7); transform-origin: right center;">
                                        <input type="checkbox" class="combo-additional-toggle" data-prod-id="<?= $product['id'] ?>" checked>
                                        <span class="cardapio-admin-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php $i++; endforeach; ?>
                </div>

            <?php else: ?>
                <p style="padding: 20px; text-align: center; color: #94a3b8;">Cadastre produtos primeiro.</p>
            <?php endif; ?>
        </div>

        <!-- Botão Salvar (Fixo e Grande) -->
        <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
            <button type="button" class="cardapio-admin-btn cardapio-admin-btn-primary" onclick="CardapioAdmin.saveCombo()" style="padding: 12px 30px; font-size: 1rem;">
                <i data-lucide="save"></i>
                Salvar Promoção
            </button>
        </div>
    </div>
</div>

<!-- Lista de Combos Ativos (Histórico) -->
<div class="cardapio-admin-card" style="margin-top: 40px;">
    <div class="cardapio-admin-card-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i data-lucide="list"></i>
            <h3 class="cardapio-admin-card-title">Promoções Ativas</h3>
        </div>
    </div>
    
    <div id="param-list-combos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <?php if (!empty($combos)): ?>
            <?php foreach ($combos as $combo): ?>
                <div class="cardapio-admin-combo-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    
                    <!-- Cabeçalho: Nome, Tag e Toggle -->
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h4 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0;"><?= htmlspecialchars($combo['name']) ?></h4>
                            <?php if (!empty($combo['discount_percent']) && $combo['discount_percent'] > 0): ?>
                                <span style="background: #fff7ed; color: #ea580c; font-size: 0.8rem; font-weight: 600; padding: 2px 8px; border-radius: 999px;">
                                    -<?= $combo['discount_percent'] ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Toggle Ativo/Inativo -->
                         <label class="cardapio-admin-toggle" title="<?= $combo['is_active'] ? 'Desativar' : 'Ativar' ?>">
                            <input type="checkbox" onchange="toggleComboActive(<?= $combo['id'] ?>, this.checked)" <?= $combo['is_active'] ? 'checked' : '' ?>>
                            <span class="cardapio-admin-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Descrição dos Itens -->
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px; line-height: 1.5;">
                        <?= htmlspecialchars($combo['items_description'] ?? $combo['description']) ?>
                    </p>

                    <!-- Divisor -->
                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin-bottom: 15px;">

                    <!-- Rodapé: Preços e Ações -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        
                        <!-- Preços -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.2rem; font-weight: 700; color: #ea580c;">
                                R$ <?= number_format($combo['price'], 2, ',', '.') ?>
                            </span>
                            <?php if (!empty($combo['original_price']) && $combo['original_price'] > $combo['price']): ?>
                                <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.9rem;">
                                    R$ <?= number_format($combo['original_price'], 2, ',', '.') ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Data e Botões -->
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="display: flex; align-items: center; gap: 4px; color: #16a34a; font-size: 0.85rem;" title="Validade">
                                <?php 
                                    if (empty($combo['valid_until'])) {
                                        echo '<i data-lucide="infinity" style="width: 14px; height: 14px;"></i> <span>Ativo</span>';
                                    } elseif ($combo['valid_until'] == date('Y-m-d')) {
                                        echo '<i data-lucide="clock" style="width: 14px; height: 14px;"></i> <span>Hoje</span>';
                                    } else {
                                        echo '<i data-lucide="calendar" style="width: 14px; height: 14px;"></i> <span>' . date('d/m/y', strtotime($combo['valid_until'])) . '</span>';
                                    }
                                ?>
                            </div>

                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="cardapio-admin-btn-icon" style="color: #475569; padding: 4px; background: transparent; border: none; cursor: pointer;" 
                                        onclick="location.href='<?= BASE_URL ?>/admin/loja/cardapio/combo/editar?id=<?= $combo['id'] ?>'" title="Editar">
                                    <i data-lucide="pencil" size="18"></i>
                                </button>
                                <button type="button" class="cardapio-admin-btn-icon" style="color: #ef4444; padding: 4px; background: transparent; border: none; cursor: pointer;" 
                                        onclick="if(confirm('Excluir este combo?')) location.href='<?= BASE_URL ?>/admin/loja/cardapio/combo/deletar?id=<?= $combo['id'] ?>'" title="Excluir">
                                    <i data-lucide="trash-2" size="18"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="padding: 20px; text-align: center; color: #94a3b8;">Nenhuma promoção ativa no momento.</p>
        <?php endif; ?>
    </div>
</div>

<style>
/* Estilos Específicos para Abas do Combo */
.combo-tab-btn.active {
    background: #fff !important;
    color: #3b82f6 !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.combo-product-card.selected {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
}
.combo-product-card.selected .check-indicator {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
}
.combo-product-card.selected .check-indicator i {
    opacity: 1 !important;
}
</style>

<script>
function toggleValidityDate() {
    // Corrigido seletor para pegar o valor corretamente
    const select = document.getElementById('combo_validity_type');
    const input = document.getElementById('combo_valid_until');
    
    if (select && input) {
        if (select.value === 'date') {
            input.style.display = 'block';
        } else {
            input.style.display = 'none';
        }
    }
}

function toggleComboTab(btn, tabId) {
    // Remove active
    document.querySelectorAll('.combo-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.combo-tab-content').forEach(c => c.style.display = 'none');
    
    // Create active
    btn.classList.add('active');
    const content = document.getElementById(tabId);
    if(content) content.style.display = 'grid';
}

function updateComboQty(id, change) {
    const qtyInput = document.getElementById('qty_prod_' + id);
    const display = document.getElementById('display_qty_' + id);
    const card = document.getElementById('card_prod_' + id);
    
    if (!qtyInput || !display) return;
    
    let current = parseInt(qtyInput.value) || 0;
    let newValue = current + change;
    
    if (newValue < 0) newValue = 0;
    
    qtyInput.value = newValue;
    display.textContent = newValue;
    
    // Visual update
    if (newValue > 0) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }
    
    calculateComboOriginalPrice();
}

function calculateComboOriginalPrice() {
    let total = 0;
    
    document.querySelectorAll('.combo-product-qty').forEach(input => {
        const qty = parseInt(input.value) || 0;
        const price = parseFloat(input.getAttribute('data-price') || 0);
        total += qty * price;
    });
    
    // Format Currency Br
    const formatter = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('combo_original_price').value = formatter.format(total);
}

function previewComboImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('combo_image_preview').innerHTML = `
                <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
            `;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
