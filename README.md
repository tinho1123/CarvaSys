# Comere

**Plataforma de gestão de crédito B2B para pequenas e médias empresas brasileiras.**

O Comere permite que empresas ofereçam crédito (fiado) aos seus clientes, gerenciem pedidos, acompanhem transações e disponibilizem um portal self-service moderno — tudo em uma única plataforma multi-tenant.

---

## Funcionalidades

- **Gestão de crédito (Fiado)** — controle de saldo devedor, histórico de transações e parcelas
- **Portal do cliente** — acesso via CPF/CNPJ com autenticação SSO (Clerk)
- **Catálogo de produtos** — produtos por empresa com categorias, busca e filtros
- **Gestão de pedidos** — criação, acompanhamento e histórico de pedidos
- **Notificações** — avisos de pedido, lembretes de pagamento e alertas de crédito
- **Pagamentos online** — integração com Stripe (PaymentIntent)
- **Painel administrativo** — interface Filament para gestão completa de cada empresa

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 10, PHP 8.4 |
| Admin | Filament 3.x |
| API Auth | Laravel Sanctum |
| Frontend | React 19 + Inertia.js 2 |
| Estilização | Tailwind CSS v4 + Vite 6 |
| Auth SSO | Clerk React |
| Banco de dados | MySQL 8.4 |
| Cache | Redis |
| Pagamentos | Stripe |
| Ambiente Dev | Laravel Sail (Docker) |
| Testes | PHPUnit 10 |

---

## Pré-requisitos

- Docker + Docker Compose
- PHP 8.1+
- Node.js 18+
- Composer

---

## Instalação

```bash
# 1. Clone o repositório
git clone <repo-url> comere
cd comere

# 2. Instale as dependências PHP
composer install

# 3. Instale as dependências Node
npm install

# 4. Configure o ambiente
cp .env.example .env
php artisan key:generate

# 5. Suba os containers
./vendor/bin/sail up -d

# 6. Execute as migrations e seeds
./vendor/bin/sail artisan migrate --seed

# 7. Inicie o frontend
./vendor/bin/sail npm run dev
```

Acesse em `http://localhost`.

---

## Variáveis de Ambiente

Edite o `.env` com suas credenciais:

```env
DB_DATABASE=comere

STRIPE_PUBLIC_KEY=pk_...
STRIPE_SECRET_KEY=sk_...

VITE_CLERK_PUBLISHABLE_KEY=pk_...
```

---

## Comandos Úteis

```bash
# Containers
./vendor/bin/sail up -d          # Subir ambiente
./vendor/bin/sail down           # Parar ambiente
./vendor/bin/sail bash           # Acessar shell do container

# Banco de dados
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed

# Qualidade de código (obrigatório antes de commits)
./vendor/bin/pint

# Testes
./vendor/bin/phpunit
./vendor/bin/phpunit tests/Feature
./vendor/bin/phpunit tests/Unit

# Frontend
npm run dev
npm run build
```

---

## Arquitetura Multi-Tenant

Cada empresa é um **tenant** isolado. Todas as tabelas possuem `company_id` como chave de escopo — nenhuma query retorna dados entre empresas distintas. Clientes podem pertencer a múltiplas empresas via tabela pivot `client_company`.

---

## Autenticação

| Tipo | Método |
|---|---|
| Admin | E-mail e senha (painel Filament) |
| Cliente (portal) | CPF ou CNPJ |
| Cliente (SSO) | Clerk JWT |
| API | Token Sanctum |

---

## Documentação

- [`CLAUDE.md`](./CLAUDE.md) — guia completo para desenvolvimento e convenções
- [`AGENTS.md`](./AGENTS.md) — padrões de código detalhados
- [`IMPLEMENTATION.md`](./IMPLEMENTATION.md) — resumo da implementação Phase 1
- [`docs/PRD-System-Overview.md`](./docs/PRD-System-Overview.md) — visão geral do sistema
- [`.agent/skills/`](./.agent/skills/) — guias aprofundados por área (Laravel, API, Filament, testes, multi-tenancy)

---

## Status do Projeto

**Phase 1 MVP — ~80% concluído**

- [x] Autenticação multi-company (CPF/CNPJ + SSO)
- [x] API do portal do cliente (Sanctum)
- [x] Gestão de pedidos e itens
- [x] Sistema de notificações
- [x] Integração Stripe (skeleton)
- [x] Painel Filament (Clientes, Produtos, Pedidos)
- [ ] Dashboard React/Inertia (Phase 2)
- [ ] Webhooks Stripe e reconciliação (Phase 2)
- [ ] Notificações por e-mail / WhatsApp (Phase 2)
- [ ] PWA mobile (Phase 2)
