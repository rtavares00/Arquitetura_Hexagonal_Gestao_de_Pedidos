<?php

namespace Rodrigotavares\Pedidos\adapter\Exceptions\PedidoRepositoryEmMemoria;

use Exception;

class PedidoNaoEncontradoException extends Exception
{
    public function __construct(string $mensagem = "O Pedido não foi localizado na memória do programa")
    {
        parent::__construct($mensagem);
    }
}
