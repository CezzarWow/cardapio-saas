<?php

namespace App\Validators;

/**
 * CategoryValidator - Validação de Categorias
 */
class CategoryValidator
{
    /**
     * Valida criação de categoria
     */
    public function validateStore(array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Nome da categoria é obrigatório';
        }

        return $errors;
    }

    /**
     * Valida atualização de categoria
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
     * Valida ID para operações
     */
    public function validateId($id): array
    {
        $errors = [];
        if (empty($id) || intval($id) <= 0) {
            $errors['id'] = 'ID inválido';
        }
        return $errors;
    }

    public function hasErrors(array $errors): bool
    {
        return !empty($errors);
    }

    public function getFirstError(array $errors): string
    {
        return reset($errors) ?: 'Erro de validação';
    }
}
