<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Meus Restaurantes</h1>
        <a href="admin/restaurantes/novo" class="btn">+ Novo Restaurante</a>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Nome</th>
                <th class="text-center" style="width: 120px;">PDV</th>
                <th class="text-center" style="width: 80px;">Cardápio</th>
                <th class="text-center" style="width: 120px;">Status</th>
                <th class="text-center" style="width: 120px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($restaurants as $loja): ?>
                <tr>
                    <td>#<?php echo $loja['id']; ?></td>
                    <td>
                        <strong><?php echo $loja['name']; ?></strong><br>
                        <small style="color:#777"><?php echo $loja['slug_display']; ?></small>
                    </td>
                    
                    <td class="text-center">
                        <a href="admin/autologin?id=<?php echo $loja['id']; ?>" class="btn-pdv" title="Gerenciar esta Loja">
                            <i class="fas fa-desktop"></i> Acessar
                        </a>
                    </td>

                    <td class="text-center">
                        <a href="<?php echo $loja['slug']; ?>" target="_blank" class="btn-link-cardapio">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </td>
                    
                    <td class="text-center">
                        <a href="admin/restaurantes/status?id=<?php echo $loja['id']; ?>" class="badge <?php echo $loja['status_class']; ?>">
                            <?php echo $loja['status_label']; ?>
                        </a>
                    </td>

                    <td class="text-center">
                        <a href="admin/restaurantes/editar?id=<?php echo $loja['id']; ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                        <a href="admin/restaurantes/deletar?id=<?php echo $loja['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Excluir?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>