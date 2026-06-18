<?php

namespace Rodrigotavares\Pedidos\Domain\Exceptions;

use Exception;

class StatusNaoPermitidoException extends Exception
{
    public function __construct(string $mensagem = "O Status do Pagamento não pode ser diferente de Pendente para efeito de confirmação do pagamento")
    {
        parent::__construct($mensagem);
    }
}
