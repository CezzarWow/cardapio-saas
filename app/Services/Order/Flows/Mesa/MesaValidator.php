<?php

namespace App\Services\Order\Flows\Mesa;

use App\Services\Order\TotalCalculator;

/**
 * Validador do Fluxo Mesa
 *
 * Valida contratos de payload para operações de mesa.
 * Usa TotalCalculator para cálculos (mesmo padrão do Balcão).
 */
class MesaValidator
{
    /**
     * Valida payload para ABRIR mesa
     *
     * @param array $data Payload recebido
     * @return array Erros encontrados (vazio = válido)
     */
    public function validateOpen(array $data): array
    {
        $errors = [];

        // 1. flow_type obrigatório
        if (($data['flow_type'] ?? '') !== 'mesa') {
            $errors['flow_type'] = 'flow_type deve ser "mesa"';
        }

        // 2. table_id obrigatório
        if (empty($data['table_id']) || !is_numeric($data['table_id'])) {
            $errors['table_id'] = 'ID da mesa é obrigatório';
        }

        // 3. Carrinho obrigatório para abrir
        if (empty($data['cart']) || !is_array($data['cart'])) {
            $errors['cart'] = 'Carrinho não pode estar vazio para abrir mesa';
        } else {
            $this->validateCartItems($data['cart'], $errors);
        }

        // NOTA: Pagamento NÃO é obrigatório na abertura

        return $errors;
    }

    /**
     * Valida payload para ADICIONAR ITENS à mesa
     *
     * @param array $data Payload recebido
     * @return array Erros encontrados
     */
    public function validateAddItems(array $data): array
    {
        $errors = [];

        // 1. flow_type
        if (($data['flow_type'] ?? '') !== 'mesa_add') {
            $errors['flow_type'] = 'flow_type deve ser "mesa_add"';
        }

        // 2. order_id obrigatório (mesa já aberta)
        if (empty($data['order_id']) || !is_numeric($data['order_id'])) {
            $errors['order_id'] = 'ID do pedido é obrigatório';
        }

        // 3. Carrinho obrigatório
        if (empty($data['cart']) || !is_array($data['cart'])) {
            $errors['cart'] = 'Carrinho não pode estar vazio';
        } else {
            $this->validateCartItems($data['cart'], $errors);
        }

        return $errors;
    }

    /**
     * Valida payload para FECHAR mesa
     *
     * @param array $data Payload recebido
     * @return array Erros encontrados
     */
    public function validateClose(array $data): array
    {
        $errors = [];

        // 1. flow_type
        if (($data['flow_type'] ?? '') !== 'mesa_fechar') {
            $errors['flow_type'] = 'flow_type deve ser "mesa_fechar"';
        }

        // 2. table_id obrigatório
        if (empty($data['table_id']) || !is_numeric($data['table_id'])) {
            $errors['table_id'] = 'ID da mesa é obrigatório';
        }

        // 3. Pagamento OBRIGATÓRIO para fechar
        if (empty($data['payments']) || !is_array($data['payments'])) {
            $errors['payments'] = 'Pagamento obrigatório para fechar mesa';
        } else {
            $this->validatePayments($data['payments'], $errors);
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
     * Valida se pagamento cobre o total da conta
     *
     * @param float $orderTotal Total da conta
     * @param array $payments Pagamentos informados
     * @return array Erros se pagamento insuficiente
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
