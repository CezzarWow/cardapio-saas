<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Comanda\ComandaValidator;

/**
 * Testes unitários para ComandaValidator
 */
class ComandaValidatorTest extends TestCase
{
    private ComandaValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ComandaValidator();
    }

    // ============ validateOpen ============

    public function testOpenRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'mesa',
            'client_id' => 1,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testOpenRejectsMissingClientId(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'comanda',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('client_id', $errors);
    }

    public function testOpenRejectsEmptyCart(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'comanda',
            'client_id' => 1,
            'cart' => []
        ]);

        $this->assertArrayHasKey('cart', $errors);
    }

    public function testOpenAcceptsValidPayload(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'comanda',
            'client_id' => 123,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 2]]
        ]);

        $this->assertEmpty($errors, 'Payload válido para abrir comanda');
    }

    public function testOpenDoesNotRequirePayment(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'comanda',
            'client_id' => 1,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayNotHasKey('payments', $errors);
    }

    // ============ validateAddItems ============

    public function testAddItemsRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validateAddItems([
            'flow_type' => 'comanda',
            'order_id' => 1,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testAddItemsRejectsMissingOrderId(): void
    {
        $errors = $this->validator->validateAddItems([
            'flow_type' => 'comanda_add',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('order_id', $errors);
    }

    public function testAddItemsAcceptsValidPayload(): void
    {
        $errors = $this->validator->validateAddItems([
            'flow_type' => 'comanda_add',
            'order_id' => 123,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertEmpty($errors);
    }

    // ============ validateClose ============

    public function testCloseRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'comanda',
            'order_id' => 1,
            'payments' => [['method' => 'pix', 'amount' => 50]]
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testCloseRejectsMissingOrderId(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'comanda_fechar',
            'payments' => [['method' => 'pix', 'amount' => 50]]
        ]);

        $this->assertArrayHasKey('order_id', $errors);
    }

    public function testCloseRejectsMissingPayments(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'comanda_fechar',
            'order_id' => 1
        ]);

        $this->assertArrayHasKey('payments', $errors);
    }

    public function testCloseAcceptsValidPayload(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'comanda_fechar',
            'order_id' => 123,
            'payments' => [['method' => 'pix', 'amount' => 50]]
        ]);

        $this->assertEmpty($errors);
    }

    // ============ validatePaymentCoversTotal ============

    public function testPaymentCoversTotal(): void
    {
        $errors = $this->validator->validatePaymentCoversTotal(
            100.00,
            [['method' => 'pix', 'amount' => 100]]
        );

        $this->assertEmpty($errors);
    }

    public function testPaymentNotCoversTotal(): void
    {
        $errors = $this->validator->validatePaymentCoversTotal(
            100.00,
            [['method' => 'pix', 'amount' => 50]]
        );

        $this->assertArrayHasKey('payments', $errors);
        $this->assertStringContainsString('insuficiente', $errors['payments']);
    }
}
