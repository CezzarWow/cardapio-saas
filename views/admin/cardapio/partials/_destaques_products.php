<?php
/**
 * PARTIAL: Produtos em Destaque (Bloco 2)
 * Extraído de _tab_destaques.php
 *
 * Requer $featuredProducts já definido no escopo
 */
?>

<!-- BLOCO 2: Produtos em Destaque -->
<div class="cardapio-admin-card" id="bloco-destaques-produtos">
    <div class="cardapio-admin-card-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="star"></i>
            <h3 class="cardapio-admin-card-title">Produtos em Destaque</h3>
        </div>
        <!-- Botão Editar/Salvar -->
        <div class="cardapio-admin-edit-controls">
            <button type="button" class="cardapio-admin-btn-edit" onclick="CardapioAdmin.Destaques.enableEditMode()">
                <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                Editar
            </button>
            <div class="cardapio-admin-save-group" style="display: none;">
                <button type="button" class="cardapio-admin-btn-cancel" onclick="CardapioAdmin.Destaques.cancelEditMode()">
                    Cancelar
                </button>
                <button type="submit" class="cardapio-admin-btn-save">
                    <i data-lucide="save" style="width: 14px; height: 14px;"></i>
                    Salvar
                </button>
            </div>
        </div>
    </div>

    <div class="cardapio-admin-hint" style="margin-bottom: 1rem;">
        <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
        <span class="view-hint">Clique em "Editar" para modificar os destaques.</span>
        <span class="edit-hint" style="display: none;">Clique em "Destacar" ou "Remover" nos produtos. Depois clique em "Salvar".</span>
    </div>

    <div class="cardapio-admin-destaques-content-wrapper disabled-overlay">
        <?php if (!empty($productsByCategory)): ?>
        
        <!-- Abas de Categorias -->
        <div class="cardapio-admin-destaques-tabs">
            <!-- Aba Destaques (primeira) -->
            <button type="button" 
                    class="cardapio-admin-destaques-tab-btn active" 
                    data-category-tab="featured"
                    onclick="CardapioAdmin.Destaques.switchTab('featured')">
                <i data-lucide="star" style="width: 16px; height: 16px;"></i>
                Destaques
            </button>
            
            <!-- Abas das Categorias -->
            <?php foreach ($productsByCategory as $categoryName => $products): ?>
            <?php $categoryKey = (string) $categoryName; ?>
            <button type="button" 
                    class="cardapio-admin-destaques-tab-btn" 
                    data-category-tab="<?= \App\Helpers\ViewHelper::e($categoryKey) ?>"
                    onclick='CardapioAdmin.Destaques.switchTab(<?= \App\Helpers\ViewHelper::js($categoryKey) ?>)'>
                <i data-lucide="folder" style="width: 16px; height: 16px;"></i>
                <?= \App\Helpers\ViewHelper::e($categoryKey) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="cardapio-admin-destaques-tab-contents">
            
            <!-- Aba Destaques -->
            <div class="cardapio-admin-destaques-tab-content active" data-category-content="featured">
                <?php if (!empty($featuredProducts)): ?>
                    <div class="cardapio-admin-destaques-products-grid" data-sortable-area="featured">
                        <?php foreach ($featuredProducts as $product): ?>
                        <?php $productId = (int) ($product['id'] ?? 0); ?>
                        <div class="cardapio-admin-destaques-product-card featured" 
                             data-product-id="<?= $productId ?>"
                             draggable="true"
                             ondragstart="CardapioAdmin.Destaques.dragStart(event)"
                             ondragover="CardapioAdmin.Destaques.dragOver(event)"
                             ondrop="CardapioAdmin.Destaques.drop(event)"
                             ondragend="CardapioAdmin.Destaques.dragEnd(event)">
                            <div class="cardapio-admin-destaques-product-info">
                                <div class="cardapio-admin-destaques-drag-handle">
                                    <i data-lucide="grip-vertical" style="width: 16px; height: 16px; color: #94a3b8;"></i>
                                </div>
                                <span class="cardapio-admin-destaques-star">⭐</span>
                                <div>
                                    <span class="cardapio-admin-destaques-product-name"><?= htmlspecialchars($product['name']) ?></span>
                                    <span class="cardapio-admin-destaques-product-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                            <button type="button" 
                                    class="cardapio-admin-destaques-highlight-btn active"
                                    onclick="CardapioAdmin.Destaques.toggleHighlight(<?= $productId ?>)">
                                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                                Remover
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="cardapio-admin-destaques-products-grid" data-sortable-area="featured" style="display: none;"></div>
                    <p class="cardapio-admin-destaques-empty">Nenhum produto em destaque. Use as outras abas para adicionar.</p>
                <?php endif; ?>
            </div>

            <!-- Abas de Categorias -->
            <?php foreach ($productsByCategory as $categoryName => $products): ?>
            <?php $categoryKey = (string) $categoryName; ?>
            <div class="cardapio-admin-destaques-tab-content" data-category-content="<?= \App\Helpers\ViewHelper::e($categoryKey) ?>">
                <div class="cardapio-admin-destaques-products-grid" data-sortable-area="<?= \App\Helpers\ViewHelper::e($categoryKey) ?>">
                    <?php foreach ($products as $product): ?>
                    <?php
                        $productId = (int) ($product['id'] ?? 0);
                        $isFeatured = !empty($product['is_featured']);
                        $displayOrder = (int) ($product['display_order'] ?? 0);
                    ?>
                    <div class="cardapio-admin-destaques-product-card <?= $isFeatured ? 'featured' : '' ?>" 
                         data-product-id="<?= $productId ?>"
                         draggable="true"
                         ondragstart="CardapioAdmin.Destaques.dragStart(event)"
                         ondragover="CardapioAdmin.Destaques.dragOver(event)"
                         ondrop="CardapioAdmin.Destaques.drop(event)"
                         ondragend="CardapioAdmin.Destaques.dragEnd(event)">
                        <div class="cardapio-admin-destaques-product-info">
                            <div class="cardapio-admin-destaques-drag-handle">
                                <i data-lucide="grip-vertical" style="width: 16px; height: 16px; color: #94a3b8;"></i>
                            </div>
                            <?php if ($isFeatured): ?>
                            <span class="cardapio-admin-destaques-star">⭐</span>
                            <?php endif; ?>
                            <div>
                                <span class="cardapio-admin-destaques-product-name"><?= htmlspecialchars($product['name']) ?></span>
                                <span class="cardapio-admin-destaques-product-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                        <button type="button" 
                                class="cardapio-admin-destaques-highlight-btn <?= $isFeatured ? 'active' : '' ?>"
                                onclick="CardapioAdmin.Destaques.toggleHighlight(<?= $productId ?>)">
                            <i data-lucide="<?= $isFeatured ? 'x' : 'star' ?>" style="width: 16px; height: 16px;"></i>
                            <?= $isFeatured ? 'Remover' : 'Destacar' ?>
                        </button>
                        <input type="checkbox" 
                               name="featured[<?= $productId ?>]" 
                               value="1" 
                               <?= $isFeatured ? 'checked' : '' ?>
                               style="display: none;"
                               data-featured-input="<?= $productId ?>">
                        <input type="hidden"
                               name="product_order[<?= $productId ?>]"
                               value="<?= $displayOrder ?>"
                               data-order-input="<?= $productId ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
        <?php endif; ?>
    </div>
</div>
