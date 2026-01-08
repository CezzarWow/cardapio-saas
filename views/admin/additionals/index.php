<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';
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
                            <button type="button" 
                               class="btn-stock-action btn-action-category" 
                               style="background: #8b5cf6; color: white;" 
                               title="Vincular Categoria inteira"
                               data-group-id="<?= $group['id'] ?>"
                               data-group-name="<?= htmlspecialchars($group['name'], ENT_QUOTES) ?>">
                                <i data-lucide="layers" size="14"></i> Categoria
                            </button>
                            <button type="button" 
                               class="btn-stock-action btn-action-link" 
                               style="background: #10b981; color: white;"
                               data-group-id="<?= $group['id'] ?>"
                               data-group-name="<?= htmlspecialchars($group['name'], ENT_QUOTES) ?>">
                                <i data-lucide="link" size="14"></i> Vincular Item
                            </button>
                            <button type="button" 
                               class="btn-stock-action btn-stock-delete btn-action-delete-group" 
                               style="border: none; cursor: pointer;"
                               data-url="<?= BASE_URL ?>/admin/loja/adicionais/grupo/deletar?id=<?= $group['id'] ?>"
                               data-name="<?= htmlspecialchars($group['name'], ENT_QUOTES) ?>">
                                <i data-lucide="trash-2" size="14"></i> Excluir
                            </button>
                        </div>
                    </div>
                    
                    <!-- Itens do Grupo (CHIPS) -->
                    <div class="group-card-body">
                        <?php if (empty($group['items'])): ?>
                            <div class="group-card-empty">
                                Nenhum item vinculado. 
                                <a href="javascript:void(0)" 
                                   class="btn-action-link"
                                   data-group-id="<?= $group['id'] ?>"
                                   data-group-name="<?= htmlspecialchars($group['name'], ENT_QUOTES) ?>"
                                   style="color: #2563eb; text-decoration: none; font-weight: 600;">+ Vincular item</a>
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
                                       onclick="return confirm('Desvincular &quot;<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>&quot; deste grupo?')"
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
                                    <button type="button" 
                                       class="btn-stock-action btn-stock-delete btn-action-delete-item" 
                                       style="padding: 8px; border: none; cursor: pointer;"
                                       data-url="<?= BASE_URL ?>/admin/loja/adicionais/item/deletar?id=<?= $item['id'] ?>"
                                       data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>">
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

<!-- Modais (Partials) -->
<?php require __DIR__ . '/partials/group-modal.php'; ?>
<?php require __DIR__ . '/partials/item-modal.php'; ?>
<?php require __DIR__ . '/partials/link-modal.php'; ?>
<?php require __DIR__ . '/partials/category-modal.php'; ?>
<?php require __DIR__ . '/../partials/delete-modal.php'; ?>

<!-- Scripts -->
<script>window.BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/components/multi-select.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/additionals.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/additionals-group-modal.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/additionals-item-modal.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/additionals-delete-modal.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/admin/additionals-ui.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
