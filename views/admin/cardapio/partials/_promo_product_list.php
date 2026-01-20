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
                <div class="cardapio-admin-combo-card" data-promo-id="<?= $product['id'] ?>" style="background: <?= $product['is_on_promotion'] ? 'white' : '#f8fafc' ?>; border: 1px solid <?= $product['is_on_promotion'] ? '#e2e8f0' : '#cbd5e1' ?>; border-radius: 12px; padding: 20px; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; <?= $product['is_on_promotion'] ? '' : 'opacity: 0.8;' ?>">
                    
                    <!-- Cabeçalho: Nome e Toggle -->
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <h4 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0;"><?= htmlspecialchars($product['name']) ?></h4>
                            <span style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($product['category_name'] ?? 'Sem categoria') ?></span>
                        </div>
                        
                        <!-- Toggle Ativo/Inativo -->
                        <label class="cardapio-admin-toggle" title="<?= $product['is_on_promotion'] ? 'Desativar' : 'Ativar' ?>">
                            <input type="checkbox" 
                                   onchange="PromoProducts.togglePromotion(<?= $product['id'] ?>, this.checked)" 
                                   <?= $product['is_on_promotion'] ? 'checked' : '' ?>>
                            <span class="cardapio-admin-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Divisor -->
                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin-bottom: 15px;">

                    <!-- Rodapé: Preços e Ações -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        
                        <!-- Preços -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.2rem; font-weight: 700; color: <?= $product['is_on_promotion'] ? '#ea580c' : '#64748b' ?>;">
                                R$ <?= number_format($product['promotional_price'], 2, ',', '.') ?>
                            </span>
                            <?php if ($product['price'] > $product['promotional_price']): ?>
                                <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.9rem;">
                                    R$ <?= number_format($product['price'], 2, ',', '.') ?>
                                </span>
                                <?php 
                                    $discountPercent = round((1 - $product['promotional_price'] / $product['price']) * 100);
                                ?>
                                <span style="background: <?= $product['is_on_promotion'] ? '#fff7ed' : '#f1f5f9' ?>; color: <?= $product['is_on_promotion'] ? '#ea580c' : '#94a3b8' ?>; font-size: 0.8rem; font-weight: 600; padding: 2px 8px; border-radius: 999px;">
                                    -<?= $discountPercent ?>%
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Data e Botões -->
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="display: flex; align-items: center; gap: 4px; color: <?= $product['is_on_promotion'] ? '#16a34a' : '#64748b' ?>; font-size: 0.85rem;" title="Validade">
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
                                        onclick="PromoProducts.removePromotion(<?= $product['id'] ?>)" title="Remover Promoção">
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
