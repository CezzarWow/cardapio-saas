<?php
/**
 * Router Class - Sistema de Roteamento Simples
 * 
 * Substitui o switch/case gigante do index.php por uma sintaxe mais limpa.
 * 
 * REGRAS DE MIGRAÇÃO:
 * - Nenhuma lógica alterada
 * - Mesmos controllers, mesmos métodos
 * - Mesma ordem de execução
 * 
 * @package App\Core
 */

namespace App\Core;

class Router
{
    private static array $routes = [];
    private static array $patterns = [];
    private static $defaultHandler = null;
    private static ?Container $container = null;

    /**
     * Define o Container de Dependências
     */
    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * Registra uma rota estática
     * 
     * @param string $path Caminho da URL (ex: '/admin')
     * @param string $controller Classe do controller
     * @param string $method Método a ser chamado
     */
    public static function add(string $path, string $controller, string $method): void
    {
        self::$routes[$path] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    /**
     * Registra uma rota com padrão regex (para rotas dinâmicas)
     * 
     * @param string $pattern Padrão regex
     * @param string $controller Classe do controller
     * @param string $method Método a ser chamado
     */
    public static function pattern(string $pattern, string $controller, string $method): void
    {
        self::$patterns[] = [
            'pattern' => $pattern,
            'controller' => $controller,
            'method' => $method
        ];
    }

    /**
     * Define o handler padrão (fallback/default)
     * 
     * @param callable $handler Função de fallback
     */
    public static function setDefault(callable $handler): void
    {
        self::$defaultHandler = $handler;
    }

    /**
     * Resolve o Controller (via Container ou new direto)
     */
    private static function resolveController(string $class)
    {
        if (self::$container) {
            return self::$container->get($class);
        }
        return new $class();
    }

    /**
     * Processa a requisição atual
     * 
     * @param string $path Caminho da URL atual
     * @return bool True se encontrou rota, false se não
     */
    public static function dispatch(string $path): bool
    {
        // 1. Tenta rotas estáticas primeiro (mais rápido)
        if (isset(self::$routes[$path])) {
            $route = self::$routes[$path];
            $controller = self::resolveController($route['controller']);
            $controller->{$route['method']}();
            return true;
        }

        // 2. Tenta rotas com padrão (regex)
        foreach (self::$patterns as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                $controller = self::resolveController($route['controller']);
                // Remove o match completo, passa só os grupos capturados
                array_shift($matches);
                $controller->{$route['method']}(...$matches);
                return true;
            }
        }

        // 3. Fallback para handler padrão
        if (self::$defaultHandler !== null) {
            (self::$defaultHandler)($path);
            return true;
        }

        return false;
    }

    /**
     * Retorna todas as rotas registradas (para debug)
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }

    /**
     * Limpa todas as rotas (útil para testes)
     */
    public static function clear(): void
    {
        self::$routes = [];
        self::$patterns = [];
        self::$defaultHandler = null;
        self::$container = null;
    }
}
