<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Categorias - <?= $_SESSION['loja_ativa_nome'] ?></title>
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-tags"></i> Categorias</h1>
        <a href="../loja/painel" class="btn" style="background:#777">Voltar</a>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3>Nova Categoria</h3>
        <form action="categories/salvar" method="POST" style="display: flex; gap: 10px;">
            <input type="text" name="name" placeholder="Ex: Lanches, Bebidas, Pizzas..." required style="flex: 1; padding: 10px;">
            <button type="submit" class="btn">Adicionar</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th class="text-center" style="width: 100px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categories as $cat): ?>
            <tr>
                <td><?= $cat['name']; ?></td>
                <td class="text-center">
                    <a href="categories/deletar?id=<?= $cat['id']; ?>" class="btn-delete" onclick="return confirm('Apagar essa categoria?');">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if(empty($categories)): ?>
            <tr>
                <td colspan="2" class="text-center">Nenhuma categoria cadastrada.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
