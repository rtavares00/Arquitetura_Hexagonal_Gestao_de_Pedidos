<?php
namespace Rodrigotavares\Pedidos\port;
use Rodrigotavares\Pedidos\Domain\Pedido;
interface ContratoNotificadorPedido{
    
    public function notificar(Pedido $pedido);

}