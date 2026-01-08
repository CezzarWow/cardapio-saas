<?php

namespace App\Controllers\Admin;

use App\Services\RestaurantService;

/**
 * AutologinController - Super Thin
 * Gerencia a seleção de loja pelo painel administrativo
 */
class AutologinController extends BaseController
{
    private RestaurantService $service;

    public function __construct() {
        $this->service = new RestaurantService();
    }

    public function login() {
        // Valida sessão de usuário (login geral)
        $userId = $this->getUserId();
        
        // Pega ID da loja
        $restaurantId = $this->getInt('id');

        if ($restaurantId <= 0) {
            $this->redirect('/admin?error=loja_invalida');
        }

        // Busca loja verificando propriedade
        // Poderíamos usar $service->findById($id), mas precisamos checar o user_id
        // Para ser RESTRICT, vou fazer o select via service filtrado, ou buscar e comparar
        
        $loja = $this->service->findById($restaurantId);

        if ($loja && (int)$loja['user_id'] === $userId) {
            // A MÁGICA: Salva na sessão que estamos gerenciando ESSA loja
            $_SESSION['loja_ativa_id'] = $loja['id'];
            $_SESSION['loja_ativa_nome'] = $loja['name'];
            $_SESSION['loja_ativa_logo'] = $loja['logo'] ?? null; 

            // Redireciona para o Painel da Loja (dashboard PDV ou Config)
            $this->redirect('/admin/loja/painel'); 
        } else {
            // Loja não encontrada ou não pertence ao usuário
            $this->redirect('/admin?error=acesso_negado');
        }
    }
}
