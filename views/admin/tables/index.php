<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; height: 100vh; overflow-y: auto; padding-bottom: 100px;">
        
        <?php require __DIR__ . '/partials/header_mesas.php'; ?>

        <?php require __DIR__ . '/partials/grid_mesas.php'; ?>

        <?php require __DIR__ . '/partials/header_comandas.php'; ?>

        <?php require __DIR__ . '/partials/grid_comandas.php'; ?>

    </div>
</main>

<?php require __DIR__ . '/partials/modals/nova_mesa.php'; ?>

<?php require __DIR__ . '/partials/modals/remover_mesa.php'; ?>

<?php require __DIR__ . '/partials/modals/cliente.php'; ?>

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

let currentPaidClientId = null;

function showPaidOrderOptions(orderId, clientName, total, clientId) {
    currentPaidOrderId = orderId;
    currentPaidClientId = clientId;
    
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

<?php require __DIR__ . '/partials/modals/pedido_pago.php'; ?>

<script src="/cardapio-saas/public/js/clientes.js"></script>

<?php require __DIR__ . '/partials/modals/dossie.php'; ?>

<script>
function openDossier(clientId) {
    const modal = document.getElementById('dossierModal');
    if(!modal) return;

    // Mostra modal carregando
    modal.style.display = 'flex';
    document.getElementById('dos_name').innerText = 'Buscando dados...';
    document.getElementById('dos_info').innerText = '...';
    document.getElementById('dos_history_list').innerHTML = '<p style="color:#94a3b8; text-align:center">Carregando...</p>';

    // Configura o botão de "Novo Pedido" para levar ao PDV
    const btnOrder = document.getElementById('btn-dossier-order');
    btnOrder.onclick = function() {
        window.location.href = BASE_URL + '/admin/loja/pdv?client_id=' + clientId;
    };

    // Busca no Backend
    fetch(BASE_URL + '/admin/loja/clientes/detalhes?id=' + clientId)
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                const cli = data.client;
                
                // Preenche Cabeçalho
                document.getElementById('dos_name').innerText = cli.name;
                const docLabel = cli.type === 'PJ' ? 'CNPJ' : 'CPF';
                document.getElementById('dos_info').innerText = `${docLabel}: ${cli.document || 'Não informado'} • Tel: ${cli.phone || '--'}`;
                
                // Preenche Financeiro
                const debt = parseFloat(cli.current_debt || 0); 
                const limit = parseFloat(cli.credit_limit || 0);
                
                document.getElementById('dos_debt').innerText = 'R$ ' + debt.toFixed(2).replace('.', ',');
                document.getElementById('dos_limit').innerText = 'R$ ' + limit.toFixed(2).replace('.', ',');

                // Preenche Histórico
                const list = document.getElementById('dos_history_list');
                list.innerHTML = '';
                
                if(!data.history || data.history.length === 0) {
                    list.innerHTML = '<div style="text-align:center; padding:20px; color:#cbd5e1;">Nenhuma movimentação registrada.</div>';
                } else {
                    data.history.forEach(item => {
                        const isPay = item.type === 'pagamento';
                        const color = isPay ? '#16a34a' : '#ef4444';
                        const sign = isPay ? '+' : '-';
                        
                        list.innerHTML += `
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                                <div>
                                    <div style="font-weight: 600; color: #334155; font-size: 0.9rem;">${item.description || item.type.toUpperCase()}</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">${new Date(item.created_at).toLocaleDateString('pt-BR')}</div>
                                </div>
                                <div style="font-weight: 700; color: ${color}; font-size: 0.95rem;">
                                    ${sign} R$ ${parseFloat(item.amount).toFixed(2).replace('.', ',')}
                                </div>
                            </div>
                        `;
                    });
                }
                
                // Renderiza ícones Lucide no modal
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                alert('Erro: ' + data.message);
                modal.style.display = 'none';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro ao buscar detalhes.');
            modal.style.display = 'none';
        });
}
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
