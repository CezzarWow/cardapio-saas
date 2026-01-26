<?php

namespace App\Middleware;

use App\Core\Logger;

/**
 * Authorization Middleware
 * 
 * Verifica se o usuário autenticado tem acesso ao restaurante solicitado.
 * 
 * IMPORTANTE: Este middleware assume que:
 * - A sessão já foi iniciada (deve vir após autenticação)
 * - O restaurante_id está disponível na sessão ou na requisição
 * 
 * Por enquanto, valida apenas se há sessão ativa.
 * Em versões futuras, pode validar permissões específicas.
 */
class AuthorizationMiddleware
{
    /**
     * Handle the incoming request
     * 
     * @return bool Returns true if authorized, false otherwise
     */
    public static function handle(): bool
    {
        // 1. Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Verifica se há usuário autenticado
        // Em modo DEV, permite continuar (comportamento atual do BaseController)
        $userId = $_SESSION['user_id'] ?? null;
        
        // Se não há usuário e não estamos em DEV, bloqueia
        // (Por enquanto, apenas loga - em produção pode bloquear)
        if (empty($userId)) {
            $appEnv = defined('APP_ENV') ? APP_ENV : ($_ENV['APP_ENV'] ?? 'production');
            
            if ($appEnv === 'production') {
                // Em produção, bloqueia requisições sem autenticação
                Logger::warning('AuthorizationMiddleware: Requisição sem usuário autenticado', [
                    'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                http_response_code(401);
                
                // Detecta se é requisição AJAX/JSON
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                $wantsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
                
                if ($isAjax || $wantsJson) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Não autorizado. Por favor, faça login novamente.'
                    ]);
                } else {
                    // Redireciona para login
                    header('Location: /admin');
                }
                
                return false;
            }
            
            // Em desenvolvimento, permite (comportamento atual)
            // Mas loga para alertar
            Logger::debug('AuthorizationMiddleware: Requisição sem usuário (DEV mode)', [
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
        }

        // 3. Validação de acesso ao restaurante (futuro)
        // Por enquanto, apenas verifica se há restaurante na sessão
        // Em versões futuras, pode validar se usuário tem permissão no restaurante
        $restaurantId = $_SESSION['loja_ativa_id'] ?? null;
        
        if (empty($restaurantId) && defined('APP_ENV') && APP_ENV === 'production') {
            Logger::warning('AuthorizationMiddleware: Requisição sem restaurante selecionado', [
                'user_id' => $userId,
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
            
            // Em produção, pode bloquear ou apenas alertar
            // Por enquanto, apenas loga
        }

        return true;
    }

    /**
     * Verifica se usuário tem acesso a um restaurante específico
     * 
     * @param int $userId ID do usuário
     * @param int $restaurantId ID do restaurante
     * @return bool
     */
    public static function hasAccessToRestaurant(int $userId, int $restaurantId): bool
    {
        // TODO: Implementar validação real de permissões
        // Por enquanto, retorna true (comportamento atual)
        // Em versões futuras, consultar tabela de permissões/roles
        
        return true;
    }
}
