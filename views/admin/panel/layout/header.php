<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel PDV - <?= $_SESSION['loja_ativa_nome'] ?? 'Minha Loja' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- CSS Modular -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/pdv.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/pdv-cart.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/stock.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/checkout.css?v=<?= time() ?>">
    <!-- CSS Cardápio (usado em /admin/loja/cardapio) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cards.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/modals.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cart.css?v=<?= time() ?>">
    <!-- Cardápio Admin - CSS Modular -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/tabs.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/cards.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/toggles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/grids.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/buttons.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/utilities.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/featured.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cardapio-admin/responsive.css?v=<?= time() ?>">
    
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 text-gray-900 h-screen overflow-hidden flex">
