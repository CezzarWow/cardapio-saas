<?php
/**
 * ============================================
 * PARTIAL: Aba Promoções
 * Combos e Destaques
 * ============================================
 */
?>

<!-- Card Combos -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="package-plus"></i>
        <h3 class="cardapio-admin-card-title">Combos</h3>
    </div>

    <div class="cardapio-admin-hint" style="margin-bottom: 1rem;">
        <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
        Combos são ofertas especiais com preço fixo. Aparecem em destaque no cardápio.
    </div>

    <!-- Lista de combos existentes -->
    <?php if (!empty($combos)): ?>
    <div style="margin-bottom: 1.5rem;">
        <?php foreach ($combos as $combo): ?>
        <div class="cardapio-admin-combo-item" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 8px; border: 1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($combo['image'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($combo['image']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                        <i data-lucide="package" style="color: #94a3b8;"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <strong style="color: #1e293b;"><?= htmlspecialchars($combo['name']) ?></strong>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 2px 0 0 0;">
                        R$ <?= number_format($combo['price'], 2, ',', '.') ?>
                        <?php if (!$combo['is_active']): ?>
                            <span style="color: #ef4444; margin-left: 8px;">• Inativo</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="<?= BASE_URL ?>/admin/loja/cardapio/combo/editar?id=<?= $combo['id'] ?>" 
                   class="cardapio-admin-btn" style="padding: 8px 12px; background: #f1f5f9; color: #475569;">
                    <i data-lucide="pencil" style="width: 16px; height: 16px;"></i>
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/cardapio/combo/deletar?id=<?= $combo['id'] ?>" 
                   class="cardapio-admin-btn" style="padding: 8px 12px; background: #fef2f2; color: #ef4444;"
                   onclick="return confirm('Deletar este combo?')">
                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color: #64748b; text-align: center; padding: 20px;">Nenhum combo cadastrado ainda.</p>
    <?php endif; ?>

    <!-- Botão Novo Combo -->
    <a href="<?= BASE_URL ?>/admin/loja/cardapio/combo/novo" class="cardapio-admin-btn cardapio-admin-btn-primary" style="width: 100%; justify-content: center;">
        <i data-lucide="plus"></i>
        Criar Novo Combo
    </a>
</div>

<!-- Card Destaques -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <i data-lucide="star"></i>
        <h3 class="cardapio-admin-card-title">Produtos em Destaque</h3>
    </div>

    <div class="cardapio-admin-hint" style="margin-bottom: 1rem;">
        <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
        Produtos destacados aparecem no topo do cardápio.
    </div>

    <?php if (!empty($allProducts)): ?>
    <div style="max-height: 400px; overflow-y: auto;">
        <?php foreach ($allProducts as $product): ?>
        <div class="cardapio-admin-toggle-row" style="padding: 10px 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-weight: 500; color: #374151;"><?= htmlspecialchars($product['name']) ?></span>
                <span style="font-size: 0.8rem; color: #6b7280;">(<?= htmlspecialchars($product['category_name'] ?? 'Sem categoria') ?>)</span>
            </div>
            <label class="cardapio-admin-toggle">
                <input type="checkbox" 
                       name="featured[<?= $product['id'] ?>]" 
                       value="1"
                       <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
                <span class="cardapio-admin-toggle-slider"></span>
            </label>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color: #64748b; text-align: center; padding: 20px;">Nenhum produto cadastrado.</p>
    <?php endif; ?>
</div>
