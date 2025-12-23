<?php
namespace App\Core;

class ViewHelper {
    // Verifica se a rota atual contém o nome desejado
    public static function isRouteActive($rotaDesejada) {
        $rotaAtual = $_SERVER['REQUEST_URI'];
        // Usa regex para match mais preciso (evita que 'cardapio' match em outras rotas)
        return preg_match('/\/' . preg_quote($rotaDesejada, '/') . '(\/|$|\?)/', $rotaAtual) === 1;
    }
}
