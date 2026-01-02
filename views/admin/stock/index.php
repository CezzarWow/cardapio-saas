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

<!-- CSS Estoque v2 (modernização) -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/stock-v2.css">

<main class="main-content">
    <?php require __DIR__ . '/../panel/layout/messages.php'; ?>
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
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

        <!-- Busca + Indicadores na mesma linha -->
        <div class="stock-search-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <input type="text" id="searchProduct" placeholder="🔍 Buscar produto por nome..." 
                   class="stock-search-input" style="width: 100%; max-width: 350px;"
                   oninput="filterProducts()">
            
            <div style="display: flex; gap: 20px; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="background: #dbeafe; padding: 6px; border-radius: 6px;">
                        <i data-lucide="package" style="width: 18px; height: 18px; color: #2563eb;"></i>
                    </div>
                    <div>
                        <span style="font-weight: 700; color: #1f2937;"><?= $totalProducts ?></span>
                        <span style="font-size: 0.8rem; color: #6b7280;"> produtos</span>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="background: <?= $criticalStock > 0 ? '#fecaca' : '#fef3c7' ?>; padding: 6px; border-radius: 6px;">
                        <i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: <?= $criticalStock > 0 ? '#dc2626' : '#d97706' ?>;"></i>
                    </div>
                    <div>
                        <span style="font-weight: 700; color: <?= $criticalStock > 0 ? '#dc2626' : '#d97706' ?>;"><?= $criticalStock ?></span>
                        <span style="font-size: 0.8rem; color: #6b7280;"> críticos</span>
                    </div>
                </div>
            </div>
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

        <!-- Grid de Cards -->
        <div id="stock-cards-view" class="stock-products-grid stock-fade-in">
            <?php if (empty($products)): ?>
                <div style="grid-column: 1 / -1; padding: 2rem; text-align: center; color: #999;">
                    Nenhum produto cadastrado.
                </div>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                <?php 
                    $stock = intval($prod['stock']);
                    $isCritical = $stock <= $STOCK_CRITICAL_LIMIT;
                    $isNegative = $stock < 0;
                    $stockClass = $isNegative ? 'stock-product-card-stock--danger' : ($isCritical ? 'stock-product-card-stock--warning' : 'stock-product-card-stock--ok');
                ?>
                <div class="stock-product-card product-row" 
                     data-name="<?= strtolower($prod['name']) ?>" 
                     data-category="<?= htmlspecialchars($prod['category_name']) ?>">
                    
                    <!-- Imagem (altura reduzida) -->
                    <?php if($prod['image']): ?>
                        <img src="<?= BASE_URL ?>/uploads/<?= $prod['image'] ?>" 
                             style="width: 100%; height: 140px; object-fit: cover; border-radius: 12px 12px 0 0;"
                             alt="<?= htmlspecialchars($prod['name']) ?>">
                    <?php else: ?>
                        <div style="width: 100%; height: 140px; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-radius: 12px 12px 0 0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                            <i data-lucide="image" style="width: 40px; height: 40px;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Corpo -->
                    <div class="stock-product-card-body">
                        <div class="stock-product-card-name"><?= htmlspecialchars($prod['name']) ?></div>
                        <span class="stock-product-card-category"><?= htmlspecialchars($prod['category_name']) ?></span>
                        
                        <div class="stock-product-card-footer">
                            <span class="stock-product-card-price">R$ <?= number_format($prod['price'], 2, ',', '.') ?></span>
                            <span class="stock-product-card-stock <?= $stockClass ?>"><?= $stock ?></span>
                        </div>
                    </div>
                    
                    <!-- Ações -->
                    <div class="stock-product-card-actions">
                        <a href="<?= BASE_URL ?>/admin/loja/produtos/editar?id=<?= $prod['id'] ?>" class="btn-edit">
                            <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                            Editar
                        </a>
                        <a href="javascript:void(0)" 
                           onclick="openDeleteModal(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['name'])) ?>')" class="btn-delete">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            Excluir
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

<!-- Modal de Confirmação de Exclusão -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 16px; width: 100%; max-width: 400px; margin: 20px; text-align: center;">
        <div style="width: 60px; height: 60px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
            <i data-lucide="trash-2" style="width: 28px; height: 28px; color: #dc2626;"></i>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Excluir Produto</h3>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">Tem certeza que deseja excluir <strong id="deleteProductName"></strong>?</p>
        <p style="color: #dc2626; font-size: 0.85rem; margin-bottom: 1.5rem;">⚠️ Esta ação não pode ser desfeita.</p>
        
        <div style="display: flex; gap: 10px;">
            <button onclick="closeDeleteModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Cancelar
            </button>
            <a id="deleteConfirmBtn" href="#" style="flex: 1; padding: 12px; background: #dc2626; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px;">
                🗑️ Excluir
            </a>
        </div>
    </div>
</div>

<script>
function openDeleteModal(productId, productName) {
    document.getElementById('deleteProductName').textContent = productName;
    document.getElementById('deleteConfirmBtn').href = '<?= BASE_URL ?>/admin/loja/produtos/deletar?id=' + productId;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
