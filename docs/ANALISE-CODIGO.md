# Análise Completa do Código CarvaSys

**Data:** 4 de Fevereiro de 2026  
**Versão:** 1.0  
**Status:** ✅ Completo

---

## 1. Visão Geral do Sistema

**CarvaSys** é um sistema de gestão de crédito (fiado) B2B multi-tenant desenvolvido em Laravel 10 com Filament 3 e React 19. O sistema permite que empresas ofereçam crédito a seus clientes, rastreiem transações e gerenciem pagamentos através de um portal moderno.

### Tecnologias Principais

- **Backend:** Laravel 10, PHP 8.1+
- **Admin Panel:** Filament 3.x
- **Frontend:** React 19.x, Inertia.js, Tailwind CSS v4
- **Database:** MySQL/MariaDB
- **Authentication:** Laravel Sanctum
- **Testing:** PHPUnit 10.x
- **Dev Environment:** Laravel Sail (Docker)
- **Payments:** Stripe (integração em desenvolvimento)

---

## 2. Estrutura de Diretórios

```
/home/wellington/projects/CarvaSys/
├── app/
│   ├── Auth/                    # Autenticação customizada
│   ├── Console/                 # Comandos Artisan
│   ├── Exceptions/              # Exception handlers
│   ├── Filament/                # Recursos Filament
│   │   └── Client/              # Painel do cliente
│   │       └── Pages/           # Páginas customizadas
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── Client/      # Controllers da API do cliente
│   │   │   │       ├── CreditController.php
│   │   │   │       ├── NotificationController.php
│   │   │   │       ├── OrderController.php
│   │   │   │       ├── PaymentController.php
│   │   │   │       └── ProductController.php
│   │   │   ├── Auth/            # Controllers de autenticação
│   │   │   ├── Client/          # Controllers web do cliente
│   │   │   ├── DashboardController.php
│   │   │   ├── FavoredTransactionController.php
│   │   │   └── UsersController.php
│   │   └── Middleware/          # Middlewares customizados
│   ├── Models/                  # 13 modelos Eloquent
│   │   ├── Client.php
│   │   ├── Company.php
│   │   ├── CompaniesUsers.php
│   │   ├── FavoredDebt.php
│   │   ├── FavoredTransaction.php
│   │   ├── Fee.php
│   │   ├── Notification.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Product.php
│   │   ├── ProductsCategories.php
│   │   ├── Transaction.php
│   │   └── User.php
│   └── Providers/               # Service providers
├── database/
│   └── migrations/              # 17 migrations
├── routes/
│   ├── api.php                  # Rotas API padrão
│   ├── api_favored.php          # API de transações fiado
│   ├── channels.php             # Broadcasting
│   ├── console.php              # Comandos console
│   └── web.php                  # Rotas web
├── resources/
│   ├── css/
│   ├── js/
│   └── views/                   # Blade templates
└── tests/
    ├── Feature/
    └── Unit/
```

---

## 3. Modelos e Relacionamentos

### 3.1 Modelo de Dados Principal

```
Company (Tenant)
├── N:N → Users (via companies_users)
├── N:N → Clients (via client_company)
├── 1:N → Products
├── 1:N → ProductsCategories
├── 1:N → Orders
├── 1:N → FavoredTransactions
├── 1:N → Transactions
├── 1:N → Fees
└── 1:N → Notifications

Client
├── N:N → Companies (via client_company)
├── 1:N → Orders
├── 1:N → FavoredTransactions
└── 1:N → Notifications

Order
├── N:1 → Company
├── N:1 → Client
└── 1:N → OrderItems

Product
├── N:1 → Company
├── N:1 → ProductsCategories
├── 1:N → FavoredTransactions
└── 1:N → OrderItems
```

### 3.2 Modelos Identificados

| Modelo | Propósito | Tenant-Scoped | UUID |
|--------|-----------|---------------|------|
| **Client** | Clientes finais | Não (N:N) | ✅ |
| **Company** | Empresas (tenants) | - | ✅ |
| **User** | Usuários admin | Não (N:N) | ❌ |
| **CompaniesUsers** | Pivot User-Company | - | ❌ |
| **Product** | Catálogo de produtos | ✅ | ✅ |
| **ProductsCategories** | Categorias | ✅ | ✅ |
| **Order** | Pedidos | ✅ | ✅ |
| **OrderItem** | Itens do pedido | Indireto | ✅ |
| **FavoredTransaction** | Transações fiado | ✅ | ✅ |
| **FavoredDebt** | Resumo de dívidas | ✅ | ✅ |
| **Transaction** | Transações gerais | ✅ | ✅ |
| **Fee** | Taxas | ✅ | ✅ |
| **Notification** | Notificações | ✅ | ✅ |

---

## 4. Controllers e Lógica de Negócio

### 4.1 API Controllers (Client Portal)

#### ProductController
**Arquivo:** `app/Http/Controllers/Api/Client/ProductController.php`

**Endpoints:**
- `GET /api/client/companies/{company}/products` - Listar produtos
- `GET /api/client/companies/{company}/products/{product}` - Detalhes do produto
- `GET /api/client/companies/{company}/categories` - Listar categorias

**Funcionalidades:**
- Paginação (15 itens por página)
- Busca por nome/descrição
- Filtro por categoria
- Filtro por featured
- Eager loading de relacionamentos

#### OrderController
**Arquivo:** `app/Http/Controllers/Api/Client/OrderController.php`

**Endpoints:**
- `GET /api/client/companies/{company}/orders` - Histórico de pedidos
- `GET /api/client/companies/{company}/orders/{order}` - Detalhes do pedido
- `POST /api/client/companies/{company}/orders` - Criar pedido

**Funcionalidades:**
- Criação de pedidos com múltiplos itens
- Cálculo automático de totais
- Validação de produtos
- Filtro por status
- Rastreamento de status

#### CreditController
**Arquivo:** `app/Http/Controllers/Api/Client/CreditController.php`

**Endpoints:**
- `GET /api/client/companies/{company}/client/credit-balance` - Saldo de crédito
- `GET /api/client/companies/{company}/client/transaction-history` - Histórico
- `GET /api/client/companies/{company}/client/upcoming-payments` - Pagamentos futuros

**Funcionalidades:**
- Cálculo de saldo devedor
- Histórico de transações fiado
- Agrupamento de pagamentos por data de vencimento
- Cálculo de crédito disponível

#### NotificationController
**Arquivo:** `app/Http/Controllers/Api/Client/NotificationController.php`

**Endpoints:**
- `GET /api/client/notifications` - Listar notificações
- `GET /api/client/notifications/unread-count` - Contador de não lidas
- `POST /api/client/notifications/{notification}/read` - Marcar como lida
- `POST /api/client/notifications/mark-all-read` - Marcar todas como lidas

**Funcionalidades:**
- Filtro por tipo e status de leitura
- Contador de não lidas
- Marcação individual e em massa
- Paginação

#### PaymentController
**Arquivo:** `app/Http/Controllers/Api/Client/PaymentController.php`

**Endpoints:**
- `POST /api/client/companies/{company}/payments/create-intent` - Criar PaymentIntent
- `POST /api/client/companies/{company}/payments/confirm` - Confirmar pagamento

**Funcionalidades:**
- Integração com Stripe
- Criação de PaymentIntent
- Confirmação de pagamento
- Tratamento de erros de pagamento

### 4.2 Web Controllers

#### FavoredTransactionController
**Arquivo:** `app/Http/Controllers/FavoredTransactionController.php`

**Funcionalidades:**
- CRUD de transações fiado
- Registro de pagamentos
- Listagem de clientes com transações
- Cálculo de saldo devedor

#### DashboardController
**Arquivo:** `app/Http/Controllers/DashboardController.php`

**Funcionalidades:**
- Dashboard principal
- Estatísticas gerais
- Resumos de crédito

---

## 5. Rotas e APIs

### 5.1 Rotas Web (`routes/web.php`)

```php
Route::get('/', fn () => redirect('/admin/login'));

// Autenticação Admin
Route::get('/login', [LoginController::class, 'index'])->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate'])->middleware('guest');
Route::post('/logout', [LogoutController::class, 'logout'])->middleware('auth');
```

### 5.2 Rotas API Favored (`routes/api_favored.php`)

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('favored-transactions')->name('favored-transactions.')->group(function () {
        Route::get('/', [FavoredTransactionController::class, 'index']);
        Route::post('/', [FavoredTransactionController::class, 'store']);
        Route::get('/clients-with-transactions', [FavoredTransactionController::class, 'getClientsWithTransactions']);
        Route::put('/{transaction}', [FavoredTransactionController::class, 'update']);
        Route::delete('/{transaction}', [FavoredTransactionController::class, 'destroy']);
        Route::post('/{transaction}/pay', [FavoredTransactionController::class, 'payDebt']);
    });
});
```

### 5.3 Rotas API Padrão (`routes/api.php`)

```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

**Nota:** As rotas da API do cliente (ProductController, OrderController, etc.) devem estar registradas em `app/Providers/RouteServiceProvider.php` conforme documentado no IMPLEMENTATION.md.

---

## 6. Módulos Principais Identificados

### 6.1 Autenticação Multi-Tenant ✅
- Login via CPF/CNPJ (não email)
- Bloqueio após 5 tentativas (30 min)
- Seleção de empresa após autenticação
- Suporte N:N cliente-empresa
- Session-based tenant tracking

### 6.2 Gestão de Clientes ✅
- Cadastro com CPF/CNPJ
- Relacionamento N:N com empresas
- Controle de acesso por empresa
- Preferências do cliente

### 6.3 Transações Fiado (Crédito) ✅
- Registro de transações a prazo
- Controle de saldo devedor
- Histórico de pagamentos
- Cálculo de saldo restante
- Pagamentos futuros agendados

### 6.4 Catálogo de Produtos ✅
- Produtos por empresa
- Categorização
- Busca e filtros
- Produtos em destaque
- Preços e descontos

### 6.5 Gestão de Pedidos ✅
- Criação com múltiplos itens
- Rastreamento de status
- Cálculo automático de totais
- Histórico completo
- Timestamps de lifecycle

### 6.6 Sistema de Notificações ✅
- Notificações in-app
- 4 tipos: order_update, payment_reminder, credit_warning, announcement
- Marcação lido/não lido
- Contador de não lidas
- Action URLs

### 6.7 Processamento de Pagamentos 🔄
- Integração Stripe (skeleton)
- PaymentIntent creation
- Confirmação de pagamentos
- **Pendente:** Histórico, webhooks, reconciliação

### 6.8 Painel Administrativo (Filament) 🔄
- Estrutura criada
- **Pendente:** Resources completos

---

## 7. Fluxos de Negócio Mapeados

### 7.1 Fluxo de Autenticação

```
1. Cliente acessa /cliente/login
2. Insere CPF/CNPJ + senha
3. Sistema valida credenciais
   ├─ Inválido → Incrementa tentativas → Bloqueia após 5
   └─ Válido → Autentica
4. Sistema verifica empresas do cliente
   ├─ 1 empresa → Auto-seleciona → Dashboard
   └─ N empresas → Tela de seleção
5. Cliente seleciona empresa
6. Sistema armazena selected_tenant_id na sessão
7. Middleware aplica scopes globais
8. Cliente acessa dashboard
```

### 7.2 Fluxo de Criação de Pedido

```
1. Cliente navega catálogo de produtos
2. Adiciona produtos ao carrinho (frontend)
3. Finaliza pedido
4. POST /api/client/companies/{company}/orders
   ├─ Valida produtos existem
   ├─ Valida quantidades
   └─ Calcula totais
5. Sistema cria Order + OrderItems
6. Retorna pedido criado (status: pending)
7. (Futuro) Notificação enviada ao cliente
```

### 7.3 Fluxo de Transação Fiado

```
1. Cliente realiza compra a prazo
2. Sistema cria FavoredTransaction
   ├─ favored_total = valor total
   ├─ favored_paid_amount = 0
   └─ remaining_balance = favored_total
3. Cliente visualiza saldo devedor
4. Cliente realiza pagamento
   ├─ POST /api/payments/create-intent
   ├─ Stripe retorna clientSecret
   ├─ Frontend processa pagamento
   └─ POST /api/payments/confirm
5. Sistema atualiza favored_paid_amount
6. Recalcula remaining_balance
7. (Futuro) Notificação de pagamento
```

### 7.4 Fluxo de Notificações

```
1. Evento ocorre (pedido enviado, pagamento vencendo, etc.)
2. Sistema cria Notification
   ├─ type: order_update | payment_reminder | credit_warning | announcement
   ├─ title, description, message
   ├─ action_url (deep link)
   └─ read_at = null
3. Cliente acessa portal
4. GET /api/client/notifications/unread-count
5. Badge mostra contador
6. Cliente clica em notificação
7. POST /api/client/notifications/{notification}/read
8. Sistema marca read_at = now()
9. Contador atualiza
```

---

## 8. Multi-Tenancy

### 8.1 Estratégia de Isolamento

**Database-level multi-tenancy** com `company_id`:

- Todas as tabelas tenant-scoped têm `company_id`
- Foreign key com CASCADE delete
- Global scopes aplicados via middleware
- Validação de acesso via pivot `client_company`

### 8.2 Resolução de Tenant

**Ordem de prioridade:**
1. Header `X-Tenant-ID`
2. URL parameter `{tenant}`
3. Session `selected_tenant_id`

### 8.3 Modelos Scoped

- Product
- ProductsCategories
- Order
- FavoredTransaction
- Transaction
- Fee
- Notification

---

## 9. Segurança

### 9.1 Implementado ✅

- Autenticação Sanctum (API tokens)
- CSRF protection (Laravel padrão)
- Account lockout (5 tentativas)
- Password hashing (bcrypt)
- SQL injection prevention (Eloquent)
- Multi-tenant data isolation
- Session regeneration on login

### 9.2 Pendente 🔄

- Two-factor authentication
- Password reset via email
- Audit logging
- Rate limiting granular
- LGPD compliance completo

---

## 10. Banco de Dados

### 10.1 Migrations Identificadas (17)

1. `create_companies.php`
2. `create_clients_table.php` (2 versões)
3. `create_users.php`
4. `create_companies_users.php`
5. `create_client_company_table.php` (2 versões)
6. `create_product_categories.php`
7. `create_products.php`
8. `create_fees.php`
9. `create_transactions.php`
10. `create_favoreds.php`
11. `create_favored_transactions.php`
12. `create_favored_debts_table.php`
13. `ensure_orders_table.php`
14. `create_order_items.php`
15. `create_notifications.php`

### 10.2 Índices Principais

- UUID columns (unique)
- Foreign keys (automatic)
- `company_id` (tenant queries)
- Composite: `(company_id, client_id)`
- Status columns
- Timestamps (sorting)

---

## 11. Testes

### 11.1 Estrutura de Testes

```
tests/
├── Feature/           # Testes de integração
└── Unit/              # Testes unitários
    └── UserLoginTest.php
```

### 11.2 Cobertura Atual

- Testes básicos implementados
- **Necessário:** Expandir cobertura para 70%+

---

## 12. Status de Implementação

### ✅ Completo (Phase 1 - 80%)

- Autenticação multi-tenant
- Modelos core
- API do cliente (5 controllers)
- Transações fiado
- Pedidos
- Notificações
- Integração Stripe (skeleton)

### 🔄 Em Progresso

- Painel Filament
- Frontend React/Inertia
- Testes completos

### 📋 Planejado (Phase 2)

- Dashboard com dados reais
- Gravação de pagamentos
- Webhooks Stripe
- Email notifications
- Componentes React
- PWA mobile

---

## 13. Pontos de Atenção

### 🔴 Crítico

1. **API Client Routes:** Verificar se rotas estão registradas em RouteServiceProvider
2. **Global Scopes:** Garantir aplicação em todos os modelos tenant-scoped
3. **Tenant Validation:** Sempre validar acesso do cliente à empresa

### 🟡 Importante

1. **Testing:** Aumentar cobertura de testes
2. **Documentation:** Manter PRDs atualizados
3. **Performance:** Monitorar N+1 queries

### 🟢 Melhorias Futuras

1. **Caching:** Implementar cache de queries
2. **Queues:** Background jobs para emails
3. **Monitoring:** APM e error tracking

---

## 14. Conclusão

O CarvaSys é um sistema bem estruturado com arquitetura multi-tenant sólida. A implementação atual (Phase 1 - 80%) cobre os módulos essenciais:

**Pontos Fortes:**
- ✅ Arquitetura multi-tenant robusta
- ✅ Separação clara de responsabilidades
- ✅ Uso de padrões Laravel
- ✅ API RESTful bem estruturada
- ✅ Segurança básica implementada

**Próximos Passos:**
- Completar painel Filament
- Implementar frontend React
- Expandir testes
- Implementar webhooks Stripe
- Sistema de notificações por email

---

**Análise realizada por:** Antigravity AI  
**Data:** 4 de Fevereiro de 2026  
**Versão do Sistema:** 1.0-MVP
