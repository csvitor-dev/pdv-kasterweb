# PDV KasterWeb — Frente de Caixa

Frente de caixa (PDV) para o operador do dia a dia: busca de produtos por nome ou código de barras, carrinho, pagamento com cálculo de troco e comprovante da venda. Backend em Laravel e frontend em React + TypeScript, desenvolvido como teste prático. O enunciado está em [`docs/PROJECT.md`](docs/PROJECT.md) e a modelagem em [`docs/MODELING.md`](docs/MODELING.md).

## Escopo atendido

### Requisitos

| Requisito do enunciado                        | Como foi atendido                                                                                                                       |
| --------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| Buscar produtos por nome ou código            | Um único campo: parte do código ou do nome, com debounce. Enter adiciona o produto lido pelo leitor de código de barras.                |
| Adicionar, ajustar quantidade e remover itens | Carrinho com +/−, campo de quantidade (limitado ao estoque) e remoção. Persiste no `sessionStorage`, então um reload não perde a venda. |
| Ver o total enquanto monta o carrinho         | Total e subtotais atualizados em tempo real.                                                                                            |
| Finalizar informando a forma de pagamento     | Modal com Dinheiro, Cartão de crédito, Cartão de débito e PIX.                                                                          |
| Dinheiro: valor recebido e troco              | Troco em tempo real, atalhos ("Valor exato" e notas), confirmação bloqueada se faltar dinheiro.                                         |
| Consultar uma venda finalizada                | Página de comprovante, com versão para impressão.                                                                                       |
| Massa de dados de exemplo                     | Seeder com 22 produtos (um inativo e um sem estoque) e um operador.                                                                     |

### Regras de negócio

Todas garantidas no backend e cobertas por testes de integração:

- **Total e subtotais confiáveis:** o cliente envia só produto e quantidade; preços, total e troco são calculados no servidor, e qualquer valor enviado é ignorado.
- **Preço registrado na venda:** nome e preço unitário são copiados para o item; mudanças posteriores no produto não alteram vendas antigas.
- **Venda com um ou mais itens:** venda sem itens é rejeitada.
- **Dinheiro:** valor recebido menor que o total é rejeitado; o troco é a diferença.
- **Produtos indisponíveis:** inativos ou sem estoque não aparecem na busca e são rejeitados na venda, com o erro mostrado na linha do carrinho.
- **Venda finalizada não muda:** a venda já nasce finalizada e não há rotas de alteração ou exclusão (`405`).

### Bônus

- **Testes automatizados** das regras principais.
- **Login** do operador.
- **Vendas do dia:** quantidade, total vendido e lista com link para cada comprovante.
- **Estoque:** baixado na venda, em transação com os produtos travados.

## Como rodar

Requisitos: PHP 8.3+ (desenvolvido com 8.4) com as extensões padrão do Laravel, Composer 2, Node.js 22+ e npm.

```bash
gh repo clone csvitor-dev/pdv-kasterweb
cd pdv-kasterweb
composer setup        # dependências, .env, APP_KEY, banco SQLite, migrations e build do frontend
php artisan db:seed   # operador e produtos de exemplo
composer dev          # servidor Laravel + Vite
```

Acesse <http://localhost:8000> e entre com `test@example.com` / `password`.

Backend e frontend sobem juntos: o React é servido pelo Laravel via Inertia, sem dois projetos separados.

### Testes e qualidade

```bash
php artisan test      # suíte PHPUnit
composer test         # Pint (check), PHPStan e PHPUnit
composer ci:check     # tudo, inclusive lint/formatação e tipos do frontend, como na CI
```

## Arquitetura

```
app/
  Http/Controllers/Api/   # API JSON (/api): busca de produtos, criação e consulta de vendas
  Http/Controllers/       # páginas Inertia: PDV, comprovante, vendas do dia
  Http/Requests/          # validação do formato da entrada → Inputs
  Http/Resources/         # contrato de saída (usado pela API e pelas páginas)
  Services/               # regras de negócio e Inputs (DTOs)
  Repositories/           # acesso a dados, atrás de interfaces
  Exceptions/Domain/      # violações de regra, respondidas como 422
resources/js/
  pages/                  # pos/, orders/ (comprovante e vendas do dia), auth/, settings/
  components/pos/         # busca, carrinho, resumo e modal de pagamento
  hooks/                  # useCart, useProductSearch, useCreateOrder
tests/Feature/            # testes de integração (HTTP → banco)
```

Fluxo do backend: `Controller → Service → Repository`, com um controller por endpoint. Detalhes, rotas, payloads e formato de erros estão em [`docs/MODELING.md`](docs/MODELING.md).

## Decisões

- **Um projeto, Inertia para páginas e API JSON para dados.** Sem CORS nem dois deploys. A busca e a criação da venda usam `useHttp` contra `/api`, e as rotas são tipadas no front com Wayfinder.
- **Sanctum em modo SPA.** A API usa o mesmo cookie de sessão do login; tokens Bearer ficam disponíveis para outros clientes.
- **Venda criada e paga em uma única requisição.** O carrinho fica no front e a venda nasce finalizada, sem estado de "pedido pendente".
- **Dinheiro em centavos (`int`)**, para evitar erros de arredondamento.
- **Uma única definição das formas de pagamento.** O enum PHP envia rótulo e se exige valor recebido; o front não fixa valores.
- **Testes de integração por regra de negócio.** Cada teste entra pela requisição HTTP e atravessa todas as camadas até o banco (SQLite em memória), sem mocks.
- **Só login.** Operadores são cadastrados fora do PDV (aqui, pelo seeder). Registro, redefinição de senha, verificação de e-mail, 2FA e passkeys do starter kit foram removidos.
- **Fuso de negócio.** Datas em UTC; o "dia" das vendas do dia segue `APP_BUSINESS_TIMEZONE` (padrão `America/Sao_Paulo`).
- **pt-BR.** Mensagens de validação e de negócio em português, com nomes amigáveis para os campos.
- **SQLite e starter kit oficial (Laravel + React).** Nada para instalar além de PHP e Node, e autenticação, UI e tooling prontos para focar no domínio.

## Fora do escopo e próximos passos

- Cadastro e edição de produtos: em um PDV real o catálogo vem de outro sistema (ERP/retaguarda).
- Textos de interface herdados do starter kit (login, menu do usuário, configurações) ainda em inglês.
- Atalhos de teclado além de Enter/Esc na busca.
- Testes automatizados do frontend (o projeto não tem test runner de JavaScript).
