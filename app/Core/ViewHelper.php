<?php
namespace App\Core;

class ViewHelper {
    // Verifica se a rota atual contém o nome desejado
    public static function isRouteActive($rotaDesejada) {
        $rotaAtual = $_SERVER['REQUEST_URI'];
        return strpos($rotaAtual, $rotaDesejada) !== false;
    }
}
