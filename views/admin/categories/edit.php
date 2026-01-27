<?php
\App\Core\View::renderFromScope('admin/panel/layout/header.php', get_defined_vars());
\App\Core\View::renderFromScope('admin/panel/layout/sidebar.php', get_defined_vars());
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; max-width: 600px;">
        
        <!-- Header -->
        <div style="margin-bottom: 20px;">
            <a href="<?= BASE_URL ?>/admin/loja/categorias" style="color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 5px; margin-bottom: 10px;">
                <i data-lucide="arrow-left" size="16"></i> Voltar para Categorias
            </a>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Editar Categoria</h1>
        </div>

        <!-- Form -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <form action="<?= BASE_URL ?>/admin/loja/categorias/atualizar" method="POST">
                <?= \App\Helpers\ViewHelper::csrfField() ?>
                <input type="hidden" name="id" value="<?= (int) ($category['id'] ?? 0) ?>">
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nome da Categoria</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required 
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="<?= BASE_URL ?>/admin/loja/categorias" 
                       style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600; text-align: center;">
                        Cancelar
                    </a>
                    <button type="submit" style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php \App\Core\View::renderFromScope('admin/panel/layout/footer.php', get_defined_vars()); ?>
