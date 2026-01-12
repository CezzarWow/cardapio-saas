<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Delivery\DeliveryValidator;

/**
 * Testes unitários para DeliveryValidator
 */
class DeliveryValidatorTest extends TestCase
{
    private DeliveryValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new DeliveryValidator();
    }

    // ============ validateCreate ============

    public function testCreateRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validateCreate([
            'flow_type' => 'comanda',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'address' => 'Rua Teste',
            'client_name' => 'João'
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testCreateRejectsEmptyCart(): void
    {
        $errors = $this->validator->validateCreate([
            'flow_type' => 'delivery',
            'cart' => [],
            'address' => 'Rua Teste',
            'client_name' => 'João'
        ]);

        $this->assertArrayHasKey('cart', $errors);
    }

    public function testCreateRejectsMissingAddress(): void
    {
        $errors = $this->validator->validateCreate([
            'flow_type' => 'delivery',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'client_name' => 'João'
        ]);

        $this->assertArrayHasKey('address', $errors);
    }

    public function testCreateRejectsMissingClient(): void
    {
        $errors = $this->validator->validateCreate([
            'flow_type' => 'delivery',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'address' => 'Rua Teste'
        ]);

        $this->assertArrayHasKey('client', $errors);
    }

    public function testCreateAcceptsClientId(): void
    {
        $errors = $this->validator->validateCreate([
            'flow_type' => 'delivery',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'address' => 'Rua Teste',
            'client_id' => 123
        ]);

        $this->assertArrayNotHasKey('client', $errors);
    }

    public function testCreateAcceptsClientName(): void
    {
        $errors = $this->validator->validateCreate([
            'flow_type' => 'delivery',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'address' => 'Rua Teste',
            'client_name' => 'Maria'
        ]);

        $this->assertArrayNotHasKey('client', $errors);
    }

    public function testCreateAcceptsValidPayload(): void
    {
        $errors = $this->validator->validateCreate([
            'flow_type' => 'delivery',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 2]],
            'address' => 'Rua Teste, 123',
            'client_name' => 'João'
        ]);

        $this->assertEmpty($errors);
    }

    public function testCreatePaymentIsOptional(): void
    {
        $errors = $this->validator->validateCreate([
            'flow_type' => 'delivery',
            'cart' => [['id' => 1, 'price' => 10, 'quantity' => 1]],
            'address' => 'Rua Teste',
            'client_name' => 'João'
            // Sem payments - deve ser aceito
        ]);

        $this->assertArrayNotHasKey('payments', $errors);
    }

    // ============ validateStatusUpdate ============

    public function testStatusUpdateRejectsWrongFlowType(): void
    {
        $errors = $this->validator->validateStatusUpdate([
            'flow_type' => 'delivery',
            'order_id' => 1,
            'new_status' => 'em_preparo'
        ]);

        $this->assertArrayHasKey('flow_type', $errors);
    }

    public function testStatusUpdateRejectsMissingOrderId(): void
    {
        $errors = $this->validator->validateStatusUpdate([
            'flow_type' => 'delivery_status',
            'new_status' => 'em_preparo'
        ]);

        $this->assertArrayHasKey('order_id', $errors);
    }

    public function testStatusUpdateRejectsMissingNewStatus(): void
    {
        $errors = $this->validator->validateStatusUpdate([
            'flow_type' => 'delivery_status',
            'order_id' => 1
        ]);

        $this->assertArrayHasKey('new_status', $errors);
    }

    public function testStatusUpdateAcceptsValidPayload(): void
    {
        $errors = $this->validator->validateStatusUpdate([
            'flow_type' => 'delivery_status',
            'order_id' => 123,
            'new_status' => 'em_preparo'
        ]);

        $this->assertEmpty($errors);
    }
}
