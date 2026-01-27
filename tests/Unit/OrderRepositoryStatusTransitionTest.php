<?php

namespace Tests\Unit;

use App\Repositories\Order\OrderRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for OrderRepository::updateStatus transition rules.
 */
class OrderRepositoryStatusTransitionTest extends TestCase
{
    public static function validTransitionsProvider(): array
    {
        return [
            ['novo', 'aguardando'],
            ['novo', 'concluido'],
            ['novo', 'cancelado'],
            ['aguardando', 'em_preparo'],
            ['aguardando', 'cancelado'],
            ['em_preparo', 'pronto'],
            ['em_preparo', 'cancelado'],
            ['pronto', 'em_entrega'],
            ['pronto', 'entregue'],
            ['pronto', 'concluido'],
            ['em_entrega', 'entregue'],
            ['em_entrega', 'cancelado'],
            ['entregue', 'concluido'],
            ['aberto', 'concluido'],
            ['aberto', 'cancelado'],
        ];
    }

    public static function invalidTransitionsProvider(): array
    {
        return [
            ['novo', 'aberto', 'Operational order cannot become financial account'],
            ['aberto', 'novo', 'Account does not become operational order'],
            ['concluido', 'aberto', 'Final state cannot transition'],
            ['concluido', 'cancelado', 'Final state cannot transition'],
            ['cancelado', 'novo', 'Final state cannot transition'],
            ['cancelado', 'aberto', 'Final state cannot transition'],
            ['entregue', 'aberto', 'Delivered order cannot reopen'],
            ['entregue', 'novo', 'Backward transition'],
            ['em_preparo', 'novo', 'Backward transition'],
            ['pronto', 'aguardando', 'Backward transition'],
        ];
    }

    #[DataProvider('validTransitionsProvider')]
    public function testValidTransitionIsAllowed(string $from, string $to): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        $this->assertArrayHasKey($from, $constant, "Status '{$from}' not defined");
        $this->assertContains($to, $constant[$from], "Transition {$from} -> {$to} should be allowed");
    }

    #[DataProvider('invalidTransitionsProvider')]
    public function testInvalidTransitionIsBlocked(string $from, string $to, string $reason): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        if (!array_key_exists($from, $constant)) {
            $this->assertTrue(true, "Status '{$from}' not defined, transition blocked");
            return;
        }

        $this->assertNotContains(
            $to,
            $constant[$from],
            "Transition {$from} -> {$to} should NOT be allowed. Reason: {$reason}"
        );
    }

    public function testFinalStatesHaveNoTransitions(): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        $this->assertEmpty($constant['concluido'], "'concluido' must be final");
        $this->assertEmpty($constant['cancelado'], "'cancelado' must be final");
    }

    public function testNovoCannotTransitionToAberto(): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        $this->assertNotContains('aberto', $constant['novo']);
    }

    public function testAbertoCanTransitionToConcluido(): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        $this->assertContains('concluido', $constant['aberto']);
    }
}
