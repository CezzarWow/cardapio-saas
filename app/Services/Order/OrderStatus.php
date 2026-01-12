<?php

namespace App\Services\Order;

/**
 * Enum de Status de Pedido
 * 
 * Centraliza todos os status válidos para evitar strings soltas.
 * Usado em todos os fluxos (Balcão, Mesa, Comanda, Delivery).
 */
final class OrderStatus
{
    // Status OPERACIONAIS (Pedidos)
    public const NOVO = 'novo';
    public const AGUARDANDO = 'aguardando';
    public const EM_PREPARO = 'em_preparo';
    public const PRONTO = 'pronto';
    public const EM_ENTREGA = 'em_entrega';
    public const ENTREGUE = 'entregue';
    
    // Status FINANCEIROS (Contas)
    public const ABERTO = 'aberto';
    
    // Status FINAIS
    public const CONCLUIDO = 'concluido';
    public const CANCELADO = 'cancelado';
    
    /**
     * Retorna todos os status válidos
     */
    public static function all(): array
    {
        return [
            self::NOVO,
            self::AGUARDANDO,
            self::EM_PREPARO,
            self::PRONTO,
            self::EM_ENTREGA,
            self::ENTREGUE,
            self::ABERTO,
            self::CONCLUIDO,
            self::CANCELADO,
        ];
    }
    
    /**
     * Verifica se um status é válido
     */
    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }
    
    /**
     * Status que indicam pedido fechado
     */
    public static function isFinal(string $status): bool
    {
        return in_array($status, [self::CONCLUIDO, self::CANCELADO], true);
    }
}
