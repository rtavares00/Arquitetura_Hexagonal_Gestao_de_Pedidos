<?php

namespace Rodrigotavares\Pedidos\port;

use Rodrigotavares\Pedidos\Domain\Pedido;

interface ContratoRepositorioPedido{

    public function buscar(int $id):Pedido;
    public function salvar(Pedido $pedido);

}