<?php
/**
 * PARTIAL: Formulário de Criação/Edição de Combo
 * Extraído de _tab_promocoes.php
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
                <h3 id="comboFormTitle" class="cardapio-admin-card-title" style="font-size: 1.1rem; color: #1e293b;">Criar Novo Combo</h3>
                <p id="comboFormSubtitle" style="font-size: 0.85rem; color: #64748b; margin: 0;">Configure sua oferta especial em uma única tela.</p>
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

            <!-- 4. Foto -->
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

            <!-- 5. Descrição -->
            <div style="grid-column: 1 / span 2; margin-top: 5px;">
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Descrição</label>
                <textarea class="cardapio-admin-input cardapio-admin-textarea" id="combo_description" placeholder="Descreva os itens..." style="height: 42px; min-height: 42px; resize: none;"></textarea>
            </div>

            <!-- 6. Validade -->
            <div style="grid-column: 3 / span 2; margin-top: 5px;">
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Validade</label>
                <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 15px; align-items: center;">
                    <select class="cardapio-admin-select" id="combo_validity_type" onchange="toggleValidityDate()" style="width: 100%;">
                        <option value="always">Sempre Ativo</option>
                        <option value="today">Só Hoje</option>
                        <option value="date">Válido até...</option>
                    </select>
                    <input type="date" class="cardapio-admin-input" id="combo_valid_until" style="display: none; border-color: #2563eb;">
                </div>
            </div>
        </div>

        <!-- Lista Resumo de Itens -->
        <div id="comboItemsSummary" style="display: none; margin-top: 20px; padding: 15px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="font-size: 0.95rem; font-weight: 600; color: #166534; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="package-check" size="18"></i>
                    Itens do Combo
                </h4>
                <button type="button" onclick="CardapioAdmin.clearComboItems()" style="font-size: 0.75rem; color: #dc2626; background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                    <i data-lucide="trash-2" size="14"></i> Limpar
                </button>
            </div>
            <ul id="comboItemsList" style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 8px;"></ul>
        </div>

        <hr style="margin: 30px 0; border-color: #e2e8f0;">

        <!-- Seleção de Itens -->
        <h4 style="font-size: 1rem; font-weight: 600; color: #334155; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="layers" size="18"></i>
            Selecione os Itens do Combo
        </h4>

        <div class="combo-selection-container" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff;">
            <?php if (!empty($productsByCategory)): ?>
                
                <!-- Abas de Categorias -->
                <div class="combo-category-tabs" style="display: flex; overflow-x: auto; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 4px;">
                    <?php $i = 0;
                foreach ($productsByCategory as $categoryName => $products): $active = $i === 0 ? 'active' : ''; ?>
                    <button type="button" 
                            class="combo-tab-btn <?= $active ?>" 
                            onclick="toggleComboTab(this, 'cat_<?= md5($categoryName) ?>')"
                            style="padding: 10px 16px; border: none; background: transparent; color: #64748b; font-weight: 500; cursor: pointer; border-radius: 6px; white-space: nowrap; transition: all 0.2s;">
                        <?= htmlspecialchars($categoryName) ?>
                    </button>
                    <?php $i++; endforeach; ?>
                </div>

                <!-- Conteúdo das Abas -->
                <div class="combo-category-contents" style="padding: 20px; background: #fff;">
                    <?php $i = 0;
                foreach ($productsByCategory as $categoryName => $products): $display = $i === 0 ? 'grid' : 'none'; ?>
                    <div id="cat_<?= md5($categoryName) ?>" class="combo-tab-content" style="display: <?= $display ?>; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                        <?php foreach ($products as $product): ?>
                            <div class="combo-product-card" 
                                 id="card_prod_<?= $product['id'] ?>"
                                 style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; position: relative; transition: all 0.2s; background: #fff;">
                                
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

        <!-- Botões Salvar/Cancelar -->
        <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" id="btnCancelCombo" class="cardapio-admin-btn" onclick="CardapioAdmin.cancelComboEdit()" style="padding: 12px 20px; font-size: 1rem; background: #f1f5f9; color: #475569; display: none;">
                <i data-lucide="x"></i>
                Cancelar
            </button>
            <button type="button" id="btnSaveCombo" class="cardapio-admin-btn cardapio-admin-btn-primary" onclick="CardapioAdmin.saveCombo()" style="padding: 12px 30px; font-size: 1rem;">
                <i data-lucide="save"></i>
                <span id="btnSaveComboText">Salvar Promoção</span>
            </button>
        </div>
    </div>
</div>
