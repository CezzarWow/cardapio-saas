<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

// [FASE 1] Cálculo de indicadores
$totalProducts = count($products);
$criticalStock = 0;
$STOCK_CRITICAL_LIMIT = 5; // Limite definido pelo técnico

foreach ($products as $p) {
    if (intval($p['stock']) <= $STOCK_CRITICAL_LIMIT) {
        $criticalStock++;
    }
}
?>

<main class="main-content">
    <?php require __DIR__ . '/../panel/layout/messages.php'; ?>
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Breadcrumb (dentro do main) -->
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>/admin">Painel</a> › 
            <span>Estoque</span> › 
            <strong>Produtos</strong>
        </div>

        <!-- Header com título e botão -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Gerenciar Estoque</h1>
            <a href="<?= BASE_URL ?>/admin/loja/produtos/novo" class="btn" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="plus" size="18"></i> Novo Produto
            </a>
        </div>

        <!-- Sub-abas do Estoque (STICKY) -->
        <div class="sticky-tabs">
            <div class="stock-tabs">
                <a href="<?= BASE_URL ?>/admin/loja/produtos" class="stock-tab active">
                    Produtos
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/categorias" class="stock-tab">
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

        <!-- Indicadores -->
        <div class="stock-indicators">
            <div class="stock-indicator">
                <div style="background: #dbeafe; padding: 10px; border-radius: 8px;">
                    <i data-lucide="package" size="24" style="color: #2563eb;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;"><?= $totalProducts ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Produtos cadastrados</div>
                </div>
            </div>
            
            <div class="stock-indicator">
                <div style="background: <?= $criticalStock > 0 ? '#fef3c7' : '#d1fae5' ?>; padding: 10px; border-radius: 8px;">
                    <i data-lucide="alert-triangle" size="24" style="color: <?= $criticalStock > 0 ? '#d97706' : '#059669' ?>;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: <?= $criticalStock > 0 ? '#d97706' : '#059669' ?>;"><?= $criticalStock ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Estoque crítico (≤ <?= $STOCK_CRITICAL_LIMIT ?>)</div>
                </div>
            </div>
        </div>

        <!-- Busca -->
        <div class="stock-search-container">
            <input type="text" id="searchProduct" placeholder="🔍 Buscar produto por nome..." 
                   class="stock-search-input" style="width: 100%; max-width: 400px;"
                   oninput="filterProducts()">
        </div>

        <!-- Chips de Categorias (usando $categories do controller) -->
        <div class="category-chips-container">
            <div class="category-chips">
                <button class="category-chip active" data-category="">
                    📂 Todas
                </button>
                <?php foreach ($categories as $cat): ?>
                    <?php if (!in_array($cat['category_type'] ?? 'default', ['featured', 'combos'])): ?>
                        <button class="category-chip" data-category="<?= htmlspecialchars($cat['name']) ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tabela de Produtos -->
        <div class="stock-table-container">
            <table id="productsTable">
                <thead>
                    <tr>
                        <th style="width: 70px;">Imagem</th>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th style="text-align: center;">Estoque</th>
                        <th style="text-align: center; width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" style="padding: 2rem; text-align: center; color: #999;">Nenhum produto cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                        <?php 
                            $stock = intval($prod['stock']);
                            $isCritical = $stock <= $STOCK_CRITICAL_LIMIT;
                            $isNegative = $stock < 0;
                        ?>
                        <tr class="product-row" 
                            data-name="<?= strtolower($prod['name']) ?>" 
                            data-category="<?= htmlspecialchars($prod['category_name']) ?>">
                            <td>
                                <?php if($prod['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= $prod['image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                                        <i data-lucide="image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: #1f2937;"><?= htmlspecialchars($prod['name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars(substr($prod['description'] ?? '', 0, 30)) ?>...</small>
                            </td>
                            <td>
                                <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: 600;">
                                    <?= htmlspecialchars($prod['category_name']) ?>
                                </span>
                            </td>
                            <td style="font-weight: bold; color: #2563eb;">R$ <?= number_format($prod['price'], 2, ',', '.') ?></td>
                            <td style="text-align: center;">
                                <span style="padding: 4px 12px; border-radius: 15px; font-size: 0.85rem; font-weight: 600;
                                    background: <?= $isNegative ? '#fecaca' : ($isCritical ? '#fef3c7' : '#d1fae5') ?>;
                                    color: <?= $isNegative ? '#dc2626' : ($isCritical ? '#d97706' : '#059669') ?>;">
                                    <?= $stock ?>
                                </span>
                            </td>
                            <td>
                                <div class="stock-actions">
                                    <a href="<?= BASE_URL ?>/admin/loja/produtos/editar?id=<?= $prod['id'] ?>" 
                                       class="btn-stock-action btn-stock-edit">
                                        <i data-lucide="pencil" size="14"></i>
                                        Editar
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/loja/produtos/deletar?id=<?= $prod['id'] ?>" 
                                       onclick="return confirm('Tem certeza que deseja apagar este produto?')"
                                       class="btn-stock-action btn-stock-delete">
                                        <i data-lucide="trash-2" size="14"></i>
                                        Excluir
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Script de Filtro UNIFICADO (busca + chips) -->
<script>
// Variável para armazenar a categoria selecionada
let selectedCategory = '';

// Função unificada de filtro
function filterProducts() {
    const search = document.getElementById('searchProduct').value.toLowerCase();
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const name = row.dataset.name;
        const cat = row.dataset.category;
        
        const matchName = name.includes(search);
        const matchCategory = !selectedCategory || cat === selectedCategory;
        
        row.style.display = (matchName && matchCategory) ? '' : 'none';
    });
}

// Event listeners para chips de categoria
document.querySelectorAll('.category-chip').forEach(chip => {
    chip.addEventListener('click', function() {
        // Remove active de todos
        document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
        // Adiciona no clicado
        this.classList.add('active');
        
        // Atualiza categoria selecionada
        selectedCategory = this.dataset.category;
        
        // Aplica filtro
        filterProducts();
    });
});
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
