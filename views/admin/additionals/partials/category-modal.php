<!-- Modal de Vincular Categoria (Bulk) -->
<div id="linkCategoryModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 450px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Vincular Categoria Inteira</h3>
        <p style="color: #6b7280; margin-bottom: 1.5rem;" id="linkCategoryGroupName">Grupo: </p>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/vincular-categoria" method="POST">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <input type="hidden" name="group_id" id="linkCategoryGroupId">
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Selecione as Categorias</label>
                <?php if (empty($categories)): ?>
                    <p style="color: #9ca3af; padding: 12px; background: #f9fafb; border-radius: 8px;">
                        Nenhuma categoria cadastrada.
                    </p>
                <?php else: ?>
                    
                    <!-- CUSTOM MULTI-SELECT -->
                    <div class="custom-select-container-cat" style="position: relative;">
                        <input type="hidden" name="dummy" value="1"> <!-- Evita envio vazio se nada selecionado -->
                        
                        <div class="select-trigger-cat" onclick="toggleCategorySelect(this)" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span class="trigger-text-cat" style="color: #6b7280;">Selecione...</span>
                            <i data-lucide="chevron-down" size="16" style="color: #9ca3af;"></i>
                        </div>
                        
                        <div class="options-list-cat" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 10; padding: 5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <?php foreach ($categories as $cat): ?>
                                <label style="display: flex; align-items: center; gap: 8px; padding: 8px; cursor: pointer; border-radius: 4px; transition: background 0.1s;">
                                    <input type="checkbox" name="category_ids[]" value="<?= $cat['id'] ?>" onchange="updateCategoryTriggerText(this)" style="width: 16px; height: 16px;">
                                    <span style="font-size: 0.95rem; color: #374151;"><?= htmlspecialchars($cat['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <p style="margin-top: 8px; font-size: 0.85rem; color: #6b7280;">
                        <i data-lucide="info" size="14" style="vertical-align: middle;"></i>
                        Os produtos das categorias selecionadas receberão vínculo com este grupo.
                    </p>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeLinkCategoryModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <?php if (!empty($categories)): ?>
                <button type="submit" style="flex: 1; padding: 12px; background: #8b5cf6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Vincular Tudo
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
