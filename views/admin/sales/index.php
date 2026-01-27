<?php
\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Histórico de Vendas</h1>
                <p style="color: #6b7280;">Extrato completo de pedidos</p>
            </div>
            <div style="background: #eff6ff; color: #1d4ed8; padding: 10px 20px; border-radius: 8px; font-weight: 700; border: 1px solid #dbeafe;">
                Total: R$ <?= \App\Helpers\ViewHelper::e($totalSalesFormatted ?? '') ?>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;"># Pedido</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Data</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Valor</th>
                        <th style="padding: 1rem; font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="4" style="padding: 3rem; text-align: center; color: #9ca3af;">Nenhuma venda realizada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $sale): ?>
                        <?php $saleId = (int) ($sale['id'] ?? 0); ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 1rem; font-weight: 600; color: #1f2937;">
                                <?= \App\Helpers\ViewHelper::e($sale['formatted_id'] ?? '') ?>
                            </td>
                            <td style="padding: 1rem; color: #4b5563;">
                                <?= \App\Helpers\ViewHelper::e($sale['formatted_date'] ?? '') ?>
                            </td>
                            <td style="padding: 1rem; font-weight: 700; color: #2563eb;">
                                <?= \App\Helpers\ViewHelper::e($sale['formatted_total'] ?? '') ?>
                            </td>
                                 <td style="padding: 1rem; display: flex; gap: 8px;">
                                    <button onclick="openOrderDetails(<?= (int) $saleId ?>)" 
                                            title="Ver Itens"
                                            style="background: #f3f4f6; border: none; padding: 8px; border-radius: 6px; cursor: pointer; color: #4b5563;">
                                        <i data-lucide="eye" style="width: 18px;"></i>
                                    </button>

                                    <?php if ($sale['can_reopen']): ?>
                                        
                                        <button onclick="reabrirMesa(<?= (int) $saleId ?>)" 
                                                title="Reabrir Mesa (Estornar e Editar)"
                                                style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 8px; border-radius: 6px; cursor: pointer; color: #2563eb;">
                                            <i data-lucide="rotate-ccw" style="width: 18px;"></i>
                                        </button>

                                        <button onclick="cancelarVenda(<?= (int) $saleId ?>)" 
                                                title="Cancelar Venda (Excluir)"
                                                style="background: #fef2f2; border: 1px solid #fecaca; padding: 8px; border-radius: 6px; cursor: pointer; color: #b91c1c;">
                                            <i data-lucide="trash-2" style="width: 18px;"></i>
                                        </button>

                                    <?php elseif ($sale['is_canceled']): ?>
                                        <span style="font-size: 0.8rem; color: #ef4444; font-weight: bold;">Cancelado</span>
                                    <?php endif; ?>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
        <?php
          $req = $_SERVER['REQUEST_URI'] ?? '/admin/loja/vendas';
          $base = preg_replace('/[?&]page=\d+/', '', $req);
          $base = rtrim(rtrim($base, '&'), '?');
          $sep = strpos($base, '?') !== false ? '&' : '?';
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding: 0 0.5rem;">
            <span style="color: #6b7280; font-size: 0.875rem;">
                Página <?= (int)$pagination['page'] ?> de <?= (int)$pagination['total_pages'] ?>
                (<?= (int)$pagination['total'] ?> vendas)
            </span>
            <div style="display: flex; gap: 8px;">
                <?php if ($pagination['page'] > 1): ?>
                    <a href="<?= htmlspecialchars($base . $sep . 'page=' . ($pagination['page'] - 1)) ?>" style="padding: 8px 12px; background: #f3f4f6; border-radius: 6px; text-decoration: none; color: #374151;">← Anterior</a>
                <?php endif; ?>
                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                    <a href="<?= htmlspecialchars($base . $sep . 'page=' . ($pagination['page'] + 1)) ?>" style="padding: 8px 12px; background: #2563eb; color: white; border-radius: 6px; text-decoration: none;">Próxima →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

</main>

<div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 20px; border-radius: 12px; width: 400px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
            <h3 style="font-weight: 700; color: #1f2937;">Itens do Pedido</h3>
            <button onclick="SalesAdmin.closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <div id="modalItemsList" style="max-height: 300px; overflow-y: auto;">
            Carregando...
        </div>

        <div style="margin-top: 15px; text-align: right;">
            <button onclick="SalesAdmin.closeModal()" 
                    style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                Fechar
            </button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/sales.js?v=<?= time() ?>"></script>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
