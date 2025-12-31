<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurant['name']) ?> - Fechado</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #ffffff;
            color: #1f2937;
            text-align: center;
        }
        .container {
            padding: 2rem;
            max-width: 400px;
            width: 90%;
        }
        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 1.5rem;
            border-radius: 50%;
        }
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #9ca3af;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #111827;
        }
        p {
            font-size: 1rem;
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 2rem;
        }
        .icon-lock {
            color: #ef4444;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($restaurant['logo'])): ?>
            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($restaurant['logo']) ?>" alt="Logo" class="logo">
        <?php else: ?>
            <div class="logo-placeholder">
                <i data-lucide="utensils" size="32"></i>
            </div>
        <?php endif; ?>

        <i data-lucide="lock" size="40" class="icon-lock"></i>
        
        <h1><?= htmlspecialchars($restaurant['name']) ?></h1>
        <p><?= htmlspecialchars($cardapioConfig['closed_message'] ?? 'Estamos fechados no momento.') ?></p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
