<?php

namespace App\Constants;
use ReflectionClass;

class Lanes
{
    public const TODO = 'todo';
    public const WAITING = 'wating';
    public const DONE = 'done';
    public const EXTRA = 'extra';
    public const NEXT = 'next';

    /**
     * Retorna uma string com todas as constantes da classe, separadas por vírgula.
     *
     * @return string
     */
    public static function getAllAsString(): string
    {
        // Pega todas as constantes definidas na classe
        $constants = (new ReflectionClass(__CLASS__))->getConstants();
        return implode(', ', $constants);
    }

    /**
     *  @return array<string>
     */
    public static function getAllAsArray(){
        // Pega todas as constantes definidas na classe
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    /**
     * Retorna um array com todas as constantes da classe em letras maiúsculas.
     *
     * @return array<string>
     */
    public static function getAllAsUpperArray(): array
    {
        // 1. Pega todas as constantes da classe em um array
        $constants = (new ReflectionClass(__CLASS__))->getConstants();

        // 2. Aplica a função strtoupper em cada elemento do array
        return array_map('strtoupper', $constants);
    }
}
