<?php

namespace App\Exceptions;

use Exception;

/**
 * DatabaseConnectionException
 * 
 * Exceção customizada para erros de conexão com o banco de dados.
 * Substitui o uso de die() para permitir tratamento adequado de erros.
 */
class DatabaseConnectionException extends Exception
{
    /**
     * Construtor
     * 
     * @param string $message Mensagem de erro
     * @param int $code Código de erro
     * @param Exception|null $previous Exceção anterior
     */
    public function __construct(string $message = '', int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retorna mensagem segura para o usuário (sem detalhes técnicos)
     * 
     * @return string
     */
    public function getUserMessage(): string
    {
        return 'Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.';
    }
}
