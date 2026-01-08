<?php

namespace App\Controllers\Admin;

use App\Services\ConfigService;
use App\Validators\ConfigValidator;
use Exception;

/**
 * ConfigController - Super Thin
 * Gerencia configurações gerais da loja (nome, logo, contato)
 */
class ConfigController extends BaseController
{
    private ConfigService $service;
    private ConfigValidator $validator;

    public function __construct() {
        $this->service = new ConfigService();
        $this->validator = new ConfigValidator();
    }

    /**
     * Tela de Configurações
     */
    public function index(): void {
        $restaurantId = $this->getRestaurantId();
        
        $loja = $this->service->getStoreData($restaurantId);

        require __DIR__ . '/../../../views/admin/config/index.php';
    }

    /**
     * Atualizar Configurações
     */
    public function update(): void {
        $restaurantId = $this->getRestaurantId();
        
        // Coleta arquivo de upload se houver
        $file = !empty($_FILES['logo']) ? $_FILES['logo'] : null;

        // Validação
        $errors = $this->validator->validateUpdate($_POST, $file);
        if ($this->validator->hasErrors($errors)) {
            // Como o form original não parece ter exibição de erro por campo detalhado, 
            // e o anterior usava alert JS, vamos mandar o primeiro erro na URL
            // ou poderíamos injetar um script se fosse crítico, mas padronizaremos.
            $msg = urlencode($this->validator->getFirstError($errors));
            // Usamos um script inline para manter comportamento similar ao alert se desejado,
            // ou apenas redirect com error param. Vou usar redirect com param para consistência.
            $this->redirect("/admin/loja/config?error={$msg}");
            return;
        }

        try {
            // Atualiza
            $result = $this->service->updateConfig($restaurantId, $_POST, $file);

            // Atualiza Sessão (para refletir mudanças imediatamente no header do admin)
            if (!empty($result['name'])) {
                $_SESSION['loja_ativa_nome'] = $result['name'];
            }
            if (!empty($result['logo'])) {
                $_SESSION['loja_ativa_logo'] = $result['logo'];
            }

            $this->redirect('/admin/loja/config?success=Configurações salvas!');

        } catch (Exception $e) {
            error_log('ConfigController::update Error: ' . $e->getMessage());
            $this->redirect('/admin/loja/config?error=Falha ao salvar configurações');
        }
    }
}
