<?php
/**
 * _caixa.php - Partial do Caixa para SPA Shell
 * 
 * Variáveis esperadas do AppShellController:
 * - $caixa: array|null - Caixa aberto ou null
 * - $resumo: array - Resumo de vendas (se aberto)
 * - $movimentosView: array - Movimentações decoradas (se aberto)
 * - $dinheiroEmCaixa: float - Valor em gaveta (se aberto)
 */

// Helper function para cards de resumo
if (!function_exists('renderSummaryCardSpa')) {
    function renderSummaryCardSpa($label, $value, $color, $textColor = null, $subtitle = null) {
        $textColor = $textColor ?? $color;
        ?>
        <div style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid <?= $color ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <span style="display: block; color: #6b7280; font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($label) ?></span>
            <span style="display: block; font-size: 1.5rem; font-weight: 800; color: <?= $textColor ?>; margin-top: 5px;">
                R$ <?= number_format($value, 2, ',', '.') ?>
            </span>
            <?php if ($subtitle): ?>
                <small style="color: #9ca3af; font-size: 0.75rem;"><?= htmlspecialchars($subtitle) ?></small>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>

<?php if (!$caixa): ?>
<!-- ========================================== -->
<!-- ESTADO: CAIXA FECHADO (Tela de Abertura)  -->
<!-- ========================================== -->
<div style="display: flex; justify-content: center; align-items: center; width: 100%; min-height: 80vh; background: #f3f4f6;">
    <div style="background: white; padding: 3rem; border-radius: 20px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
        <div style="background: #dcfce7; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <i data-lucide="unlock" style="width: 40px; height: 40px; color: #166534;"></i>
        </div>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #1f2937; margin-bottom: 0.5rem;">Iniciar Dia</h1>
        <p style="color: #6b7280; margin-bottom: 2rem;">Informe o Fundo de Troco para abrir o caixa.</p>
        <form action="<?= BASE_URL ?>/admin/loja/caixa/abrir" method="POST" id="form-open-cashier">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <input type="text" name="opening_balance" required placeholder="R$ 0,00" 
                   style="width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1.5rem; font-weight: bold; text-align: center; color: #2563eb; margin-bottom: 20px;">
            <button type="submit" style="width: 100%; padding: 15px; font-size: 1.1rem; border: none; border-radius: 12px; cursor: pointer; background: #16a34a; color: white; font-weight: bold;">
                Abrir Novo Caixa
            </button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ========================================== -->
<!-- ESTADO: CAIXA ABERTO (Dashboard)          -->
<!-- ========================================== -->
<div style="padding: 2rem; width: 100%; height: 100%; overflow-y: auto;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #1f2937;">Financeiro & Caixa</h1>
            <p style="color: #6b7280;">Visão geral do turno atual (Aberto em <?= date('d/m/Y H:i', strtotime($caixa['opened_at'])) ?>)</p>
        </div>
        <div style="background: #dcfce7; color: #166534; padding: 5px 15px; border-radius: 20px; font-weight: bold; border: 1px solid #bbf7d0;">
            ● Caixa Aberto
        </div>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <?php
        renderSummaryCardSpa('TOTAL BRUTO', $resumo['total_bruto'], '#2563eb', '#1f2937');
        renderSummaryCardSpa('DINHEIRO (GAVETA)', $dinheiroEmCaixa, '#16a34a', null, 'Início: R$ ' . number_format($caixa['opening_balance'], 2, ',', '.'));
        renderSummaryCardSpa('CRÉDITO', $resumo['credito'], '#4f46e5');
        renderSummaryCardSpa('DÉBITO', $resumo['debito'], '#f97316');
        renderSummaryCardSpa('PIX', $resumo['pix'], '#9333ea');
        ?>
    </div>

    <!-- Main Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        
        <!-- Fluxo do Caixa -->
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
                                <i data-lucide="<?= $mov['icon'] ?>" style="width: 18px; height: 18px;"></i>
                            </div>
                            <div>
                                <strong style="color: #374151; text-transform: capitalize;"><?= htmlspecialchars($mov['type']) ?></strong>
                                <div style="font-size: 0.8rem; color: #6b7280;">
                                    <?= htmlspecialchars($mov['description'] ?? 'Sem descrição') ?>
                                </div>

                                <div style="margin-top: 5px; display: flex; gap: 10px;">
                                    <?php if ($mov['type'] == 'venda' && $mov['order_id']): ?>
                                        <a href="<?= BASE_URL ?>/admin/loja/caixa/estornar-pdv?id=<?= $mov['id'] ?>" 
                                           onclick="return confirm('Editar venda? O valor sairá do caixa e os itens irão para o balcão.')"
                                           style="font-size: 0.75rem; color: #2563eb; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                            <i data-lucide="edit-3" style="width: 12px; height: 12px;"></i> Editar
                                        </a>

                                        <?php if ($mov['is_table_reopen']): ?>
                                            <a href="<?= BASE_URL ?>/admin/loja/caixa/estornar-mesa?id=<?= $mov['id'] ?>" 
                                               onclick="return confirm('Reabrir mesa? A mesa ficará ocupada novamente.')"
                                               style="font-size: 0.75rem; color: #d97706; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i> Reabrir
                                            </a>
                                        <?php endif; ?>

                                        <a href="javascript:void(0)" onclick="CashierSPA.openOrderDetails(<?= $mov['order_id'] ?>, '<?= number_format($mov['amount'], 2, ',', '.') ?>', '<?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?>')"
                                           style="font-size: 0.75rem; color: #6b7280; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                            <i data-lucide="scroll-text" style="width: 12px; height: 12px;"></i> Ver Comanda
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= BASE_URL ?>/admin/loja/caixa/remover?id=<?= $mov['id'] ?>" 
                                       onclick="return confirm('Tem certeza? Isso apagará o registro do caixa. Se for venda, também cancelará o pedido.')"
                                       style="font-size: 0.75rem; color: #dc2626; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                        <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i> Apagar
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

        <!-- Sidebar Ações -->
        <div style="display: flex; flex-direction: column; gap: 15px;">
            
            <!-- Ações Rápidas -->
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 15px;">Ações Rápidas</h3>
                
                <button onclick="CashierSPA.openModal('suprimento')" style="width: 100%; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 10px;">
                    <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Adicionar Dinheiro
                </button>

                <button onclick="CashierSPA.openModal('sangria')" style="width: 100%; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="minus-circle" style="width: 18px; height: 18px;"></i> Retirar Valor
                </button>
            </div>

            <!-- Encerrar Turno -->
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center;">
                <i data-lucide="lock" style="width: 32px; height: 32px; color: #ef4444; margin-bottom: 10px;"></i>
                <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 5px;">Encerrar Turno</h3>
                <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 15px;">Finalize o dia para conferência.</p>
                
                <a href="<?= BASE_URL ?>/admin/loja/caixa/fechar" onclick="return confirm('Tem certeza que deseja fechar o caixa?')" 
                   style="display: block; width: 100%; background: #ef4444; color: white; padding: 12px; border-radius: 8px; font-weight: 700; text-decoration: none;">
                    Fechar Caixa
                </a>
            </div>

        </div>
    </div>

</div>

<!-- Modal Movimentação (Suprimento/Sangria) -->
<div id="modalMovimento" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 400px; max-width: 90%;">
        <h3 id="modalTitle" style="font-weight: 800; font-size: 1.2rem; margin-bottom: 15px;">Nova Movimentação</h3>
        
        <form action="<?= BASE_URL ?>/admin/loja/caixa/movimentar" method="POST" id="form-movimento">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <input type="hidden" name="type" id="movType">
            
            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Valor (R$)</label>
            <input type="text" name="amount" required placeholder="0,00" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px;">

            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Motivo / Descrição</label>
            <input type="text" name="description" required placeholder="Ex: Pagamento Fornecedor" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalMovimento').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid #ddd; background: white; border-radius: 8px; cursor: pointer;">Cancelar</button>
                <button type="submit" style="flex: 1; padding: 10px; border: none; border-radius: 8px; cursor: pointer; background: #2563eb; color: white; font-weight: 600;">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Comanda (Estilo Cupom Fiscal) -->
<div id="orderDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center;">
    <div style="background: #fff; padding: 0; border-radius: 5px; width: 320px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); font-family: 'Courier New', Courier, monospace; border: 1px solid #e5e7eb;">
        
        <!-- Cabeçalho do Cupom -->
        <div style="background: #fef3c7; padding: 15px; text-align: center; border-bottom: 2px dashed #d1d5db; border-radius: 5px 5px 0 0;">
            <h3 style="font-weight: 800; font-size: 1.1rem; color: #000; margin: 0; text-transform: uppercase;">Comprovante</h3>
            <div id="receiptDate" style="font-size: 0.75rem; color: #4b5563; margin-top: 5px;"></div>
        </div>
        
        <!-- Lista de Itens -->
        <div id="modalItemsList" style="padding: 15px; max-height: 400px; overflow-y: auto; background: #fffbeeb0;">
            Carregando...
        </div>

        <!-- Total e Rodapé -->
        <div style="padding: 15px; background: #fff; border-top: 2px dashed #d1d5db; border-radius: 0 0 5px 5px;">
            <div style="display: flex; justify-content: space-between; align-items: center; font-weight: 800; font-size: 1.1rem;">
                <span>TOTAL</span>
                <span id="receiptTotal">R$ 0,00</span>
            </div>
            
            <button onclick="document.getElementById('orderDetailsModal').style.display='none'" 
                    style="width: 100%; margin-top: 15px; background: #000; color: #fff; border: none; padding: 10px; font-family: sans-serif; font-weight: bold; border-radius: 4px; cursor: pointer; text-transform: uppercase; font-size: 0.8rem;">
                Fechar
            </button>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Config JSON para SPA -->
<div id="caixa-config" data-config='<?= json_encode([
    "baseUrl" => BASE_URL,
    "caixaAberto" => $caixa ? true : false
]) ?>'></div>

<!-- Script do Caixa SPA -->
<script data-spa-script src="<?= BASE_URL ?>/js/admin/cashier.js?v=<?= time() ?>"></script>
