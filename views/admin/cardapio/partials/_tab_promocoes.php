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

        <!-- Linha 1: Nome e Preços -->
        <div class="cardapio-admin-grid cardapio-admin-grid-3" style="gap: 20px; align-items: start;">
            <!-- Nome -->
            <div class="cardapio-admin-form-group" style="grid-column: span 1;">
                <label class="cardapio-admin-label" for="combo_name">Nome da Oferta *</label>
                <input type="text" class="cardapio-admin-input" id="combo_name" placeholder="Ex: Combo Família Feliz">
            </div>

            <!-- Preços -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; grid-column: span 1;">
                <div class="cardapio-admin-form-group">
                    <label class="cardapio-admin-label" for="combo_price" style="color: #16a34a;">Preço Promocional *</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 10px; top: 10px; color: #64748b;">R$</span>
                        <input type="text" class="cardapio-admin-input" id="combo_price" placeholder="0,00" style="padding-left: 35px; font-weight: 600; color: #16a34a;">
                    </div>
                </div>
                <div class="cardapio-admin-form-group">
                    <label class="cardapio-admin-label" for="combo_original_price">Preço Original</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 10px; top: 10px; color: #94a3b8;">R$</span>
                        <input type="text" class="cardapio-admin-input" id="combo_original_price" placeholder="0,00" readonly style="padding-left: 35px; background: #f1f5f9; color: #64748b;">
                    </div>
                </div>
            </div>

            <!-- Validade -->
            <div class="cardapio-admin-form-group" style="grid-column: span 1;">
                <label class="cardapio-admin-label">Validade da Oferta</label>
                <div style="display: flex; gap: 10px;">
                    <select class="cardapio-admin-select" id="combo_validity_type" onchange="toggleValidityDate()">
                        <option value="always">Sempre Ativo</option>
                        <option value="today">Só Hoje</option>
                        <option value="date">Válido até...</option>
                    </select>
                    <input type="date" class="cardapio-admin-input" id="combo_valid_until" style="display: none;">
                </div>
            </div>
        </div>

        <!-- Linha 2: Descrição e Foto -->
        <div class="cardapio-admin-grid cardapio-admin-grid-2" style="gap: 20px; margin-top: 20px;">
            <div class="cardapio-admin-form-group">
                <label class="cardapio-admin-label" for="combo_description">Descrição</label>
                <textarea class="cardapio-admin-input cardapio-admin-textarea" id="combo_description" placeholder="Descreva o que vem neste combo..." style="height: 100px; resize: none;"></textarea>
            </div>

            <!-- Upload de Imagem Simplificado -->
            <div class="cardapio-admin-form-group">
                <label class="cardapio-admin-label">Foto do Combo</label>
                <div class="image-upload-area" onclick="document.getElementById('combo_image').click()" style="border: 2px dashed #cbd5e1; border-radius: 8px; height: 100px; display: flex; align-items: center; justify-content: center; cursor: pointer; background: #f8fafc; transition: all 0.2s;">
                    <input type="file" id="combo_image" accept="image/*" style="display: none;" onchange="previewComboImage(this)">
                    <div id="combo_image_preview" style="text-align: center; color: #64748b;">
                        <i data-lucide="image" style="margin: 0 auto 5px;"></i>
                        <span style="font-size: 0.85rem;">Clique para adicionar foto</span>
                    </div>
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
    
    <div id="param-list-combos">
        <?php if (!empty($combos)): ?>
            <?php foreach ($combos as $combo): ?>
                <div class="cardapio-admin-combo-item" style="padding: 15px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <img src="<?= !empty($combo['image']) ? BASE_URL . '/uploads/' . $combo['image'] : 'https://via.placeholder.com/50' ?>" 
                             style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                        <div>
                            <strong style="display: block; color: #1e293b;"><?= htmlspecialchars($combo['name']) ?></strong>
                            <span style="color: #16a34a; font-weight: 600;">R$ <?= number_format($combo['price'], 2, ',', '.') ?></span>
                            <?php if (!empty($combo['valid_until'])): ?>
                                <span style="font-size: 0.8rem; color: #ef4444; margin-left: 10px;">Válido até: <?= date('d/m/Y', strtotime($combo['valid_until'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                         <button type="button" class="cardapio-admin-btn" style="color: #475569;" onclick="location.href='<?= BASE_URL ?>/admin/loja/cardapio/combo/editar?id=<?= $combo['id'] ?>'">
                            <i data-lucide="pencil" size="16"></i>
                        </button>
                        <button type="button" class="cardapio-admin-btn" style="color: #ef4444;" onclick="if(confirm('Tem certeza?')) location.href='<?= BASE_URL ?>/admin/loja/cardapio/combo/deletar?id=<?= $combo['id'] ?>'">
                            <i data-lucide="trash-2" size="16"></i>
                        </button>
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
