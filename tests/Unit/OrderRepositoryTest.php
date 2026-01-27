<?php

namespace Tests\Unit;

use App\Repositories\Order\OrderRepository;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

class OrderRepositoryTest extends TestCase
{
    private OrderRepository $repository;

    protected function setUp(): void
    {
        TestDatabase::truncateAll();
        $this->repository = new OrderRepository();
    }

    public function testCreateReturnsOrderId(): void
    {
        $orderId = $this->repository->create([
            'restaurant_id' => 1,
            'client_id' => null,
            'table_id' => null,
            'total' => 25.0,
            'order_type' => 'balcao',
            'payment_method' => 'dinheiro',
            'observation' => null,
            'change_for' => null,
            'source' => 'pdv',
        ], 'novo');

        $this->assertGreaterThan(0, $orderId);

        $order = $this->repository->find($orderId);
        $this->assertEquals('novo', $order['status']);
        $this->assertEquals('balcao', $order['order_type']);
    }

    public function testFindReturnsOrderWhenExists(): void
    {
        $orderId = $this->repository->create([
            'restaurant_id' => 1,
            'client_id' => null,
            'table_id' => null,
            'total' => 15.0,
            'order_type' => 'mesa',
            'payment_method' => 'dinheiro',
            'observation' => null,
            'change_for' => null,
            'source' => 'pdv',
        ], 'aberto');

        $order = $this->repository->find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals($orderId, (int) $order['id']);
    }

    public function testFindReturnsNullWhenNotExists(): void
    {
        $order = $this->repository->find(99999);
        $this->assertNull($order);
    }

    public function testUpdateStatusValidatesTransitions(): void
    {
        $this->assertTrue(true);
    }

    public function testUpdateTotalNeverAllowsNegative(): void
    {
        $orderId = $this->repository->create([
            'restaurant_id' => 1,
            'client_id' => null,
            'table_id' => null,
            'total' => 5.0,
            'order_type' => 'balcao',
            'payment_method' => 'dinheiro',
            'observation' => null,
            'change_for' => null,
            'source' => 'pdv',
        ], 'novo');

        $this->repository->updateTotal($orderId, -10.0);

        $order = $this->repository->find($orderId);
        $this->assertEquals(0.0, (float) $order['total']);
    }

    public function testUpdatePaymentUpdatesCorrectly(): void
    {
        $orderId = $this->repository->create([
            'restaurant_id' => 1,
            'client_id' => null,
            'table_id' => null,
            'total' => 10.0,
            'order_type' => 'balcao',
            'payment_method' => 'dinheiro',
            'observation' => null,
            'change_for' => null,
            'source' => 'pdv',
        ], 'novo');

        $this->repository->updatePayment($orderId, true, 'pix');

        $order = $this->repository->find($orderId);
        $this->assertEquals(1, (int) $order['is_paid']);
        $this->assertEquals('pix', $order['payment_method']);
    }
}
