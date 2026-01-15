<?php

namespace App\Validators;

/**
 * AdditionalValidator - Validação e Sanitização de dados de Adicionais
 *
 * Responsabilidades:
 * - Validar dados de entrada (grupos, itens, vínculos)
 * - Sanitizar dados antes de enviar para Services
 * - Retornar erros estruturados
 */
class AdditionalValidator
{
    // ==========================================
    // VALIDAÇÃO DE GRUPO
    // ==========================================

    /**
     * Valida dados para criar/atualizar grupo
     * @return array Erros encontrados (vazio = válido)
     */
    public function validateGroup(array $data): array
    {
        $errors = [];

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $errors['name'] = 'Nome do grupo é obrigatório';
        } elseif (strlen($name) > 100) {
            $errors['name'] = 'Nome do grupo deve ter no máximo 100 caracteres';
        }

        return $errors;
    }

    /**
     * Sanitiza dados do grupo
     */
    public function sanitizeGroup(array $data): array
    {
        return [
            'name' => trim(strip_tags($data['name'] ?? '')),
            'item_ids' => $this->sanitizeIntArray($data['item_ids'] ?? [])
        ];
    }

    // ==========================================
    // VALIDAÇÃO DE ITEM
    // ==========================================

    /**
     * Valida dados para criar item
     * @return array Erros encontrados (vazio = válido)
     */
    public function validateItem(array $data): array
    {
        $errors = [];

        // Nome obrigatório
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $errors['name'] = 'Nome do item é obrigatório';
        } elseif (strlen($name) > 100) {
            $errors['name'] = 'Nome do item deve ter no máximo 100 caracteres';
        }

        // Preço válido
        $price = $data['price'] ?? '';
        if ($price === '' || $price === null) {
            $errors['price'] = 'Preço é obrigatório';
        } elseif (!is_numeric($price)) {
            $errors['price'] = 'Preço deve ser um número válido';
        } elseif (floatval($price) < 0) {
            $errors['price'] = 'Preço não pode ser negativo';
        }

        return $errors;
    }

    /**
     * Valida dados para atualizar item (inclui ID)
     */
    public function validateItemUpdate(array $data): array
    {
        $errors = $this->validateItem($data);

        $id = $data['id'] ?? 0;
        if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
            $errors['id'] = 'ID do item é inválido';
        }

        return $errors;
    }

    /**
     * Sanitiza dados do item
     */
    public function sanitizeItem(array $data): array
    {
        return [
            'id' => intval($data['id'] ?? 0),
            'name' => trim(strip_tags($data['name'] ?? '')),
            'price' => number_format(floatval($data['price'] ?? 0), 2, '.', ''),
            'group_ids' => $this->sanitizeIntArray($data['group_ids'] ?? [])
        ];
    }

    // ==========================================
    // VALIDAÇÃO DE VÍNCULOS
    // ==========================================

    /**
     * Valida dados para vincular item a grupo
     */
    public function validateLink(array $data): array
    {
        $errors = [];

        if (empty($data['group_id']) || intval($data['group_id']) <= 0) {
            $errors['group_id'] = 'Grupo é obrigatório';
        }

        if (empty($data['item_id']) || intval($data['item_id']) <= 0) {
            $errors['item_id'] = 'Item é obrigatório';
        }

        return $errors;
    }

    /**
     * Valida dados para vincular múltiplos itens
     */
    public function validateMultipleLink(array $data): array
    {
        $errors = [];

        if (empty($data['group_id']) || intval($data['group_id']) <= 0) {
            $errors['group_id'] = 'Grupo é obrigatório';
        }

        $itemIds = $data['item_ids'] ?? [];
        if (empty($itemIds) || !is_array($itemIds)) {
            $errors['item_ids'] = 'Selecione pelo menos um item';
        }

        return $errors;
    }

    /**
     * Valida dados para vincular categorias
     */
    public function validateCategoryLink(array $data): array
    {
        $errors = [];

        if (empty($data['group_id']) || intval($data['group_id']) <= 0) {
            $errors['group_id'] = 'Grupo é obrigatório';
        }

        // category_ids pode ser vazio (desvincular todas)

        return $errors;
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Sanitiza array para conter apenas inteiros positivos
     */
    private function sanitizeIntArray(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        return array_values(array_filter(
            array_map('intval', $input),
            fn ($v) => $v > 0
        ));
    }

    /**
     * Verifica se há erros
     */
    public function hasErrors(array $errors): bool
    {
        return !empty($errors);
    }

    /**
     * Formata erros para exibição
     */
    public function formatErrors(array $errors): string
    {
        return implode(', ', array_values($errors));
    }
}
