<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel da Loja - <?= $_SESSION['loja_ativa_nome'] ?></title>
    <link rel="stylesheet" href="../../css/admin.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .top-bar { background: #2c3e50; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .top-bar a { color: #ecf0f1; text-decoration: none; font-size: 0.9em; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .menu-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: 0.3s; text-decoration: none; color: #333; }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .menu-card i { font-size: 3em; margin-bottom: 15px; color: #3498db; }
        .menu-card h3 { margin: 0; font-size: 1.2em; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div>
            <strong>Loja:</strong> <?= $_SESSION['loja_ativa_nome'] ?>
        </div>
        <div>
            <a href="../../admin"><i class="fas fa-arrow-left"></i> Voltar para Admin Geral</a>
        </div>
    </div>

    <div class="container">
        <h2 style="margin-top: 20px;">Painel de Gestão</h2>
        
        <div class="menu-grid">
            <a href="categories" class="menu-card">
                <i class="fas fa-tags"></i>
                <h3>Categorias</h3>
            </a>

            <a href="produtos" class="menu-card">
                <i class="fas fa-hamburger"></i>
                <h3>Produtos</h3>
            </a>

            <a href="#" class="menu-card" style="opacity: 0.5;">
                <i class="fas fa-motorcycle"></i>
                <h3>Pedidos (Em breve)</h3>
            </a>
            
            <a href="#" class="menu-card" style="opacity: 0.5;">
                <i class="fas fa-cog"></i>
                <h3>Configurações</h3>
            </a>
        </div>
    </div>

</body>
</html>
