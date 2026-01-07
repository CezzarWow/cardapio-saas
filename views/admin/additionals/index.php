<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

$totalGroups = count($groups);
$totalItems = count($allItems);
?>

<!-- CSS Estoque v2 (modernização) -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/stock-v2.css">

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header com Botão Dinâmico -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Adicionais</h1>
            
            <div style="display: flex; gap: 10px;">
                <!-- Botões Fixos de Ação -->
                <button onclick="openItemModal()" 
                   style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                    <i data-lucide="plus-circle" size="18"></i> Novo Item
                </button>
                
                <button onclick="openGroupModal()" 
                        style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                    <i data-lucide="folder-plus" size="18"></i> Novo Grupo
                </button>
            </div>
        </div>

        <!-- Sub-abas (STICKY) -->
        <div class="sticky-tabs">
            <div class="stock-tabs">
                <a href="<?= BASE_URL ?>/admin/loja/produtos" class="stock-tab">
                    Produtos
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/categorias" class="stock-tab">
                    Categorias
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/adicionais" class="stock-tab active">
                    Adicionais
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/reposicao" class="stock-tab">
                    Reposição
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" class="stock-tab">
                    Movimentações
                </a>
            </div>
        </div>

        <!-- Busca + Indicadores na mesma linha -->
        <div class="stock-search-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; gap: 15px; align-items: center; flex: 1;">
                <input type="text" id="searchInput" placeholder="🔍 Buscar grupo..." 
                       class="stock-search-input" style="width: 100%; max-width: 300px;"
                       oninput="handleSearch(this.value)">

                <!-- Toggle de Visualização (Grupos | Itens) -->
                <div class="stock-view-toggle">
                    <button class="stock-view-btn active" onclick="setAdditionalView('groups', this)">
                        📂 Grupos
                    </button>
                    <button class="stock-view-btn" onclick="setAdditionalView('items', this)">
                        📦 Itens
                    </button>
                </div>
            </div>
            
            <div style="display: flex; gap: 20px; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="background: #dbeafe; padding: 6px; border-radius: 6px;">
                        <i data-lucide="layers" style="width: 18px; height: 18px; color: #2563eb;"></i>
                    </div>
                    <div>
                        <span style="font-weight: 700; color: #1f2937;"><?= $totalGroups ?></span>
                        <span style="font-size: 0.8rem; color: #6b7280;"> grupos</span>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="background: #d1fae5; padding: 6px; border-radius: 6px;">
                        <i data-lucide="package" style="width: 18px; height: 18px; color: #059669;"></i>
                    </div>
                    <div>
                        <span style="font-weight: 700; color: #059669;"><?= $totalItems ?></span>
                        <span style="font-size: 0.8rem; color: #6b7280;"> itens</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW 1: GRUPOS (Accordion) -->
        <div id="view-groups" class="stock-fade-in">
            <?php if (empty($groups)): ?>
                <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-top: 20px;">
                    <i data-lucide="layers" size="48" style="color: #d1d5db; margin-bottom: 15px;"></i>
                    <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhum grupo cadastrado</h3>
                    <p style="color: #9ca3af; margin-bottom: 20px;">Crie set primeiro grupo de adicionais</p>
                    <button onclick="openGroupModal()" style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        Criar Primeiro Grupo
                    </button>
                </div>
            <?php else: ?>
                <div id="groupsList" style="margin-top: 20px;">
                <?php foreach ($groups as $group): ?>
                <div class="group-card" data-name="<?= strtolower(htmlspecialchars($group['name'])) ?>" style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden;">
                    <!-- Header do Grupo -->
                    <div style="padding: 15px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i data-lucide="layers" size="20" style="color: #2563eb;"></i>
                            <span style="font-weight: 700; font-size: 1.1rem; color: #1f2937;"><?= htmlspecialchars($group['name']) ?></span>
                            <span style="color: #6b7280; font-size: 0.85rem;">(<?= count($group['items']) ?> itens)</span>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button onclick="openLinkCategoryModal(<?= $group['id'] ?>, '<?= htmlspecialchars($group['name']) ?>')" 
                               class="btn-stock-action" style="background: #8b5cf6; color: white;" title="Vincular Categoria inteira">
                                <i data-lucide="layers" size="14"></i> Categoria
                            </button>
                            <button onclick="openLinkModal(<?= $group['id'] ?>, '<?= htmlspecialchars($group['name']) ?>')" 
                               class="btn-stock-action" style="background: #10b981; color: white;">
                                <i data-lucide="link" size="14"></i> Vincular Item
                            </button>
                            <button onclick="openDeleteModal('<?= BASE_URL ?>/admin/loja/adicionais/grupo/deletar?id=<?= $group['id'] ?>', '<?= addslashes(htmlspecialchars($group['name'])) ?>')" 
                               class="btn-stock-action btn-stock-delete" style="border: none; cursor: pointer;">
                                <i data-lucide="trash-2" size="14"></i> Excluir
                            </button>
                        </div>
                    </div>
                    
                    <!-- Itens do Grupo (CHIPS) -->
                    <div class="group-card-body">
                        <?php if (empty($group['items'])): ?>
                            <div class="group-card-empty">
                                Nenhum item vinculado. 
                                <a href="javascript:void(0)" onclick="openLinkModal(<?= $group['id'] ?>, '<?= htmlspecialchars($group['name']) ?>')" style="color: #2563eb; text-decoration: none; font-weight: 600;">+ Vincular item</a>
                            </div>
                        <?php else: ?>
                            <div class="item-chips-container">
                                <?php foreach ($group['items'] as $item): ?>
                                <div class="item-chip">
                                    <span class="item-chip-name"><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="item-chip-price <?= $item['price'] > 0 ? 'paid' : 'free' ?>">
                                        <?= $item['price'] > 0 ? '+R$ ' . number_format($item['price'], 2, ',', '.') : 'Grátis' ?>
                                    </span>
                                    <a href="<?= BASE_URL ?>/admin/loja/adicionais/desvincular?grupo=<?= $group['id'] ?>&item=<?= $item['id'] ?>" 
                                       onclick="return confirm('Desvincular &quot;<?= htmlspecialchars($item['name']) ?>&quot; deste grupo?')"
                                       title="Desvincular"
                                       class="item-chip-unlink">
                                        <i data-lucide="x" size="12"></i>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- VIEW 2: ITENS (Table/List) -->
        <div id="view-items" class="stock-fade-in" style="display: none;">
            <?php if (empty($allItems)): ?>
                 <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-top: 20px;">
                    <i data-lucide="package" size="48" style="color: #d1d5db; margin-bottom: 15px;"></i>
                    <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhum item cadastrado</h3>
                    <button onclick="openItemModal()" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px;">
                        Criar Primeiro Item
                    </button>
                </div>
            <?php else: ?>
                <div class="stock-products-grid" id="itemsList" style="margin-top: 20px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <?php foreach ($allItems as $item): ?>
                        <div class="stock-product-card item-card-row" data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>">
                            <div class="stock-product-card-body" style="flex-direction: row; justify-content: space-between; align-items: center;">
                                <div>
                                    <div class="stock-product-card-name" style="font-size: 1.1rem; margin-bottom: 4px;">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </div>
                                    <div style="font-size: 0.9rem; color: #64748b;">
                                        <?= $item['price'] > 0 ? 'R$ ' . number_format($item['price'], 2, ',', '.') : '<span style="color:#059669; font-weight:600;">Grátis</span>' ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <button onclick="openEditItemModal(<?= $item['id'] ?>)" class="btn-stock-action" style="background: #f1f5f9; color: #475569; padding: 8px; border: none; cursor: pointer;">
                                        <i data-lucide="pencil" style="width: 16px; height: 16px;"></i>
                                    </button>
                                    <button onclick="openDeleteModal('<?= BASE_URL ?>/admin/loja/adicionais/item/deletar?id=<?= $item['id'] ?>', '<?= addslashes(htmlspecialchars($item['name'])) ?>')" 
                                       class="btn-stock-action btn-stock-delete" style="padding: 8px; border: none; cursor: pointer;">
                                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Modal de Novo Grupo -->
<div id="groupModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">Novo Grupo</h3>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/grupo/salvar" method="POST">
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

<!-- Modal de Exclusão Genérico -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 0; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <div style="background: #fee2e2; padding: 1.5rem; text-align: center;">
            <div style="background: #fecaca; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i data-lucide="alert-triangle" size="32" style="color: #ef4444;"></i>
            </div>
            <h3 style="margin-top: 1rem; color: #991b1b; font-weight: 700; font-size: 1.25rem;">Excluir Item?</h3>
        </div>
        
        <div style="padding: 1.5rem;">
            <p style="text-align: center; color: #4b5563; margin-bottom: 1.5rem; line-height: 1.5;">
                Tem certeza que deseja excluir <strong id="deleteItemName" style="color: #1f2937;">este item</strong>?<br>
                <span style="font-size: 0.9rem; color: #6b7280;">Esta ação não pode ser desfeita.</span>
            </p>
            
            <div style="display: flex; gap: 10px;">
                <button onclick="closeDeleteModal()" style="flex: 1; padding: 10px; border: 1px solid #d1d5db; background: white; color: #374151; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    Cancelar
                </button>
                <a id="confirmDeleteBtn" href="#" style="flex: 1; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    Sim, Excluir
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Vincular Itens (Multi-select) -->
<div id="linkModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 500px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Vincular Itens ao Grupo</h3>
        <p style="color: #6b7280; margin-bottom: 1.5rem;" id="linkGroupName">Grupo: </p>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/vincular-multiplos" method="POST">
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
                                    <input type="checkbox" name="item_ids[]" value="<?= $item['id'] ?>" onchange="updateItemsTriggerText()" style="width: 18px; height: 18px; accent-color: #10b981;">
                                    <span style="flex: 1; font-size: 0.95rem; color: #374151;"><?= htmlspecialchars($item['name']) ?></span>
                                    <span style="padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; background: <?= $item['price'] > 0 ? '#dbeafe' : '#d1fae5' ?>; color: <?= $item['price'] > 0 ? '#1d4ed8' : '#059669' ?>;">
                                        <?= $item['price'] > 0 ? '+R$ ' . number_format($item['price'], 2, ',', '.') : 'Grátis' ?>
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

<!-- Modal de Vincular Categoria (Bulk) -->
<div id="linkCategoryModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 450px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Vincular Categoria Inteira</h3>
        <p style="color: #6b7280; margin-bottom: 1.5rem;" id="linkCategoryGroupName">Grupo: </p>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/vincular-categoria" method="POST">
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

<script>
    // Expor BASE_URL para o JS externo
    window.BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/js/components/multi-select.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/additionals.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
