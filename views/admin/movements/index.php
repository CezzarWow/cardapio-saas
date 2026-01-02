<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

$productFilter = $_GET['product'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$totalMovements = count($movements);

// Contar entradas e saídas
$entradas = 0;
$saidas = 0;
foreach ($movements as $m) {
    if ($m['type'] == 'entrada') $entradas++;
    else $saidas++;
}
?>

<!-- CSS Estoque v2 (modernização) -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/stock-v2.css">

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Movimentações de Estoque</h1>
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
                <a href="<?= BASE_URL ?>/admin/loja/adicionais" class="stock-tab">
                    Adicionais
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/reposicao" class="stock-tab">
                    Reposição
                </a>
                <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" class="stock-tab active">
                    Movimentações
                </a>
            </div>
        </div>

        <!-- Indicadores -->
        <div class="stock-indicators">
            <div class="stock-indicator">
                <div style="background: #dbeafe; padding: 10px; border-radius: 8px;">
                    <i data-lucide="activity" size="24" style="color: #2563eb;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;"><?= $totalMovements ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Movimentações</div>
                </div>
            </div>
            <div class="stock-indicator">
                <div style="background: #d1fae5; padding: 10px; border-radius: 8px;">
                    <i data-lucide="trending-up" size="24" style="color: #059669;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #059669;"><?= $entradas ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Entradas</div>
                </div>
            </div>
            <div class="stock-indicator">
                <div style="background: #fecaca; padding: 10px; border-radius: 8px;">
                    <i data-lucide="trending-down" size="24" style="color: #dc2626;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;"><?= $saidas ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Saídas</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="stock-search-container" style="padding: 15px 20px;">
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 0.85rem;">Produto</label>
                    <select name="product" class="stock-search-input" style="min-width: 200px;">
                        <option value="">Todos os produtos</option>
                        <?php foreach ($products as $prod): ?>
                            <option value="<?= $prod['id'] ?>" <?= $productFilter == $prod['id'] ? 'selected' : '' ?>><?= htmlspecialchars($prod['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 0.85rem;">Categoria</label>
                    <select name="category" class="stock-search-input" style="min-width: 180px;">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>" <?= $categoryFilter == $cat['name'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-stock-action" style="background: #2563eb; color: white; padding: 10px 20px;">
                    <i data-lucide="filter" size="14"></i> Filtrar
                </button>
                <?php if ($productFilter || $categoryFilter): ?>
                    <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" class="btn-stock-action btn-stock-edit" style="padding: 10px 20px;">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabela de Movimentações -->
        <div class="stock-table-container">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Produto</th>
                        <th style="text-align: center;">Tipo</th>
                        <th style="text-align: center;">Qtd</th>
                        <th style="text-align: center;">Antes</th>
                        <th style="text-align: center;">Depois</th>
                        <th style="text-align: center;">Origem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                        <tr><td colspan="7" style="padding: 2rem; text-align: center; color: #999;">Nenhuma movimentação registrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($movements as $mov): ?>
                        <tr>
                            <td style="color: #6b7280; font-size: 0.9rem;">
                                <?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if($mov['product_image']): ?>
                                        <img src="<?= BASE_URL ?>/uploads/<?= $mov['product_image'] ?>" style="width: 35px; height: 35px; object-fit: cover; border-radius: 6px;">
                                    <?php else: ?>
                                        <div style="width: 35px; height: 35px; background: #eee; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #999;">
                                            <i data-lucide="image" size="16"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong style="color: #1f2937; font-size: 0.9rem;"><?= htmlspecialchars($mov['product_name']) ?></strong><br>
                                        <small style="color: #6b7280;"><?= htmlspecialchars($mov['category_name'] ?? '-') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span style="padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 600;
                                    background: <?= $mov['type'] == 'entrada' ? '#d1fae5' : '#fecaca' ?>;
                                    color: <?= $mov['type'] == 'entrada' ? '#059669' : '#dc2626' ?>;">
                                    <?= $mov['type'] == 'entrada' ? '↑ Entrada' : '↓ Saída' ?>
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 700; font-size: 1.1rem; color: <?= $mov['type'] == 'entrada' ? '#059669' : '#dc2626' ?>;">
                                <?= $mov['type'] == 'entrada' ? '+' : '-' ?><?= $mov['quantity'] ?>
                            </td>
                            <td style="text-align: center; color: #6b7280;">
                                <?= $mov['stock_before'] ?>
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #1f2937;">
                                <?= $mov['stock_after'] ?>
                            </td>
                            <td style="text-align: center;">
                                <span style="padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #e0f2fe; color: #0369a1; text-transform: capitalize;">
                                    <?= str_replace('_', ' ', $mov['source']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($movements) >= 100): ?>
        <p style="margin-top: 15px; text-align: center; color: #6b7280; font-size: 0.9rem;">
            Mostrando as últimas 100 movimentações. Use os filtros para refinar a busca.
        </p>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
