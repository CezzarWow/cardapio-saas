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
                <span style="font-weight: 700; color: #1f2937;"><?= (int) ($totalGroups ?? 0) ?></span>
                <span style="font-size: 0.8rem; color: #6b7280;"> grupos</span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="background: #d1fae5; padding: 6px; border-radius: 6px;">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px; color: #059669;"></i>
            </div>
            <div>
                <span style="font-weight: 700; color: #1f2937;"><?= (int) ($totalItems ?? 0) ?></span>
                <span style="font-size: 0.8rem; color: #6b7280;"> itens</span>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 10px;">
        <button onclick="openItemModal()" 
           style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Novo Item
        </button>
        
        <button onclick="openGroupModal()" 
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
        <button onclick="openGroupModal()" style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
            Criar Primeiro Grupo
        </button>
    </div>
<?php else: ?>
    <div class="additionals-groups">
        <?php foreach ($groups as $group): ?>
        <?php
            $groupId = (int) ($group['id'] ?? 0);
            $groupName = (string) ($group['name'] ?? '');
            $itemIds = array_map('intval', array_column($group['items'] ?? [], 'id'));
            $confirmMsg = 'Excluir o grupo \"' . $groupName . '\" e todos seus vinculos?';
        ?>
        <div class="group-card">
            <div class="group-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="folder" style="width: 20px; height: 20px; color: #2563eb;"></i>
                    <span style="font-weight: 700; color: #1f2937;"><?= \App\Helpers\ViewHelper::e($groupName) ?></span>
                    <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;">
                        <?= (int) count($group['items'] ?? []) ?> itens
                    </span>
                </div>
                <!-- Botões de Ação do Grupo -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <button type="button" class="btn-action-link" 
                            data-group-id="<?= $groupId ?>" 
                            data-group-name="<?= \App\Helpers\ViewHelper::e($groupName) ?>"
                            data-item-ids="<?= \App\Helpers\ViewHelper::e(\App\Helpers\ViewHelper::js($itemIds)) ?>"
                            style="padding: 6px 10px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px;"
                            title="Vincular Itens">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        Itens
                    </button>
                    <!-- Botão Categoria Removido -->
                    <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/adicionais/grupo/deletar?id=<?= $groupId ?>"
                       onclick='return confirm(<?= \App\Helpers\ViewHelper::js($confirmMsg) ?>)'
                       style="color: #dc2626; padding: 6px; border-radius: 6px; text-decoration: none;">
                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                    </a>
                </div>
            </div>
            <div class="group-card-body">
                <?php if (empty($group['items'])): ?>
                    <div class="group-card-empty">
                        Nenhum item vinculado a este grupo
                    </div>
                <?php else: ?>
                    <div class="item-chips-container">
                        <?php foreach ($group['items'] as $item): ?>
                        <?php
                            $itemId = (int) ($item['id'] ?? 0);
                            $itemName = (string) ($item['name'] ?? '');
                            $itemPrice = (float) ($item['price'] ?? 0);
                        ?>
                        <div class="item-chip">
                            <span class="item-chip-name"><?= \App\Helpers\ViewHelper::e($itemName) ?></span>
                            <span class="item-chip-price <?= $itemPrice > 0 ? 'paid' : 'free' ?>">
                                <?= $itemPrice > 0 ? ('R$ ' . number_format($itemPrice, 2, ',', '.')) : 'Grátis' ?>
                            </span>
                            <a href="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/admin/loja/adicionais/desvincular?grupo=<?= $groupId ?>&item=<?= $itemId ?>"
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

<!-- Modais de Adicionais -->
<?php \App\Core\View::renderFromScope('admin/additionals/partials/group-modal.php', get_defined_vars()); ?>
<!-- Modal de Categoria Removido -->
<?php \App\Core\View::renderFromScope('admin/additionals/partials/link-modal.php', get_defined_vars()); ?>
<?php \App\Core\View::renderFromScope('admin/additionals/partials/item-modal.php', get_defined_vars()); ?>

<!-- Scripts de Adicionais -->
<!-- MultiSelect precisa ser executado toda vez para garantir disponibilidade no contexto SPA -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/components/multi-select.js?v=<?= time() ?>"></script>

<!-- Scripts de adicionais unificados (Bundle) -->
<script src="<?= \App\Helpers\ViewHelper::e(BASE_URL) ?>/js/admin/additionals-bundle.js?v=<?= time() ?>"></script>
