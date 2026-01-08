<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';
?>

<!-- CSS Estoque v2 (modernização) -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/stock-v2.css">

<main class="main-content">
    <?php require __DIR__ . '/../panel/layout/messages.php'; ?>
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Categorias</h1>
            <button onclick="openCategoryModal()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="plus" size="18"></i> Nova Categoria
            </button>
        </div>

        <!-- Sub-abas do Estoque (STICKY) -->
        <div class="sticky-tabs">
            <div class="stock-tabs">
                <a href="<?= BASE_URL ?>/admin/loja/produtos" class="stock-tab">
                    Produtos
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/categorias" class="stock-tab active">
                    Categorias
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/adicionais" class="stock-tab">
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

        <!-- Indicador -->
        <div class="stock-indicators">
            <div class="stock-indicator">
                <div style="background: #dbeafe; padding: 10px; border-radius: 8px;">
                    <i data-lucide="tags" size="24" style="color: #2563eb;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;"><?= $totalCategories ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Categorias cadastradas</div>
                </div>
            </div>
        </div>

        <!-- Busca -->
        <div class="stock-search-container">
            <input type="text" id="searchCategories" placeholder="🔍 Buscar categoria..." 
                   class="stock-search-input" style="width: 100%; max-width: 400px;"
                   oninput="filterCategories(this.value)">
        </div>

        <!-- Tabela de Categorias -->
        <?php if (empty($sortedCategories)): ?>
            <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <i data-lucide="tags" size="48" style="color: #d1d5db; margin-bottom: 15px;"></i>
                <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhuma categoria cadastrada</h3>
                <p style="color: #9ca3af; margin-bottom: 20px;">Crie categorias para organizar seus produtos</p>
                <button onclick="openCategoryModal()" style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Primeira Categoria
                </button>
            </div>
        <?php else: ?>
            <div class="stock-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nome da Categoria</th>
                            <th style="text-align: center; width: 180px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTable">
                        <?php foreach ($sortedCategories as $cat): ?>
                        <?php 
                            $isSystemCategory = in_array($cat['category_type'] ?? 'default', ['featured', 'combos']);
                        ?>
                        <tr class="category-row" data-name="<?= strtolower(htmlspecialchars($cat['name'])) ?>">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i data-lucide="tag" size="16" style="color: <?= $isSystemCategory ? '#f59e0b' : '#2563eb' ?>;"></i>
                                    <span style="font-weight: 500; color: #1f2937;"><?= htmlspecialchars($cat['name']) ?></span>
                                    <?php if ($isSystemCategory): ?>
                                        <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 600;">Sistema</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="stock-actions" style="justify-content: flex-start;">
                                    <a href="<?= BASE_URL ?>/admin/loja/categorias/editar?id=<?= $cat['id'] ?>" 
                                       class="btn-stock-action btn-stock-edit">
                                        <i data-lucide="pencil" size="14"></i>
                                        Editar
                                    </a>
                                    <?php if (!$isSystemCategory): ?>
                                        <a href="<?= BASE_URL ?>/admin/loja/categorias/deletar?id=<?= $cat['id'] ?>" 
                                           onclick="return confirm('Excluir a categoria &quot;<?= htmlspecialchars($cat['name']) ?>&quot;?')"
                                           class="btn-stock-action btn-stock-delete">
                                            <i data-lucide="trash-2" size="14"></i>
                                            Excluir
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal de Nova Categoria -->
<div id="categoryModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">Nova Categoria</h3>
        
        <form action="<?= BASE_URL ?>/admin/loja/categorias/salvar" method="POST">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome da Categoria</label>
                <input type="text" name="name" placeholder="Ex: Lanches, Bebidas, Pizzas..." required 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeCategoryModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="submit" style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/categories.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
