<?php
/**
 * EDIT.PHP - Edição de Produto
 *
 * View refatorada usando componentes externos:
 * - partials/cropper-modal.php (Modal de recorte)
 * - js/components/icon-selector.js (Seletor de ícone)
 * - js/components/price-mask.js (Máscara de preço)
 * - js/components/cropper-modal.js (Lógica de cropper)
 * - js/components/multi-select.js (Multi-select existente)
 */
\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; display: flex; justify-content: center; overflow-y: auto;">
        
        <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 1100px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: fit-content;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700; color: #1f2937;">Editar Produto</h2>
            
            <form action="<?= BASE_URL ?>/admin/loja/produtos/atualizar" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
                <?= \App\Helpers\ViewHelper::csrfField() ?>
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Nome do Produto</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Categoria</label>
                        <select name="category_id" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="flex: 1; position: relative;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Vincular Adicionais</label>
                        
                        <div class="custom-select-container" style="position: relative;">
                            <?php
                                $checkedCount = 0;
foreach ($additionalGroups as $g) {
    if (in_array($g['id'], $linkedGroups)) {
        $checkedCount++;
    }
}
$triggerLabel = $checkedCount > 0 ? $checkedCount . ' Selecionado(s)' : 'Selecione...';
$triggerStyle = $checkedCount > 0 ? 'color: #1f2937; font-weight: 600;' : 'color: #6b7280;';
?>
                            <div class="select-trigger" onclick="toggleSelect(this)" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                <span class="trigger-text" style="<?= $triggerStyle ?>"><?= $triggerLabel ?></span>
                                <i data-lucide="chevron-down" size="16" style="color: #9ca3af;"></i>
                            </div>
                            
                            <div class="options-list" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 10; padding: 5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                <?php if (empty($additionalGroups)): ?>
                                    <p style="color: #9ca3af; font-size: 0.9rem; padding: 8px; text-align: center; margin: 0;">Nenhum grupo cadastrado</p>
                                <?php else: ?>
                                    <?php foreach ($additionalGroups as $group): ?>
                                        <label style="display: flex; align-items: center; gap: 8px; padding: 8px; cursor: pointer; border-radius: 4px; transition: background 0.1s;">
                                            <input type="checkbox" name="additional_groups[]" value="<?= $group['id'] ?>" 
                                                <?= in_array($group['id'], $linkedGroups) ? 'checked' : '' ?>
                                                onchange="updateTriggerText(this)" style="width: 16px; height: 16px;">
                                            <span style="font-size: 0.95rem; color: #374151;"><?= htmlspecialchars($group['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PREÇO E ESTOQUE -->
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Preço (R$)</label>
                        <input type="text" name="price" id="priceInput" required 
                               value="<?= number_format($product['price'], 2, ',', '') ?>" 
                               style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; font-weight: 600; text-align: right;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Estoque</label>
                        <input type="number" name="stock" value="<?= intval($product['stock']) ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                </div>

                <!-- FOTO E ÍCONE -->
                <?php
                $icons = [
                    '🍔' => 'Hambúrguer', '🌭' => 'Cachorro Quente', '🍺' => 'Cerveja Garrafa',
                    '🍾' => 'Cerveja Longneck', '🥫' => 'Latinha', '🍻' => 'Chopp',
                    '🍕' => 'Pizza', '🍢' => 'Petiscos', '🥘' => 'Porções',
                    '🥤' => 'Refrigerante', '🧃' => 'Sucos', '🍬' => 'Doces e Balas',
                    '🍫' => 'Chocolate', '🍟' => 'Batata Frita', '🍱' => 'Combos',
                    '🍰' => 'Sobremesas', '🍦' => 'Sorvete'
                ];
$currentIcon = $product['icon'] ?? '🍔';
if (!array_key_exists($currentIcon, $icons)) {
    $currentIcon = '🍔';
}
$hasImage = !empty($product['image']);
$iconAsPhoto = ($product['icon_as_photo'] ?? 0) == 1;
?>
                
                <div style="display: flex; gap: 20px;">
                    <!-- FOTO -->
                    <div style="flex: 0.8;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Foto do Produto</label>
                        <?php if (!empty($product['image'])): ?>
                            <div style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                <img src="<?= BASE_URL ?>/uploads/<?= $product['image'] ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="color: #6b7280; font-size: 0.75rem;">Atual</span>
                                    <button type="button" onclick="openCropper('<?= BASE_URL ?>/uploads/<?= $product['image'] ?>')" 
                                            style="background: white; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px 12px; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.1s; font-size: 0.8rem; color: #374151;"
                                            title="Recortar novamente">
                                        <i data-lucide="pencil" style="width: 14px; height: 14px;"></i> Editar
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" id="imageInput" accept="image/*" style="width: 100%; padding: 8px; border: 1px dashed #d1d5db; border-radius: 8px; background: #f9fafb; font-size: 0.8rem;">
                        <div id="iconAsPhotoOption" style="display: <?= $hasImage ? 'none' : 'block' ?>; margin-top: 8px; padding: 8px; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="checkbox" name="icon_as_photo" id="iconAsPhotoCheckbox" <?= $iconAsPhoto ? 'checked' : '' ?> style="width: 14px; height: 14px;">
                                <span style="font-weight: 600; color: #92400e; font-size: 0.75rem;">Usar ícone como foto</span>
                            </label>
                        </div>
                    </div>

                    <!-- ÍCONE (COLAPSÁVEL) -->
                    <div style="flex: 2;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">
                            Ícone do Produto <span style="color: #dc2626;">*</span>
                            <small style="font-weight: 400; color: #6b7280;">(Balcão PDV e Cardápio)</small>
                        </label>
                        <input type="hidden" name="icon" id="selectedIcon" value="<?= $currentIcon ?>" required>
                        
                        <div class="icon-selector-container" style="border: 1px solid #e5e7eb; border-radius: 8px; background: white; position: relative;">
                            <div class="icon-selector-header" onclick="toggleIconGrid()" style="padding: 10px 15px; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span id="selectedIconDisplay" style="font-size: 1.5rem;"><?= $currentIcon ?></span>
                                    <span style="font-weight: 600; color: #374151; font-size: 0.95rem;">Selecionar Ícone</span>
                                </div>
                                <i data-lucide="chevron-down" size="20" style="color: #9ca3af; transition: transform 0.2s;" id="iconChevron"></i>
                            </div>
                            
                            <div id="iconGrid" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 50; max-height: 300px; overflow-y: auto; grid-template-columns: repeat(auto-fill, minmax(50px, 1fr)); gap: 8px; padding: 15px; background: white; border: 1px solid #e5e7eb; border-top: none; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                                <?php foreach ($icons as $emoji => $label):
                                    $isSelected = ($emoji === $currentIcon);
                                    ?>
                                    <div class="icon-option" data-icon="<?= $emoji ?>" onclick="selectIcon('<?= $emoji ?>')" 
                                         title="<?= $label ?>"
                                         style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 50px; background: <?= $isSelected ? '#eff6ff' : 'white' ?>; border: 2px solid <?= $isSelected ? '#2563eb' : '#e5e7eb' ?>; border-radius: 6px; cursor: pointer; transition: all 0.15s;">
                                        <span style="font-size: 1.5rem;"><?= $emoji ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DESCRIÇÃO -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Descrição</label>
                    <textarea name="description" rows="3" placeholder="Ingredientes, detalhes, etc." style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-family: sans-serif; resize: vertical;"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                    <a href="<?= BASE_URL ?>/admin/loja/produtos" style="width: 150px; text-align: center; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; text-decoration: none; font-weight: 600; background: white; transition: background 0.15s;">Cancelar</a>
                    <button type="submit" class="btn-primary" style="width: 150px; padding: 12px; border-radius: 8px; font-weight: 600;">Salvar</button>
                </div>

            </form>
        </div>

    </div>
</main>

<?php // Modal de Recorte?>
<?php \App\Core\View::renderFromScope('admin/products/partials/cropper-modal.php', get_defined_vars()); ?>

<?php // Scripts de Componentes?>
<script src="<?= BASE_URL ?>/js/components/multi-select.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/components/icon-selector.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/components/price-mask.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/components/cropper-modal.js?v=<?= time() ?>"></script>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
