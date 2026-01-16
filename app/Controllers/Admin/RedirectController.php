<?php

namespace App\Controllers\Admin;

/**
 * RedirectController - Redireciona rotas legado para SPA
 * 
 * Após a migração completa para SPA, as URLs antigas agora redirecionam
 * automaticamente para o shell SPA com o hash correto.
 */
class RedirectController extends BaseController
{
    private function redirectToSpa(string $section): void
    {
        $baseUrl = BASE_URL;
        header("Location: {$baseUrl}/admin/loja/spa#{$section}");
        exit;
    }

    public function toSpaBalcao(): void
    {
        $this->redirectToSpa('balcao');
    }

    public function toSpaMesas(): void
    {
        $this->redirectToSpa('mesas');
    }

    public function toSpaDelivery(): void
    {
        $this->redirectToSpa('delivery');
    }

    public function toSpaCardapio(): void
    {
        $this->redirectToSpa('cardapio');
    }

    public function toSpaEstoque(): void
    {
        $this->redirectToSpa('estoque');
    }

    public function toSpaCaixa(): void
    {
        $this->redirectToSpa('caixa');
    }
}
