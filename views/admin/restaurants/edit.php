<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Restaurante</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; display: flex; justify-content: center; }
        .form-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .back { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #666; }
    </style>
</head>
<body>

    <div class="form-box">
        <h2>✏️ Editar Loja</h2>
        
        <form action="atualizar" method="POST">
            
            <input type="hidden" name="id" value="<?php echo $restaurant['id']; ?>">

            <label>Nome do Restaurante</label>
            <input type="text" name="name" value="<?php echo $restaurant['name']; ?>" required>
            
            <label>Link do Cardápio (Slug)</label>
            <input type="text" name="slug" value="<?php echo $restaurant['slug']; ?>" required>
            
            <button type="submit">Salvar Alterações</button>
        </form>

        <a href="../../admin" class="back">Cancelar</a>    </div>

</body>
</html>