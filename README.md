# PDV KasterWeb — Frente de Caixa

Frente de caixa (PDV) simples, com backend em Laravel e frontend em React + TypeScript, desenvolvida como teste prático. O enunciado completo está em [`docs/PROJECT.md`](docs/PROJECT.md).

## Stack

| Camada    | Tecnologia                                                        |
| --------- | ----------------------------------------------------------------- |
| Backend   | PHP 8.4, Laravel 13, Laravel Fortify                              |
| Frontend  | React 19, TypeScript, Inertia.js v3, Tailwind CSS v4, Shadcn/ui   |
| Rotas     | Laravel Wayfinder (funções TypeScript tipadas para rotas Laravel) |
| Banco     | SQLite                                                            |
| Qualidade | PHPUnit, Larastan (PHPStan), Laravel Pint                         |
| Build     | Vite                                                              |

## Requisitos

- PHP 8.3+ (desenvolvido com 8.4) com as extensões padrão do Laravel
- Composer 2
- Node.js 22+ e npm

## Como rodar

```bash
gh repo clone csvitor-dev/pdv-kasterweb
cd pdv-kasterweb
composer setup     # dependências, .env, APP_KEY, migrations e build do frontend
php artisan db:seed  # massa de dados de exemplo
composer dev       # servidor Laravel + Vite em paralelo
```

A aplicação fica disponível em <http://localhost:8000>. Entre com o operador de exemplo criado pelo seeder: `test@example.com` / `password`. Backend e frontend rodam juntos: o React é servido pelo Laravel via Inertia, então não é necessário subir dois projetos separados.

## Testes e qualidade

```bash
composer test          # Pint (check), PHPStan e suíte PHPUnit
php artisan test       # apenas a suíte de testes
composer lint          # formata o código PHP com Pint
npm run types:check    # checagem de tipos TypeScript
composer ci:check      # tudo acima, como na CI
```

## Estrutura do projeto

```
app/                 # Código do backend (models, controllers, actions, requests)
database/            # Migrations, factories e seeders
resources/js/        # Frontend React + TypeScript
  pages/             # Páginas Inertia
  components/        # Componentes reutilizáveis
routes/              # Rotas web
tests/               # Testes Feature e Unit (PHPUnit)
docs/                # Enunciado e documentação do projeto
```

## Decisões tomadas

- **Laravel + React via Inertia.js:** um único projeto em vez de backend e SPA separados, sem a sobrecarga de CORS e dois deploys. O Inertia serve as páginas; os dados (busca de produtos, criação e consulta de vendas) vêm de uma API JSON em `/api`. As rotas são tipadas no frontend com Wayfinder.
- **Sanctum em modo SPA:** autentica a API com o mesmo cookie de sessão do login, mantendo o `api.php` sem sessão para outros clientes, que podem usar tokens Bearer.
- **Controller → Service → Repository:** controllers de um endpoint só, Inputs (DTOs) como contrato de entrada dos services, repositories atrás de interfaces e API Resources como contrato de saída. Detalhes em [`docs/MODELING.md`](docs/MODELING.md).
- **Testes de integração por regra de negócio:** cada regra do enunciado é coberta por um teste que entra pela requisição HTTP e atravessa todas as camadas até o banco (SQLite em memória), sem mocks.
- **Starter kit oficial (Laravel React):** já entrega autenticação (Fortify), componentes de UI (Radix + Tailwind) e tooling configurado, permitindo focar no domínio do PDV.
- **SQLite:** banco em arquivo, sem servidor para instalar ou configurar. Suficiente para o escopo do teste e simplifica rodar o projeto.
- **Localização pt_BR:** mensagens de validação, autenticação e regras de negócio em português (`lang/pt_BR`), com nomes amigáveis para os campos da API (ex.: "valor recebido"). O inglês fica como fallback para chaves não traduzidas.
- **Autenticação só com login:** operadores são cadastrados fora do PDV (aqui, pelo seeder). Registro, redefinição de senha, verificação de e-mail, 2FA e passkeys do starter kit foram desativados e removidos, deixando só login e logout.
- **Fuso de negócio:** datas gravadas em UTC; o "dia" das vendas do dia é calculado em `APP_BUSINESS_TIMEZONE` (padrão `America/Sao_Paulo`), para que uma venda às 22h não caia no dia seguinte.
