<!-- Modal de Novo Item (Completo) -->
<div id="itemModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 500px; margin: 20px;">
        <h3 id="itemModalTitle" style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">Novo Item</h3>
        
        <form id="itemForm" action="<?= BASE_URL ?>/admin/loja/adicionais/item/salvar-modal" method="POST">
            <input type="hidden" name="id" id="itemIdInputHidden">
            <!-- Nome -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome do Item</label>
                <input type="text" name="name" placeholder="Ex: Bacon, Cheddar..." required 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
            </div>

            <!-- Preço -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Preço (R$)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                     <input type="text" name="price" id="itemPriceInput" placeholder="0,00" oninput="formatCurrency(this)"
                       style="flex: 1; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                     
                     <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; user-select: none;">
                         <input type="checkbox" onchange="toggleItemFree(this)" style="width: 18px; height: 18px; accent-color: #10b981;">
                         <span style="font-weight: 500; color: #374151;">Grátis</span>
                     </label>
                </div>
            </div>

            <!-- Seleção de Grupos -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Vincular a Grupos (Opcional)</label>
                <?php if (empty($groups)): ?>
                    <p style="color: #9ca3af; padding: 12px; background: #f9fafb; border-radius: 8px;">
                        Nenhum grupo cadastrado.
                    </p>
                <?php else: ?>
                    <div class="custom-select-container-groups" style="position: relative;">
                        
                        <div class="select-trigger-groups" onclick="toggleGroupsSelect(this)" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span class="trigger-text-groups" style="color: #6b7280;">Selecione os grupos...</span>
                            <i data-lucide="chevron-down" size="16" style="color: #9ca3af;"></i>
                        </div>
                        
                        <div class="options-list-groups" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 10; padding: 5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <?php foreach ($groups as $grp): ?>
                                <label style="display: flex; align-items: center; gap: 10px; padding: 10px; cursor: pointer; border-radius: 6px; transition: background 0.1s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" name="group_ids[]" value="<?= $grp['id'] ?>" onchange="updateGroupsTriggerText()" style="width: 18px; height: 18px; accent-color: #2563eb;">
                                    <span style="flex: 1; font-size: 0.95rem; color: #374151;"><?= htmlspecialchars($grp['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeItemModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="submit" style="flex: 1; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Salvar Item
                </button>
            </div>
        </form>
    </div>
</div>
