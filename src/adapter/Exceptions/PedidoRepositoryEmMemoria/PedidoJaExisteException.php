<?php

namespace Rodrigotavares\Pedidos\adapter\Exceptions\PedidoRepositoryEmMemoria;

use Exception;

class PedidoJaExisteException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("O Pedido de id {$id} já existe na memória do programa");
    }
}
