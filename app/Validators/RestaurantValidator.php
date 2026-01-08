<?php

namespace App\Validators;

/**
 * RestaurantValidator - Validação de Dados de Restaurantes
 */
class RestaurantValidator
{
    /**
     * Valida criação de restaurante
     */
    public function validateStore(array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Nome é obrigatório';
        }

        if (empty(trim($data['slug'] ?? ''))) {
            $errors['slug'] = 'Slug é obrigatório';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
            $errors['slug'] = 'Slug deve conter apenas letras minúsculas, números e hífens';
        }

        return $errors;
    }

    /**
     * Valida atualização de restaurante
     */
    public function validateUpdate(array $data): array
    {
        $errors = $this->validateStore($data);

        if (empty($data['id']) || intval($data['id']) <= 0) {
            $errors['id'] = 'ID inválido';
        }

        return $errors;
    }

    /**
     * Valida ID para operações de leitura/delete
     */
    public function validateId($id): array
    {
        $errors = [];

        if (empty($id) || intval($id) <= 0) {
            $errors['id'] = 'ID inválido';
        }

        return $errors;
    }

    /**
     * Verifica se há erros
     */
    public function hasErrors(array $errors): bool
    {
        return !empty($errors);
    }

    /**
     * Retorna primeira mensagem de erro
     */
    public function getFirstError(array $errors): string
    {
        return reset($errors) ?: 'Erro de validação';
    }
}
