<?php

namespace Rodrigotavares\Pedidos\Domain\Exceptions;

use DomainException;

class ValorNaoConfereException extends DomainException
{
    public function __construct(string $mensagem = "O Valor do Pagamento não está conferindo com o Valor do Pedido")
    {
        parent::__construct($mensagem);
    }
}
