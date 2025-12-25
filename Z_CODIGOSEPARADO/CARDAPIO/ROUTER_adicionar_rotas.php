/**
 * ═══════════════════════════════════════════════════════════════════════════
 * LOCALIZAÇÃO ORIGINAL: public/index.php (TRECHO RELEVANTE)
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * DESCRIÇÃO: Router principal - Define TODAS as rotas do sistema
 * 
 * ⚠️ IMPORTANTE: A ROTA DO CARDÁPIO NÃO ESTÁ DEFINIDA AINDA!
 * 
 * PARA ADICIONAR A ROTA, COPIE ESTE BLOCO PARA O public/index.php:
 * ═══════════════════════════════════════════════════════════════════════════
 */

// ========================
// ROTAS DO CARDÁPIO
// Adicionar no public/index.php dentro do switch($path)
// ========================

case '/admin/loja/cardapio':
    require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
    (new \App\Controllers\Admin\CardapioController())->index();
    break;

// Quando tiver mais funcionalidades, adicionar assim:
/*
case '/admin/loja/cardapio/configurar':
    require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
    (new \App\Controllers\Admin\CardapioController())->configure();
    break;

case '/admin/loja/cardapio/salvar':
    require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
    (new \App\Controllers\Admin\CardapioController())->save();
    break;

case '/admin/loja/cardapio/preview':
    require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
    (new \App\Controllers\Admin\CardapioController())->preview();
    break;
*/

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * ESTRUTURA DO ROUTER (public/index.php)
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * 1. Configurações iniciais (session_start, BASE_URL, etc)
 * 2. Captura a URL: $path = str_replace('/cardapio-saas/public', '', $url_clean);
 * 3. Switch case com todas as rotas
 * 4. case 'default' para 404
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */
