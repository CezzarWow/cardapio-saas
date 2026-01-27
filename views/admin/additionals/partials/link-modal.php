<!-- Modal de Vincular Itens (Multi-select) -->
<div id="linkModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 500px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Vincular Itens ao Grupo</h3>
        <p style="color: #6b7280; margin-bottom: 1.5rem;" id="linkGroupName">Grupo: </p>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/vincular-multiplos" method="POST">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <input type="hidden" name="group_id" id="linkGroupId">
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Selecione os Itens</label>
                <?php if (empty($allItems)): ?>
                    <p style="color: #9ca3af; padding: 12px; background: #f9fafb; border-radius: 8px;">
                        Nenhum item cadastrado. <a href="<?= BASE_URL ?>/admin/loja/adicionais/itens" style="color: #2563eb;">Criar itens primeiro</a>
                    </p>
                <?php else: ?>
                    
                    <!-- CUSTOM MULTI-SELECT -->
                    <div class="custom-select-container-items" style="position: relative;">
                        <input type="hidden" name="dummy" value="1">
                        
                        <div class="select-trigger-items" onclick="toggleItemsSelect(this)" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span class="trigger-text-items" style="color: #6b7280;">Selecione os itens...</span>
                            <i data-lucide="chevron-down" size="16" style="color: #9ca3af;"></i>
                        </div>
                        
                        <div class="options-list-items" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-radius: 8px; max-height: 250px; overflow-y: auto; z-index: 10; padding: 5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <?php foreach ($allItems as $item): ?>
                                <label style="display: flex; align-items: center; gap: 10px; padding: 10px; cursor: pointer; border-radius: 6px; transition: background 0.1s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" name="item_ids[]" value="<?= (int) ($item['id'] ?? 0) ?>" onchange="updateItemsTriggerText()" style="width: 18px; height: 18px; accent-color: #10b981;">
                                    <span style="flex: 1; font-size: 0.95rem; color: #374151;"><?= htmlspecialchars($item['name']) ?></span>
                                    <span style="padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; background: <?= ((float) ($item['price'] ?? 0)) > 0 ? '#dbeafe' : '#d1fae5' ?>; color: <?= ((float) ($item['price'] ?? 0)) > 0 ? '#1d4ed8' : '#059669' ?>;">
                                        <?= ((float) ($item['price'] ?? 0)) > 0 ? '+R$ ' . number_format((float) ($item['price'] ?? 0), 2, ',', '.') : 'Grátis' ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <p style="margin-top: 8px; font-size: 0.85rem; color: #6b7280;">
                        <i data-lucide="info" size="14" style="vertical-align: middle;"></i>
                        Selecione um ou mais itens para vincular ao grupo.
                    </p>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeLinkModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <?php if (!empty($allItems)): ?>
                <button type="submit" style="flex: 1; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Vincular Selecionados
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
