<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Adicionais</h1>
                <p style="color: #6b7280; margin-top: 5px;">Gerencie grupos e itens de adicionais</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="openGroupModal()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="folder-plus" size="18"></i> Novo Grupo
                </button>
            </div>
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
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Movimentações
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/adicionais" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #2563eb; color: white;">
                Adicionais
            </a>
        </div>

        <!-- Listagem de Grupos -->
        <?php if (empty($groups)): ?>
            <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <i data-lucide="layers" size="48" style="color: #d1d5db; margin-bottom: 15px;"></i>
                <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhum grupo de adicional cadastrado</h3>
                <p style="color: #9ca3af; margin-bottom: 20px;">Crie seu primeiro grupo para começar a adicionar itens</p>
                <button onclick="openGroupModal()" style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Primeiro Grupo
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($groups as $group): ?>
            <div class="group-card" data-group-id="<?= $group['id'] ?>" style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden;">
                <!-- Header do Grupo -->
                <div style="padding: 15px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i data-lucide="layers" size="20" style="color: #2563eb;"></i>
                        <span style="font-weight: 700; font-size: 1.1rem; color: #1f2937;"><?= htmlspecialchars($group['name']) ?></span>
                        <span style="padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; background: <?= $group['required'] ? '#fef3c7' : '#e0f2fe' ?>; color: <?= $group['required'] ? '#d97706' : '#0369a1' ?>;">
                            <?= $group['required'] ? 'Obrigatório' : 'Opcional' ?>
                        </span>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="openItemModal(<?= $group['id'] ?>, '<?= htmlspecialchars(addslashes($group['name'])) ?>')" 
                                style="padding: 6px 12px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                            <i data-lucide="plus" size="14"></i> Item
                        </button>
                        <button onclick="openGroupModal(<?= $group['id'] ?>, '<?= htmlspecialchars(addslashes($group['name'])) ?>', <?= $group['required'] ?>)" 
                                style="padding: 6px 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                            <i data-lucide="pencil" size="14"></i>
                        </button>
                        <button onclick="deleteGroup(<?= $group['id'] ?>, '<?= htmlspecialchars(addslashes($group['name'])) ?>')" 
                                style="padding: 6px 12px; background: #fef2f2; color: #dc2626; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                            <i data-lucide="trash-2" size="14"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Itens do Grupo -->
                <div style="padding: 15px 20px;">
                    <?php if (empty($group['items'])): ?>
                        <p style="color: #9ca3af; font-size: 0.9rem; text-align: center; padding: 15px;">Nenhum item neste grupo</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach ($group['items'] as $item): ?>
                            <div class="item-row" data-item-id="<?= $item['id'] ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; background: #f9fafb; border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i data-lucide="circle" size="8" style="color: #2563eb;"></i>
                                    <span style="font-weight: 500; color: #1f2937;"><?= htmlspecialchars($item['name']) ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <span style="font-weight: 600; color: <?= $item['price'] > 0 ? '#059669' : '#6b7280' ?>;">
                                        <?= $item['price'] > 0 ? '+R$ ' . number_format($item['price'], 2, ',', '.') : 'Grátis' ?>
                                    </span>
                                    <div style="display: flex; gap: 6px;">
                                        <button onclick="openItemModal(<?= $group['id'] ?>, '<?= htmlspecialchars(addslashes($group['name'])) ?>', <?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>', <?= $item['price'] ?>)" 
                                                style="padding: 4px 8px; background: #e0f2fe; color: #0369a1; border: none; border-radius: 4px; cursor: pointer;">
                                            <i data-lucide="pencil" size="12"></i>
                                        </button>
                                        <button onclick="deleteItem(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>')" 
                                                style="padding: 4px 8px; background: #fef2f2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer;">
                                            <i data-lucide="trash-2" size="12"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Modal de Grupo -->
<div id="groupModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;" id="groupModalTitle">Novo Grupo</h3>
        
        <input type="hidden" id="groupId">
        
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Nome do Grupo</label>
            <input type="text" id="groupName" placeholder="Ex: Molhos, Extras..." 
                   style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" id="groupRequired" style="width: 18px; height: 18px;">
                <span style="font-weight: 500; color: #374151;">Obrigatório</span>
                <span style="font-size: 0.8rem; color: #6b7280;">(cliente deve escolher)</span>
            </label>
        </div>

        <div style="display: flex; gap: 10px;">
            <button onclick="closeGroupModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Cancelar
            </button>
            <button onclick="saveGroup()" style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Salvar
            </button>
        </div>
    </div>
</div>

<!-- Modal de Item -->
<div id="itemModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;" id="itemModalTitle">Novo Item</h3>
        <p style="color: #6b7280; margin-bottom: 1.5rem; font-size: 0.9rem;">Grupo: <span id="itemGroupName" style="font-weight: 600;"></span></p>
        
        <input type="hidden" id="itemId">
        <input type="hidden" id="itemGroupId">
        
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Nome do Item</label>
            <input type="text" id="itemName" placeholder="Ex: Bacon, Queijo Extra..." 
                   style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Preço Adicional (R$)</label>
            <input type="text" id="itemPrice" placeholder="0,00 (grátis)" 
                   style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
            <small style="color: #6b7280;">Deixe 0 para itens gratuitos</small>
        </div>

        <div style="display: flex; gap: 10px;">
            <button onclick="closeItemModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Cancelar
            </button>
            <button onclick="saveItem()" style="flex: 1; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Salvar
            </button>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';

// ==========================================
// GRUPO
// ==========================================
function openGroupModal(id = null, name = '', required = 0) {
    isSaving = false; // Reset flag ao abrir modal
    document.getElementById('groupId').value = id || '';
    document.getElementById('groupName').value = name;
    document.getElementById('groupRequired').checked = required == 1;
    document.getElementById('groupModalTitle').textContent = id ? 'Editar Grupo' : 'Novo Grupo';
    document.getElementById('groupModal').style.display = 'flex';
    document.getElementById('groupName').focus();
}

function closeGroupModal() {
    isSaving = false; // Reset flag ao fechar
    document.getElementById('groupModal').style.display = 'none';
}

let isSaving = false; // Previne clique duplo

function saveGroup() {
    if (isSaving) return; // Bloqueia se já está salvando
    
    const id = document.getElementById('groupId').value;
    const name = document.getElementById('groupName').value.trim();
    const required = document.getElementById('groupRequired').checked ? 1 : 0;

    if (!name) {
        alert('Nome do grupo é obrigatório');
        return;
    }

    isSaving = true; // Bloqueia novos cliques
    
    const url = id 
        ? BASE_URL + '/admin/loja/adicionais/grupo/atualizar'
        : BASE_URL + '/admin/loja/adicionais/grupo/criar';

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, name, required })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            isSaving = false; // Libera em caso de erro
            alert('Erro: ' + data.message);
        }
    })
    .catch(() => {
        isSaving = false;
    });
}

function deleteGroup(id, name) {
    if (!confirm(`Excluir o grupo "${name}" e todos os seus itens?`)) return;

    fetch(BASE_URL + '/admin/loja/adicionais/grupo/excluir', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    });
}

// ==========================================
// ITEM
// ==========================================
function openItemModal(groupId, groupName, itemId = null, itemName = '', itemPrice = 0) {
    document.getElementById('itemGroupId').value = groupId;
    document.getElementById('itemGroupName').textContent = groupName;
    document.getElementById('itemId').value = itemId || '';
    document.getElementById('itemName').value = itemName;
    document.getElementById('itemPrice').value = itemPrice > 0 ? itemPrice.toString().replace('.', ',') : '';
    document.getElementById('itemModalTitle').textContent = itemId ? 'Editar Item' : 'Novo Item';
    document.getElementById('itemModal').style.display = 'flex';
    document.getElementById('itemName').focus();
}

function closeItemModal() {
    document.getElementById('itemModal').style.display = 'none';
}

function saveItem() {
    if (isSaving) return; // Bloqueia se já está salvando
    
    const id = document.getElementById('itemId').value;
    const groupId = document.getElementById('itemGroupId').value;
    const name = document.getElementById('itemName').value.trim();
    const price = document.getElementById('itemPrice').value.replace(',', '.') || '0';

    if (!name) {
        alert('Nome do item é obrigatório');
        return;
    }

    isSaving = true; // Bloqueia novos cliques

    const url = id 
        ? BASE_URL + '/admin/loja/adicionais/item/atualizar'
        : BASE_URL + '/admin/loja/adicionais/item/criar';

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, group_id: groupId, name, price })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            isSaving = false;
            alert('Erro: ' + data.message);
        }
    })
    .catch(() => {
        isSaving = false;
    });
}

function deleteItem(id, name) {
    if (!confirm(`Excluir o item "${name}"?`)) return;

    fetch(BASE_URL + '/admin/loja/adicionais/item/excluir', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    });
}

// Fechar modais ao clicar fora
document.getElementById('groupModal').addEventListener('click', function(e) {
    if (e.target === this) closeGroupModal();
});
document.getElementById('itemModal').addEventListener('click', function(e) {
    if (e.target === this) closeItemModal();
});
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
