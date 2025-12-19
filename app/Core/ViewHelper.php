<?php
namespace App\Core;

class ViewHelper {
    
    // Função estática para verificar se o link é o atual
    public static function isActive($rotaDesejada) {
        // Pega a URL atual do navegador
        $rotaAtual = $_SERVER['REQUEST_URI'];
        
        // Se a rota atual contém o nome (ex: 'pdv'), retorna as classes de ativo
        if (strpos($rotaAtual, $rotaDesejada) !== false) {
            return 'bg-blue-600 text-white shadow-lg shadow-blue-900/40';
        }
        
        // Se não, retorna classes padrão
        return 'text-gray-400 hover:bg-gray-800 hover:text-white';
    }
}
