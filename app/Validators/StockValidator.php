<?php

namespace App\Validators;

/**
 * StockValidator - Validação e Sanitização de Produtos (Estoque)
 */
class StockValidator
{
    public function validateProduct(array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Nome do produto é obrigatório';
        }

        $price = str_replace(',', '.', $data['price'] ?? '');
        if (!is_numeric($price) || floatval($price) < 0) {
            $errors['price'] = 'Preço deve ser um número válido';
        }

        if (empty($data['category_id']) || intval($data['category_id']) <= 0) {
            $errors['category_id'] = 'Categoria é obrigatória';
        }

        return $errors;
    }

    public function validateProductUpdate(array $data): array
    {
        $errors = $this->validateProduct($data);

        if (empty($data['id']) || intval($data['id']) <= 0) {
            $errors['id'] = 'ID do produto é inválido';
        }

        return $errors;
    }

    public function validateReposition(array $data): array
    {
        $errors = [];

        $productId = intval($data['product_id'] ?? 0);
        $amount = intval($data['amount'] ?? 0);

        if ($productId <= 0) {
            $errors['product_id'] = 'Produto inválido';
        }

        if ($amount == 0) {
            $errors['amount'] = 'Quantidade não pode ser zero';
        }

        return $errors;
    }

    public function sanitizeProduct(array $data): array
    {
        return [
            'id' => intval($data['id'] ?? 0),
            'name' => trim(strip_tags($data['name'] ?? '')),
            'price' => number_format(floatval(str_replace(',', '.', $data['price'] ?? 0)), 2, '.', ''),
            'category_id' => intval($data['category_id'] ?? 0),
            'description' => trim(strip_tags($data['description'] ?? '')),
            'stock' => intval($data['stock'] ?? 0),
            'icon' => trim($data['icon'] ?? 'package'),
            'icon_as_photo' => isset($data['icon_as_photo']) ? 1 : 0,
            'additional_groups' => array_map('intval', $data['additional_groups'] ?? [])
        ];
    }

    public function hasErrors(array $errors): bool
    {
        return !empty($errors);
    }
}
