<?php
// LOCALIZACAO ORIGINAL: views/admin/additionals/items.php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <a href="<?= BASE_URL ?>/admin/loja/adicionais" style="color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 5px; margin-bottom: 8px; font-size: 0.9rem;">
                    <i data-lucide="arrow-left" size="16"></i> Voltar para Grupos
                </a>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Catálogo de Itens</h1>
                <p style="color: #6b7280; margin-top: 5px;">Itens disponíveis para vincular aos grupos</p>
            </div>
            <button onclick="openItemModal()" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="plus" size="18"></i> Novo Item
            </button>
        </div>

        <!-- Barra de Pesquisa -->
        <div style="background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="position: relative;">
                <i data-lucide="search" size="18" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                <input type="text" id="searchItems" placeholder="Pesquisar itens..." 
                       oninput="filterItems(this.value)"
                       style="width: 100%; padding: 10px 12px 10px 40px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Lista de Itens -->
        <?php if (empty($items)): ?>
            <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <i data-lucide="package" size="48" style="color: #d1d5db; margin-bottom: 15px;"></i>
                <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhum item cadastrado</h3>
                <p style="color: #9ca3af; margin-bottom: 20px;">Crie seus itens de adicionais para vincular aos grupos</p>
                <button onclick="openItemModal()" style="padding: 12px 24px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Primeiro Item
                </button>
            </div>
        <?php else: ?>
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                            <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #374151;">Nome</th>
                            <th style="padding: 15px 20px; text-align: right; font-weight: 600; color: #374151;">Preço</th>
                            <th style="padding: 15px 20px; text-align: center; font-weight: 600; color: #374151;">Grupos</th>
                            <th style="padding: 15px 20px; text-align: center; font-weight: 600; color: #374151; width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTable">
                        <?php foreach ($items as $item): ?>
                        <tr class="item-row" data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>" style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 15px 20px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i data-lucide="circle" size="8" style="color: #10b981;"></i>
                                    <span style="font-weight: 500; color: #1f2937;"><?= htmlspecialchars($item['name']) ?></span>
                                </div>
                            </td>
                            <td style="padding: 15px 20px; text-align: right;">
                                <span style="font-weight: 600; color: <?= $item['price'] > 0 ? '#059669' : '#6b7280' ?>;">
                                    <?= $item['price'] > 0 ? '+R$ ' . number_format($item['price'], 2, ',', '.') : 'Grátis' ?>
                                </span>
                            </td>
                            <td style="padding: 15px 20px; text-align: center;">
                                <span style="padding: 4px 10px; background: #e0f2fe; color: #0369a1; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                    <?= $item['groups_count'] ?> grupo(s)
                                </span>
                            </td>
                            <td style="padding: 15px 20px; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="<?= BASE_URL ?>/admin/loja/adicionais/item/editar?id=<?= $item['id'] ?>" 
                                       style="padding: 6px 10px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px;"
                                       title="Editar">
                                        <i data-lucide="pencil" size="14"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/loja/adicionais/item/deletar?id=<?= $item['id'] ?>" 
                                       onclick="return confirm('Excluir &quot;<?= htmlspecialchars($item['name']) ?>&quot;? Isso remove de todos os grupos!')"
                                       style="padding: 6px 10px; background: #fef2f2; color: #dc2626; text-decoration: none; border-radius: 6px;"
                                       title="Excluir">
                                        <i data-lucide="trash-2" size="14"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal de Novo Item -->
<div id="itemModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">Novo Item</h3>
        
        <form action="<?= BASE_URL ?>/admin/loja/adicionais/item/salvar" method="POST">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome do Item</label>
                <input type="text" name="name" placeholder="Ex: Bacon, Queijo Extra..." required 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Preço (R$)</label>
                <input type="text" name="price" placeholder="0,00" value="0" 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                <small style="color: #6b7280; margin-top: 5px; display: block;">Deixe 0 para itens gratuitos</small>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeItemModal()" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="submit" style="flex: 1; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Item
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openItemModal() {
    document.getElementById('itemModal').style.display = 'flex';
}
function closeItemModal() {
    document.getElementById('itemModal').style.display = 'none';
}

document.getElementById('itemModal').addEventListener('click', function(e) {
    if (e.target === this) closeItemModal();
});

function filterItems(query) {
    const rows = document.querySelectorAll('.item-row');
    const q = query.toLowerCase().trim();
    rows.forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>

