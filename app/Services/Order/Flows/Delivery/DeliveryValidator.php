<?php

namespace App\Services\Order\Flows\Delivery;

use App\Services\Order\TotalCalculator;

/**
 * Validador do Fluxo Delivery
 *
 * Valida contratos de payload para operações de delivery.
 * DELIVERY é pedido independente, NÃO cria conta aberta.
 */
class DeliveryValidator
{
    /**
     * Valida payload para CRIAR delivery
     *
     * @param array $data Payload recebido
     * @return array Erros encontrados (vazio = válido)
     */
    public function validateCreate(array $data): array
    {
        $errors = [];

        // 1. flow_type obrigatório
        if (($data['flow_type'] ?? '') !== 'delivery') {
            $errors['flow_type'] = 'flow_type deve ser "delivery"';
        }

        // 2. Carrinho obrigatório
        if (empty($data['cart']) || !is_array($data['cart'])) {
            $errors['cart'] = 'Carrinho não pode estar vazio';
        } else {
            $this->validateCartItems($data['cart'], $errors);
        }

        // 3. Endereço obrigatório para delivery
        if (empty($data['address'])) {
            $errors['address'] = 'Endereço é obrigatório para delivery';
        }

        // 4. Cliente obrigatório (nome ou ID)
        if (empty($data['client_name']) && empty($data['client_id'])) {
            $errors['client'] = 'Nome ou ID do cliente é obrigatório';
        }

        // NOTA: Pagamento pode ser opcional (depende da regra do restaurante)
        // Se payments for informado, valida
        if (!empty($data['payments'])) {
            $this->validatePayments($data['payments'], $errors);
        }

        return $errors;
    }

    /**
     * Valida payload para ATUALIZAR STATUS
     */
    public function validateStatusUpdate(array $data): array
    {
        $errors = [];

        // 1. flow_type
        if (($data['flow_type'] ?? '') !== 'delivery_status') {
            $errors['flow_type'] = 'flow_type deve ser "delivery_status"';
        }

        // 2. order_id obrigatório
        if (empty($data['order_id']) || !is_numeric($data['order_id'])) {
            $errors['order_id'] = 'ID do pedido é obrigatório';
        }

        // 3. new_status obrigatório
        if (empty($data['new_status'])) {
            $errors['new_status'] = 'Novo status é obrigatório';
        }

        return $errors;
    }

    /**
     * Valida itens do carrinho
     */
    private function validateCartItems(array $cart, array &$errors): void
    {
        foreach ($cart as $index => $item) {
            if (empty($item['id'])) {
                $errors["cart.{$index}.id"] = 'ID do produto obrigatório';
            }
            if (!isset($item['price']) || floatval($item['price']) <= 0) {
                $errors["cart.{$index}.price"] = 'Preço deve ser maior que zero';
            }
            if (!isset($item['quantity']) || intval($item['quantity']) < 1) {
                $errors["cart.{$index}.quantity"] = 'Quantidade mínima é 1';
            }
        }
    }

    /**
     * Valida pagamentos
     */
    private function validatePayments(array $payments, array &$errors): void
    {
        foreach ($payments as $index => $payment) {
            if (!isset($payment['amount']) || floatval($payment['amount']) <= 0) {
                $errors["payments.{$index}.amount"] = 'Valor do pagamento deve ser maior que zero';
            }
            if (empty($payment['method'])) {
                $errors["payments.{$index}.method"] = 'Método de pagamento obrigatório';
            }
        }
    }

    /**
     * Valida se pagamento cobre o total
     */
    public function validatePaymentCoversTotal(float $orderTotal, array $payments): array
    {
        $errors = [];
        $paid = TotalCalculator::fromPayments($payments);

        if ($paid < $orderTotal) {
            $errors['payments'] = sprintf(
                'Pagamento insuficiente. Total: R$ %.2f, Pago: R$ %.2f',
                $orderTotal,
                $paid
            );
        }

        return $errors;
    }
}
