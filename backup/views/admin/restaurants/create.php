<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Restaurante</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; display: flex; justify-content: center; }
        .form-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background: #218838; }
        .back { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #666; }
    </style>
</head>
<body>

    <div class="form-box">
        <h2>Cadastrar Loja</h2>
        
        <form action="salvar" method="POST">
            <?= \App\Helpers\ViewHelper::csrfField() ?>
            <label>Nome do Restaurante</label>
            <input type="text" name="name" placeholder="Ex: Hamburgueria do Zé" required>
            
            <label>Link do Cardápio (Slug)</label>
            <input type="text" name="slug" placeholder="Ex: hamburgueria-do-ze" required>
            
            <button type="submit">Salvar Restaurante</button>
        </form>

        <a href="../../admin" class="back">Voltar</a>
    </div> 

</body>
</html>