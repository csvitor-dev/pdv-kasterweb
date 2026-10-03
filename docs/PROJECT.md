# Teste Prático — Frente de Caixa (PDV)

## Objetivo
Construir uma frente de caixa simples, com um backend que sirva os dados e um
frontend para o operador usar no dia a dia.

Queremos entender como você pensa, organiza o código e resolve problemas do
mundo real. Não existe um jeito único de fazer: tome as decisões que fizerem
sentido e saiba explicá-las depois.

## O que construir
Uma tela de PDV onde o operador consegue:
- Buscar produtos (por nome ou código).
- Adicionar produtos ao carrinho, ajustar quantidade e remover itens.
- Ver o total da venda sendo calculado enquanto monta o carrinho.
- Finalizar a venda informando a forma de pagamento.
- No pagamento em dinheiro, informar o valor recebido e ver o troco.
- Consultar uma venda já finalizada (comprovante/resumo).

O backend deve expor os dados e receber as vendas. O frontend deve consumir
esse backend.

## Regras de negócio
- O total e os subtotais da venda são confiáveis: considere que o cliente
  pode enviar qualquer coisa, então o valor final precisa ser garantido pela
  sua aplicação.
- O preço do produto no momento da venda deve ficar registrado na venda,
  mesmo que o preço do produto mude depois.
- Uma venda tem um ou mais itens.
- No pagamento em dinheiro, o valor recebido não pode ser menor que o total,
  e o troco é a diferença.
- Produtos que não estão mais disponíveis não devem entrar em novas vendas.
- Uma venda finalizada não deve ser alterada.

## Stack
- Backend: Laravel.
- Frontend: React. (com TypeScript é bem-vindo, mas não obrigatório)

Use o que você já conhece de cada stack. Bibliotecas extras são bem-vindas,
desde que justificadas.

## O que avaliaremos
- **Backend:** como você modela os dados, garante as regras de negócio,
  valida as entradas e organiza o código (rotas, controllers, serviços,
  testes).
- **Frontend:** componentização, consumo da API, estados de carregamento /
  erro / vazio, tratamento de falhas, cálculo do troco e usabilidade.
- **Código:** legibilidade, nomes claros, organização e segurança básica.

## Entrega
- Repositório Git com o projeto.
- README explicando como rodar (backend e frontend) e as decisões tomadas.
- Massa de dados de exemplo (produtos) para testar sem digitar tudo na mão.

## Prazo
1 a 5 dias. Não precisa entregar tudo: prefira um escopo menor, bem feito e
explicado, do que muita coisa inacabada.

## Bônus (opcional)
- Testes automatizados das regras principais.
- Login/autenticação.
- Histórico de vendas do dia.
- Cuidados com estoque.
