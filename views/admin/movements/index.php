<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

$productFilter = $_GET['product'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Movimentações de Estoque</h1>
            <p style="color: #6b7280; margin-top: 5px;">Histórico de todas as entradas e saídas de estoque</p>
        </div>

        <!-- Sub-abas do Estoque -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/admin/loja/produtos" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Produtos
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/reposicao" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Reposição
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #2563eb; color: white;">
                Movimentações
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/adicionais" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Adicionais
            </a>
        </div>

        <!-- Filtros -->
        <div style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 0.85rem;">Produto</label>
                    <select name="product" style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; background: white; min-width: 200px;">
                        <option value="">Todos os produtos</option>
                        <?php foreach ($products as $prod): ?>
                            <option value="<?= $prod['id'] ?>" <?= $productFilter == $prod['id'] ? 'selected' : '' ?>><?= htmlspecialchars($prod['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 0.85rem;">Categoria</label>
                    <select name="category" style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; background: white; min-width: 180px;">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>" <?= $categoryFilter == $cat['name'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Filtrar
                </button>
                <?php if ($productFilter || $categoryFilter): ?>
                    <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" style="padding: 10px 20px; background: #f3f4f6; color: #374151; border-radius: 8px; text-decoration: none; font-weight: 600;">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabela de Movimentações -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Data</th>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Produto</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Tipo</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Quantidade</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Antes</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Depois</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Origem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                        <tr><td colspan="7" style="padding: 2rem; text-align: center; color: #999;">Nenhuma movimentação registrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($movements as $mov): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 15px; color: #6b7280; font-size: 0.9rem;">
                                <?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?>
                            </td>
                            <td style="padding: 15px;">
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
                            <td style="padding: 15px; text-align: center;">
                                <span style="padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 600;
                                    background: <?= $mov['type'] == 'entrada' ? '#d1fae5' : '#fecaca' ?>;
                                    color: <?= $mov['type'] == 'entrada' ? '#059669' : '#dc2626' ?>;">
                                    <?= $mov['type'] == 'entrada' ? '↑ Entrada' : '↓ Saída' ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: 700; font-size: 1.1rem; color: <?= $mov['type'] == 'entrada' ? '#059669' : '#dc2626' ?>;">
                                <?= $mov['type'] == 'entrada' ? '+' : '-' ?><?= $mov['quantity'] ?>
                            </td>
                            <td style="padding: 15px; text-align: center; color: #6b7280;">
                                <?= $mov['stock_before'] ?>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: 600; color: #1f2937;">
                                <?= $mov['stock_after'] ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
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
