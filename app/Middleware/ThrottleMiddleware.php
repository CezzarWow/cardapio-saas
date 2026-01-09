<?php

namespace App\Middleware;

/**
 * Throttle Middleware
 * Limita a taxa de requisições por IP para prevenir abusos (Rate Limiting)
 */
class ThrottleMiddleware
{
    private const MAX_ATTEMPTS = 60; // Máximo de requisições
    private const DECAY_MINUTES = 1; // Intervalo em minutos
    
    /**
     * Handle the incoming request
     */
    public static function handle(): bool
    {
        // Apenas aplica rate limiting em rotas de API ou login para evitar overhead em assets
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $isApi = strpos($uri, '/api') !== false;
        $isLogin = strpos($uri, 'login') !== false || strpos($uri, 'autologin') !== false;
        
        // Se não for API nem Login, permite passar
        if (!$isApi && !$isLogin) {
            return true;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'throttle_' . md5($ip);
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $key;

        // Limpa cache antigo se existir
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data['expires_at'] < time()) {
                unlink($file);
                $data = null;
            }
        } else {
            $data = null;
        }

        // Inicializa ou incrementa
        if (!$data) {
            $data = [
                'attempts' => 1,
                'expires_at' => time() + (self::DECAY_MINUTES * 60)
            ];
        } else {
            $data['attempts']++;
        }

        // Verifica limite
        if ($data['attempts'] > self::MAX_ATTEMPTS) {
            http_response_code(429);
            header('Retry-After: ' . ($data['expires_at'] - time()));
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Muitas requisições. Tente novamente em alguns segundos.']);
            return false; // Bloqueia a execução
        }

        // Salva estado
        file_put_contents($file, json_encode($data));

        return true;
    }
}
