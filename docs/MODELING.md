# Modelagem — PDV KasterWeb

Documento de modelagem do domínio, das rotas e das telas, derivado do enunciado em [`PROJECT.md`](PROJECT.md).

Banco de dados: **SQLite**.

## Escopo

### Dentro do escopo

- Busca de produtos por nome ou código.
- Carrinho (adicionar, ajustar quantidade, remover) com total calculado em tempo real.
- Finalização da venda com forma de pagamento; em dinheiro, valor recebido e troco.
- Consulta de venda finalizada (comprovante).
- Login do operador.
- Bônus: histórico de vendas do dia e controle de estoque.

### Fora do escopo

- **Cadastro/edição de produtos.** Não é responsabilidade de uma frente de caixa: em um cenário real, o catálogo vem de outro sistema (ERP/retaguarda). Os produtos são fornecidos por seeder.
- **Auto-registro de usuários.** Operadores não se cadastram sozinhos; o usuário de exemplo é criado por seeder e o registro do Fortify fica desativado.

## Entidades

Valores monetários são armazenados em **centavos (`int`)** para evitar erros de arredondamento de ponto flutuante.

Os `uuid` são gerados automaticamente (`HasUuids`, UUIDv7) e não são atribuíveis em massa. Campos calculados pelo backend (`total_cents`, `user_id`, snapshots do item) também ficam fora do `fillable` e são preenchidos explicitamente.

### User (operador)

| Campo      | Tipo     | Observação                 |
| ---------- | -------- | -------------------------- |
| `id`       | int      | PK                         |
| `name`     | string   |                            |
| `email`    | string   | único                      |
| `password` | string   | hash (Fortify)             |

### Product

| Campo         | Tipo   | Observação                                                         |
| ------------- | ------ | ------------------------------------------------------------------ |
| `id`          | int    | PK                                                                 |
| `uuid`        | uuid   | identificador público                                              |
| `code`        | string | único; código de barras (EAN pode ter zeros à esquerda)            |
| `name`        | string | indexado para busca                                                |
| `price_cents` | int    | preço atual                                                        |
| `stock`       | int    | opcional (bônus)                                                   |
| `is_active`   | bool   | produtos inativos não entram em novas vendas                       |

### Order (venda)

| Campo                   | Tipo          | Observação                                       |
| ----------------------- | ------------- | ------------------------------------------------ |
| `id`                    | int           | PK                                               |
| `uuid`                  | uuid          | identificador público e chave de rota (URL do comprovante não enumerável) |
| `user_id`               | int           | FK → `users` (operador que realizou a venda)     |
| `payment_method`        | PaymentMethod | enum armazenado como inteiro                     |
| `total_cents`           | int           | calculado pelo backend                           |
| `amount_received_cents` | int, nulo     | apenas para pagamento em dinheiro                |
| `change_cents`          | int, nulo     | accessor (não persistido): `amount_received_cents - total_cents` |
| `created_at`            | timestamp     | momento da finalização                           |

### OrderItem

| Campo              | Tipo   | Observação                                             |
| ------------------ | ------ | ------------------------------------------------------ |
| `id`               | int    | PK                                                     |
| `order_id`         | int    | FK → `orders`                                          |
| `product_id`       | int    | FK → `products`                                        |
| `product_name`     | string | snapshot do nome para o comprovante                    |
| `unit_price_cents` | int    | snapshot do preço no momento da venda                  |
| `quantity`         | int    | ≥ 1                                                    |
| `subtotal_cents`   | int    | accessor (não persistido): `unit_price_cents * quantity` |

Restrição única em (`order_id`, `product_id`): o mesmo produto repetido no carrinho é agrupado em um único item.

### PaymentMethod (enum)

Backed enum PHP (`int`, em `app/Enums`) com cast no model `Order`:

- `CASH` (0) — dinheiro (exige valor recebido)
- `CREDIT_CARD` (1) — cartão de crédito
- `DEBIT_CARD` (2) — cartão de débito
- `PIX` (3)

### Relacionamentos

```
User 1 ──── N Order 1 ──── N OrderItem N ──── 1 Product
```

## Regras de negócio

| Regra do enunciado                                        | Como é garantida                                                                                  |
| --------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| Total e subtotais são confiáveis                          | O cliente envia apenas `product_uuid` e `quantity`; preços e totais são calculados no backend.    |
| Preço no momento da venda fica registrado                 | `order_items.unit_price_cents` (e `product_name`) copiados do produto na criação da venda; o subtotal é calculado a partir deles. |
| Uma venda tem um ou mais itens                            | Validação `items` obrigatório com `min:1`.                                                        |
| Em dinheiro, valor recebido ≥ total; troco é a diferença  | Validação após o cálculo do total; `change_cents` calculado e persistido.                         |
| Produtos indisponíveis não entram em novas vendas         | Validação de `is_active` (e de estoque, se implementado) para todos os itens.                     |
| Venda finalizada não é alterada                           | A venda já nasce finalizada; não existem rotas de update/delete para pedidos.                     |

A criação da venda ocorre dentro de uma **transação**. Com controle de estoque, os produtos são lidos com `lockForUpdate()` e o estoque é decrementado na mesma transação.

## Rotas

Todas as rotas exigem autenticação.

| Método | Rota                    | Tipo    | Descrição                                                                                 |
| ------ | ----------------------- | ------- | ----------------------------------------------------------------------------------------- |
| GET    | `/`                     | Inertia | Tela do PDV                                                                               |
| GET    | `/products?search=...`  | JSON    | Busca: correspondência exata por `code` ou parcial (`LIKE`) por `name`; apenas ativos     |
| POST   | `/orders`               | Inertia | Cria e finaliza a venda; em caso de sucesso, redireciona para o comprovante               |
| GET    | `/orders/{uuid}`        | Inertia | Comprovante/resumo da venda                                                               |
| GET    | `/orders?date=today`    | Inertia | Histórico de vendas do dia (bônus)                                                        |

### Payload de `POST /orders`

```json
{
  "items": [
    { "product_uuid": "…", "quantity": 2 }
  ],
  "payment_method": 0,
  "amount_received_cents": 5000
}
```

`amount_received_cents` é obrigatório apenas quando `payment_method` é `CASH`.

### Decisão: venda criada e paga em uma única requisição

Foi considerado o fluxo em duas etapas (`POST /orders` + `POST /orders/{uuid}/payment`), mas ele introduz um estado de "pedido pendente" que o enunciado não exige, além de pedidos abandonados e dúvidas sobre reserva de estoque. Mantendo o carrinho no frontend e enviando tudo de uma vez, a venda é atômica e já nasce finalizada.

### Decisão: busca como endpoint JSON

A busca é disparada enquanto o operador digita (ou lê um código de barras), então é consumida via `useHttp` (Inertia v3) em vez de uma visita de página. Um único parâmetro `search` cobre tanto código quanto nome.

## Telas

### Login

Tela de autenticação do starter kit (Fortify), sem registro.

### PDV (página principal)

- Campo de busca com resultados (estados: carregando, sem resultados, erro).
- Carrinho: lista de itens com quantidade ajustável, remoção e subtotal (estado vazio inicial).
- Total da venda calculado em tempo real (exibição; o valor final é do backend).
- **Modal de pagamento**: seleção da forma de pagamento; em dinheiro, campo de valor recebido com troco calculado em tempo real e bloqueio quando o valor é menor que o total. Confirmar envia `POST /orders`.

O pagamento é um modal na própria tela, e não uma página separada, para não perder o estado do carrinho na navegação.

### Comprovante (`/orders/{uuid}`)

Itens com preço unitário registrado, quantidades, subtotais, total, forma de pagamento, valor recebido e troco, operador e data/hora. Ação para iniciar nova venda.

### Histórico do dia (bônus)

Lista das vendas do dia com link para cada comprovante.
