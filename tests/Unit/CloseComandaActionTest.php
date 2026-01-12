<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Order\Flows\Comanda\CloseComandaAction;
use App\Services\Order\Flows\Comanda\ComandaValidator;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\Order\OrderRepository;
use App\Services\Order\OrderStatus;

/**
 * Testes unitários para CloseComandaAction
 */
class CloseComandaActionTest extends TestCase
{
    private $paymentService;
    private $cashRegisterService;
    private $orderRepo;
    private $validator;
    private CloseComandaAction $action;

    protected function setUp(): void
    {
        $this->paymentService = $this->createMock(PaymentService::class);
        $this->cashRegisterService = $this->createMock(CashRegisterService::class);
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->validator = new ComandaValidator();

        $this->action = new CloseComandaAction(
            $this->paymentService,
            $this->cashRegisterService,
            $this->orderRepo,
            $this->validator
        );
    }

    public function testExecuteClosesComandaSuccessfully(): void
    {
        $restaurantId = 1;
        $data = [
            'order_id' => 456,
            'payments' => [['method' => 'pix', 'amount' => 100]]
        ];

        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepo
            ->method('find')
            ->willReturn([
                'id' => 456,
                'client_id' => 10,
                'order_type' => 'comanda',
                'status' => OrderStatus::ABERTO,
                'total' => 100.00
            ]);

        $this->paymentService
            ->expects($this->once())
            ->method('registerPayments');

        $this->orderRepo
            ->expects($this->once())
            ->method('updateStatus')
            ->with(456, OrderStatus::CONCLUIDO)
            ->willReturn(1);

        $this->orderRepo
            ->expects($this->once())
            ->method('updatePayment')
            ->with(456, true, 'pix');

        $this->cashRegisterService
            ->expects($this->once())
            ->method('registerMovement');

        $result = $this->action->execute($restaurantId, $data);

        $this->assertEquals(456, $result['order_id']);
        $this->assertEquals(100.00, $result['total']);
        $this->assertEquals(OrderStatus::CONCLUIDO, $result['status']);
    }

    public function testExecuteFailsWhenComandaNotFound(): void
    {
        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepo
            ->method('find')
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não encontrada');

        $this->action->execute(1, [
            'order_id' => 999,
            'payments' => [['method' => 'pix', 'amount' => 100]]
        ]);
    }

    public function testExecuteRejectsNonComandaOrder(): void
    {
        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepo
            ->method('find')
            ->willReturn([
                'id' => 456,
                'order_type' => 'mesa', // NÃO é comanda
                'status' => OrderStatus::ABERTO,
                'total' => 100.00
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não é uma comanda');

        $this->action->execute(1, [
            'order_id' => 456,
            'payments' => [['method' => 'pix', 'amount' => 100]]
        ]);
    }

    public function testExecuteFailsWhenPaymentInsufficient(): void
    {
        $this->cashRegisterService
            ->method('assertOpen')
            ->willReturn(['id' => 1]);

        $this->orderRepo
            ->method('find')
            ->willReturn([
                'id' => 456,
                'order_type' => 'comanda',
                'status' => OrderStatus::ABERTO,
                'total' => 100.00
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('insuficiente');

        $this->action->execute(1, [
            'order_id' => 456,
            'payments' => [['method' => 'pix', 'amount' => 50]]
        ]);
    }
}
