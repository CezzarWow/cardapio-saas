<?php
// LOCALIZACAO ORIGINAL: views/admin/stock/index.php 
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
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header com título e botão -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Gerenciar Estoque</h1>
            <a href="<?= BASE_URL ?>/admin/loja/produtos/novo" class="btn" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="plus" size="18"></i> Novo Produto
            </a>
        </div>

        <!-- [FASE 3] Sub-abas do Estoque -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/admin/loja/produtos" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #2563eb; color: white;">
                Produtos
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/categorias" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
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

        <!-- [FASE 1] Indicadores -->
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px;">
                <div style="background: #dbeafe; padding: 10px; border-radius: 8px;">
                    <i data-lucide="package" size="24" style="color: #2563eb;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;"><?= $totalProducts ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Produtos cadastrados</div>
                </div>
            </div>
            
            <div style="background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px;">
                <div style="background: <?= $criticalStock > 0 ? '#fef3c7' : '#d1fae5' ?>; padding: 10px; border-radius: 8px;">
                    <i data-lucide="alert-triangle" size="24" style="color: <?= $criticalStock > 0 ? '#d97706' : '#059669' ?>;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: <?= $criticalStock > 0 ? '#d97706' : '#059669' ?>;"><?= $criticalStock ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Estoque crítico (≤ <?= $STOCK_CRITICAL_LIMIT ?>)</div>
                </div>
            </div>
        </div>

        <!-- [FASE 1] Busca e Filtros -->
        <div style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <input type="text" id="searchProduct" placeholder="Buscar produto..." 
                       style="flex: 1; min-width: 200px; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem;"
                       oninput="filterProducts()">
                <select id="filterCategory" style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; background: white; min-width: 150px;" onchange="filterProducts()">
                    <option value="">Todas as categorias</option>
                    <?php 
                    $cats = [];
                    foreach ($products as $p) { 
                        if (!in_array($p['category_name'], $cats)) {
                            $cats[] = $p['category_name'];
                            echo '<option value="'.htmlspecialchars($p['category_name']).'">'.htmlspecialchars($p['category_name']).'</option>';
                        }
                    } 
                    ?>
                </select>
            </div>
        </div>

        <!-- Tabela de Produtos -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;" id="productsTable">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Imagem</th>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Produto</th>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Categoria</th>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Preço</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Estoque</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Ações</th>
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
                            data-category="<?= htmlspecialchars($prod['category_name']) ?>"
                            style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 10px;">
                                <?php if($prod['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= $prod['image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                                        <i data-lucide="image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <strong style="color: #1f2937;"><?= htmlspecialchars($prod['name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars(substr($prod['description'] ?? '', 0, 30)) ?>...</small>
                            </td>
                            <td style="padding: 15px;">
                                <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: 600;">
                                    <?= htmlspecialchars($prod['category_name']) ?>
                                </span>
                            </td>
                            <td style="padding: 15px; font-weight: bold; color: #2563eb;">R$ <?= number_format($prod['price'], 2, ',', '.') ?></td>
                            <td style="padding: 15px; text-align: center;">
                                <span style="padding: 4px 12px; border-radius: 15px; font-size: 0.85rem; font-weight: 600;
                                    background: <?= $isNegative ? '#fecaca' : ($isCritical ? '#fef3c7' : '#d1fae5') ?>;
                                    color: <?= $isNegative ? '#dc2626' : ($isCritical ? '#d97706' : '#059669') ?>;">
                                    <?= $stock ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 10px;">
                                    <a href="<?= BASE_URL ?>/admin/loja/produtos/editar?id=<?= $prod['id'] ?>" style="color: #2563eb;" title="Editar">
                                        <i data-lucide="pencil" style="width: 18px;"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/loja/produtos/deletar?id=<?= $prod['id'] ?>" onclick="return confirm('Tem certeza que deseja apagar este produto?')" style="color: #ef4444;" title="Excluir">
                                        <i data-lucide="trash-2" style="width: 18px;"></i>
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

<!-- [FASE 1] Script de Busca e Filtro -->
<script>
function filterProducts() {
    const search = document.getElementById('searchProduct').value.toLowerCase();
    const category = document.getElementById('filterCategory').value;
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const name = row.dataset.name;
        const cat = row.dataset.category;
        
        const matchName = name.includes(search);
        const matchCategory = !category || cat === category;
        
        row.style.display = (matchName && matchCategory) ? '' : 'none';
    });
}
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>

