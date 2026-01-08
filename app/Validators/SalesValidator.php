<?php

namespace App\Validators;

/**
 * SalesValidator - Validação de Dados de Vendas
 */
class SalesValidator
{
    /**
     * Valida ID de pedido para operações
     */
    public function validateOrderId(array $data): array
    {
        $errors = [];

        if (empty($data['id']) || intval($data['id']) <= 0) {
            $errors['id'] = 'ID do pedido inválido';
        }

        return $errors;
    }

    /**
     * Valida ID via GET
     */
    public function validateGetId($id): array
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
