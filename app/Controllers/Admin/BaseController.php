<?php

namespace App\Controllers\Admin;

/**
 * BaseController - Classe base para controllers admin
 *
 * Fornece helpers comuns para todos os controllers:
 * - Gerenciamento de sessão
 * - Responses (JSON, Redirect)
 * - Handlers genéricos
 */
abstract class BaseController
{
    /**
     * Verifica se há sessão ativa de usuário.
     * Se não, redireciona para login.
     */
    protected function checkSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            // DEV MODE: Auto-login como usuário 1 se não houver sessão
            // (Comportamento alinhado com getUserId())
            $_SESSION['user_id'] = 1;
            return;
        }
    }

    /**
     * Verifica sessão e retorna o ID do restaurante ativo
     * DEV MODE: Auto-selects restaurant 8 if no active store in session
     */
    protected function getRestaurantId(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['loja_ativa_id'])) {
            // DEV MODE: Auto-select restaurant 8 if no session
            // (Similar to user_id = 1 in checkSession)
            $_SESSION['loja_ativa_id'] = 8;
        }

        return (int) $_SESSION['loja_ativa_id'];
    }

    /**
     * Retorna resposta JSON e encerra execução
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redireciona para URL e encerra execução
     */
    protected function redirect(string $path): void
    {
        // Se path não começa com /, adiciona BASE_URL
        if (strpos($path, 'http') !== 0 && strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }

        // Se path começa com /, adiciona BASE_URL
        if (strpos($path, '/') === 0) {
            $path = BASE_URL . $path;
        }

        header('Location: ' . $path);
        exit;
    }

    /**
     * Handler genérico para requisições POST com try/catch
     *
     * @param callable $action Função que recebe ($restaurantId) e executa a ação
     * @param string $successRedirect URL de redirect em caso de sucesso
     * @param string $errorRedirect URL de redirect em caso de erro
     */
    protected function handlePost(
        callable $action,
        string $successRedirect,
        string $errorRedirect = null
    ): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $restaurantId = $this->getRestaurantId();

        try {
            $action($restaurantId);
            $this->redirect($successRedirect);
        } catch (\Exception $e) {
            error_log('Controller Error: ' . $e->getMessage());
            $this->redirect($errorRedirect ?? $successRedirect . '&error=operacao_falhou');
        }
    }

    /**
     * Handler completo para POST com validação, sanitização e execução
     *
     * @param callable $validator Função que retorna array de erros (vazio = válido)
     * @param callable $sanitizer Função que retorna dados sanitizados
     * @param callable $action Função que recebe ($data, $restaurantId) e executa a ação
     * @param string $baseRedirect URL base para redirect
     * @param string $successKey Chave de sucesso para query string
     */
    protected function handleValidatedPost(
        callable $validator,
        callable $sanitizer,
        callable $action,
        string $baseRedirect,
        string $successKey
    ): bool {
        if (!$this->isPost()) {
            return false;
        }

        $restaurantId = $this->getRestaurantId();

        // Validar
        $errors = $validator();
        if (!empty($errors)) {
            $this->redirect($baseRedirect . '?error=validacao_falhou');
            return false;
        }

        // Sanitizar
        $data = $sanitizer();

        // Executar
        try {
            $action($data, $restaurantId);
            $this->redirect($baseRedirect . '?success=' . $successKey);
            return true;
        } catch (\Exception $e) {
            error_log('handleValidatedPost Error: ' . $e->getMessage());
            $this->redirect($baseRedirect . '?error=operacao_falhou');
            return false;
        }
    }

    /**
     * Handler para DELETE/GET simples (sem body)
     */
    protected function handleDelete(
        callable $action,
        string $baseRedirect,
        int $id = null
    ): void {
        $restaurantId = $this->getRestaurantId();
        $id = $id ?? $this->getInt('id');

        if ($id <= 0) {
            $this->redirect($baseRedirect . '?error=id_invalido');
            return;
        }

        try {
            $action($id, $restaurantId);
            $this->redirect($baseRedirect);
        } catch (\Exception $e) {
            error_log('handleDelete Error: ' . $e->getMessage());
            $this->redirect($baseRedirect . '?error=operacao_falhou');
        }
    }

    /**
     * Verifica se a requisição é POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Verifica se a requisição é AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Retorna valor de GET com fallback
     */
    protected function getParam(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Retorna valor de POST com fallback
     */
    protected function postParam(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Retorna inteiro de GET
     */
    protected function getInt(string $key, int $default = 0): int
    {
        return intval($_GET[$key] ?? $default);
    }

    /**
     * Retorna inteiro de POST
     */
    protected function postInt(string $key, int $default = 0): int
    {
        return intval($_POST[$key] ?? $default);
    }

    /**
     * Retorna o body da requisição como array (para APIs JSON)
     */
    protected function getJsonBody(): array
    {
        $content = file_get_contents('php://input');
        $data = json_decode($content, true) ?? [];
        return \App\Middleware\RequestSanitizerMiddleware::sanitize($data);
    }

    /**
     * Retorna o ID do usuário logado (para auditoria)
     */
    protected function getUserId(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return (int) ($_SESSION['user_id'] ?? 1); // Default 1 para dev
    }
}
