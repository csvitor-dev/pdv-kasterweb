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

A aplicação fica disponível em <http://localhost:8000>. Backend e frontend rodam juntos: o React é servido pelo Laravel via Inertia, então não é necessário subir dois projetos separados.

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

- **Laravel + React via Inertia.js:** um único projeto em vez de API REST + SPA separada. O frontend continua sendo React consumindo dados do backend, mas sem a sobrecarga de CORS, autenticação por token e dois deploys. As rotas são tipadas no frontend com Wayfinder.
- **Starter kit oficial (Laravel React):** já entrega autenticação (Fortify), componentes de UI (Radix + Tailwind) e tooling configurado, permitindo focar no domínio do PDV.
- **SQLite:** banco em arquivo, sem servidor para instalar ou configurar. Suficiente para o escopo do teste e simplifica rodar o projeto.
