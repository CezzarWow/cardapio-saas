<?php
\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/cashier/partials/_summary_card.php', get_defined_vars());
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; height: 100%; overflow-y: auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #1f2937;">Financeiro & Caixa</h1>
                <p style="color: #6b7280;">Visão geral do turno atual (Aberto em <?= date('d/m/Y H:i', strtotime($caixa['opened_at'])) ?>)</p>
            </div>
            <div style="background: #dcfce7; color: #166534; padding: 5px 15px; border-radius: 20px; font-weight: bold; border: 1px solid #bbf7d0;">
                ● Caixa Aberto
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <?php
            renderSummaryCard('TOTAL BRUTO', $resumo['total_bruto'], '#2563eb', '#1f2937');
renderSummaryCard('DINHEIRO (GAVETA)', $dinheiroEmCaixa, '#16a34a', null, 'Início: R$ ' . number_format($caixa['opening_balance'], 2, ',', '.'));
renderSummaryCard('CRÉDITO', $resumo['credito'], '#4f46e5');
renderSummaryCard('DÉBITO', $resumo['debito'], '#f97316');
renderSummaryCard('PIX', $resumo['pix'], '#9333ea');
?>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 15px; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px;">
                    Fluxo do Caixa (Extrato)
                </h3>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($movimentosView)): ?>
                        <p style="color: #9ca3af; text-align: center; padding: 20px;">Nenhuma movimentação ainda.</p>
                    <?php else: ?>
                        <?php foreach ($movimentosView as $mov): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
                            
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <div style="background: <?= $mov['color_bg'] ?>; padding: 8px; border-radius: 8px; color: <?= $mov['color_text'] ?>;">
                                    <i data-lucide="<?= $mov['icon'] ?>" size="18"></i>
                                </div>
                                <div>
                                    <strong style="color: #374151; text-transform: capitalize;"><?= htmlspecialchars($mov['type']) ?></strong>
                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                        <?= htmlspecialchars($mov['description'] ?? 'Sem descrição') ?>
                                    </div>

                                    <div style="margin-top: 5px; display: flex; gap: 10px;">

                                        <?php if ($mov['type'] == 'venda' && $mov['order_id']): ?>
                                                
                                                <a href="caixa/estornar-pdv?id=<?= $mov['id'] ?>" 
                                                   onclick="return confirm('Editar venda? O valor sairá do caixa e os itens irão para o balcão.')"
                                                   style="font-size: 0.75rem; color: #2563eb; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                                    <i data-lucide="edit-3" size="12"></i> Editar
                                                </a>

                                                <?php if ($mov['is_table_reopen']): ?>
                                                    <a href="caixa/estornar-mesa?id=<?= $mov['id'] ?>" 
                                                       onclick="return confirm('Reabrir mesa? A mesa ficará ocupada novamente.')"
                                                       style="font-size: 0.75rem; color: #d97706; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                                        <i data-lucide="rotate-ccw" size="12"></i> Reabrir
                                                    </a>
                                                <?php endif; ?>

                                                <a href="javascript:void(0)" onclick="openOrderDetails(<?= $mov['order_id'] ?>, '<?= number_format($mov['amount'], 2, ',', '.') ?>', '<?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?>')"
                                                   style="font-size: 0.75rem; color: #6b7280; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                                    <i data-lucide="scroll-text" size="12"></i> Ver Comanda
                                                </a>

                                        <?php endif; ?>

                                        <a href="caixa/remover?id=<?= $mov['id'] ?>" 
                                           onclick="return confirm('Tem certeza? Isso apagará o registro do caixa. Se for venda, também cancelará o pedido.')"
                                           style="font-size: 0.75rem; color: #dc2626; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                            <i data-lucide="trash-2" size="12"></i> Apagar
                                        </a>

                                    </div>
                                </div>
                            </div>

                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: <?= $mov['color_text'] ?>;">
                                    <?= $mov['sign'] ?> R$ <?= number_format($mov['amount'], 2, ',', '.') ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #9ca3af;">
                                    <?= date('H:i', strtotime($mov['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 15px;">
                
                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 15px;">Ações Rápidas</h3>
                    
                    <button onclick="openModal('suprimento')" style="width: 100%; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 10px;">
                        <i data-lucide="plus-circle" size="18"></i> Adicionar Dinheiro
                    </button>

                    <button onclick="openModal('sangria')" style="width: 100%; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i data-lucide="minus-circle" size="18"></i> Retirar Valor
                    </button>
                </div>

                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center;">
                    <i data-lucide="lock" size="32" color="#ef4444" style="margin-bottom: 10px;"></i>
                    <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 5px;">Encerrar Turno</h3>
                    <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 15px;">Finalize o dia para conferência.</p>
                    
                    <a href="caixa/fechar" onclick="return confirm('Tem certeza que deseja fechar o caixa?')" 
                       style="display: block; width: 100%; background: #ef4444; color: white; padding: 12px; border-radius: 8px; font-weight: 700; text-decoration: none;">
                        Fechar Caixa
                    </a>
                </div>

            </div>
        </div>

    </div>
</main>

<?php \App\Core\View::renderFromScope('admin/cashier/partials/_modal_movimento.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/cashier/partials/_modal_comanda.php', get_defined_vars()); ?>

<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/admin/cashier.js?v=<?= time() ?>"></script>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
