<?php

require_once __DIR__ . '/vendor/autoload.php';

use Rodrigotavares\Pedidos\adapter\PedidoRepositoryEmMemoria;
use Rodrigotavares\Pedidos\adapter\NotificadorPedidoConsole;
use Rodrigotavares\Pedidos\usecase\ConfirmarPagamento;

$repositorio = new PedidoRepositoryEmMemoria();
$mensageiro = new NotificadorPedidoConsole();
$ConfirmarPagamento = new ConfirmarPagamento($repositorio,$mensageiro);
$ConfirmarPagamento->confirmar(1, 150.00);

