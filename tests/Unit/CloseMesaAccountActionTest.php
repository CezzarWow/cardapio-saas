<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Mesa\CloseMesaAccountAction;
use App\Services\Order\Flows\Mesa\MesaValidator;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\Order\OrderRepository;
use App\Repositories\TableRepository;
use App\Services\Order\OrderStatus;

/**
 * Testes unitários para CloseMesaAccountAction
 */
class CloseMesaAccountActionTest extends TestCase
{
    private $paymentService;
    private $cashRegisterService;
    private $orderRepo;
    private $tableRepo;
    private $validator;
    private CloseMesaAccountAction $action;

    protected function setUp(): void
    {
        $this->paymentService = $this->createMock(PaymentService::class);
        $this->cashRegisterService = $this->createMock(CashRegisterService::class);
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->tableRepo = $this->createMock(TableRepository::class);
        $this->validator = new MesaValidator();

        $this->action = new CloseMesaAccountAction(
            $this->paymentService,
            $this->cashRegisterService,
            $this->orderRepo,
            $this->tableRepo,
            $this->validator
        );
    }

    public function testExecuteClosesTableSuccessfully(): void
    {
        // Arrange
        $restaurantId = 1;
        $data = [
            'table_id' => 5,
            'payments' => [['method' => 'pix', 'amount' => 100]]
        ];

        // Mock caixa aberto
        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        // Mock mesa com pedido
        $this->tableRepo
            ->method('findWithCurrentOrder')
            ->with(5, $restaurantId)
            ->willReturn(['id' => 5, 'number' => 5, 'current_order_id' => 123]);

        // Mock pedido aberto
        $this->orderRepo
            ->method('find')
            ->with(123, $restaurantId)
            ->willReturn([
                'id' => 123,
                'status' => OrderStatus::ABERTO,
                'total' => 100.00
            ]);

        // Expect payment registration
        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments');

        // Expect status update to CONCLUIDO
        $this->orderRepo
            ->expects($this->once())
            ->method('updateStatus')
            ->with(123, OrderStatus::CONCLUIDO)
            ->willReturn(1);

        // Expect updatePayment
        $this->orderRepo
            ->expects($this->once())
            ->method('updatePayment')
            ->with(123, true, 'pix');

        // Expect table release
        $this->tableRepo
            ->expects($this->once())
            ->method('free')
            ->with(5);

        // Expect cash movement
        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement');

        // Act
        $result = $this->action->execute($restaurantId, $data);

        // Assert
        $this->assertEquals(123, $result['order_id']);
        $this->assertEquals(100.00, $result['total']);
        $this->assertEquals(OrderStatus::CONCLUIDO, $result['status']);
    }

    public function testExecuteFailsWhenTableNotFound(): void
    {
        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->tableRepo
            ->method('findWithCurrentOrder')
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não encontrada');

        $this->action->execute(1, [
            'table_id' => 999,
            'payments' => [['method' => 'pix', 'amount' => 100]]
        ]);
    }

    public function testExecuteFailsWhenTableHasNoOrder(): void
    {
        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->tableRepo
            ->method('findWithCurrentOrder')
            ->willReturn(['id' => 5, 'number' => 5, 'current_order_id' => null]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não tem pedido aberto');

        $this->action->execute(1, [
            'table_id' => 5,
            'payments' => [['method' => 'pix', 'amount' => 100]]
        ]);
    }

    public function testExecuteFailsWhenOrderNotOpen(): void
    {
        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->tableRepo
            ->method('findWithCurrentOrder')
            ->willReturn(['id' => 5, 'number' => 5, 'current_order_id' => 123]);

        $this->orderRepo
            ->method('find')
            ->willReturn([
                'id' => 123,
                'status' => OrderStatus::CONCLUIDO, // Já fechado
                'total' => 100.00
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não tem conta aberta');

        $this->action->execute(1, [
            'table_id' => 5,
            'payments' => [['method' => 'pix', 'amount' => 100]]
        ]);
    }

    public function testExecuteFailsWhenPaymentInsufficient(): void
    {
        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->tableRepo
            ->method('findWithCurrentOrder')
            ->willReturn(['id' => 5, 'number' => 5, 'current_order_id' => 123]);

        $this->orderRepo
            ->method('find')
            ->willReturn([
                'id' => 123,
                'status' => OrderStatus::ABERTO,
                'total' => 100.00
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('insuficiente');

        $this->action->execute(1, [
            'table_id' => 5,
            'payments' => [['method' => 'pix', 'amount' => 50]] // Só 50 de 100
        ]);
    }
}
