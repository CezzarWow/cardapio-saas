<?php
/**
 * ============================================
 * LOGGER - Sistema de Logs Simples (VERSÃO CORRIGIDA)
 * Arquivo: app/Core/Logger.php
 * 
 * AJUSTES APLICADOS:
 * - NÃO depende de $_SESSION (recebe via context)
 * - À prova de falhas (nunca quebra o request)
 * - Silencia todos os erros internos
 * 
 * Uso:
 *   use App\Core\Logger;
 *   Logger::error('Mensagem', ['restaurant_id' => 1, 'order_id' => 123]);
 *   Logger::info('Ação', ['user' => 'admin']);
 * ============================================
 */

namespace App\Core;

class Logger {
    
    /**
     * Diretório onde os logs serão salvos
     */
    private static $logDir = __DIR__ . '/../../logs/';
    
    /**
     * Log de erro (problemas críticos)
     * @param string $message Mensagem descritiva
     * @param array $context Dados adicionais (opcional)
     */
    public static function error($message, $context = []) {
        self::write('ERROR', $message, $context);
    }
    
    /**
     * Log de aviso (situações inesperadas mas não críticas)
     * @param string $message Mensagem descritiva
     * @param array $context Dados adicionais (opcional)
     */
    public static function warning($message, $context = []) {
        self::write('WARNING', $message, $context);
    }
    
    /**
     * Log informativo (ações importantes para auditoria)
     * @param string $message Mensagem descritiva
     * @param array $context Dados adicionais (opcional)
     */
    public static function info($message, $context = []) {
        self::write('INFO', $message, $context);
    }
    
    /**
     * Escreve a linha no arquivo de log
     * NUNCA lança exceção - falha silenciosamente
     */
    private static function write($level, $message, $context = []) {
        // Bloco try-catch total - Logger NUNCA pode derrubar o sistema
        try {
            // Garante que a pasta existe (com supressão de erro)
            if (!@is_dir(self::$logDir)) {
                @mkdir(self::$logDir, 0755, true);
            }
            
            // Se ainda não existe a pasta, desiste silenciosamente
            if (!@is_dir(self::$logDir)) {
                return;
            }
            
            // Arquivo nomeado por data (rotação automática)
            $filename = self::$logDir . date('Y-m-d') . '.log';
            
            // Monta a linha de log
            $timestamp = date('Y-m-d H:i:s');
            
            // Restaurant ID vem do context (NÃO da sessão!)
            $restaurantId = $context['restaurant_id'] ?? 'N/A';
            
            // Contexto em JSON (se houver)
            $contextJson = !empty($context) ? ' ' . @json_encode($context, JSON_UNESCAPED_UNICODE) : '';
            
            // Formato: [TIMESTAMP] [LEVEL] [RID:X] Mensagem {contexto}
            $line = "[{$timestamp}] [{$level}] [RID:{$restaurantId}] {$message}{$contextJson}" . PHP_EOL;
            
            // Escreve no arquivo (append, com lock para evitar conflito)
            // Usa @ para suprimir qualquer warning de permissão
            @file_put_contents($filename, $line, FILE_APPEND | LOCK_EX);
            
        } catch (\Throwable $e) {
            // Silencia QUALQUER erro - inclusive Error do PHP 7+
            // Em último caso, tenta error_log nativo (mas não obriga)
            @error_log("Logger failed: " . $e->getMessage());
        }
    }
    
    /**
     * Limpa logs antigos (mais de X dias)
     * Chamar periodicamente via cron ou manualmente
     * @param int $days Dias para manter
     */
    public static function cleanup($days = 30) {
        try {
            $files = @glob(self::$logDir . '*.log');
            if (!is_array($files)) return;
            
            $threshold = strtotime("-{$days} days");
            
            foreach ($files as $file) {
                if (@filemtime($file) < $threshold) {
                    @unlink($file);
                }
            }
        } catch (\Throwable $e) {
            // Silencia
        }
    }
}
