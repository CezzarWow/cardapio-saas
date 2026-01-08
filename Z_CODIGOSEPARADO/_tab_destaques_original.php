<?php
/**
 * ============================================
 * PARTIAL: Aba Destaques
 * Prioridade de categorias e produtos em destaque
 * ============================================
 */
?>

<div class="cardapio-admin-destaques-container">
    
    <!-- Coluna Esquerda (70%) -->
    <div class="cardapio-admin-destaques-main">
        
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
                        $isSystem = in_array($category['category_type'] ?? 'default', ['featured', 'combos']);
                        $icon = 'folder';
                        $color = '#64748b';
                        $label = $category['name'];

                        if (($category['category_type'] ?? '') === 'featured') {
                            $icon = 'star';
                            $color = '#eab308';
                        } elseif (($category['category_type'] ?? '') === 'combos') {
                            $icon = 'flame';
                            $color = '#ef4444';
                        }
                    ?>
                    <div class="cardapio-admin-destaques-category-row" data-category-id="<?= $category['id'] ?>" style="<?= $isSystem ? 'background-color: #f8fafc;' : '' ?>">
                        <div class="cardapio-admin-destaques-category-info">
                            <i data-lucide="<?= $icon ?>" style="width: 18px; height: 18px; color: <?= $color ?>;"></i>
                            <span class="cardapio-admin-destaques-category-name" style="<?= $isSystem ? 'font-weight: 600; color: #1e293b;' : '' ?>">
                                <?= htmlspecialchars($label) ?>
                                <?php if($isSystem): ?>
                                    <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400; margin-left: 6px;">(Sistema)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="cardapio-admin-destaques-category-actions">
                            <!-- Toggle Habilitar/Desabilitar -->
                            <label class="cardapio-admin-toggle" title="<?= ($category['is_active'] ?? 1) ? 'Desabilitar' : 'Habilitar' ?>">
                                <input type="checkbox" 
                                       name="category_enabled[<?= $category['id'] ?>]" 
                                       value="1"
                                       <?= ($category['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="cardapio-admin-toggle-slider"></span>
                            </label>
                            
                            <!-- Setas de Ordenação -->
                            <div class="cardapio-admin-destaques-arrows">
                                <button type="button" 
                                        class="cardapio-admin-destaques-arrow-btn" 
                                        onclick="CardapioAdmin.Destaques.moveCategory(<?= $category['id'] ?>, 'up')"
                                        <?= $index === 0 ? 'disabled' : '' ?>>
                                    <i data-lucide="chevron-up" style="width: 16px; height: 16px;"></i>
                                </button>
                                <button type="button" 
                                        class="cardapio-admin-destaques-arrow-btn" 
                                        onclick="CardapioAdmin.Destaques.moveCategory(<?= $category['id'] ?>, 'down')"
                                        <?= $index === count($categories) - 1 ? 'disabled' : '' ?>>
                                    <i data-lucide="chevron-down" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>
                            
                            <!-- Input hidden para salvar a ordem -->
                            <input type="hidden" 
                                   name="category_order[<?= $category['id'] ?>]" 
                                   value="<?= $index ?>"
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
                    <button type="button" 
                            class="cardapio-admin-destaques-tab-btn" 
                            data-category-tab="<?= htmlspecialchars($categoryName) ?>"
                            onclick="CardapioAdmin.Destaques.switchTab('<?= htmlspecialchars($categoryName) ?>')">
                        <i data-lucide="folder" style="width: 16px; height: 16px;"></i>
                        <?= htmlspecialchars($categoryName) ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- Conteúdo das Abas -->
                <div class="cardapio-admin-destaques-tab-contents">
                    
                    <!-- Aba Destaques -->
                    <div class="cardapio-admin-destaques-tab-content active" data-category-content="featured">
                        <?php 
                        $featuredProducts = array_filter($allProducts ?? [], fn($p) => ($p['is_featured'] ?? 0));
                        // Ordena destaques visualmente (já vem do controller, mas garante consistência na view)
                        usort($featuredProducts, fn($a, $b) => ($a['display_order'] ?? 999) - ($b['display_order'] ?? 999));
                        
                        if (!empty($featuredProducts)): ?>
                            <div class="cardapio-admin-destaques-products-grid" data-sortable-area="featured">
                                <?php foreach ($featuredProducts as $product): ?>
                                <div class="cardapio-admin-destaques-product-card featured" 
                                     data-product-id="<?= $product['id'] ?>"
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
                                            onclick="CardapioAdmin.Destaques.toggleHighlight(<?= $product['id'] ?>)">
                                        <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                                        Remover
                                    </button>
                                    <!-- SEM inputs aqui - os inputs ficam apenas nas abas de Categoria -->
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
                    <div class="cardapio-admin-destaques-tab-content" data-category-content="<?= htmlspecialchars($categoryName) ?>">
                        <div class="cardapio-admin-destaques-products-grid" data-sortable-area="<?= htmlspecialchars($categoryName) ?>">
                            <?php foreach ($products as $product): ?>
                            <div class="cardapio-admin-destaques-product-card <?= ($product['is_featured'] ?? 0) ? 'featured' : '' ?>" 
                                 data-product-id="<?= $product['id'] ?>"
                                 draggable="true"
                                 ondragstart="CardapioAdmin.Destaques.dragStart(event)"
                                 ondragover="CardapioAdmin.Destaques.dragOver(event)"
                                 ondrop="CardapioAdmin.Destaques.drop(event)"
                                 ondragend="CardapioAdmin.Destaques.dragEnd(event)">
                                <div class="cardapio-admin-destaques-product-info">
                                    <div class="cardapio-admin-destaques-drag-handle">
                                        <i data-lucide="grip-vertical" style="width: 16px; height: 16px; color: #94a3b8;"></i>
                                    </div>
                                    <?php if ($product['is_featured'] ?? 0): ?>
                                    <span class="cardapio-admin-destaques-star">⭐</span>
                                    <?php endif; ?>
                                    <div>
                                        <span class="cardapio-admin-destaques-product-name"><?= htmlspecialchars($product['name']) ?></span>
                                        <span class="cardapio-admin-destaques-product-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                    </div>
                                </div>
                                <button type="button" 
                                        class="cardapio-admin-destaques-highlight-btn <?= ($product['is_featured'] ?? 0) ? 'active' : '' ?>"
                                        onclick="CardapioAdmin.Destaques.toggleHighlight(<?= $product['id'] ?>)">
                                    <i data-lucide="<?= ($product['is_featured'] ?? 0) ? 'x' : 'star' ?>" style="width: 16px; height: 16px;"></i>
                                    <?= ($product['is_featured'] ?? 0) ? 'Remover' : 'Destacar' ?>
                                </button>
                                <input type="checkbox" 
                                       name="featured[<?= $product['id'] ?>]" 
                                       value="1" 
                                       <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>
                                       style="display: none;"
                                       data-featured-input="<?= $product['id'] ?>">
                                <input type="hidden"
                                       name="product_order[<?= $product['id'] ?>]"
                                       value="<?= $product['display_order'] ?? 0 ?>"
                                       data-order-input="<?= $product['id'] ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Coluna Direita (30%) - Preview -->
    <div class="cardapio-admin-destaques-preview">
        <div class="cardapio-admin-card" style="position: sticky; top: 20px;">
            <div class="cardapio-admin-card-header">
                <i data-lucide="eye"></i>
                <h3 class="cardapio-admin-card-title">Preview</h3>
            </div>

            <div class="cardapio-admin-destaques-preview-notice">
                <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                Salve para atualizar o preview
            </div>

            <!-- Seção Destaques -->
            <div class="cardapio-admin-destaques-preview-section">
                <h5 class="cardapio-admin-destaques-preview-title">⭐ Destaques</h5>
                <?php 
                $featuredProducts = array_filter($allProducts ?? [], fn($p) => ($p['is_featured'] ?? 0));
                if (!empty($featuredProducts)): ?>
                    <?php foreach ($featuredProducts as $product): ?>
                    <div class="cardapio-admin-destaques-preview-item featured">
                        <span><?= htmlspecialchars($product['name']) ?></span>
                        <span class="price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="cardapio-admin-destaques-preview-empty">Nenhum produto destacado</p>
                <?php endif; ?>
            </div>

            <!-- Categorias na ordem -->
            <div class="cardapio-admin-destaques-preview-section">
                <h5 class="cardapio-admin-destaques-preview-title">📂 Categorias</h5>
                <?php if (!empty($categories)): ?>
                    <?php 
                    $sortedCategories = $categories;
                    usort($sortedCategories, fn($a, $b) => ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0));
                    ?>
                    <?php foreach ($sortedCategories as $idx => $cat): ?>
                    <div class="cardapio-admin-destaques-preview-item">
                        <span><?= $idx + 1 ?>. <?= htmlspecialchars($cat['name']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="cardapio-admin-destaques-preview-empty">Nenhuma categoria</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>
