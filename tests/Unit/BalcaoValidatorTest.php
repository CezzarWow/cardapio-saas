<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Balcao\BalcaoValidator;

/**
 * Testes unitários para BalcaoValidator
 */
class BalcaoValidatorTest extends TestCase
{
    private BalcaoValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new BalcaoValidator();
    }

    // ============ flow_type ============

    public function testRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'mesa',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'payments' => [['method' => 'pix', 'amount' => 10]]
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testRejectsMissingFlowType(): void
    {
        $errors = $this->validator->validate([
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'payments' => [['method' => 'pix', 'amount' => 10]]
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    // ============ cart ============

    public function testRejectsEmptyCart(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [],
            'payments' => [['method' => 'pix', 'amount' => 10]]
        ]);

        $this->assertArrayHasKey('cart', $errors);
    }

    public function testRejectsMissingCart(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'payments' => [['method' => 'pix', 'amount' => 10]]
        ]);

        $this->assertArrayHasKey('cart', $errors);
    }

    public function testRejectsCartItemWithoutId(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [['price' => 10, 'quantity' => 1]],
            'payments' => [['method' => 'pix', 'amount' => 10]]
        ]);

        $this->assertArrayHasKey('cart.0.id', $errors);
    }

    public function testRejectsCartItemWithZeroPrice(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [['id' => 1, 'price' => 0, 'quantity' => 1]],
            'payments' => [['method' => 'pix', 'amount' => 10]]
        ]);

        $this->assertArrayHasKey('cart.0.price', $errors);
    }

    // ============ payments ============

    public function testRejectsEmptyPayments(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'payments' => []
        ]);

        $this->assertArrayHasKey('payments', $errors);
    }

    public function testRejectsMissingPayments(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('payments', $errors);
    }

    public function testRejectsInsufficientPayment(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [['id' => 1, 'price' => 100, 'quantity' => 1]],
            'payments' => [['method' => 'pix', 'amount' => 50]]
        ]);

        $this->assertArrayHasKey('payments', $errors);
        $this->assertStringContainsString('insuficiente', $errors['payments']);
    }

    // ============ valid payload ============

    public function testAcceptsValidPayload(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [
                ['id' => 1, 'price' => 25.00, 'quantity' => 2],
                ['id' => 2, 'price' => 15.00, 'quantity' => 1]
            ],
            'payments' => [
                ['method' => 'pix', 'amount' => 65.00]
            ],
            'discount' => 0
        ]);

        $this->assertEmpty($errors, 'Payload válido não deve ter erros');
    }

    public function testAcceptsPayloadWithDiscount(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [['id' => 1, 'price' => 100, 'quantity' => 1]],
            'payments' => [['method' => 'dinheiro', 'amount' => 90]],
            'discount' => 10
        ]);

        $this->assertEmpty($errors, 'Pagamento deve cobrir total - desconto');
    }

    public function testAcceptsMultiplePayments(): void
    {
        $errors = $this->validator->validate([
            'flow_type' => 'balcao',
            'cart' => [['id' => 1, 'price' => 100, 'quantity' => 1]],
            'payments' => [
                ['method' => 'dinheiro', 'amount' => 50],
                ['method' => 'pix', 'amount' => 50]
            ]
        ]);

        $this->assertEmpty($errors, 'Múltiplos pagamentos devem ser aceitos');
    }
}
