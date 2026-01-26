<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\OrderStatus;

/**
 * Testes unitários para OrderStatus
 */
class OrderStatusTest extends TestCase
{
    public function testAllReturnsAllStatuses(): void
    {
        $all = OrderStatus::all();

        $this->assertIsArray($all);
        $this->assertContains(OrderStatus::NOVO, $all);
        $this->assertContains(OrderStatus::ABERTO, $all);
        $this->assertContains(OrderStatus::CONCLUIDO, $all);
        $this->assertContains(OrderStatus::CANCELADO, $all);
    }

    public function testIsValidReturnsTrueForValidStatus(): void
    {
        $this->assertTrue(OrderStatus::isValid(OrderStatus::NOVO));
        $this->assertTrue(OrderStatus::isValid(OrderStatus::ABERTO));
        $this->assertTrue(OrderStatus::isValid(OrderStatus::CONCLUIDO));
    }

    public function testIsValidReturnsFalseForInvalidStatus(): void
    {
        $this->assertFalse(OrderStatus::isValid('invalid_status'));
        $this->assertFalse(OrderStatus::isValid(''));
        $this->assertFalse(OrderStatus::isValid('novo_invalid'));
    }

    public function testIsFinalReturnsTrueForFinalStatuses(): void
    {
        $this->assertTrue(OrderStatus::isFinal(OrderStatus::CONCLUIDO));
        $this->assertTrue(OrderStatus::isFinal(OrderStatus::CANCELADO));
    }

    public function testIsFinalReturnsFalseForNonFinalStatuses(): void
    {
        $this->assertFalse(OrderStatus::isFinal(OrderStatus::NOVO));
        $this->assertFalse(OrderStatus::isFinal(OrderStatus::ABERTO));
        $this->assertFalse(OrderStatus::isFinal(OrderStatus::EM_PREPARO));
    }
}
