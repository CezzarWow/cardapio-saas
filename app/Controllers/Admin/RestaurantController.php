<?php

namespace App\Controllers\Admin;

use App\Services\RestaurantService;
use App\Validators\RestaurantValidator;
use Exception;

/**
 * RestaurantController - Super Thin
 * 
 * Gerencia CRUD de restaurantes.
 * Lógica de negócio no RestaurantService.
 * Validações no RestaurantValidator.
 */
class RestaurantController extends BaseController
{
    private RestaurantService $service;
    private RestaurantValidator $validator;

    public function __construct()
    {
        $this->service = new RestaurantService();
        $this->validator = new RestaurantValidator();
    }

    /**
     * Formulário de criação
     */
    public function create(): void
    {
        require __DIR__ . '/../../../views/admin/restaurants/create.php';
    }

    /**
     * Salvar novo restaurante
     */
    public function store(): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'slug' => $_POST['slug'] ?? ''
        ];

        $errors = $this->validator->validateStore($data);
        if ($this->validator->hasErrors($errors)) {
            // Redireciona de volta com erro
            $this->redirect('/admin/restaurantes/criar?error=' . urlencode($this->validator->getFirstError($errors)));
        }

        try {
            $userId = $this->getUserId();
            $restaurantId = $this->service->create($data, $userId);
            
            $this->redirect('/admin?success=restaurante_criado');
        } catch (Exception $e) {
            error_log('RestaurantController::store Error: ' . $e->getMessage());
            $this->redirect('/admin/restaurantes/criar?error=falha_ao_criar');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit(): void
    {
        $id = $this->getInt('id');
        
        $errors = $this->validator->validateId($id);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin?error=id_invalido');
        }

        $restaurant = $this->service->findById($id);
        
        if (!$restaurant) {
            $this->redirect('/admin?error=restaurante_nao_encontrado');
        }

        require __DIR__ . '/../../../views/admin/restaurants/edit.php';
    }

    /**
     * Atualizar restaurante
     */
    public function update(): void
    {
        $data = [
            'id' => $_POST['id'] ?? 0,
            'name' => $_POST['name'] ?? '',
            'slug' => $_POST['slug'] ?? ''
        ];

        $errors = $this->validator->validateUpdate($data);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin/restaurantes/editar?id=' . $data['id'] . '&error=validacao_falhou');
        }

        try {
            $this->service->update((int)$data['id'], $data);
            $this->redirect('/admin?success=restaurante_atualizado');
        } catch (Exception $e) {
            error_log('RestaurantController::update Error: ' . $e->getMessage());
            $this->redirect('/admin/restaurantes/editar?id=' . $data['id'] . '&error=falha_ao_atualizar');
        }
    }

    /**
     * Deletar restaurante
     */
    public function delete(): void
    {
        $id = $this->getInt('id');

        $errors = $this->validator->validateId($id);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin?error=id_invalido');
        }

        try {
            $this->service->delete($id);
            $this->redirect('/admin?success=restaurante_deletado');
        } catch (Exception $e) {
            error_log('RestaurantController::delete Error: ' . $e->getMessage());
            $this->redirect('/admin?error=falha_ao_deletar');
        }
    }

    /**
     * Alternar status ativo/inativo
     */
    public function toggleStatus(): void
    {
        $id = $this->getInt('id');

        $errors = $this->validator->validateId($id);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin?error=id_invalido');
        }

        try {
            $this->service->toggleStatus($id);
            $this->redirect('/admin');
        } catch (Exception $e) {
            error_log('RestaurantController::toggleStatus Error: ' . $e->getMessage());
            $this->redirect('/admin?error=falha_ao_alterar_status');
        }
    }
}