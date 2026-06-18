# Pedidos — Estudo de Arquitetura Hexagonal

Sistema simples, em PHP puro, criado para estudar **Arquitetura Hexagonal**
(*Ports & Adapters*). O caso de uso central é a **confirmação de pagamento de
um pedido**: buscar o pedido, validar suas regras de negócio, persistir a
mudança de status e notificar o cliente.

> Projeto irmão de estudo: `LOJA_VENDE_PRODUTO` (venda de produto).

---

## 📑 Sumário

- [Objetivo](#-objetivo)
- [O que é Arquitetura Hexagonal](#-o-que-é-arquitetura-hexagonal)
- [Estrutura de diretórios](#-estrutura-de-diretórios)
- [As camadas em detalhe](#-as-camadas-em-detalhe)
- [Fluxo da confirmação de pagamento](#-fluxo-da-confirmação-de-pagamento)
- [Regras de negócio do Pedido](#-regras-de-negócio-do-pedido)
- [Como executar](#-como-executar)
- [Decisões de design](#-decisões-de-design)
- [Limitações conhecidas e próximos passos](#-limitações-conhecidas-e-próximos-passos)

---

## 🎯 Objetivo

Demonstrar, num exemplo pequeno e legível, como a Arquitetura Hexagonal
isola o **núcleo da aplicação** (domínio + casos de uso) dos **detalhes de
infraestrutura** (banco, console, e-mail, etc.), de modo que:

- as regras de negócio não dependem de tecnologia;
- trocar um detalhe (ex.: notificar por e-mail em vez de console) **não toca**
  no domínio nem no caso de uso;
- o código fica testável, porque tudo que é externo entra por **interfaces**.

---

## 🧭 O que é Arquitetura Hexagonal

A ideia-chave é a **regra da dependência**: a seta sempre aponta **para
dentro**. O núcleo não conhece o mundo externo; quem conhece o núcleo é a
infraestrutura.

```
                 ┌─────────────────────────────────────────┐
                 │                 ADAPTERS                  │
                 │   (detalhes: memória, console, e-mail)    │
                 │                                           │
                 │     ┌───────────────────────────────┐    │
                 │     │            PORTS               │    │
                 │     │   (interfaces / contratos)     │    │
                 │     │                                │    │
                 │     │   ┌────────────────────────┐   │    │
                 │     │   │       USE CASES        │   │    │
                 │     │   │  (orquestra o fluxo)   │   │    │
                 │     │   │   ┌────────────────┐   │   │    │
                 │     │   │   │     DOMAIN     │   │   │    │
                 │     │   │   │ (regras puras) │   │   │    │
                 │     │   │   └────────────────┘   │   │    │
                 │     │   └────────────────────────┘   │    │
                 │     └───────────────────────────────┘    │
                 └─────────────────────────────────────────┘

        Dependências apontam SEMPRE para o centro  ───►
```

- **Domain**: entidades e regras de negócio. Não importa nada de fora.
- **Ports**: interfaces que o núcleo define ("eu preciso de alguém que saiba
  buscar/salvar pedido", "alguém que saiba notificar"). São **contratos**.
- **Use Cases**: orquestram o fluxo usando apenas os ports (interfaces).
- **Adapters**: implementações concretas dos ports (memória, console, …).
- **Composition Root** (`index.php`): o único lugar que conhece todo mundo —
  ele instancia os adapters e injeta nos casos de uso.

---

## 📁 Estrutura de diretórios

```
PEDIDOS/
├── index.php                      # Composition Root (monta e dispara o fluxo)
├── composer.json                  # Autoload PSR-4: Rodrigotavares\Pedidos\ -> src/
├── README.md
└── src/
    ├── Domain/                    # NÚCLEO — regras de negócio
    │   ├── Pedido.php             #   Entidade Pedido + enum StatusPedido
    │   └── Exceptions/            #   Exceções de domínio (\DomainException)
    │       ├── StatusNaoPermitidoException.php
    │       └── ValorNaoConfereException.php
    │
    ├── usecase/                   # CASOS DE USO — orquestração
    │   └── ConfirmarPagamento.php
    │
    ├── port/                      # PORTS — interfaces (contratos)
    │   ├── ContratoRepositorioPedido.php
    │   └── ContratoNotificadorPedido.php
    │
    └── adapter/                   # ADAPTERS — implementações concretas
        ├── PedidoRepositoryEmMemoria.php   # repositório fake (em memória)
        ├── NotificadorPedidoConsole.php    # notifica imprimindo no console
        └── Exceptions/
            └── PedidoRepositoryEmMemoria/  # exceções específicas do adapter
                ├── PedidoNaoEncontradoException.php
                └── PedidoJaExisteException.php
```

> **Namespace base:** `Rodrigotavares\Pedidos\` mapeado para `src/` via PSR-4.
> Por isso o nome de cada arquivo é **igual** ao da classe/interface que ele
> contém (exigência do PSR-4).

---

## 🔍 As camadas em detalhe

### Domain — `src/Domain/Pedido.php`

Entidade `Pedido` com seus dados (`id`, `valor`, `status`) e as **regras de
negócio** do próprio pedido. O status é tipado pelo enum `StatusPedido`
(`Pendente`, `Pago`, `Cancelado`) — não é uma string solta, então um status
inválido sequer consegue existir.

As validações são *guard clauses* (cláusulas de guarda) privadas, que
**lançam exceção** quando a regra é violada:

| Método | Garante que… | Exceção |
|--------|--------------|---------|
| `garantirStatusValidoParaPagamento()` | o pedido está `Pendente` | `StatusNaoPermitidoException` |
| `garantirValogPagoConfereValorDoPedido()` | o valor pago bate com o do pedido | `ValorNaoConfereException` |

### Ports — `src/port/`

Interfaces que o núcleo exige do mundo externo:

- `ContratoRepositorioPedido` — `buscar(int $id): Pedido` e `salvar(Pedido): void`.
- `ContratoNotificadorPedido` — `notificar(Pedido): void`.

### Use Case — `src/usecase/ConfirmarPagamento.php`

Orquestra o fluxo dependendo **apenas das interfaces** (repositório e
notificador são injetados no construtor). Não sabe se o repositório é memória
ou MySQL, nem se a notificação é console ou e-mail.

### Adapters — `src/adapter/`

- `PedidoRepositoryEmMemoria` — guarda pedidos num array; já vem populado
  (`seed()`) com 3 pedidos fake. `salvar()` faz **upsert** (atualiza se o id
  já existe, insere se não).
- `NotificadorPedidoConsole` — "notifica" imprimindo no terminal. É a versão
  de desenvolvimento; um futuro `NotificadorPedidoEmail` implementaria a mesma
  interface sem alterar o núcleo.

---

## 🔄 Fluxo da confirmação de pagamento

```
index.php (Composition Root)
   │  injeta PedidoRepositoryEmMemoria + NotificadorPedidoConsole
   ▼
ConfirmarPagamento::confirmar(id, valorPago)
   │
   ├─ 1. $repositorio->buscar(id) ............... obtém o Pedido
   ├─ 2. $pedido->confirmarPagamento(valorPago) . valida regras e muda status p/ Pago
   ├─ 3. $repositorio->salvar($pedido) .......... persiste a mudança
   └─ 4. $notificador->notificar($pedido) ....... avisa o cliente
```

Se qualquer regra do passo 2 falhar, uma exceção é lançada e o fluxo é
interrompido **antes** de salvar/notificar.

---

## 📐 Regras de negócio do Pedido

Para um pagamento ser confirmado:

1. O pedido precisa estar com status **`Pendente`**
   (senão → `StatusNaoPermitidoException`).
2. O **valor pago** precisa ser **igual** ao valor do pedido
   (senão → `ValorNaoConfereException`).

Cumpridas as duas, o status passa para **`Pago`**.

---

## ▶️ Como executar

**Pré-requisitos:** PHP 8.1+ (o projeto usa enums; testado em PHP 8.3) e
Composer.

```bash
# 1. Instalar o autoload (não há dependências externas)
composer install

# 2. Rodar o exemplo
php index.php
```

Saída esperada:

```
Pedido #1 confirmado. Status: pago.
```

---

## 🧠 Decisões de design

- **Enum como tipo de verdade.** `StatusPedido` é o tipo da propriedade
  `status`, não uma string. Isso elimina conversões `->value` espalhadas e
  impede estados inválidos.
- **Exceções por contexto.** Regras do domínio lançam exceções de domínio
  (estendem `\DomainException`); falhas de infraestrutura ficam em exceções
  do adapter (ex.: `PedidoNaoEncontradoException`), isoladas por repositório
  em `adapter/Exceptions/PedidoRepositoryEmMemoria/`.
- **Guard clauses em vez de retorno booleano.** `confirmarPagamento()` é
  `void`: ou conclui com sucesso, ou lança. Sem `if ($confirmou)` redundante
  no caso de uso.
- **Injeção de dependência no construtor.** Colaboradores (repositório,
  notificador) entram pelo construtor; dados da operação (id, valor) entram
  pelo método. Isso mantém o núcleo desacoplado e testável.

---

## ⚠️ Limitações conhecidas e próximos passos

- **`float` para dinheiro é arriscado** (erros de arredondamento, ex.:
  `0.1 + 0.2 != 0.3`). Evolução natural: usar `int` em centavos ou um
  *value object* `Dinheiro`.
- **Persistência apenas em memória** — os dados se perdem ao fim da execução.
  Um adapter real (MySQL, arquivo, etc.) implementaria o mesmo
  `ContratoRepositorioPedido`.
- **Notificação só por console** — falta canal real (e-mail/SMS), que exigirá
  o `Pedido` carregar o contato do cliente (há um `emailCliente` já previsto,
  comentado, na entidade).
