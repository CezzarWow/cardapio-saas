<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; height: 100vh; overflow-y: auto; padding-bottom: 100px;">
        
        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.4rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="layout-grid" size="24" color="#2563eb"></i> SALÃO (MESAS)
            </h2>
            
            <div style="display: flex; gap: 10px;">
                <button onclick="openRemoveTableModal()" style="background: white; border: 1px solid #fca5a5; padding: 10px 15px; border-radius: 8px; font-weight: 700; color: #b91c1c; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="minus-circle" size="18"></i> Remover Mesa
                </button>

                <button onclick="openNewTableModal()" style="background: #2563eb; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; color: white; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                    <i data-lucide="plus-circle" size="18"></i> Nova Mesa
                </button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <?php foreach ($tables as $mesa): ?>
                <?php 
                    $isOccupied = ($mesa['status'] == 'ocupada');
                    
                    // Visual Clássico que você gostou
                    if ($isOccupied) {
                        // OCUPADA: Fundo Vermelho claro, Borda Vermelha
                        $bg = '#fef2f2';
                        $border = '#ef4444';
                        $textColor = '#b91c1c';
                        $iconColor = '#ef4444';
                        $statusText = 'OCUPADA';
                        $valor = 'R$ ' . number_format($mesa['current_total'], 2, ',', '.');
                    } else {
                        // LIVRE: Fundo Branco, Borda Verde/Cinza
                        $bg = 'white';
                        $border = '#22c55e'; // Borda verde para destacar que está livre
                        $textColor = '#15803d';
                        $iconColor = '#22c55e';
                        $statusText = 'LIVRE';
                        $valor = 'Disponível';
                    }
                ?>

                <div onclick="abrirMesa(<?= $mesa['id'] ?>, <?= $mesa['number'] ?>)" 
                     style="background: <?= $bg ?>; border: 2px solid <?= $border ?>; border-radius: 12px; cursor: pointer; transition: transform 0.1s; position: relative; overflow: hidden; height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    
                    <span style="font-size: 2.2rem; font-weight: 800; color: <?= $textColor ?>; line-height: 1;">
                        <?= $mesa['number'] ?>
                    </span>
                    
                    <span style="font-size: 0.75rem; font-weight: 700; color: <?= $iconColor ?>; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;">
                        <?= $statusText ?>
                    </span>

                    <?php if ($isOccupied): ?>
                        <div style="margin-top: 8px; background: rgba(255,255,255,0.6); padding: 2px 8px; border-radius: 4px; font-weight: 800; color: <?= $textColor ?>; font-size: 0.9rem;">
                            <?= $valor ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <hr style="border: 0; border-top: 2px dashed #e2e8f0; margin: 2rem 0;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.4rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="users" size="24" color="#059669"></i> CLIENTES / COMANDAS
            </h2>
            <div style="display: flex; gap: 10px;">
                <button onclick="openNewClientModal('PF')" style="background: #059669; border: none; padding: 10px 15px; border-radius: 8px; font-weight: 700; color: white; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);">
                    <i data-lucide="user" size="18"></i> Novo Cliente
                </button>

                <button onclick="openNewClientModal('PJ')" style="background: #4f46e5; border: none; padding: 10px 15px; border-radius: 8px; font-weight: 700; color: white; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);">
                    <i data-lucide="building-2" size="18"></i> Nova Empresa
                </button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
            <?php if (empty($clientOrders)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
                    <i data-lucide="clipboard-list" style="width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p style="margin: 0; font-weight: 500;">Nenhuma comanda aberta no momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($clientOrders as $order): ?>
                    <?php 
                        $isPaid = !empty($order['is_paid']) && $order['is_paid'] == 1; 
                        $borderColor = $isPaid ? '#22c55e' : '#f59e0b'; // Verde ou Laranja
                        $bgColor = $isPaid ? '#f0fdf4' : 'white'; 
                    ?>
                    <?php if ($isPaid): ?>
                        <div onclick="showPaidOrderOptions(<?= $order['order_id'] ?>, '<?= addslashes($order['client_name']) ?>', <?= $order['total'] ?>)" 
                             style="background: <?= $bgColor ?>; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <?php else: ?>
                        <div onclick="window.location.href='<?= BASE_URL ?>/admin/loja/pdv?order_id=<?= $order['order_id'] ?>'" 
                             style="background: <?= $bgColor ?>; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <?php endif; ?>
                        
                        <!-- Barra Lateral Colorida -->
                        <div style="position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: <?= $borderColor ?>;"></div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 40px; height: 40px; background: <?= $isPaid ? '#dcfce7' : '#fff7ed' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: <?= $borderColor ?>;">
                                    <i data-lucide="<?= $isPaid ? 'check-circle' : 'user' ?>" size="20"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 1rem; line-height: 1.2;">
                                        <?= substr($order['client_name'], 0, 25) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                        Comanda #<?= $order['order_id'] ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($isPaid): ?>
                                <span style="background: #16a34a; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800;">PAGO</span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                            <div style="display: flex; align-items: center; gap: 6px; color: #64748b; font-size: 0.8rem;">
                                <i data-lucide="clock" size="14"></i>
                                <?= date('H:i', strtotime($order['created_at'])) ?>
                            </div>
                            <div style="font-weight: 800; color: <?= $borderColor ?>; font-size: 1.25rem;">
                                R$ <?= number_format($order['total'], 2, ',', '.') ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

<div id="newTableModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 300px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="font-weight: 700; color: #1f2937; margin-bottom: 15px;">Nova Mesa</h3>
        <input type="number" id="new_table_number" min="0" placeholder="Número (Ex: 10)" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 20px; font-size: 1.2rem; text-align: center; font-weight: bold;">
        <div style="display: flex; gap: 10px;">
            <button onclick="document.getElementById('newTableModal').style.display='none'" style="flex: 1; padding: 10px; background: #f3f4f6; border: none; border-radius: 8px; font-weight: 600;">Cancelar</button>
            <button onclick="saveTable()" style="flex: 1; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600;">Salvar</button>
        </div>
    </div>
</div>

<div id="removeTableModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 300px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="font-weight: 700; color: #b91c1c; margin-bottom: 15px;">Remover Mesa</h3>
        <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 10px;">Digite o número da mesa para excluir.</p>
        
        <input type="number" id="remove_table_number" placeholder="Número (Ex: 5)" style="width: 100%; padding: 12px; border: 2px solid #fca5a5; border-radius: 8px; margin-bottom: 20px; font-size: 1.2rem; text-align: center; font-weight: bold; color: #b91c1c;">
        
        <div style="display: flex; gap: 10px;">
            <button onclick="document.getElementById('removeTableModal').style.display='none'" style="flex: 1; padding: 10px; background: #f3f4f6; border: none; border-radius: 8px; font-weight: 600;">Cancelar</button>
            <button onclick="removeTable()" style="flex: 1; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600;">Excluir</button>
        </div>
    </div>
</div>

<div id="superClientModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center;">
    <div style="background: white; padding: 0; border-radius: 12px; width: 800px; max-width: 95%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        
        <div style="padding: 20px 25px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font-weight: 800; color: #1e293b; font-size: 1.25rem;">Novo Cadastro</h3>
                <p id="modal-subtitle" style="margin: 4px 0 0 0; font-size: 0.9rem; color: #64748b;">Preencha os dados do cliente</p>
            </div>
            <button onclick="document.getElementById('superClientModal').style.display='none'" style="border: none; background: none; color: #94a3b8; cursor: pointer; padding: 5px;">
                <i data-lucide="x" size="24"></i>
            </button>
        </div>

        <div style="padding: 25px 30px; overflow-y: auto; background: #fff;">
            
            <!-- SEÇÃO 1: DADOS -->
            <div style="margin-bottom: 25px;">
                <h4 id="header-dados" style="margin: 0 0 15px 0; font-size: 0.95rem; font-weight: 700; color: #2563eb; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">DADOS PESSOAIS</h4>
                
                <div style="margin-bottom: 15px;">
                    <label id="lbl-name" style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Nome Completo <span style="color: #ef4444">*</span></label>
                    <input type="text" id="cli_name" placeholder="Digite o nome completo" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label id="lbl-doc" style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">CPF</label>
                        <input type="text" id="cli_doc" placeholder="000.000.000-00" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Telefone</label>
                        <input type="text" id="cli_phone" placeholder="(00) 00000-0000" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>
            </div>

            <!-- SEÇÃO 2: ENDEREÇO -->
            <div style="margin-bottom: 25px;">
                <h4 style="margin: 0 0 15px 0; font-size: 0.95rem; font-weight: 700; color: #2563eb; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">ENDEREÇO</h4>
                
                <div style="display: grid; grid-template-columns: 3fr 1fr 1.5fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Logradouro</label>
                        <input type="text" id="cli_addr" placeholder="Rua, Av, Travessa..." style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Número</label>
                        <input type="text" id="cli_num" placeholder="123" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">CEP</label>
                        <input type="text" id="cli_zip" placeholder="00000-000" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Bairro</label>
                        <input type="text" id="cli_bairro" placeholder="Bairro" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Cidade</label>
                        <input type="text" id="cli_city" placeholder="Cidade" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>
            </div>

            <!-- SEÇÃO 3: CRÉDITO -->
            <div>
                <h4 style="margin: 0 0 15px 0; font-size: 0.95rem; font-weight: 700; color: #ea580c; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">FINANCEIRO (CREDIÁRIO)</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #fff7ed; padding: 15px; border-radius: 8px; border: 1px dashed #fdba74;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #c2410c; margin-bottom: 6px;">Limite de Crédito (R$)</label>
                        <input type="text" id="cli_limit" placeholder="R$ 0,00" style="width: 100%; padding: 10px; border: 1px solid #fdba74; border-radius: 6px; font-weight: 700; color: #c2410c; background: white;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #c2410c; margin-bottom: 6px;">Dia do Vencimento</label>
                        <input type="number" id="cli_due" placeholder="Dia (1-31)" min="1" max="31" style="width: 100%; padding: 10px; border: 1px solid #fdba74; border-radius: 6px; font-weight: 700; color: #c2410c; background: white;">
                    </div>
                </div>
            </div>

        </div>

        <div style="padding: 20px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px;">
            <button onclick="document.getElementById('superClientModal').style.display='none'" style="padding: 10px 24px; background: white; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 700; color: #475569; cursor: pointer;">Cancelar</button>
            <button onclick="saveSuperClient()" style="padding: 10px 30px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">Salvar Cadastro</button>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';

// --- ADICIONAR ---
function openNewTableModal() {
    document.getElementById('newTableModal').style.display = 'flex';
    document.getElementById('new_table_number').focus();
}

function saveTable() {
    const number = document.getElementById('new_table_number').value;
    if(!number) return;
    
    // Caminho absoluto para evitar erro de rota relativa
    fetch('mesas/salvar', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({number: number})
    }).then(r => r.json()).then(data => {
        if(data.success) window.location.reload();
        else alert(data.message);
    });
}

// --- REMOVER (COM LÓGICA DE CONFIRMAÇÃO DUPLA) ---
function openRemoveTableModal() {
    document.getElementById('removeTableModal').style.display = 'flex';
    document.getElementById('remove_table_number').focus();
}

function removeTable() {
    const number = document.getElementById('remove_table_number').value;
    if(!number) return;

    if(!confirm(`Tem certeza que deseja excluir a MESA ${number}?`)) return;

    // Tenta excluir
    fetch('mesas/deletar', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({number: number, force: false})
    }).then(r => r.json()).then(data => {
        
        if(data.success) {
            window.location.reload();
        } 
        else if (data.occupied) {
            // SE ESTIVER OCUPADA: Pede confirmação extra
            if(confirm(`ATENÇÃO: A Mesa ${number} está OCUPADA!\n\nExcluir agora pode causar erros nos pedidos.\nDeseja forçar a exclusão mesmo assim?`)) {
                
                // Tenta de novo com force: true
                fetch('mesas/deletar', {
                    method: 'POST', headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({number: number, force: true})
                }).then(r2 => r2.json()).then(d2 => {
                    if(d2.success) window.location.reload();
                    else alert('Erro ao excluir: ' + d2.message);
                });
            }
        } else {
            alert(data.message);
        }
    });
}

function abrirMesa(id, numero) {
    window.location.href = 'pdv?mesa_id=' + id + '&mesa_numero=' + numero;
}

// --- FUNÇÕES DE CLIENTE ---
function openNewClientModal(startType) {
    // Se o modal ainda não existir (fase 3 incompleta), avisa e não quebra
    const modal = document.getElementById('superClientModal');
    if (!modal) {
        alert('🚧 Super Modal em construção! (Parte 3 em andamento)');
        return;
    }

    modal.style.display = 'flex';
    
    // Já configura o tipo correto na hora de abrir
    if (typeof setType === 'function') {
        setType(startType); 
    }
    
    // Foca no nome para digitar rápido
    const nameInput = document.getElementById('cli_name');
    if(nameInput) nameInput.focus();
}

// --- FUNÇÕES PARA PEDIDOS PAGOS (RETIRADA) ---
let currentPaidOrderId = null;

function showPaidOrderOptions(orderId, clientName, total) {
    currentPaidOrderId = orderId;
    
    document.getElementById('paid-order-client-name').innerText = clientName;
    document.getElementById('paid-order-total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
    document.getElementById('paidOrderModal').style.display = 'flex';
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closePaidOrderModal() {
    document.getElementById('paidOrderModal').style.display = 'none';
    currentPaidOrderId = null;
}

function deliverOrder() {
    if (!currentPaidOrderId) return;
    
    fetch(BASE_URL + '/admin/loja/pedidos/entregar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: currentPaidOrderId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closePaidOrderModal();
            // Recarrega a página para atualizar a lista
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Falha ao entregar'));
        }
    })
    .catch(err => alert('Erro: ' + err.message));
}

function editPaidOrder() {
    if (!currentPaidOrderId) return;
    
    // Redireciona para o PDV com o pedido carregado
    window.location.href = BASE_URL + '/admin/loja/pdv?order_id=' + currentPaidOrderId + '&edit_paid=1';
}
</script>

<!-- MODAL: Opções para Pedido Pago (Retirada) -->
<div id="paidOrderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 16px; width: 350px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 60px; height: 60px; background: #dcfce7; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="shopping-bag" size="28" style="color: #16a34a;"></i>
            </div>
            <h3 style="margin: 0; color: #1e293b; font-size: 1.2rem; font-weight: 800;">Pedido para Retirada</h3>
            <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">
                <strong id="paid-order-client-name">Cliente</strong>
            </p>
            <div style="margin-top: 10px; background: #f0fdf4; padding: 8px 15px; border-radius: 8px; display: inline-block;">
                <span style="font-weight: 800; color: #16a34a; font-size: 1.3rem;" id="paid-order-total">R$ 0,00</span>
                <span style="background: #22c55e; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; margin-left: 8px;">PAGO</span>
            </div>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <button onclick="deliverOrder()" style="width: 100%; padding: 14px; background: #22c55e; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(34, 197, 94, 0.3);">
                <i data-lucide="package-check" size="20"></i> ENTREGAR (Concluir)
            </button>
            
            <button onclick="editPaidOrder()" style="width: 100%; padding: 14px; background: #3b82f6; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);">
                <i data-lucide="edit-3" size="20"></i> EDITAR Pedido
            </button>
            
            <button onclick="closePaidOrderModal()" style="width: 100%; padding: 12px; background: #f1f5f9; color: #64748b; border: none; border-radius: 10px; font-weight: 700; cursor: pointer;">
                Voltar
            </button>
        </div>
    </div>
</div>

<script src="/cardapio-saas/public/js/clientes.js"></script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
