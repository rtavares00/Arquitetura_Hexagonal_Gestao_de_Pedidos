<?php
namespace Rodrigotavares\Pedidos\port;
use Rodrigotavares\Pedidos\Domain\Pedido;
use Rodrigotavares\Pedidos\Domain\NotificadorPedidoConsole;
interface ContratoNotificadorPedido{
    
    public function notificar(Pedido $pedido);

}