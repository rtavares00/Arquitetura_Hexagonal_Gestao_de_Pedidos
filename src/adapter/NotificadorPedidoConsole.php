<?php

namespace Rodrigotavares\Pedidos\adapter;
use Rodrigotavares\Pedidos\Domain\Pedido;
use Rodrigotavares\Pedidos\port\ContratoNotificadorPedido;

final class NotificadorPedidoConsole implements ContratoNotificadorPedido
{
    public function __construct(){

    }

    public function notificar(Pedido $pedido):void
    {
        echo "Pedido #{$pedido->id()} confirmado. Status: {$pedido->status()}." . PHP_EOL;
    }
}