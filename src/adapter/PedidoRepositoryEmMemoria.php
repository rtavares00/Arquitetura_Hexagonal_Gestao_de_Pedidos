<?php

namespace Rodrigotavares\Pedidos\adapter;
use Rodrigotavares\Pedidos\Domain\Pedido;
use Rodrigotavares\Pedidos\Domain\StatusPedido;
use Rodrigotavares\Pedidos\adapter\Exceptions\PedidoRepositoryEmMemoria\PedidoNaoEncontradoException;
use Rodrigotavares\Pedidos\port\ContratoRepositorioPedido;

final class PedidoRepositoryEmMemoria implements ContratoRepositorioPedido
{
    /** @var Pedido[] lista de pedidos em memoria */
    private array $pedidos = [];

    public function __construct(){
        $this->seed();
    }

    private function seed():void
    {
        // Dados fake para teste em memoria
        array_push($this->pedidos, new Pedido(1, 150.00, StatusPedido::Pendente));
        array_push($this->pedidos, new Pedido(2, 89.90, StatusPedido::Pago));
        array_push($this->pedidos, new Pedido(3, 320.50, StatusPedido::Cancelado));
    }

    public function buscar(int $id):Pedido{

        foreach($this->pedidos as $pedido):
            if($id == $pedido->id()):
                $objPedido = new Pedido($pedido->id(),$pedido->valor(),$pedido->status());
                return $objPedido;
            endif;
        endforeach;

        throw new PedidoNaoEncontradoException();
    }
    public function salvar(Pedido $pedido):void
    {
        foreach($this->pedidos as $indice => $existente):
            if($existente->id() == $pedido->id()):
                $this->pedidos[$indice] = $pedido;
                return;
            endif;
        endforeach;

        array_push($this->pedidos,$pedido);
    }
}