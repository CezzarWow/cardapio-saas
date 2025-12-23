<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; display: flex; justify-content: center; overflow-y: auto;">
        
        <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 600px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: fit-content;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700; color: #1f2937;">Cadastrar Produto</h2>
            
            <form action="<?= BASE_URL ?>/admin/loja/produtos/salvar" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Nome do Produto</label>
                    <input type="text" name="name" required placeholder="Ex: X-Salada" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Categoria</label>
                        <select name="category_id" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Preço (R$)</label>
                        <input type="text" name="price" required placeholder="0,00" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    </div>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Descrição</label>
                    <textarea name="description" rows="3" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-family: sans-serif;"></textarea>
                </div>

                <!-- [FASE 1] Campo de Estoque Inicial -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Estoque Inicial</label>
                    <input type="number" name="stock" value="0" min="0" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <small style="color: #6b7280; font-size: 0.85rem;">Quantidade inicial em estoque</small>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #374151;">Foto do Produto</label>
                    <input type="file" name="image" accept="image/*" style="width: 100%; padding: 10px; border: 1px dashed #d1d5db; border-radius: 8px; background: #f9fafb;">
                </div>

                <div style="margin-top: 10px; display: flex; gap: 10px; align-items: center;">
                    <button type="submit" class="btn-primary" style="flex: 1;">Salvar Produto</button>
                    <a href="<?= BASE_URL ?>/admin/loja/produtos" style="padding: 12px; color: #6b7280; text-decoration: none; font-weight: 600;">Cancelar</a>
                </div>

            </form>
        </div>

    </div>
</main>
<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
