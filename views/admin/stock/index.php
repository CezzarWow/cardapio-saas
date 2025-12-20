<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <div class="header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">Gerenciar Estoque</h1>
            <a href="<?= BASE_URL ?>/admin/loja/produtos/novo" class="btn" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">+ Novo Produto</a>
        </div>

        <div style="background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Imagem</th>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Produto</th>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Categoria</th>
                        <th style="padding: 15px; text-align: left; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Preço</th>
                        <th style="padding: 15px; text-align: center; color: #6b7280; font-size: 0.85rem; text-transform: uppercase;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="5" style="padding: 2rem; text-align: center; color: #999;">Nenhum produto cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 10px;">
                                <?php if($prod['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= $prod['image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                                        <i data-lucide="image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <strong style="color: #1f2937;"><?= $prod['name'] ?></strong><br>
                                <small style="color: #6b7280;"><?= substr($prod['description'], 0, 30) ?>...</small>
                            </td>
                            <td style="padding: 15px;"><span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: 600;"><?= $prod['category_name'] ?></span></td>
                            <td style="padding: 15px; font-weight: bold; color: #2563eb;">R$ <?= number_format($prod['price'], 2, ',', '.') ?></td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="<?= BASE_URL ?>/admin/loja/produtos/deletar?id=<?= $prod['id'] ?>" onclick="return confirm('Apagar?')" style="color: #ef4444;"><i data-lucide="trash-2" style="width: 18px;"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
