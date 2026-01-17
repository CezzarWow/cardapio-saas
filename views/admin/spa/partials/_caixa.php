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
?>

<!-- Cashier CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin/cashier.css?v=<?= APP_VERSION ?>">

<?php if (!$caixa): ?>
<!-- ========================================== -->
<!-- ESTADO: CAIXA FECHADO (Tela de Abertura)  -->
<!-- ========================================== -->
<div class="cashier-closed-container">
    <div class="cashier-open-card">
        <div class="cashier-icon-circle">
            <i data-lucide="unlock"></i>
        </div>
        <h1 class="cashier-title">Iniciar Dia</h1>
        <p class="cashier-subtitle">Informe o Fundo de Troco para abrir o caixa.</p>
        <form action="<?= BASE_URL ?>/admin/loja/caixa/abrir" method="POST" id="form-open-cashier">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <input type="text" name="opening_balance" required placeholder="R$ 0,00" class="cashier-input">
            <button type="submit" class="cashier-btn-open">
                Abrir Novo Caixa
            </button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ========================================== -->
<!-- ESTADO: CAIXA ABERTO (Dashboard)          -->
<!-- ========================================== -->
<div class="cashier-dashboard">
    
    <!-- Header -->
    <div class="cashier-header">
        <div>
            <h1 class="cashier-header-title">Financeiro & Caixa</h1>
            <p class="cashier-header-subtitle">Visão geral do turno atual (Aberto em <?= date('d/m/Y H:i', strtotime($caixa['opened_at'])) ?>)</p>
        </div>
        <div class="cashier-status-badge">
            ● Caixa Aberto
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="cashier-summary-grid">
        <div class="cashier-summary-card card-blue">
            <span class="cashier-summary-label">TOTAL BRUTO</span>
            <span class="cashier-summary-value">R$ <?= number_format($resumo['total_bruto'], 2, ',', '.') ?></span>
        </div>
        <div class="cashier-summary-card card-green">
            <span class="cashier-summary-label">DINHEIRO (GAVETA)</span>
            <span class="cashier-summary-value">R$ <?= number_format($dinheiroEmCaixa, 2, ',', '.') ?></span>
            <small class="cashier-summary-subtitle">Início: R$ <?= number_format($caixa['opening_balance'], 2, ',', '.') ?></small>
        </div>
        <div class="cashier-summary-card card-indigo">
            <span class="cashier-summary-label">CRÉDITO</span>
            <span class="cashier-summary-value">R$ <?= number_format($resumo['credito'], 2, ',', '.') ?></span>
        </div>
        <div class="cashier-summary-card card-orange">
            <span class="cashier-summary-label">DÉBITO</span>
            <span class="cashier-summary-value">R$ <?= number_format($resumo['debito'], 2, ',', '.') ?></span>
        </div>
        <div class="cashier-summary-card card-purple">
            <span class="cashier-summary-label">PIX</span>
            <span class="cashier-summary-value">R$ <?= number_format($resumo['pix'], 2, ',', '.') ?></span>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="cashier-main-grid">
        
        <!-- Fluxo do Caixa -->
        <div class="cashier-panel">
            <h3 class="cashier-panel-title">
                Fluxo do Caixa (Extrato)
            </h3>
            
            <div class="cashier-flow-list">
                <?php if (empty($movimentosView)): ?>
                    <p class="cashier-flow-empty">Nenhuma movimentação ainda.</p>
                <?php else: ?>
                    <?php foreach ($movimentosView as $mov): ?>
                    <div class="cashier-flow-item">
                        <div class="cashier-flow-left">
                            <div class="cashier-flow-icon" style="background: <?= $mov['color_bg'] ?>; color: <?= $mov['color_text'] ?>;">
                                <i data-lucide="<?= $mov['icon'] ?>"></i>
                            </div>
                            <div>
                                <strong class="cashier-flow-type"><?= htmlspecialchars($mov['type']) ?></strong>
                                <div class="cashier-flow-desc">
                                    <?= htmlspecialchars($mov['description'] ?? 'Sem descrição') ?>
                                </div>

                                <div class="cashier-flow-actions">
                                    <?php if ($mov['type'] == 'venda' && $mov['order_id']): ?>
                                        <a href="<?= BASE_URL ?>/admin/loja/caixa/estornar-pdv?id=<?= $mov['id'] ?>" 
                                           onclick="return confirm('Editar venda? O valor sairá do caixa e os itens irão para o balcão.')"
                                           class="cashier-flow-link link-edit">
                                            <i data-lucide="edit-3"></i> Editar
                                        </a>

                                        <?php if ($mov['is_table_reopen']): ?>
                                            <a href="<?= BASE_URL ?>/admin/loja/caixa/estornar-mesa?id=<?= $mov['id'] ?>" 
                                               onclick="return confirm('Reabrir mesa? A mesa ficará ocupada novamente.')"
                                               class="cashier-flow-link link-reopen">
                                                <i data-lucide="rotate-ccw"></i> Reabrir
                                            </a>
                                        <?php endif; ?>

                                        <a href="javascript:void(0)" onclick="CashierSPA.openOrderDetails(<?= $mov['order_id'] ?>, '<?= number_format($mov['amount'], 2, ',', '.') ?>', '<?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?>')"
                                           class="cashier-flow-link link-view">
                                            <i data-lucide="scroll-text"></i> Ver Comanda
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= BASE_URL ?>/admin/loja/caixa/remover?id=<?= $mov['id'] ?>" 
                                       onclick="return confirm('Tem certeza? Isso apagará o registro do caixa. Se for venda, também cancelará o pedido.')"
                                       class="cashier-flow-link link-delete">
                                        <i data-lucide="trash-2"></i> Apagar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="cashier-flow-right">
                            <div class="cashier-flow-amount" style="color: <?= $mov['color_text'] ?>;">
                                <?= $mov['sign'] ?> R$ <?= number_format($mov['amount'], 2, ',', '.') ?>
                            </div>
                            <div class="cashier-flow-time">
                                <?= date('H:i', strtotime($mov['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Ações -->
        <div class="cashier-sidebar">
            
            <!-- Ações Rápidas -->
            <div class="cashier-panel">
                <h3 class="cashier-actions-title">Ações Rápidas</h3>
                
                <button onclick="CashierSPA.openModal('suprimento')" class="cashier-btn-supply">
                    <i data-lucide="plus-circle"></i> Adicionar Dinheiro
                </button>

                <button onclick="CashierSPA.openModal('sangria')" class="cashier-btn-withdraw">
                    <i data-lucide="minus-circle"></i> Retirar Valor
                </button>
            </div>

            <!-- Encerrar Turno -->
            <div class="cashier-panel cashier-close-section">
                <i data-lucide="lock"></i>
                <h3 class="cashier-close-title">Encerrar Turno</h3>
                <p class="cashier-close-desc">Finalize o dia para conferência.</p>
                
                <a href="<?= BASE_URL ?>/admin/loja/caixa/fechar" onclick="return confirm('Tem certeza que deseja fechar o caixa?')" 
                   class="cashier-btn-close">
                    Fechar Caixa
                </a>
            </div>

        </div>
    </div>

</div>

<!-- Modal Movimentação (Suprimento/Sangria) -->
<div id="modalMovimento" class="cashier-modal">
    <div class="cashier-modal-content">
        <h3 id="modalTitle" class="cashier-modal-title">Nova Movimentação</h3>
        
        <form action="<?= BASE_URL ?>/admin/loja/caixa/movimentar" method="POST" id="form-movimento">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <input type="hidden" name="type" id="movType">
            
            <label class="cashier-form-label">Valor (R$)</label>
            <input type="text" name="amount" required placeholder="0,00" class="cashier-form-input">

            <label class="cashier-form-label">Motivo / Descrição</label>
            <input type="text" name="description" required placeholder="Ex: Pagamento Fornecedor" class="cashier-form-input">

            <div class="cashier-form-actions">
                <button type="button" onclick="document.getElementById('modalMovimento').classList.remove('active')" class="cashier-btn-cancel">Cancelar</button>
                <button type="submit" class="cashier-btn-save">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Comanda (Estilo Cupom Fiscal) -->
<div id="orderDetailsModal" class="receipt-modal">
    <div class="receipt-card">
        
        <!-- Cabeçalho do Cupom -->
        <div class="receipt-header">
            <h3 class="receipt-title">Comprovante</h3>
            <div id="receiptDate" class="receipt-date"></div>
        </div>
        
        <!-- Lista de Itens -->
        <div id="modalItemsList" class="receipt-items">
            Carregando...
        </div>

        <!-- Total e Rodapé -->
        <div class="receipt-footer">
            <div class="receipt-total-row">
                <span>TOTAL</span>
                <span id="receiptTotal">R$ 0,00</span>
            </div>
            
            <button onclick="document.getElementById('orderDetailsModal').classList.remove('active')" class="receipt-btn-close">
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
<script data-spa-script src="<?= BASE_URL ?>/js/admin/cashier.js?v=<?= APP_VERSION ?>"></script>
