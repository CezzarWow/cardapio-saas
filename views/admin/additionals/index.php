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
                <p style="color: #6b7280; margin-top: 5px;">Gerencie grupos e vincule itens</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= BASE_URL ?>/admin/loja/adicionais/itens" 
                   style="padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="package" size="18"></i> Catálogo de Itens
                </a>
                <button onclick="openGroupModal()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="folder-plus" size="18"></i> Novo Grupo
                </button>
            </div>
        </div>

        <!-- Sub-abas -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/admin/loja/produtos" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Produtos
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/categorias" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Categorias
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/adicionais" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #2563eb; color: white;">
                Adicionais
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/reposicao" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Reposição
            </a>
            <a href="<?= BASE_URL ?>/admin/loja/movimentacoes" 
               style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; background: #f3f4f6; color: #6b7280;">
                Movimentações
            </a>
        </div>

        <!-- Barra de Pesquisa -->
        <div style="background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="position: relative;">
                <i data-lucide="search" size="18" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                <input type="text" id="searchGroups" placeholder="Pesquisar grupos..." 
                       oninput="filterGroups(this.value)"
                       style="width: 100%; padding: 10px 12px 10px 40px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Lista de Grupos -->
        <?php if (empty($groups)): ?>
            <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <i data-lucide="layers" size="48" style="color: #d1d5db; margin-bottom: 15px;"></i>
                <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhum grupo cadastrado</h3>
                <p style="color: #9ca3af; margin-bottom: 20px;">Crie seu primeiro grupo de adicionais</p>
                <button onclick="openGroupModal()" style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Primeiro Grupo
                </button>
            </div>
        <?php else: ?>
            <div id="groupsList">
            <?php foreach ($groups as $group): ?>
            <div class="group-card" data-name="<?= strtolower(htmlspecialchars($group['name'])) ?>" style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden;">
                <!-- Header do Grupo -->
                <div style="padding: 15px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i data-lucide="layers" size="20" style="color: #2563eb;"></i>
                        <span style="font-weight: 700; font-size: 1.1rem; color: #1f2937;"><?= htmlspecialchars($group['name']) ?></span>
                        <span style="color: #6b7280; font-size: 0.85rem;">(<?= count($group['items']) ?> itens)</span>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="openLinkModal(<?= $group['id'] ?>, '<?= htmlspecialchars($group['name']) ?>')" 
                           style="padding: 6px 12px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                            <i data-lucide="link" size="14"></i> Vincular Item
                        </button>
                        <a href="<?= BASE_URL ?>/admin/loja/adicionais/grupo/deletar?id=<?= $group['id'] ?>" 
                           onclick="return confirm('Excluir o grupo &quot;<?= htmlspecialchars($group['name']) ?>&quot;?')"
                           style="padding: 6px 12px; background: #fef2f2; color: #dc2626; text-decoration: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                            <i data-lucide="trash-2" size="14"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Itens do Grupo -->
                <div style="padding: 15px 20px;">
                    <?php if (empty($group['items'])): ?>
                        <p style="color: #9ca3af; font-size: 0.9rem; text-align: center; padding: 15px;">
                            Nenhum item vinculado. 
                            <a href="javascript:void(0)" onclick="openLinkModal(<?= $group['id'] ?>, '<?= htmlspecialchars($group['name']) ?>')" style="color: #2563eb; text-decoration: none;">Vincular item</a>
                        </p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach ($group['items'] as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; background: #f9fafb; border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i data-lucide="circle" size="8" style="color: #2563eb;"></i>
                                    <span style="font-weight: 500; color: #1f2937;"><?= htmlspecialchars($item['name']) ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <span style="font-weight: 600; color: <?= $item['price'] > 0 ? '#059669' : '#6b7280' ?>;">
                                        <?= $item['price'] > 0 ? '+R$ ' . number_format($item['price'], 2, ',', '.') : 'Grátis' ?>
                                    </span>
                                    <a href="<?= BASE_URL ?>/admin/loja/adicionais/desvincular?grupo=<?= $group['id'] ?>&item=<?= $item['id'] ?>" 
                                       onclick="return confirm('Desvincular &quot;<?= htmlspecialchars($item['name']) ?>&quot; deste grupo?')"
                                       title="Desvincular do grupo"
                                       style="padding: 4px 8px; background: #fef2f2; color: #dc2626; text-decoration: none; border-radius: 4px;">
                                        <i data-lucide="unlink" size="12"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal de Novo Grupo -->
<div id="groupModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">Novo Grupo</h3>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/grupo/salvar" method="POST">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome do Grupo</label>
                <input type="text" name="name" placeholder="Ex: Molhos, Extras, Bordas..." required 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeGroupModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="submit" style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Grupo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Vincular Item -->
<div id="linkModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 450px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Vincular Item ao Grupo</h3>
        <p style="color: #6b7280; margin-bottom: 1.5rem;" id="linkGroupName">Grupo: </p>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/vincular" method="POST">
            <input type="hidden" name="group_id" id="linkGroupId">
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Selecione um Item</label>
                <?php if (empty($allItems)): ?>
                    <p style="color: #9ca3af; padding: 12px; background: #f9fafb; border-radius: 8px;">
                        Nenhum item cadastrado. <a href="<?= BASE_URL ?>/admin/loja/adicionais/itens" style="color: #2563eb;">Criar itens primeiro</a>
                    </p>
                <?php else: ?>
                    <select name="item_id" required style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                        <option value="">Escolha um item...</option>
                        <?php foreach ($allItems as $item): ?>
                            <option value="<?= $item['id'] ?>">
                                <?= htmlspecialchars($item['name']) ?> 
                                (<?= $item['price'] > 0 ? '+R$ ' . number_format($item['price'], 2, ',', '.') : 'Grátis' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeLinkModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <?php if (!empty($allItems)): ?>
                <button type="submit" style="flex: 1; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Vincular
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
function openGroupModal() {
    document.getElementById('groupModal').style.display = 'flex';
}
function closeGroupModal() {
    document.getElementById('groupModal').style.display = 'none';
}

function openLinkModal(groupId, groupName) {
    document.getElementById('linkGroupId').value = groupId;
    document.getElementById('linkGroupName').textContent = 'Grupo: ' + groupName;
    document.getElementById('linkModal').style.display = 'flex';
}
function closeLinkModal() {
    document.getElementById('linkModal').style.display = 'none';
}

// Fechar modais ao clicar fora
document.getElementById('groupModal').addEventListener('click', function(e) {
    if (e.target === this) closeGroupModal();
});
document.getElementById('linkModal').addEventListener('click', function(e) {
    if (e.target === this) closeLinkModal();
});

// Filtrar grupos
function filterGroups(query) {
    const cards = document.querySelectorAll('.group-card');
    const q = query.toLowerCase().trim();
    cards.forEach(card => {
        card.style.display = card.dataset.name.includes(q) ? 'block' : 'none';
    });
}
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
