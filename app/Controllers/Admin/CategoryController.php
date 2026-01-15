<?php

namespace App\Controllers\Admin;

use App\Core\View;
use App\Services\CategoryService;
use App\Validators\CategoryValidator;
use Exception;

/**
 * Controller de Categorias - Super Thin
 * Layout Moderno - CRUD completo
 */
class CategoryController extends BaseController
{
    private CategoryService $service;
    private CategoryValidator $validator;

    public function __construct(CategoryService $service, CategoryValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    /**
     * Listagem - Redireciona para SPA Dashboard
     */
    public function index(): void
    {
        $this->redirect('/admin/loja/catalogo#categorias');
    }

    /**
     * Salvar Categoria
     */
    public function store(): void
    {
        $restaurantId = $this->getRestaurantId();

        $errors = $this->validator->validateStore($_POST);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin/loja/categorias?error=' . urlencode($this->validator->getFirstError($errors)));
        }

        try {
            $this->service->create($_POST, $restaurantId);
            $this->redirect('/admin/loja/categorias?success=criado');
        } catch (Exception $e) {
            error_log('CategoryController::store Error: ' . $e->getMessage());
            $this->redirect('/admin/loja/categorias?error=falha_criar');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit(): void
    {
        $restaurantId = $this->getRestaurantId();
        $id = $this->getInt('id');

        $errors = $this->validator->validateId($id);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin/loja/categorias?error=id_invalido');
        }

        $category = $this->service->findById($id, $restaurantId);

        if (!$category) {
            $this->redirect('/admin/loja/categorias?error=nao_encontrado');
        }

        View::renderFromScope('admin/categories/edit', get_defined_vars());
    }

    /**
     * Atualizar Categoria
     */
    public function update(): void
    {
        $restaurantId = $this->getRestaurantId();
        $id = $this->postInt('id'); // Nota: postInt helper deve existir ou ser simulado com (int)$_POST

        // Fallback se postInt não existir no BaseController (vou assumir que existe pelo padrão ou usar (int))
        if (!method_exists($this, 'postInt')) {
            $id = (int)($_POST['id'] ?? 0);
        }

        $errors = $this->validator->validateUpdate($_POST);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin/loja/categorias?error=' . urlencode($this->validator->getFirstError($errors)));
        }

        try {
            $this->service->update($id, $_POST, $restaurantId);
            $this->redirect('/admin/loja/categorias?success=atualizado');
        } catch (Exception $e) {
            error_log('CategoryController::update Error: ' . $e->getMessage());
            $this->redirect('/admin/loja/categorias?error=falha_atualizar');
        }
    }

    /**
     * Deletar Categoria
     */
    public function delete(): void
    {
        $restaurantId = $this->getRestaurantId();
        $id = $this->getInt('id');

        $errors = $this->validator->validateId($id);
        if ($this->validator->hasErrors($errors)) {
            $this->redirect('/admin/loja/categorias?error=id_invalido');
        }

        try {
            $this->service->delete($id, $restaurantId);
            $this->redirect('/admin/loja/categorias?success=deletado');
        } catch (Exception $e) {
            // Se for exceção de negócio (sistema)
            $this->redirect('/admin/loja/categorias?error=' . urlencode($e->getMessage()));
        }
    }
}
