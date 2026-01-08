<?php

namespace App\Validators;

/**
 * CardapioValidator - Validação de Dados do Cardápio
 */
class CardapioValidator
{
    /**
     * Valida configurações do cardápio
     */
    public function validateConfig(array $data): array
    {
        $errors = [];
        
        // Aqui poderíamos validar campos específicos de config se necessário
        // Por enquanto, aceitamos tudo como string opcional, mas vamos garantir sanidade básica
        
        return $errors;
    }

    /**
     * Valida criação/atualização de combo
     */
    public function validateCombo(array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Nome do combo é obrigatório';
        }

        if (empty($data['products']) || !is_array($data['products'])) {
            $errors['products'] = 'Selecione pelo menos um produto';
        }

        // Validação de preço
        $price = $data['price'] ?? 0;
        if (is_string($price)) {
            $price = str_replace(',', '.', $price);
            $price = preg_replace('/[^\d.]/', '', $price);
        }
        if (!is_numeric($price) || floatval($price) < 0) {
            $errors['price'] = 'Preço inválido';
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

    /**
     * Valida toggle status
     */
    public function validateToggleStatus(array $data): array
    {
        $errors = [];
        if (empty($data['id']) || intval($data['id']) <= 0) {
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
