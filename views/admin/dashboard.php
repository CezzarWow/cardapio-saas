<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { background: #3498db; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: middle; }
        th { background: #333; color: white; }
        tr:hover { background: #f1f1f1; }

        /* Estilos dos Botões de Status */
        .btn-status { 
            padding: 5px 10px; 
            border-radius: 15px; 
            font-weight: bold; 
            text-decoration: none; 
            border: 1px solid; 
            font-size: 0.9em;
            margin-left: 15px; 
        }
        .status-ativo { color: green; border-color: green; }
        .status-suspenso { color: red; border-color: red; }
    </style>
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
                <th>ID</th>
                <th>Nome</th>
                <th>Cardápio</th>
                <th>Ações e Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($restaurants as $loja): ?>
                <tr>
                    <td>#<?php echo $loja['id']; ?></td>
                    <td><?php echo $loja['name']; ?></td>
                    
                    <td>
                        <a href="../<?php echo $loja['slug']; ?>" target="_blank" style="text-decoration: none; color: #2980b9; font-weight: bold;">
                            <?php echo $loja['slug']; ?> 🔗
                        </a>
                    </td>
                    
                    <td>
                        <a href="admin/restaurantes/editar?id=<?php echo $loja['id']; ?>" 
                           style="color: blue; text-decoration: none; margin-right: 10px;">
                           ✏️ Editar
                        </a>

                        <a href="admin/restaurantes/deletar?id=<?php echo $loja['id']; ?>" 
                           onclick="return confirm('Tem certeza que deseja EXCLUIR DEFINITIVAMENTE o restaurante <?php echo $loja['name']; ?>?');"
                           style="color: red; text-decoration: none; margin-right: 10px;">
                           🗑️ Excluir
                        </a>

                        <?php if ($loja['is_active'] == 1): ?>
                            <a href="admin/restaurantes/status?id=<?php echo $loja['id']; ?>" 
                               class="btn-status status-ativo"
                               onclick="return confirm('⚠️ Tem certeza que deseja SUSPENDER a loja <?php echo $loja['name']; ?>? O cliente perderá o acesso!');">
                               🟢 Ativo
                            </a>
                        <?php else: ?>
                            <a href="admin/restaurantes/status?id=<?php echo $loja['id']; ?>" 
                               class="btn-status status-suspenso"
                               onclick="return confirm('Deseja REATIVAR a loja <?php echo $loja['name']; ?>?');">
                               🔴 Suspenso
                            </a>
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($restaurants)): ?>
                <tr>
                    <td colspan="4" style="text-align:center">Nenhum restaurante criado ainda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>