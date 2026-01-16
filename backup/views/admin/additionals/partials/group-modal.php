<!-- Modal de Novo Grupo -->
<div id="groupModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">Novo Grupo</h3>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/grupo/salvar" method="POST">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome do Grupo</label>
                <input type="text" name="name" placeholder="Ex: Molhos, Extras, Bordas..." required 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
            </div>

            <!-- Vincular Itens ao Grupo (Opcional) -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Vincular Itens (Opcional)</label>
                <?php if (empty($allItems)): ?>
                    <p style="color: #9ca3af; padding: 12px; background: #f9fafb; border-radius: 8px;">
                        Nenhum item cadastrado. <a href="javascript:void(0)" onclick="closeGroupModal(); openItemModal();" style="color: #2563eb;">Criar item primeiro</a>
                    </p>
                <?php else: ?>
                    <div class="custom-select-container-group-items" style="position: relative;">
                        
                        <div class="select-trigger-group-items" onclick="toggleGroupItemsSelect(this)" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span class="trigger-text-group-items" style="color: #6b7280;">Selecione os itens...</span>
                            <i data-lucide="chevron-down" size="16" style="color: #9ca3af;"></i>
                        </div>
                        
                        <div class="options-list-group-items" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 10; padding: 5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <?php foreach ($allItems as $itm): ?>
                                <label style="display: flex; align-items: center; gap: 10px; padding: 10px; cursor: pointer; border-radius: 6px; transition: background 0.1s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" name="item_ids[]" value="<?= $itm['id'] ?>" onchange="updateGroupItemsTriggerText()" style="width: 18px; height: 18px; accent-color: #2563eb;">
                                    <span style="flex: 1; font-size: 0.95rem; color: #374151;"><?= htmlspecialchars($itm['name']) ?></span>
                                    <span style="font-size: 0.8rem; color: #6b7280;">
                                        <?= $itm['price'] > 0 ? 'R$ ' . number_format($itm['price'], 2, ',', '.') : 'Grátis' ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeGroupModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="submit" style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Grupo
                </button>
            </div>
        </form>
    </div>
</div>
