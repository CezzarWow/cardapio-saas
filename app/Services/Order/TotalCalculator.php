<?php

namespace App\Services\Order;

/**
 * Calculador de Total do Carrinho
 *
 * Método reutilizável entre Validators e Actions.
 * Evita divergência silenciosa entre validação e execução.
 */
final class TotalCalculator
{
    /**
     * Calcula o total a partir do carrinho e desconto
     *
     * @param array $cart Array de itens [{price, quantity}, ...]
     * @param float $discount Valor do desconto
     * @return float Total final (nunca negativo)
     */
    public static function fromCart(array $cart, float $discount = 0): float
    {
        $subtotal = 0;

        foreach ($cart as $item) {
            $price = floatval($item['price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 1);
            $subtotal += $price * $quantity;
        }

        return max(0, $subtotal - $discount);
    }

    /**
     * Calcula o total pago a partir dos pagamentos
     *
     * @param array $payments Array de pagamentos [{amount}, ...]
     * @return float Total pago
     */
    public static function fromPayments(array $payments): float
    {
        $total = 0;

        foreach ($payments as $payment) {
            $total += floatval($payment['amount'] ?? 0);
        }

        return $total;
    }

    /**
     * Verifica se o pagamento cobre o total
     */
    public static function isPaymentSufficient(array $cart, array $payments, float $discount = 0): bool
    {
        $total = self::fromCart($cart, $discount);
        $paid = self::fromPayments($payments);

        return $paid >= $total;
    }
}
