<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\CloseCommandAction;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\Order\OrderRepository;

/**
 * Unit tests for CloseCommandAction
 * 
 * @see implementation_plan.md Seção 0 (Separação Pedido vs Conta)
 */
class CloseCommandActionTest extends TestCase
{
    private $paymentService;
    private $cashRegisterService;
    private $orderRepository;
    private $action;

    protected function setUp(): void
    {
        $this->paymentService = $this->createMock(PaymentService::class);
        $this->cashRegisterService = $this->createMock(CashRegisterService::class);
        $this->orderRepository = $this->createMock(OrderRepository::class);

        $this->action = new CloseCommandAction(
            $this->paymentService,
            $this->cashRegisterService,
            $this->orderRepository
        );
    }

    /**
     * Test: CloseCommandAction deve chamar updatePayment e updateStatus
     */
    public function testExecuteCallsUpdatePaymentAndUpdateStatus(): void
    {
        // Arrange
        $orderId = 123;
        $restaurantId = 1;
        $payments = [
            ['method' => 'pix', 'amount' => 100.00]
        ];

        // Mock caixa aberto
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        // Mock pedido existente com status 'aberto'
        $this->orderRepository
            ->expects($this->once())
            ->method('find')
            ->with($orderId, $restaurantId)
            ->willReturn([
                'id' => $orderId,
                'status' => 'aberto',
                'is_paid' => 0,
                'total' => 100.00
            ]);

        // Expect registerPayments to be called
        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments')
            ->willReturn(100.00);

        // Expect registerMovement to be called
        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement');

        // Expect updatePayment to be called
        $this->orderRepository
            ->expects($this->once())
            ->method('updatePayment')
            ->with($orderId, true, 'pix');

        // Expect updateStatus to be called with 'concluido' and return 1
        $this->orderRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($orderId, 'concluido')
            ->willReturn(1);

        // Act - should not throw
        $this->action->execute($restaurantId, $orderId, $payments);

        // Assert - if we get here, all expectations were met
        $this->assertTrue(true);
    }

    /**
     * Test: CloseCommandAction deve lançar exceção se pedido não encontrado
     */
    public function testExecuteThrowsExceptionWhenOrderNotFound(): void
    {
        // Arrange
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn(null);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Pedido não encontrado');

        // Act
        $this->action->execute(1, 999, []);
    }

    /**
     * Test: CloseCommandAction deve lançar exceção se comanda não está aberta
     */
    public function testExecuteThrowsExceptionWhenOrderNotOpen(): void
    {
        // Arrange
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn([
                'id' => 123,
                'status' => 'concluido', // Já fechado!
                'is_paid' => 1
            ]);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não está aberta');

        // Act
        $this->action->execute(1, 123, [['method' => 'pix', 'amount' => 100]]);
    }

    /**
     * Test: CloseCommandAction deve lançar exceção se pagamento vazio e não pago
     */
    public function testExecuteThrowsExceptionWhenNoPaymentAndNotPaid(): void
    {
        // Arrange
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn([
                'id' => 123,
                'status' => 'aberto',
                'is_paid' => 0
            ]);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Nenhum pagamento informado');

        // Act
        $this->action->execute(1, 123, []);
    }

    /**
     * Test: CloseCommandAction deve lançar RuntimeException se updateStatus retorna 0
     */
    public function testExecuteThrowsRuntimeExceptionWhenUpdateStatusAffectsZeroRows(): void
    {
        // Arrange
        $this->cashRegisterService
            ->expects($this->once())
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn([
                'id' => 123,
                'status' => 'aberto',
                'is_paid' => 0
            ]);

        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments')
            ->willReturn(100.00);

        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement');

        $this->orderRepository
            ->expects($this->once())
            ->method('updatePayment');

        // updateStatus returns 0 (no rows affected)
        $this->orderRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->willReturn(0);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('updateStatus affected 0 rows');

        // Act
        $this->action->execute(1, 123, [['method' => 'dinheiro', 'amount' => 100]]);
    }
}
