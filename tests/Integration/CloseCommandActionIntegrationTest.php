<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Services\Order\CloseCommandAction;
use App\Services\PaymentService;
use App\Services\CashRegisterService;
use App\Repositories\Order\OrderRepository;
use App\Repositories\CashRegisterRepository;
use App\Repositories\Order\OrderPaymentRepository;

/**
 * Integration tests for CloseCommandAction
 * 
 * Testes que verificam a integração real com o banco de dados.
 * 
 * @group integration
 * @group database
 */
class CloseCommandActionIntegrationTest extends TestCase
{
    private static $conn;
    private $orderRepo;
    private $cashRegisterRepo;
    private $paymentService;
    private $cashRegisterService;
    private $action;
    
    private $testOrderId;
    private $testCaixaId;
    private static $testUserId = 1;
    private static $testRestaurantId = 1; // Use existing test restaurant

    public static function setUpBeforeClass(): void
    {
        self::$conn = Database::connect();
    }

    protected function setUp(): void
    {
        $this->orderRepo = new OrderRepository();
        $this->cashRegisterRepo = new CashRegisterRepository();
        $this->paymentService = new PaymentService(new OrderPaymentRepository());
        $this->cashRegisterService = new CashRegisterService($this->cashRegisterRepo);

        $this->action = new CloseCommandAction(
            $this->paymentService,
            $this->cashRegisterService,
            $this->orderRepo
        );

        // Garantir que restaurante existe
        $this->ensureRestaurantExists();

        // Criar caixa aberto para testes
        $this->ensureOpenCashRegister();
        
        // Criar comanda de teste com status 'aberto'
        $this->createTestOrder();
    }

    protected function tearDown(): void
    {
        // Limpar dados de teste
        $this->cleanupTestData();
    }

    /**
     * Test: Fechar comanda deve atualizar status para 'concluido'
     */
    public function testCloseCommandUpdatesStatusToConcluido(): void
    {
        // Arrange
        $payments = [
            ['method' => 'pix', 'amount' => 50.00]
        ];

        // Act
        $this->action->execute(self::$testRestaurantId, $this->testOrderId, $payments);

        // Assert - Verificar no banco
        $stmt = self::$conn->prepare("SELECT status, is_paid FROM orders WHERE id = :id");
        $stmt->execute(['id' => $this->testOrderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('concluido', $order['status'], "Status deve ser 'concluido'");
        $this->assertEquals(1, $order['is_paid'], "is_paid deve ser 1");
    }

    /**
     * Test: Fechar comanda sem pagamento quando is_paid=0 deve lançar exceção
     */
    public function testCloseCommandWithoutPaymentWhenNotPaidThrowsException(): void
    {
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Nenhum pagamento informado');

        // Act
        $this->action->execute(self::$testRestaurantId, $this->testOrderId, []);
    }

    /**
     * Test: Fechar comanda já concluída deve lançar exceção
     */
    public function testCloseAlreadyClosedCommandThrowsException(): void
    {
        // Arrange - Fechar a comanda primeiro
        $payments = [['method' => 'dinheiro', 'amount' => 50.00]];
        $this->action->execute(self::$testRestaurantId, $this->testOrderId, $payments);

        // Criar nova comanda já fechada
        $closedOrderId = $this->createClosedTestOrder();

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não está aberta');

        // Act - Tentar fechar novamente
        $this->action->execute(self::$testRestaurantId, $closedOrderId, $payments);
    }

    // ============ Helper Methods ============

    private function ensureOpenCashRegister(): void
    {
        // Verificar se já existe caixa aberto
        $stmt = self::$conn->prepare("
            SELECT id FROM cash_registers 
            WHERE restaurant_id = :rid AND status = 'aberto' 
            LIMIT 1
        ");
        $stmt->execute(['rid' => self::$testRestaurantId]);
        $caixa = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($caixa) {
            $this->testCaixaId = $caixa['id'];
            return;
        }

        // Criar caixa aberto
        $stmt = self::$conn->prepare("
            INSERT INTO cash_registers (restaurant_id, status, opening_balance, opened_at)
            VALUES (:rid, 'aberto', 100.00, NOW())
        ");
        $stmt->execute(['rid' => self::$testRestaurantId]);
        $this->testCaixaId = self::$conn->lastInsertId();
    }

    private function createTestOrder(): void
    {
        $stmt = self::$conn->prepare("
            INSERT INTO orders (restaurant_id, status, order_type, total, is_paid, payment_method, created_at)
            VALUES (:rid, 'aberto', 'comanda', 50.00, 0, 'dinheiro', NOW())
        ");
        $stmt->execute(['rid' => self::$testRestaurantId]);
        $this->testOrderId = self::$conn->lastInsertId();
    }

    private function createClosedTestOrder(): int
    {
        $stmt = self::$conn->prepare("
            INSERT INTO orders (restaurant_id, status, order_type, total, is_paid, payment_method, created_at)
            VALUES (:rid, 'concluido', 'comanda', 50.00, 1, 'pix', NOW())
        ");
        $stmt->execute(['rid' => self::$testRestaurantId]);
        return (int) self::$conn->lastInsertId();
    }

    private function cleanupTestData(): void
    {
        if ($this->testOrderId) {
            // Limpar pagamentos primeiro (FK)
            self::$conn->prepare("DELETE FROM order_payments WHERE order_id = :id")
                ->execute(['id' => $this->testOrderId]);
            
            // Limpar pedido
            self::$conn->prepare("DELETE FROM orders WHERE id = :id")
                ->execute(['id' => $this->testOrderId]);
        }

        // Limpar qualquer pedido de teste criado
        self::$conn->prepare("
            DELETE FROM orders 
            WHERE restaurant_id = :rid 
            AND order_type = 'comanda' 
            AND total = 50.00 
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ")->execute(['rid' => self::$testRestaurantId]);
    }
    private function ensureRestaurantExists(): void
    {
        // Garantir que user existe
        $this->ensureUserExists();

        // Verificar se restaurante 1 existe
        $stmt = self::$conn->prepare("SELECT id FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => self::$testRestaurantId]);
        
        if (!$stmt->fetch()) {
            // Criar restaurante de teste
            $stmt = self::$conn->prepare("
                INSERT INTO restaurants (id, user_id, name, slug, created_at)
                VALUES (:id, :uid, 'Test Restaurant', 'test-restaurant', NOW())
            ");
            $stmt->execute([
                'id' => self::$testRestaurantId,
                'uid' => self::$testUserId
            ]);
        }
    }

    private function ensureUserExists(): void
    {
        $stmt = self::$conn->prepare("SELECT id FROM users WHERE id = :id");
        $stmt->execute(['id' => self::$testUserId]);

        if (!$stmt->fetch()) {
            $stmt = self::$conn->prepare("
                INSERT INTO users (id, name, email, password, created_at)
                VALUES (:id, 'Test User', 'test@example.com', 'password', NOW())
            ");
            $stmt->execute(['id' => self::$testUserId]);
        }
    }
}
