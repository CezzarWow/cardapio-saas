<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Repositories\Order\OrderRepository;

/**
 * Unit tests for OrderRepository::updateStatus
 * 
 * Tests transition validation per implementation_plan.md Seção 2.4 e 2.5
 */
class OrderRepositoryStatusTransitionTest extends TestCase
{
    /**
     * Data provider for valid transitions
     */
    public static function validTransitionsProvider(): array
    {
        return [
            // PEDIDOS (operacionais)
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
            // CONTAS (financeiras)
            ['aberto', 'concluido'],
            ['aberto', 'cancelado'],
        ];
    }

    /**
     * Data provider for invalid transitions
     */
    public static function invalidTransitionsProvider(): array
    {
        return [
            // Transições proibidas conforme doc
            ['novo', 'aberto', 'Mistura pedido operacional com conta financeira'],
            ['aberto', 'novo', 'Conta não vira pedido'],
            ['concluido', 'aberto', 'Estado final não pode mudar'],
            ['concluido', 'cancelado', 'Estado final não pode mudar'],
            ['cancelado', 'novo', 'Estado final não pode mudar'],
            ['cancelado', 'aberto', 'Estado final não pode mudar'],
            ['entregue', 'aberto', 'Já entregue não reabre'],
            ['entregue', 'novo', 'Backward transition proibida'],
            // Transições inexistentes
            ['em_preparo', 'novo', 'Backward transition'],
            ['pronto', 'aguardando', 'Backward transition'],
        ];
    }

    /**
     * Test: Transição válida deve estar no array VALID_TRANSITIONS
     * 
     * @dataProvider validTransitionsProvider
     */
    public function testValidTransitionIsAllowed(string $from, string $to): void
    {
        // Usamos reflection para acessar a constante privada
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        $this->assertArrayHasKey($from, $constant, "Status '{$from}' não está definido em VALID_TRANSITIONS");
        $this->assertContains($to, $constant[$from], "Transição {$from} → {$to} deveria ser válida");
    }

    /**
     * Test: Transição inválida NÃO deve estar no array VALID_TRANSITIONS
     * 
     * @dataProvider invalidTransitionsProvider
     */
    public function testInvalidTransitionIsBlocked(string $from, string $to, string $reason): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        if (!array_key_exists($from, $constant)) {
            // Status não definido = implicitamente bloqueado
            $this->assertTrue(true, "Status '{$from}' não definido, transição bloqueada");
            return;
        }

        $this->assertNotContains(
            $to, 
            $constant[$from], 
            "Transição {$from} → {$to} NÃO deveria ser permitida. Razão: {$reason}"
        );
    }

    /**
     * Test: Estados finais (concluido, cancelado) não podem transitar para nenhum outro
     */
    public function testFinalStatesHaveNoTransitions(): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        $this->assertEmpty($constant['concluido'], "'concluido' deve ser estado final sem transições");
        $this->assertEmpty($constant['cancelado'], "'cancelado' deve ser estado final sem transições");
    }

    /**
     * Test: 'novo' NÃO pode transitar para 'aberto' (regra crítica)
     */
    public function testNovoCannotTransitionToAberto(): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        $this->assertNotContains(
            'aberto', 
            $constant['novo'], 
            "'novo' → 'aberto' é PROIBIDO: mistura pedido operacional com conta financeira"
        );
    }

    /**
     * Test: 'aberto' pode transitar para 'concluido' (fechamento de comanda)
     */
    public function testAbertoCanTransitionToConcluido(): void
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $constant = $reflection->getConstant('VALID_TRANSITIONS');

        $this->assertContains(
            'concluido', 
            $constant['aberto'], 
            "'aberto' → 'concluido' é necessário para CloseCommandAction"
        );
    }
}
