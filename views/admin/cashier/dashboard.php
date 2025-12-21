<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
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
            
            <div style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #2563eb; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <span style="display: block; color: #6b7280; font-size: 0.85rem; font-weight: 600;">TOTAL BRUTO</span>
                <span style="display: block; font-size: 1.5rem; font-weight: 800; color: #1f2937; margin-top: 5px;">
                    R$ <?= number_format($resumo['total_bruto'], 2, ',', '.') ?>
                </span>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #16a34a; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <span style="display: block; color: #6b7280; font-size: 0.85rem; font-weight: 600;">DINHEIRO (GAVETA)</span>
                <span style="display: block; font-size: 1.5rem; font-weight: 800; color: #16a34a; margin-top: 5px;">
                    R$ <?= number_format($dinheiroEmCaixa, 2, ',', '.') ?>
                </span>
                <small style="color: #9ca3af; font-size: 0.75rem;">Início: R$ <?= number_format($caixa['opening_balance'], 2, ',', '.') ?></small>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #4f46e5; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <span style="display: block; color: #6b7280; font-size: 0.85rem; font-weight: 600;">CRÉDITO</span>
                <span style="display: block; font-size: 1.5rem; font-weight: 800; color: #4f46e5; margin-top: 5px;">
                    R$ <?= number_format($resumo['credito'], 2, ',', '.') ?>
                </span>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #f97316; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <span style="display: block; color: #6b7280; font-size: 0.85rem; font-weight: 600;">DÉBITO</span>
                <span style="display: block; font-size: 1.5rem; font-weight: 800; color: #f97316; margin-top: 5px;">
                    R$ <?= number_format($resumo['debito'], 2, ',', '.') ?>
                </span>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #9333ea; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <span style="display: block; color: #6b7280; font-size: 0.85rem; font-weight: 600;">PIX</span>
                <span style="display: block; font-size: 1.5rem; font-weight: 800; color: #9333ea; margin-top: 5px;">
                    R$ <?= number_format($resumo['pix'], 2, ',', '.') ?>
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 15px; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px;">
                    Fluxo do Caixa (Extrato)
                </h3>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($movimentos)): ?>
                        <p style="color: #9ca3af; text-align: center; padding: 20px;">Nenhuma movimentação ainda.</p>
                    <?php else: ?>
                        <?php foreach($movimentos as $mov): 
                            $cor = ($mov['type'] == 'sangria') ? '#fee2e2' : '#dcfce7';
                            $texto = ($mov['type'] == 'sangria') ? '#991b1b' : '#166534';
                            $sinal = ($mov['type'] == 'sangria') ? '-' : '+';
                            $icone = ($mov['type'] == 'sangria') ? 'arrow-up-right' : 'arrow-down-left';
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
                            
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <div style="background: <?= $cor ?>; padding: 8px; border-radius: 8px; color: <?= $texto ?>;">
                                    <i data-lucide="<?= $icone ?>" size="18"></i>
                                </div>
                                <div>
                                    <strong style="color: #374151; text-transform: capitalize;"><?= $mov['type'] ?></strong>
                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                        <?= $mov['description'] ?? 'Sem descrição' ?>
                                    </div>

                                    <div style="margin-top: 5px; display: flex; gap: 10px;">

                                        <?php if ($mov['type'] == 'venda' && $mov['order_id']): ?>
                                            <div style="margin-top: 5px; display: flex; gap: 10px;">
                                                
                                                <a href="caixa/estornar-pdv?id=<?= $mov['id'] ?>" 
                                                   onclick="return confirm('Editar venda? O valor sairá do caixa e os itens irão para o balcão.')"
                                                   style="font-size: 0.75rem; color: #2563eb; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                                    <i data-lucide="edit-3" size="12"></i> Editar
                                                </a>

                                                <?php if (strpos($mov['description'], 'Mesa') !== false): ?>
                                                    <a href="caixa/estornar-mesa?id=<?= $mov['id'] ?>" 
                                                       onclick="return confirm('Reabrir mesa? A mesa ficará ocupada novamente.')"
                                                       style="font-size: 0.75rem; color: #d97706; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                                        <i data-lucide="rotate-ccw" size="12"></i> Reabrir
                                                    </a>
                                                <?php endif; ?>

                                                <a href="javascript:void(0)" onclick="openOrderDetails(<?= $mov['order_id'] ?>)"
                                                   style="font-size: 0.75rem; color: #6b7280; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                                    <i data-lucide="eye" size="12"></i> Ver
                                                </a>

                                            </div>
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
                                <div style="font-weight: 700; color: <?= $texto ?>;">
                                    <?= $sinal ?> R$ <?= number_format($mov['amount'], 2, ',', '.') ?>
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

<div id="modalMovimento" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 400px; max-width: 90%;">
        <h3 id="modalTitle" style="font-weight: 800; font-size: 1.2rem; margin-bottom: 15px;">Nova Movimentação</h3>
        
        <form action="caixa/movimentar" method="POST">
            <input type="hidden" name="type" id="movType">
            
            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Valor (R$)</label>
            <input type="text" name="amount" required placeholder="0,00" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px;">

            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Motivo / Descrição</label>
            <input type="text" name="description" required placeholder="Ex: Pagamento Fornecedor" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalMovimento').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid #ddd; background: white; border-radius: 8px; cursor: pointer;">Cancelar</button>
                <button type="submit" class="btn-primary" style="flex: 1; padding: 10px; border: none; border-radius: 8px; cursor: pointer;">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(type) {
    const modal = document.getElementById('modalMovimento');
    const title = document.getElementById('modalTitle');
    const inputType = document.getElementById('movType');

    inputType.value = type;
    
    if(type === 'sangria') {
        title.innerText = "Retirar Valor (Saída)";
        title.style.color = "#b91c1c";
    } else {
        title.innerText = "Adicionar Dinheiro (Entrada)";
        title.style.color = "#1d4ed8";
    }
    
    modal.style.display = 'flex';
}
</script>

<div id="orderDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center;">
    <div style="background: white; padding: 20px; border-radius: 12px; width: 400px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
            <h3 style="font-weight: 700; color: #1f2937;">Detalhes do Pedido</h3>
            <button onclick="document.getElementById('orderDetailsModal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <div id="modalItemsList" style="max-height: 300px; overflow-y: auto;">
            Carregando...
        </div>

        <div style="margin-top: 15px; text-align: right;">
            <button onclick="document.getElementById('orderDetailsModal').style.display='none'" 
                    style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
function openOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const list = document.getElementById('modalItemsList');
    
    // Mostra o modal carregando
    modal.style.display = 'flex';
    list.innerHTML = '<p style="text-align:center; color:#666;">Buscando itens...</p>';

    // Chama a rota de itens de venda (já existe em SalesController)
    fetch('../vendas/itens?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if(data.length === 0) {
                list.innerHTML = '<p>Nenhum item encontrado.</p>';
                return;
            }

            let html = '<ul style="list-style: none; padding: 0;">';
            data.forEach(item => {
                html += `
                    <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                        <div>
                            <span style="font-weight: 600; color: #374151;">${item.quantity}x</span> 
                            ${item.name}
                        </div>
                        <div style="font-weight: 600; color: #1f2937;">
                            R$ ${parseFloat(item.price).toFixed(2).replace('.', ',')}
                        </div>
                    </li>
                `;
            });
            html += '</ul>';
            list.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            list.innerHTML = '<p style="color:red;">Erro ao carregar itens.</p>';
        });
}


<script>
    // Força a renderização dos ícones caso o footer demore
    if(typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
