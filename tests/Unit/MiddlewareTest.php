<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RequestSanitizerMiddleware;
use App\Middleware\AuthorizationMiddleware;

/**
 * Testes unitários para Middlewares
 */
class MiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        // Limpar sessão antes de cada teste
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }

    public function testCsrfMiddlewareGeneratesToken(): void
    {
        // Simular requisição GET (não precisa de token)
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/admin';
        
        $result = CsrfMiddleware::handle();
        
        $this->assertTrue($result);
        $this->assertNotEmpty($_SESSION['csrf_token']);
    }

    public function testCsrfMiddlewareValidatesToken(): void
    {
        // Simular requisição POST com token válido
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/admin/loja/produtos/salvar';
        
        // Gerar token
        $token = CsrfMiddleware::getToken();
        $_POST['csrf_token'] = $token;
        
        $result = CsrfMiddleware::handle();
        
        $this->assertTrue($result);
    }

    public function testCsrfMiddlewareRejectsInvalidToken(): void
    {
        // Simular requisição POST com token inválido
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/admin/loja/produtos/salvar';
        
        // Gerar token válido mas enviar inválido
        CsrfMiddleware::getToken();
        $_POST['csrf_token'] = 'invalid_token';
        
        // Capturar output
        ob_start();
        $result = CsrfMiddleware::handle();
        $output = ob_get_clean();
        
        // Deve bloquear: retorna false e/ou envia resposta
        $this->assertTrue($result === false || !empty($output));
    }

    public function testRequestSanitizerMiddlewareCleansInput(): void
    {
        $_GET['test'] = '  <script>alert("xss")</script>  ';
        $_POST['name'] = '  Test Name  ';
        
        RequestSanitizerMiddleware::handle();
        
        $this->assertStringNotContainsString('<script>', $_GET['test']);
        $this->assertEquals('Test Name', $_POST['name']);
    }

    public function testRequestSanitizerMiddlewareTrimsWhitespace(): void
    {
        $_POST['value'] = '  spaced  ';
        
        RequestSanitizerMiddleware::handle();
        
        $this->assertEquals('spaced', $_POST['value']);
    }

    public function testAuthorizationMiddlewareAllowsInDevelopment(): void
    {
        // Em desenvolvimento, permite mesmo sem sessão
        $_ENV['APP_ENV'] = 'development';
        $_SERVER['REQUEST_URI'] = '/admin';
        
        $result = AuthorizationMiddleware::handle();
        
        $this->assertTrue($result);
    }
}
