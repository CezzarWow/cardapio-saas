<?php 
/**
 * ============================================
 * ADMIN CARDÁPIO - FORMULÁRIO DE COMBO
 * ============================================
 */

require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

$isEdit = isset($combo) && $combo;
$title = $isEdit ? 'Editar Combo' : 'Novo Combo';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin.css?v=<?= time() ?>">

<main class="main-content">
    <?php require __DIR__ . '/../panel/layout/messages.php'; ?>
    
    <div class="cardapio-admin-container">
        
        <!-- Header -->
        <div class="cardapio-admin-header">
            <h1 class="cardapio-admin-title"><?= $title ?></h1>
            <p class="cardapio-admin-subtitle">
                <a href="<?= BASE_URL ?>/admin/loja/cardapio" style="color: #2563eb;">← Voltar para Configurações</a>
            </p>
        </div>

        <!-- Formulário -->
        <form method="POST" action="<?= BASE_URL ?>/admin/loja/cardapio/combo/<?= $isEdit ? 'atualizar' : 'salvar' ?>" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $combo['id'] ?>">
            <?php endif; ?>

            <div class="cardapio-admin-card">
                <div class="cardapio-admin-card-header">
                    <i data-lucide="package-plus"></i>
                    <h3 class="cardapio-admin-card-title">Informações do Combo</h3>
                </div>

                <div class="cardapio-admin-form-group">
                    <label class="cardapio-admin-label" for="name">Nome do Combo *</label>
                    <input type="text" 
                           class="cardapio-admin-input" 
                           id="name" 
                           name="name" 
                           required
                           placeholder="Ex: Combo Família"
                           value="<?= htmlspecialchars($combo['name'] ?? '') ?>">
                </div>

                <div class="cardapio-admin-form-group">
                    <label class="cardapio-admin-label" for="description">Descrição</label>
                    <textarea class="cardapio-admin-input cardapio-admin-textarea" 
                              id="description" 
                              name="description" 
                              placeholder="Descreva o que está incluído no combo..."><?= htmlspecialchars($combo['description'] ?? '') ?></textarea>
                </div>

                <div class="cardapio-admin-grid cardapio-admin-grid-2">
                    <div class="cardapio-admin-form-group">
                        <label class="cardapio-admin-label" for="price">Preço (R$) *</label>
                        <input type="text" 
                               class="cardapio-admin-input" 
                               id="price" 
                               name="price" 
                               required
                               placeholder="49,90"
                               value="<?= isset($combo['price']) ? number_format($combo['price'], 2, ',', '.') : '' ?>">
                    </div>

                    <div class="cardapio-admin-form-group">
                        <label class="cardapio-admin-label" for="display_order">Ordem de Exibição</label>
                        <input type="number" 
                               class="cardapio-admin-input" 
                               id="display_order" 
                               name="display_order" 
                               min="0"
                               placeholder="0"
                               value="<?= $combo['display_order'] ?? 0 ?>">
                        <p class="cardapio-admin-hint">Menor número = aparece primeiro</p>
                    </div>
                </div>

                <div class="cardapio-admin-toggle-row" style="margin-top: 1rem;">
                    <span class="cardapio-admin-toggle-label">Combo Ativo</span>
                    <label class="cardapio-admin-toggle">
                        <input type="checkbox" name="is_active" value="1"
                               <?= ($combo['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <span class="cardapio-admin-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Produtos do Combo -->
            <div class="cardapio-admin-card">
                <div class="cardapio-admin-card-header">
                    <i data-lucide="list"></i>
                    <h3 class="cardapio-admin-card-title">Produtos Incluídos</h3>
                </div>

                <div class="cardapio-admin-hint" style="margin-bottom: 1rem;">
                    <i data-lucide="info" style="width: 14px; height: 14px; display: inline;"></i>
                    Selecione os produtos que fazem parte deste combo (informativo).
                </div>

                <?php if (!empty($products)): ?>
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($products as $product): ?>
                    <div class="cardapio-admin-toggle-row" style="padding: 8px 0;">
                        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" 
                                       name="products[]" 
                                       value="<?= $product['id'] ?>"
                                       id="prod_<?= $product['id'] ?>"
                                       <?= in_array($product['id'], $comboProducts ?? []) ? 'checked' : '' ?>
                                       style="width: 18px; height: 18px;">
                                <label for="prod_<?= $product['id'] ?>" style="cursor: pointer;">
                                    <span style="font-weight: 500;"><?= htmlspecialchars($product['name']) ?></span>
                                    <span style="color: #6b7280; font-size: 0.85rem;"> - R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                </label>
                            </div>
                            
                            <!-- Toggle Permitir Adicionais -->
                            <div class="allow-additionals-toggle" style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 0.85rem; color: #64748b;">Permitir adicionais</span>
                                <label class="cardapio-admin-toggle" style="transform: scale(0.8);">
                                    <input type="checkbox" 
                                           name="allow_additionals[<?= $product['id'] ?>]" 
                                           value="1"
                                           <?= (isset($comboItemsSettings[$product['id']]['allow_additionals']) && $comboItemsSettings[$product['id']]['allow_additionals'] == 0) ? '' : 'checked' ?>>
                                    <span class="cardapio-admin-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color: #6b7280;">Nenhum produto cadastrado.</p>
                <?php endif; ?>
            </div>

            <!-- Botões -->
            <div style="display: flex; gap: 12px; margin-top: 1rem;">
                <button type="submit" class="cardapio-admin-btn cardapio-admin-btn-primary" style="flex: 1; justify-content: center;">
                    <i data-lucide="save"></i>
                    <?= $isEdit ? 'Atualizar Combo' : 'Criar Combo' ?>
                </button>
                <a href="<?= BASE_URL ?>/admin/loja/cardapio" class="cardapio-admin-btn" style="background: #f1f5f9; color: #475569;">
                    Cancelar
                </a>
            </div>

        </form>

    </div>
</main>

<!-- Cardapio Admin - Modular Scripts (v2.0) -->
<script src="<?= BASE_URL ?>/js/cardapio-admin/utils.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/pix.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/whatsapp.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/forms.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/combos.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/featured.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/js/cardapio-admin/index.js?v=<?= time() ?>"></script>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
