<?php
/**
 * PARTIAL: Prioridade das Categorias (Bloco 1)
 * Extraído de _tab_destaques.php
 */
?>

<!-- BLOCO 1: Prioridade das Categorias -->
<div class="cardapio-admin-card">
    <div class="cardapio-admin-card-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="list-ordered"></i>
            <h3 class="cardapio-admin-card-title">Prioridade das Categorias</h3>
        </div>
    </div>

    <div class="cardapio-admin-hint" style="margin-bottom: 1rem;">
        <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
        Use as setas para reordenar. Categorias desabilitadas não aparecem no cardápio web.
    </div>

    <?php if (!empty($categories)): ?>
    <div class="cardapio-admin-destaques-category-list-scroll">
        <div class="cardapio-admin-destaques-category-list" id="categoryList">
            <?php foreach ($categories as $index => $category): ?>
            <?php
                $categoryId = (int) ($category['id'] ?? 0);
                $isSystem = in_array($category['category_type'] ?? 'default', ['featured', 'combos']);
                $icon = 'folder';
                $color = '#64748b';
                $label = $category['name'];
                $rowStyle = $isSystem ? 'background-color: #f8fafc;' : '';
                $nameStyle = $isSystem ? 'font-weight: 600; color: #1e293b;' : '';
                $activeChecked = (($category['is_active'] ?? 1) ? 'checked' : '');
                $upDisabled = $index === 0 ? 'disabled' : '';
                $downDisabled = $index === count($categories) - 1 ? 'disabled' : '';

                if (($category['category_type'] ?? '') === 'featured') {
                    $icon = 'star';
                    $color = '#eab308';
                } elseif (($category['category_type'] ?? '') === 'combos') {
                    $icon = 'flame';
                    $color = '#ef4444';
                }
                ?>
            <div class="cardapio-admin-destaques-category-row" data-category-id="<?= (int) $categoryId ?>" style="<?= \App\Helpers\ViewHelper::e($rowStyle) ?>">
                <div class="cardapio-admin-destaques-category-info">
                    <i data-lucide="<?= \App\Helpers\ViewHelper::e($icon) ?>" style="width: 18px; height: 18px; color: <?= \App\Helpers\ViewHelper::e($color) ?>;"></i>
                    <span class="cardapio-admin-destaques-category-name" style="<?= \App\Helpers\ViewHelper::e($nameStyle) ?>">
                        <?= htmlspecialchars($label) ?>
                        <?php if ($isSystem): ?>
                            <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400; margin-left: 6px;">(Sistema)</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                    <div class="cardapio-admin-destaques-category-actions">
                        <!-- Toggle Habilitar/Desabilitar -->
                    <label class="cardapio-admin-toggle" title="<?= \App\Helpers\ViewHelper::e(($category['is_active'] ?? 1) ? 'Desabilitar' : 'Habilitar') ?>">
                        <input type="checkbox" 
                               name="category_enabled[<?= (int) $categoryId ?>]" 
                               value="1"
                               <?= \App\Helpers\ViewHelper::e($activeChecked) ?>>
                        <span class="cardapio-admin-toggle-slider"></span>
                    </label>
                    
                    <!-- Setas de Ordenação -->
                    <div class="cardapio-admin-destaques-arrows">
                         <button type="button" 
                                 class="cardapio-admin-destaques-arrow-btn" 
                                onclick="CardapioAdmin.Destaques.moveCategory(<?= (int) $categoryId ?>, 'up')"
                                <?= \App\Helpers\ViewHelper::e($upDisabled) ?>>
                             <i data-lucide="chevron-up" style="width: 16px; height: 16px;"></i>
                         </button>
                         <button type="button" 
                                 class="cardapio-admin-destaques-arrow-btn" 
                                onclick="CardapioAdmin.Destaques.moveCategory(<?= (int) $categoryId ?>, 'down')"
                                <?= \App\Helpers\ViewHelper::e($downDisabled) ?>>
                             <i data-lucide="chevron-down" style="width: 16px; height: 16px;"></i>
                         </button>
                    </div>
                    
                     <!-- Input hidden para salvar a ordem -->
                     <input type="hidden" 
                           name="category_order[<?= (int) $categoryId ?>]" 
                           value="<?= (int) $index ?>"
                           data-order-input>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <p style="color: #64748b; text-align: center; padding: 20px;">Nenhuma categoria cadastrada.</p>
    <?php endif; ?>
</div>
