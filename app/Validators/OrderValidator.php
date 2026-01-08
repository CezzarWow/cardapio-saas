<?php

namespace App\Validators;

/**
 * OrderValidator - Validação de Dados de Pedidos
 * 
 * Centraliza todas as validações relacionadas a pedidos:
 * criar, fechar mesa/comanda, remover item, cancelar, entregar
 */
class OrderValidator
{
    /**
     * Valida criação de pedido (store)
     */
    public function validateStore(array $data): array
    {
        $errors = [];

        if (empty($data['cart']) || !is_array($data['cart'])) {
            $errors['cart'] = 'Carrinho vazio ou inválido';
        }

        return $errors;
    }

    /**
     * Valida fechamento de mesa
     */
    public function validateCloseTable(array $data): array
    {
        $errors = [];

        if (empty($data['table_id'])) {
            $errors['table_id'] = 'Mesa não informada';
        }

        return $errors;
    }

    /**
     * Valida fechamento de comanda
     */
    public function validateCloseCommand(array $data): array
    {
        $errors = [];

        if (empty($data['order_id'])) {
            $errors['order_id'] = 'Pedido não informado';
        }

        return $errors;
    }

    /**
     * Valida remoção de item
     */
    public function validateRemoveItem(array $data): array
    {
        $errors = [];

        if (empty($data['item_id'])) {
            $errors['item_id'] = 'Item não informado';
        }

        if (empty($data['order_id'])) {
            $errors['order_id'] = 'Pedido não informado';
        }

        return $errors;
    }

    /**
     * Valida cancelamento de pedido
     */
    public function validateCancelOrder(array $data): array
    {
        $errors = [];

        if (empty($data['order_id'])) {
            $errors['order_id'] = 'Pedido não informado';
        }

        if (empty($data['table_id'])) {
            $errors['table_id'] = 'Mesa não informada';
        }

        return $errors;
    }

    /**
     * Valida entrega de pedido
     */
    public function validateDeliverOrder(array $data): array
    {
        $errors = [];

        if (empty($data['order_id'])) {
            $errors['order_id'] = 'ID do pedido não informado';
        }

        return $errors;
    }

    /**
     * Valida inclusão de itens em pedido pago
     */
    public function validateIncludePaidItems(array $data): array
    {
        $errors = [];

        if (empty($data['order_id'])) {
            $errors['order_id'] = 'Pedido não identificado';
        }

        if (empty($data['cart']) || !is_array($data['cart'])) {
            $errors['cart'] = 'Carrinho vazio';
        }

        return $errors;
    }

    /**
     * Verifica se há erros no array
     */
    public function hasErrors(array $errors): bool
    {
        return !empty($errors);
    }

    /**
     * Retorna a primeira mensagem de erro
     */
    public function getFirstError(array $errors): string
    {
        return reset($errors) ?: 'Erro de validação';
    }
}
