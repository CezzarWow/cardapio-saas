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
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $hash = $section;
        
        // Se houver query string, mantemos na URL principal (antes do hash)
        // O AdminSPA agora lê params da URL principal na inicialização
        $url = "{$baseUrl}/admin/loja/spa";
        if (!empty($queryString)) {
            $url .= "?{$queryString}";
        }
        $url .= "#{$hash}";

        header("Location: {$url}");
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
