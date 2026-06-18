<?php

namespace Rodrigotavares\Pedidos\Domain;
use Rodrigotavares\Pedidos\Domain\Exceptions\StatusNaoPermitidoException;
use Rodrigotavares\Pedidos\Domain\Exceptions\ValorNaoConfereException;

enum StatusPedido: string
{
    case Pendente = 'pendente';
    case Pago = 'pago';
    case Cancelado = 'cancelado';
}

class Pedido
{
    
    public function __construct(private int $id, /*private string $emailCliente,*/ private float $valor, private string $status)
    {

    }

    public function id():int
    {
        return $this->id;
    }

    public function valor():float
    {
        return $this->valor;
    }

    public function status():string
    {
        return $this->status;
    }

    /*
    public function emailCliente():string
    {
        return $this->emailCliente;
    }
    */

    public function garantirStatusValidoParaPagamento():void
    {
        if(StatusPedido::Pendente->value != $this->status()):
            throw new StatusNaoPermitidoException();
        endif;
    }

    public function garantirValogPagoConfereValorDoPedido(float $valorPago):void
    {
        if($valorPago != $this->valor()):
            throw new ValorNaoConfereException();
        endif;
    }

    public function confirmarPagamento(float $valorPago):bool
    {
        $this->garantirStatusValidoParaPagamento();
        $this->garantirValogPagoConfereValorDoPedido($valorPago);
        $this->status = StatusPedido::Pago->value;
        return true;
    }
}