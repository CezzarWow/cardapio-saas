<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Mesa\MesaValidator;

/**
 * Testes unitários para MesaValidator
 */
class MesaValidatorTest extends TestCase
{
    private MesaValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new MesaValidator();
    }

    // ============ validateOpen ============

    public function testOpenRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'balcao',
            'table_id' => 1,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testOpenRejectsMissingTableId(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'mesa',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('table_id', $errors);
    }

    public function testOpenRejectsEmptyCart(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'mesa',
            'table_id' => 1,
            'cart' => []
        ]);

        $this->assertArrayHasKey('cart', $errors);
    }

    public function testOpenAcceptsValidPayload(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'mesa',
            'table_id' => 1,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 2]]
        ]);

        $this->assertEmpty($errors, 'Payload válido para abrir mesa');
    }

    public function testOpenDoesNotRequirePayment(): void
    {
        $errors = $this->validator->validateOpen([
            'flow_type' => 'mesa',
            'table_id' => 1,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
            // Sem payments - deve ser aceito
        ]);

        $this->assertArrayNotHasKey('payments', $errors);
    }

    // ============ validateAddItems ============

    public function testAddItemsRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validateAddItems([
            'flow_type' => 'mesa',
            'order_id' => 1,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testAddItemsRejectsMissingOrderId(): void
    {
        $errors = $this->validator->validateAddItems([
            'flow_type' => 'mesa_add',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertArrayHasKey('order_id', $errors);
    }

    public function testAddItemsAcceptsValidPayload(): void
    {
        $errors = $this->validator->validateAddItems([
            'flow_type' => 'mesa_add',
            'order_id' => 123,
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]]
        ]);

        $this->assertEmpty($errors);
    }

    // ============ validateClose ============

    public function testCloseRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'mesa',
            'table_id' => 1,
            'payments' => [['method' => 'pix', 'amount' => 50]]
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testCloseRejectsMissingTableId(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'mesa_fechar',
            'payments' => [['method' => 'pix', 'amount' => 50]]
        ]);

        $this->assertArrayHasKey('table_id', $errors);
    }

    public function testCloseRejectsMissingPayments(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'mesa_fechar',
            'table_id' => 1
        ]);

        $this->assertArrayHasKey('payments', $errors);
    }

    public function testCloseRejectsEmptyPayments(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'mesa_fechar',
            'table_id' => 1,
            'payments' => []
        ]);

        $this->assertArrayHasKey('payments', $errors);
    }

    public function testCloseAcceptsValidPayload(): void
    {
        $errors = $this->validator->validateClose([
            'flow_type' => 'mesa_fechar',
            'table_id' => 1,
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

    public function testMultiplePaymentsCoverTotal(): void
    {
        $errors = $this->validator->validatePaymentCoversTotal(
            100.00,
            [
                ['method' => 'pix', 'amount' => 60],
                ['method' => 'dinheiro', 'amount' => 40]
            ]
        );

        $this->assertEmpty($errors);
    }
}
