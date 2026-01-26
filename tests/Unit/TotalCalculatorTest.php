<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\TotalCalculator;

/**
 * Testes unitários para TotalCalculator
 */
class TotalCalculatorTest extends TestCase
{
    public function testFromCartCalculatesCorrectly(): void
    {
        $cart = [
            ['price' => 10.00, 'quantity' => 2],
            ['price' => 5.00, 'quantity' => 3]
        ];

        $total = TotalCalculator::fromCart($cart, 0);

        $this->assertEquals(35.00, $total); // (10*2) + (5*3) = 35
    }

    public function testFromCartWithDiscount(): void
    {
        $cart = [
            ['price' => 10.00, 'quantity' => 2]
        ];

        $total = TotalCalculator::fromCart($cart, 5.00);

        $this->assertEquals(15.00, $total); // (10*2) - 5 = 15
    }

    public function testFromCartNeverReturnsNegative(): void
    {
        $cart = [
            ['price' => 10.00, 'quantity' => 1]
        ];

        $total = TotalCalculator::fromCart($cart, 100.00);

        $this->assertEquals(0.00, $total); // Não pode ser negativo
    }

    public function testFromCartWithEmptyCart(): void
    {
        $total = TotalCalculator::fromCart([], 0);

        $this->assertEquals(0.00, $total);
    }

    public function testFromPaymentsCalculatesCorrectly(): void
    {
        $payments = [
            ['amount' => 50.00],
            ['amount' => 30.00],
            ['amount' => 20.00]
        ];

        $total = TotalCalculator::fromPayments($payments);

        $this->assertEquals(100.00, $total);
    }

    public function testFromPaymentsWithEmptyArray(): void
    {
        $total = TotalCalculator::fromPayments([]);

        $this->assertEquals(0.00, $total);
    }

    public function testIsPaymentSufficientReturnsTrue(): void
    {
        $cart = [
            ['price' => 10.00, 'quantity' => 2]
        ];
        $payments = [
            ['amount' => 25.00]
        ];

        $result = TotalCalculator::isPaymentSufficient($cart, $payments, 0);

        $this->assertTrue($result); // 25 >= 20
    }

    public function testIsPaymentSufficientReturnsFalse(): void
    {
        $cart = [
            ['price' => 10.00, 'quantity' => 2]
        ];
        $payments = [
            ['amount' => 15.00]
        ];

        $result = TotalCalculator::isPaymentSufficient($cart, $payments, 0);

        $this->assertFalse($result); // 15 < 20
    }

    public function testIsPaymentSufficientWithDiscount(): void
    {
        $cart = [
            ['price' => 10.00, 'quantity' => 2]
        ];
        $payments = [
            ['amount' => 15.00]
        ];

        $result = TotalCalculator::isPaymentSufficient($cart, $payments, 5.00);

        $this->assertTrue($result); // 15 >= (20 - 5) = 15
    }
}
