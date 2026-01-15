<?php

namespace App\Core;

class View
{
    /**
     * Renderiza uma view a partir da pasta /views
     * @param string $view Caminho relativo dentro de /views sem extensão (ex: 'admin/panel/dashboard')
     * @param array $data Variáveis a disponibilizar na view
     */
    public static function render(string $view, array $data = []): void
    {
        $viewPath = ltrim($view, '/');
        if (!str_ends_with($viewPath, '.php')) {
            $viewPath .= '.php';
        }
        $file = __DIR__ . '/../../views/' . $viewPath;

        if (!file_exists($file)) {
            throw new \Exception("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);
        include $file;
    }

    /**
     * Renderiza uma view usando o array de variáveis do escopo do chamador.
     * Remove automaticamente chaves perigosas como 'this' e 'GLOBALS'.
     * @param string $view
     * @param array $scope
     */
    public static function renderFromScope(string $view, array $scope): void
    {
        $viewPath = ltrim($view, '/');
        if (!str_ends_with($viewPath, '.php')) {
            $viewPath .= '.php';
        }
        $file = __DIR__ . '/../../views/' . $viewPath;

        if (!file_exists($file)) {
            throw new \Exception("View not found: {$view}");
        }

        // Protege variáveis reservadas e grandes arrays
        unset($scope['this']);
        unset($scope['GLOBALS']);
        unset($scope['_SERVER'], $scope['_GET'], $scope['_POST'], $scope['_FILES'], $scope['_COOKIE'], $scope['_REQUEST'], $scope['_ENV']);

        extract($scope, EXTR_SKIP);
        include $file;
    }
}
