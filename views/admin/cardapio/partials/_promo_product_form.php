<?php
/**
 * PARTIAL: Formulário para adicionar item em promoção
 */
?>

<!-- Container: Adicionar Item em Promoção -->
<div class="cardapio-admin-card" id="promoProductFormContainer" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
    <div class="cardapio-admin-card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i data-lucide="tag" size="24" style="color: #3b82f6;"></i>
            <div>
                <h3 class="cardapio-admin-card-title" style="font-size: 1.1rem; color: #1e293b; margin: 0;">Adicionar Item em Promoção</h3>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Selecione um produto e defina o preço promocional.</p>
            </div>
        </div>
        <button type="button" class="cardapio-admin-btn cardapio-admin-btn-primary" onclick="PromoProducts.save()" style="padding: 10px 24px; font-size: 0.95rem;">
            <i data-lucide="plus"></i>
            Adicionar
        </button>
    </div>

    <div style="padding: 24px;">
        <!-- Layout Grid 4 colunas -->
        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 15px; align-items: start;">
            
            <!-- 1. Produto -->
            <div>
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Produto <span style="color: red">*</span></label>
                <select class="cardapio-admin-select" id="promo_product_id" style="width: 100%;" onchange="PromoProducts.onProductChange(this)">
                    <option value="" data-price="0">Selecione um produto...</option>
                    <?php
                    $currentCategory = '';
                    foreach (($availableForPromotion ?? []) as $product):
                        if ($currentCategory !== $product['category_name']):
                            if ($currentCategory !== '') echo '</optgroup>';
                            $currentCategory = $product['category_name'] ?? 'Sem categoria';
                            echo '<optgroup label="' . htmlspecialchars($currentCategory) . '">';
                        endif;
                    ?>
                        <option value="<?= $product['id'] ?>" data-price="<?= $product['price'] ?>">
                            <?= htmlspecialchars($product['name']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if ($currentCategory !== '') echo '</optgroup>'; ?>
                </select>
            </div>

            <!-- 2. Preço Original (readonly) -->
            <div>
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Preço Original</label>
                <div class="cardapio-input-group" style="display: flex; width: 100%;">
                    <span class="cardapio-input-group-btn" style="padding: 10px 12px; border-radius: 8px 0 0 8px; border-right: 0; display: flex; align-items: center; justify-content: center; background: #e2e8f0; border: 1px solid #d1d5db;">R$</span>
                    <input type="text" class="cardapio-admin-input" id="promo_original_price" placeholder="0,00" readonly style="background-color: #f1f5f9; color: #64748b; border-radius: 0 8px 8px 0; width: 100%; flex: 1; border-right: 1px solid #d1d5db;">
                </div>
            </div>

            <!-- 3. Preço Promocional -->
            <div>
                <label class="cardapio-admin-label" style="margin-bottom: 4px; color: #16a34a;">Preço Promo <span style="color: red">*</span></label>
                <div class="cardapio-input-group" style="display: flex; width: 100%;">
                    <span class="cardapio-input-group-btn" style="padding: 10px 12px; border-radius: 8px 0 0 8px; border-right: 0; display: flex; align-items: center; justify-content: center; background: #e2e8f0; border: 1px solid #d1d5db;">R$</span>
                    <input type="text" class="cardapio-admin-input" id="promo_price" placeholder="0,00" onkeyup="formatCurrency(this)" style="font-weight: 700; color: #16a34a; border-radius: 0 8px 8px 0; width: 100%; flex: 1; border-right: 1px solid #d1d5db;">
                </div>
            </div>

            <!-- 4. Validade (select + date inline) -->
            <div>
                <label class="cardapio-admin-label" style="margin-bottom: 4px;">Validade</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <select class="cardapio-admin-select" id="promo_validity_type" onchange="PromoProducts.toggleValidityDate()" style="width: 100%;">
                        <option value="always">Sempre Ativo</option>
                        <option value="today">Só Hoje</option>
                        <option value="date">Válido até...</option>
                    </select>
                    <input type="date" class="cardapio-admin-input" id="promo_expires_at" style="display: none; border-color: #2563eb;">
                </div>
            </div>
        </div>

        <!-- Info sobre desconto -->
        <div id="promo-discount-preview" style="display: none; margin-top: 15px; padding: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="percent" style="width: 18px; height: 18px; color: #16a34a;"></i>
                <span id="promo-discount-text" style="color: #166534; font-weight: 500;"></span>
            </div>
        </div>
    </div>
</div>
