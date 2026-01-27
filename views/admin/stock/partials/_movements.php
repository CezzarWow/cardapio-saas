<?php
/**
 * Movements Partial - Para carregamento AJAX
 * Arquivo: views/admin/stock/partials/_movements.php
 */
?>

<!-- Indicadores -->
<div class="stock-indicators" style="margin-bottom: 20px;">
    <div class="stock-indicator">
        <div style="background: #dbeafe; padding: 10px; border-radius: 8px;">
            <i data-lucide="activity" style="width: 24px; height: 24px; color: #2563eb;"></i>
        </div>
        <div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;"><?= (int) ($totalMovements ?? 0) ?></div>
            <div style="font-size: 0.85rem; color: #6b7280;">Movimentações</div>
        </div>
    </div>
    <div class="stock-indicator">
        <div style="background: #d1fae5; padding: 10px; border-radius: 8px;">
            <i data-lucide="trending-up" style="width: 24px; height: 24px; color: #059669;"></i>
        </div>
        <div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669;"><?= (int) ($entradas ?? 0) ?></div>
            <div style="font-size: 0.85rem; color: #6b7280;">Entradas</div>
        </div>
    </div>
    <div class="stock-indicator">
        <div style="background: #fecaca; padding: 10px; border-radius: 8px;">
            <i data-lucide="trending-down" style="width: 24px; height: 24px; color: #dc2626;"></i>
        </div>
        <div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;"><?= (int) ($saidas ?? 0) ?></div>
            <div style="font-size: 0.85rem; color: #6b7280;">Saídas</div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="stock-search-container" style="padding: 15px 20px; margin-bottom: 20px;">
    <form method="GET" action="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/catalogo" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
        <input type="hidden" name="tab" value="movimentacoes">
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 0.85rem;">Data Início</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>" 
                   class="stock-search-input" style="min-width: 140px; padding: 10px;">
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 0.85rem;">Data Fim</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>" 
                   class="stock-search-input" style="min-width: 140px; padding: 10px;">
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 0.85rem;">Produto</label>
            <select name="product" class="stock-search-input" style="min-width: 200px;">
                <option value="">Todos os produtos</option>
                <?php foreach ($products as $prod): ?>
                    <option value="<?= (int) ($prod['id'] ?? 0) ?>" <?= (int) $productFilter === (int) ($prod['id'] ?? 0) ? 'selected' : '' ?>><?= \App\Helpers\ViewHelper::e($prod['name'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 0.85rem;">Categoria</label>
            <select name="category" class="stock-search-input" style="min-width: 180px;">
                <option value="">Todas as categorias</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= \App\Helpers\ViewHelper::e($cat['name'] ?? '') ?>" <?= (string) $categoryFilter === (string) ($cat['name'] ?? '') ? 'selected' : '' ?>><?= \App\Helpers\ViewHelper::e($cat['name'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-stock-action" style="background: #2563eb; color: white; padding: 10px 20px;">
            <i data-lucide="filter" style="width: 14px; height: 14px;"></i> Filtrar
        </button>
        <?php if ($productFilter || $categoryFilter || !empty($_GET['start_date']) || !empty($_GET['end_date'])): ?>
            <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/catalogo#movimentacoes" class="btn-stock-action btn-stock-edit" style="padding: 10px 20px;">
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
                <?php $productImageFile = !empty($mov['product_image']) ? basename((string) $mov['product_image']) : ''; ?>
                <tr>
                    <td style="color: #6b7280; font-size: 0.9rem;">
                        <?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <?php if ($productImageFile !== ''): ?>
                                <img src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/uploads/thumb/<?= \App\Helpers\ViewHelper::e($productImageFile) ?>" style="width: 35px; height: 35px; object-fit: cover; border-radius: 6px;" loading="lazy">
                            <?php else: ?>
                                <div style="width: 35px; height: 35px; background: #eee; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #999;">
                                    <i data-lucide="image" style="width: 16px; height: 16px;"></i>
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
                        <?= $mov['type'] == 'entrada' ? '+' : '-' ?><?= (int) ($mov['quantity'] ?? 0) ?>
                    </td>
                    <td style="text-align: center; color: #6b7280;">
                        <?= (int) ($mov['stock_before'] ?? 0) ?>
                    </td>
                    <td style="text-align: center; font-weight: 600; color: #1f2937;">
                        <?= (int) ($mov['stock_after'] ?? 0) ?>
                    </td>
                    <td style="text-align: center;">
                        <span style="padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #e0f2fe; color: #0369a1; text-transform: capitalize;">
                            <?= \App\Helpers\ViewHelper::e(str_replace('_', ' ', (string) ($mov['source'] ?? ''))) ?>
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
