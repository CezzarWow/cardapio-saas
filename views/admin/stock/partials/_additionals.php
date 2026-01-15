<?php
/**
 * Additionals Partial - Para carregamento AJAX
 * Arquivo: views/admin/stock/partials/_additionals.php
 */
?>

<!-- Busca + Indicadores -->
<div class="stock-search-container" style="margin-bottom: 20px;">
    <input type="text" id="searchAdditionals" placeholder="🔍 Buscar adicional..." 
           class="stock-search-input" style="width: 100%; max-width: 350px;">
    
    <div style="display: flex; gap: 20px; align-items: center;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="background: #dbeafe; padding: 6px; border-radius: 6px;">
                <i data-lucide="folder" style="width: 18px; height: 18px; color: #2563eb;"></i>
            </div>
            <div>
                <span style="font-weight: 700; color: #1f2937;"><?= $totalGroups ?></span>
                <span style="font-size: 0.8rem; color: #6b7280;"> grupos</span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="background: #d1fae5; padding: 6px; border-radius: 6px;">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px; color: #059669;"></i>
            </div>
            <div>
                <span style="font-weight: 700; color: #1f2937;"><?= $totalItems ?></span>
                <span style="font-size: 0.8rem; color: #6b7280;"> itens</span>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 10px;">
        <button onclick="StockSPA.openItemModal()" 
           style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Novo Item
        </button>
        
        <button onclick="StockSPA.openGroupModal()" 
                style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="folder-plus" style="width: 18px; height: 18px;"></i> Novo Grupo
        </button>
    </div>
</div>

<!-- Grupos de Adicionais -->
<?php if (empty($groups)): ?>
    <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <i data-lucide="folder-plus" style="width: 48px; height: 48px; color: #d1d5db; margin-bottom: 15px;"></i>
        <h3 style="color: #6b7280; font-size: 1.1rem; margin-bottom: 10px;">Nenhum grupo de adicionais</h3>
        <p style="color: #9ca3af; margin-bottom: 20px;">Crie grupos para organizar seus adicionais</p>
        <button onclick="StockSPA.openGroupModal()" style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
            Criar Primeiro Grupo
        </button>
    </div>
<?php else: ?>
    <div class="additionals-groups">
        <?php foreach ($groups as $group): ?>
        <div class="group-card">
            <div class="group-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="folder" style="width: 20px; height: 20px; color: #2563eb;"></i>
                    <span style="font-weight: 700; color: #1f2937;"><?= htmlspecialchars($group['name']) ?></span>
                    <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;">
                        <?= count($group['items'] ?? []) ?> itens
                    </span>
                </div>
                <a href="<?= BASE_URL ?>/admin/loja/adicionais/grupo/deletar?id=<?= $group['id'] ?>"
                   onclick="return confirm('Excluir o grupo &quot;<?= htmlspecialchars($group['name']) ?>&quot; e todos seus vínculos?')"
                   style="color: #dc2626; padding: 6px; border-radius: 6px; text-decoration: none;">
                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                </a>
            </div>
            <div class="group-card-body">
                <?php if (empty($group['items'])): ?>
                    <div class="group-card-empty">
                        Nenhum item vinculado a este grupo
                    </div>
                <?php else: ?>
                    <div class="item-chips-container">
                        <?php foreach ($group['items'] as $item): ?>
                        <div class="item-chip">
                            <span class="item-chip-name"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="item-chip-price <?= $item['price'] > 0 ? 'paid' : 'free' ?>">
                                <?= $item['price'] > 0 ? 'R$ ' . number_format($item['price'], 2, ',', '.') : 'Grátis' ?>
                            </span>
                            <a href="<?= BASE_URL ?>/admin/loja/adicionais/desvincular?grupo=<?= $group['id'] ?>&item=<?= $item['id'] ?>"
                               class="item-chip-unlink" title="Desvincular">
                                <i data-lucide="x" style="width: 12px; height: 12px;"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
