<?php

namespace App\Services\Order\Flows\Balcao;

use App\Services\Order\TotalCalculator;

/**
 * Validador do Fluxo Balcão
 *
 * Valida contrato de payload para venda direta.
 * Usa TotalCalculator para cálculos (mesmo método da Action).
 */
class BalcaoValidator
{
    /**
     * Valida payload do fluxo Balcão
     *
     * @param array $data Payload recebido
     * @return array Erros encontrados (vazio = válido)
     */
    public function validate(array $data): array
    {
        $errors = [];

        // 1. flow_type obrigatório
        if (($data['flow_type'] ?? '') !== 'balcao') {
            $errors['flow_type'] = 'flow_type deve ser "balcao"';
        }

        // 2. Carrinho obrigatório
        if (empty($data['cart']) || !is_array($data['cart'])) {
            $errors['cart'] = 'Carrinho não pode estar vazio';
        } else {
            // Validar itens do carrinho
            foreach ($data['cart'] as $index => $item) {
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

        // 3. Pagamento obrigatório para Balcão
        if (empty($data['payments']) || !is_array($data['payments'])) {
            $errors['payments'] = 'Pagamento obrigatório para venda balcão';
        } else {
            // Validar pagamentos
            foreach ($data['payments'] as $index => $payment) {
                if (!isset($payment['amount']) || floatval($payment['amount']) <= 0) {
                    $errors["payments.{$index}.amount"] = 'Valor do pagamento deve ser maior que zero';
                }
                if (empty($payment['method'])) {
                    $errors["payments.{$index}.method"] = 'Método de pagamento obrigatório';
                }
            }
        }

        // 4. Validar se pagamento cobre o total (usando TotalCalculator)
        if (empty($errors) && !empty($data['cart']) && !empty($data['payments'])) {
            $discount = floatval($data['discount'] ?? 0);

            if (!TotalCalculator::isPaymentSufficient($data['cart'], $data['payments'], $discount)) {
                $total = TotalCalculator::fromCart($data['cart'], $discount);
                $paid = TotalCalculator::fromPayments($data['payments']);
                $errors['payments'] = sprintf(
                    'Pagamento insuficiente. Total: R$ %.2f, Pago: R$ %.2f',
                    $total,
                    $paid
                );
            }
        }

        return $errors;
    }
}
