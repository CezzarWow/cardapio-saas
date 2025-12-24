<?php
// LOCALIZACAO ORIGINAL: views/admin/categories/index.php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Categorias</h1>
                <p style="color: #6b7280; margin-top: 5px;">Gerencie as categorias dos produtos</p>
            </div>
            <button onclick="openCategoryModal()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="plus" size="18"></i> Nova Categoria
            </button>
        </div>

        <!-- Sub-abas do Estoque -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/admin/loja/produtos" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Produtos
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/categorias" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #2563eb; color: white;">
                Categorias
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/adicionais" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Adicionais
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/reposicao" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Reposição
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Movimentações
            </a>
        </div>

        <!-- Barra de Pesquisa -->
        <div style="background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="position: relative;">
                <i data-lucide="search" size="18" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                <input type="text" id="searchCategories" placeholder="Pesquisar categorias..." 
                       oninput="filterCategories(this.value)"
                       style="width: 100%; padding: 10px 12px 10px 40px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Lista de Categorias -->
        <?php if (empty($categories)): ?>
            <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <i data-lucide="tags" size="48" style="color: #d1d5db; margin-bottom: 15px;"></i>
                <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhuma categoria cadastrada</h3>
                <p style="color: #9ca3af; margin-bottom: 20px;">Crie categorias para organizar seus produtos</p>
                <button onclick="openCategoryModal()" style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Primeira Categoria
                </button>
            </div>
        <?php else: ?>
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                            <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #374151;">Nome</th>
                            <th style="padding: 15px 20px; text-align: center; font-weight: 600; color: #374151; width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTable">
                        <?php foreach ($categories as $cat): ?>
                        <tr class="category-row" data-name="<?= strtolower(htmlspecialchars($cat['name'])) ?>" style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 15px 20px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i data-lucide="tag" size="16" style="color: #2563eb;"></i>
                                    <span style="font-weight: 500; color: #1f2937;"><?= htmlspecialchars($cat['name']) ?></span>
                                </div>
                            </td>
                            <td style="padding: 15px 20px; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="<?= BASE_URL ?>/admin/loja/categorias/editar?id=<?= $cat['id'] ?>" 
                                       style="padding: 6px 10px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px;"
                                       title="Editar">
                                        <i data-lucide="pencil" size="14"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/loja/categorias/deletar?id=<?= $cat['id'] ?>" 
                                       onclick="return confirm('Excluir a categoria &quot;<?= htmlspecialchars($cat['name']) ?>&quot;?')"
                                       style="padding: 6px 10px; background: #fef2f2; color: #dc2626; text-decoration: none; border-radius: 6px;"
                                       title="Excluir">
                                        <i data-lucide="trash-2" size="14"></i>
                                    </a>
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

<script>
function openCategoryModal() {
    document.getElementById('categoryModal').style.display = 'flex';
}
function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeCategoryModal();
});

function filterCategories(query) {
    const rows = document.querySelectorAll('.category-row');
    const q = query.toLowerCase().trim();
    rows.forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>

