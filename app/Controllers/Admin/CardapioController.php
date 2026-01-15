<?php

namespace App\Controllers\Admin;

use App\Core\Logger;
use App\Services\Admin\ComboService;
use App\Services\Cardapio\CardapioQueryService;
use App\Services\Cardapio\UpdateCardapioConfigService;
use App\Validators\CardapioValidator;
use Exception;

/**
 * CardapioController - Super Thin
 * Gerencia configurações e combos do cardápio web
 */
class CardapioController extends BaseController
{
    private CardapioQueryService $queryService;
    private UpdateCardapioConfigService $configService;
    private ComboService $comboService;
    private CardapioValidator $validator;

    public function __construct(
        CardapioQueryService $queryService,
        UpdateCardapioConfigService $configService,
        ComboService $comboService,
        CardapioValidator $validator
    ) {
        $this->queryService = $queryService;
        $this->configService = $configService;
        $this->comboService = $comboService;
        $this->validator = $validator;
    }

    /**
     * Listagem principal e Configurações
     */
    public function index(): void
    {
        $restaurantId = $this->getRestaurantId();

        $data = $this->queryService->getIndexData($restaurantId);
        extract($data);

        View::renderFromScope('admin/cardapio/index', get_defined_vars());
    }

    /**
     * Atualizar configurações
     */
    public function update(): void
    {
        $restaurantId = $this->getRestaurantId();

        // Validação (opcional por enquanto, mas estrutura pronta)
        // $errors = $this->validator->validateConfig($_POST);

        try {
            $this->configService->execute($restaurantId, $_POST);

            if (class_exists(Logger::class)) {
                Logger::info('Configurações do cardápio atualizadas', ['restaurant_id' => $restaurantId]);
            }

            $this->redirect('/admin/loja/cardapio?success=salvo#destaques');
        } catch (Exception $e) {
            error_log('CardapioConfig::update Error: ' . $e->getMessage());
            $this->redirect('/admin/loja/cardapio?error=falha_salvar');
        }
    }

    /**
     * Formulário de criação de Combo
     */
    public function comboForm(): void
    {
        $restaurantId = $this->getRestaurantId();

        $combo = null;
        $comboProducts = [];
        $comboItemsSettings = []; // Configurações vazias padrão

        $data = $this->queryService->getComboFormData($restaurantId);
        $products = $data['products'];

        View::renderFromScope('admin/cardapio/combo_form', get_defined_vars());
    }

    /**
     * Salvar Combo
     */
    public function storeCombo(): void
    {
        $restaurantId = $this->getRestaurantId();

        $errors = $this->validator->validateCombo($_POST);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin/loja/cardapio/combo/criar?error=' . urlencode($this->validator->getFirstError($errors)));
        }

        try {
            $this->comboService->store($_POST, $restaurantId);
            $this->redirect('/admin/loja/cardapio?success=combo_criado');
        } catch (Exception $e) {
            error_log('Combo::store Error: ' . $e->getMessage());
            $this->redirect('/admin/loja/cardapio?error=falha_criar_combo');
        }
    }

    /**
     * Editar Combo (View e AJAX)
     */
    public function editCombo(): void
    {
        $restaurantId = $this->getRestaurantId();
        $id = $this->getInt('id');
        $isAjax = isset($_GET['json']) && $_GET['json'] == '1';

        $errors = $this->validator->validateId($id);
        if ($this->validator->hasErrors($errors)) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => $this->validator->getFirstError($errors)], 400);
            }
            $this->redirect('/admin/loja/cardapio?error=id_invalido');
            return; // Garante retorno
        }

        $result = $this->comboService->getForEdit($id, $restaurantId);

        if (!$result) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Combo não encontrado'], 404);
            }
            $this->redirect('/admin/loja/cardapio?error=combo_nao_encontrado');
            return; // Garante retorno
        }

        // Se for AJAX, retorna JSON
        if ($isAjax) {
            $this->json([
                'success' => true,
                'combo' => $result['combo'],
                'items' => $result['items'],
                'settings' => $result['comboItemsSettings']
            ]);
        }

        // Se for View tradicional
        $combo = $result['combo'];
        $comboProducts = $result['comboProducts'];
        $comboItemsSettings = $result['comboItemsSettings'];

        // Produtos para o select
        $data = $this->queryService->getComboFormData($restaurantId);
        $products = $data['products'];

        View::renderFromScope('admin/cardapio/combo_form', get_defined_vars());
    }

    /**
     * Atualizar Combo
     */
    public function updateCombo(): void
    {
        $restaurantId = $this->getRestaurantId();
        $id = $this->postInt('id');

        $errors = $this->validator->validateCombo($_POST);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin/loja/cardapio/combo/editar?id=' . $id . '&error=' . urlencode($this->validator->getFirstError($errors)));
        }

        try {
            $this->comboService->update($id, $_POST, $restaurantId);
            $this->redirect('/admin/loja/cardapio?success=combo_atualizado');
        } catch (Exception $e) {
            error_log('Combo::update Error: ' . $e->getMessage());
            $this->redirect('/admin/loja/cardapio/combo/editar?id=' . $id . '&error=falha_atualizar_combo');
        }
    }

    /**
     * Deletar Combo
     */
    public function deleteCombo(): void
    {
        $restaurantId = $this->getRestaurantId();
        $id = $this->getInt('id');

        $errors = $this->validator->validateId($id);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin/loja/cardapio?error=id_invalido');
        }

        try {
            $this->comboService->delete($id, $restaurantId);
            $this->redirect('/admin/loja/cardapio?success=combo_deletado');
        } catch (Exception $e) {
            error_log('Combo::delete Error: ' . $e->getMessage());
            $this->redirect('/admin/loja/cardapio?error=falha_deletar_combo');
        }
    }

    /**
     * Alternar Status do Combo (AJAX)
     */
    public function toggleComboStatus(): void
    {
        $restaurantId = $this->getRestaurantId();
        $data = $this->getJsonBody();

        $errors = $this->validator->validateToggleStatus($data);
        if ($this->validator->hasErrors($errors)) {
            $this->json(['success' => false, 'error' => $this->validator->getFirstError($errors)], 400);
        }

        try {
            $active = !empty($data['active']);
            $success = $this->comboService->toggleStatus((int)$data['id'], $active, $restaurantId);
            $this->json(['success' => $success]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
