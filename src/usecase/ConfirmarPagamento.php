<?php

namespace Rodrigotavares\Pedidos\usecase;

use Rodrigotavares\Pedidos\Domain\Pedido;
use Rodrigotavares\Pedidos\port\ContratoRepositorioPedido;
use Rodrigotavares\Pedidos\port\ContratoNotificadorPedido;

Class ConfirmarPagamento{

    public function __construct(
        private ContratoRepositorioPedido $repositorio,
        private ContratoNotificadorPedido $mensageiro
    ){

    }

    public function confirmar(int $id,float $valorPago){
        $pedido = $this->repositorio->buscar($id);
        $isConfirmed = $pedido->confirmarPagamento($valorPago);

        if($isConfirmed):
            $this->mensageiro->notificar($pedido);
        endif;
    }

}