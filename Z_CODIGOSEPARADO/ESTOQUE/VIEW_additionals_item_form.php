<?php
// LOCALIZACAO ORIGINAL: views/admin/additionals/item_form.php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';

$isEdit = isset($item) && $item;
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; max-width: 600px;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px;">
            <a href="<?= BASE_URL ?>/admin/loja/adicionais/itens" style="color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 5px; margin-bottom: 10px;">
                <i data-lucide="arrow-left" size="16"></i> Voltar para Catálogo
            </a>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">
                <?= $isEdit ? 'Editar Item' : 'Novo Item' ?>
            </h1>
        </div>

        <!-- Form -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <form action="<?= BASE_URL ?>/admin/loja/adicionais/item/<?= $isEdit ? 'atualizar' : 'salvar' ?>" method="POST">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <?php endif; ?>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome do Item</label>
                    <input type="text" name="name" placeholder="Ex: Bacon, Queijo Extra, Maionese..." required 
                           value="<?= $isEdit ? htmlspecialchars($item['name']) : '' ?>"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Preço Adicional (R$)</label>
                    <input type="text" name="price" placeholder="0,00" 
                           value="<?= $isEdit ? number_format($item['price'], 2, ',', '') : '0' ?>"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                    <small style="color: #6b7280; margin-top: 5px; display: block;">Deixe 0 para itens gratuitos</small>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="<?= BASE_URL ?>/admin/loja/adicionais/itens" 
                       style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600; text-align: center;">
                        Cancelar
                    </a>
                    <button type="submit" style="flex: 1; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <?= $isEdit ? 'Salvar Alterações' : 'Criar Item' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>

