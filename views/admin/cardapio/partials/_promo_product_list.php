<?php
/**
 * PARTIAL: Lista de Itens em Promoção
 */
?>

<!-- Lista de Itens em Promoção -->
<div class="cardapio-admin-card" style="margin-top: 20px;">
    <div class="cardapio-admin-card-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i data-lucide="percent"></i>
            <h3 class="cardapio-admin-card-title">Itens em Promoção</h3>
        </div>
    </div>
    
    <div id="promo-products-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; padding: 20px;">
        <?php if (!empty($productPromotions)): ?>
            <?php foreach ($productPromotions as $product): ?>
                <?php
                    $productId = (int) ($product['id'] ?? 0);
                    $isOnPromotion = !empty($product['is_on_promotion']);
                    $productName = (string) ($product['name'] ?? '');
                    $categoryName = (string) ($product['category_name'] ?? 'Sem categoria');
                    $price = (float) ($product['price'] ?? 0);
                    $promoPrice = (float) ($product['promotional_price'] ?? 0);
                    $promoBg = $isOnPromotion ? 'white' : '#f8fafc';
                    $promoBorder = $isOnPromotion ? '#e2e8f0' : '#cbd5e1';
                    $promoOpacity = $isOnPromotion ? '' : 'opacity: 0.8;';
                    $promoPriceColor = $isOnPromotion ? '#ea580c' : '#64748b';
                    $badgeBg = $isOnPromotion ? '#fff7ed' : '#f1f5f9';
                    $badgeColor = $isOnPromotion ? '#ea580c' : '#94a3b8';
                    $validityColor = $isOnPromotion ? '#16a34a' : '#64748b';
                    $checkedAttr = $isOnPromotion ? 'checked' : '';
                ?>
                <div class="cardapio-admin-combo-card" data-promo-id="<?= (int) $productId ?>" style="background: <?= \App\Helpers\ViewHelper::e($promoBg) ?>; border: 1px solid <?= \App\Helpers\ViewHelper::e($promoBorder) ?>; border-radius: 12px; padding: 20px; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; <?= \App\Helpers\ViewHelper::e($promoOpacity) ?>">
                    
                    <!-- Cabeçalho: Nome e Toggle -->
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <h4 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0;"><?= \App\Helpers\ViewHelper::e($productName) ?></h4>
                            <span style="font-size: 0.8rem; color: #64748b;"><?= \App\Helpers\ViewHelper::e($categoryName) ?></span>
                        </div>
                        
                        <!-- Toggle Ativo/Inativo -->
                        <label class="cardapio-admin-toggle" title="<?= \App\Helpers\ViewHelper::e($isOnPromotion ? 'Desativar' : 'Ativar') ?>">
                            <input type="checkbox" 
                                   onchange="PromoProducts.togglePromotion(<?= (int) $productId ?>, this.checked)" 
                                   <?= \App\Helpers\ViewHelper::e($checkedAttr) ?>>
                            <span class="cardapio-admin-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Divisor -->
                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin-bottom: 15px;">

                    <!-- Rodapé: Preços e Ações -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        
                        <!-- Preços -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.2rem; font-weight: 700; color: <?= \App\Helpers\ViewHelper::e($promoPriceColor) ?>;">
                                R$ <?= number_format($promoPrice, 2, ',', '.') ?>
                            </span>
                            <?php if ($price > $promoPrice && $price > 0): ?>
                                <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.9rem;">
                                    R$ <?= number_format($price, 2, ',', '.') ?>
                                </span>
                                <?php 
                                    $discountPercent = round((1 - $promoPrice / $price) * 100);
                                ?>
                                <span style="background: <?= \App\Helpers\ViewHelper::e($badgeBg) ?>; color: <?= \App\Helpers\ViewHelper::e($badgeColor) ?>; font-size: 0.8rem; font-weight: 600; padding: 2px 8px; border-radius: 999px;">
                                    -<?= (int) $discountPercent ?>%
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Data e Botões -->
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="display: flex; align-items: center; gap: 4px; color: <?= \App\Helpers\ViewHelper::e($validityColor) ?>; font-size: 0.85rem;" title="Validade">
                                <?php
                                    if (empty($product['promo_expires_at'])) {
                                        echo '<i data-lucide="infinity" style="width: 14px; height: 14px;"></i> <span>Sempre</span>';
                                    } elseif ($product['promo_expires_at'] == date('Y-m-d')) {
                                        echo '<i data-lucide="clock" style="width: 14px; height: 14px;"></i> <span>Hoje</span>';
                                    } else {
                                        echo '<i data-lucide="calendar" style="width: 14px; height: 14px;"></i> <span>' . date('d/m/y', strtotime($product['promo_expires_at'])) . '</span>';
                                    }
                ?>
                            </div>

                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="cardapio-admin-btn-icon" style="color: #ef4444; padding: 4px; background: transparent; border: none; cursor: pointer;" 
                                        onclick="PromoProducts.removePromotion(<?= (int) $productId ?>)" title="Remover Promoção">
                                    <i data-lucide="trash-2" size="18"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p id="no-promo-products-msg" style="padding: 20px; text-align: center; color: #94a3b8; grid-column: 1 / -1;">Nenhum item em promoção no momento.</p>
        <?php endif; ?>
    </div>
</div>
