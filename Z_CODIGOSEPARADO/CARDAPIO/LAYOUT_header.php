<!-- 
═══════════════════════════════════════════════════════════════════════════
LOCALIZAÇÃO ORIGINAL: views/admin/panel/layout/header.php
═══════════════════════════════════════════════════════════════════════════

DESCRIÇÃO: Cabeçalho HTML incluído em TODAS as páginas
CONTÉM:
- Doctype e meta tags
- Link para Tailwind CSS (CDN)
- Link para pdv.css (estilos customizados)
- Lucide Icons (ícones)

IMPORTANTE: 
- O título usa $_SESSION['loja_ativa_nome']
- O CSS principal é /css/pdv.css
═══════════════════════════════════════════════════════════════════════════
-->

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel PDV - <?= $_SESSION['loja_ativa_nome'] ?? 'Minha Loja' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/pdv.css?v=<?= time() ?>">
    
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 text-gray-900 h-screen overflow-hidden flex">
