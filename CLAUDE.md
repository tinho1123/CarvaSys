# CLAUDE.md - CarvaSys AI Assistant Guide

## Project Overview

**CarvaSys** is a multi-tenant B2B credit management SaaS (fiado/installment system) targeting small-to-medium Brazilian businesses. Companies use it to offer credit to their clients, track transactions, manage orders, and provide a self-service client portal.

**Phase 1 MVP status:** ~80% complete (as of Feb 2026)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 10, PHP 8.1+ (8.4+ in Sail) |
| Admin Panel | Filament 3.x |
| API Auth | Laravel Sanctum |
| Frontend | React 19.x + Inertia.js 2.x |
| Styling | Tailwind CSS v4 + Vite 6.x |
| Client Auth | Clerk React (SSO) |
| Database | MySQL 8.4 (Eloquent ORM) |
| Cache | Redis |
| Payments | Stripe |
| Dev Env | Laravel Sail (Docker) |
| Testing | PHPUnit 10.x |
| Formatter | Laravel Pint |

---

## Directory Structure

```
app/
├── Filament/Admin/Resources/   # Admin panel resources (Clients, Products, Orders)
├── Http/
│   ├── Controllers/
│   │   ├── Api/Client/         # Sanctum-protected client portal API (5 controllers)
│   │   └── Marketplace/        # Public marketplace + SSO controllers
│   └── Middleware/             # 12 middleware classes (tenant, auth, lockout, etc.)
├── Models/                     # 13 Eloquent models
└── Providers/                  # Service providers

routes/
├── web.php                     # Marketplace, SSO, admin login routes
├── api.php                     # Base API routes
└── api_favored.php             # Fiado/credit transaction API routes

resources/
├── js/                         # React pages + Inertia entry (app.jsx)
└── views/                      # Blade templates

database/
├── migrations/                 # 20 migration files
├── seeders/
└── factories/

docs/                           # PRD documents
.agent/skills/                  # Extended AI skill guides (Laravel, API, Filament, etc.)
```

---

## Essential Commands

### Development Environment
```bash
./vendor/bin/sail up -d              # Start Docker containers (recommended)
./vendor/bin/sail down               # Stop containers
./vendor/bin/sail bash               # Shell into app container

composer install                     # PHP dependencies
npm install                          # Node dependencies
```

### Code Quality (MANDATORY before commits)
```bash
./vendor/bin/pint                    # Format all PHP code — ALWAYS run before committing
```

### Testing
```bash
./vendor/bin/phpunit                              # All tests
./vendor/bin/phpunit tests/Unit                  # Unit tests only
./vendor/bin/phpunit tests/Feature               # Feature tests only
./vendor/bin/phpunit --filter test_method_name   # Single test method
```

### Database
```bash
php artisan migrate                  # Run pending migrations
php artisan migrate:fresh --seed     # Full reset + seed (dev only)
```

### Frontend
```bash
npm run dev                          # Vite watch mode
npm run build                        # Production build
./vendor/bin/sail npm run dev        # Via Sail
```

### Cache Clearing
```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

---

## Pre-commit Checklist

1. `./vendor/bin/pint` — fix all PHP formatting issues
2. `./vendor/bin/phpunit` — all tests must pass
3. Verify no `.env` or secrets committed
4. Confirm all new DB tables have `company_id` for tenant isolation
5. Verify multi-tenant scoping for any new queries

---

## Architecture: Multi-Tenancy

**This is the most critical architectural constraint.** Every resource belongs to a `Company` (tenant).

- All tenant-scoped tables have a `company_id` foreign key with cascade delete
- All queries must be filtered by `company_id` — never return data across tenants
- Current company context: `auth()->user()->companies->first()->id`
- Models auto-set `company_id` in the `boot()` method
- Filament routes include `{tenant}` (company UUID) parameter
- Client-company relationship uses the `client_company` pivot table (`client_id`, `company_id`, `is_active`)

---

## Code Conventions

### PHP / Laravel
- **Formatter:** Laravel Pint (enforced) — 4 spaces, UTF-8, LF line endings
- **Classes:** `PascalCase`
- **Methods/Variables:** `camelCase`
- **Constants:** `UPPER_SNAKE_CASE`
- **DB tables/columns:** `snake_case`
- **Route keys:** All models use UUID — implement `getRouteKeyName(): string { return 'uuid'; }`

### Import Order (strict)
```php
// 1. Framework imports
use Illuminate\Database\Eloquent\Model;

// 2. Package imports
use Laravel\Sanctum\HasApiTokens;

// 3. App imports
use App\Models\Company;
```

### Model Template
```php
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'company_id', 'amount', 'description'];

    protected $casts = ['amount' => 'decimal:2', 'created_at' => 'datetime'];

    public function getRouteKeyName(): string { return 'uuid'; }

    // Relationships, scopes, etc.
}
```

### Migration Template
```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('company_id')->constrained()->onDelete('cascade'); // REQUIRED
    // ... columns
    $table->timestamps();
});
```

### API Response Format
```json
{
  "success": true,
  "data": {},
  "message": "Description"
}
```

### React / JavaScript
- Functional components with hooks only (no class components)
- `useState`/`useReducer` for local state
- Axios with async/await for HTTP calls
- Tailwind CSS v4 utility classes for all styling
- Inertia.js for server-side routing integration

---

## Authentication Architecture

| User Type | Method | Guard |
|---|---|---|
| Admin | Email/password | `auth` (web guard) |
| Client (portal) | CPF/CNPJ | `auth:client` guard |
| Client (SSO) | Clerk JWT | SSO callback |
| API | Sanctum tokens | `auth:sanctum` |

- Account lockout: 5 failed attempts → 30-minute lock
- Session-based tenant tracking via `selected_tenant_id`

---

## API Endpoints (Client Portal — `auth:sanctum`)

```
GET  /api/client/companies/{company}/products           # Product listing
GET  /api/client/companies/{company}/orders             # Order history
POST /api/client/companies/{company}/orders             # Create order
GET  /api/client/companies/{company}/client/credit-balance
GET  /api/client/companies/{company}/client/transaction-history
GET  /api/client/notifications                          # All notifications
POST /api/client/companies/{company}/payments/create-intent  # Stripe
POST /api/client/companies/{company}/payments/confirm
```

---

## Web Routes (Marketplace)

```
GET  /                              # Marketplace index
GET  /store/{company:uuid}          # Company store
POST /marketplace/login             # Client CPF/CNPJ login
POST /marketplace/logout
POST /sso-callback                  # Clerk SSO
GET  /complete-profile              # Profile completion
GET  /meus-pedidos                  # My orders (auth:client)
GET  /login                         # Admin login
POST /login
```

---

## Core Models

| Model | Key Relationships |
|---|---|
| `Company` | hasMany Users, belongsToMany Clients, hasMany Products/Orders/Transactions |
| `Client` | belongsToMany Companies (via `client_company`), hasMany Orders/Notifications |
| `User` | belongsToMany Companies (via `companies_users`) |
| `Product` | belongsTo Company, belongsTo ProductsCategories |
| `Order` | belongsTo Company, belongsTo Client, hasMany OrderItems |
| `OrderItem` | belongsTo Order, belongsTo Product |
| `Transaction` | belongsTo Company |
| `FavoredTransaction` | belongsTo Company, has `order_id`, `due_date`, `metadata` |
| `FavoredDebt` | Debt tracking |
| `Notification` | Types: `order_update`, `payment_reminder`, `credit_warning`, `announcement` |
| `Fee` | belongsTo Company |

### Order Status Flow
`pending → confirmed → processing → shipped → delivered` (cancellation supported)

---

## Testing Patterns

```php
/** @test */
public function it_can_create_order_for_authenticated_client()
{
    // Arrange
    $company = Company::factory()->create();
    $client = Client::factory()->create();

    // Act
    $response = $this->actingAs($client, 'client')
        ->postJson("/api/client/companies/{$company->uuid}/orders", [...]);

    // Assert
    $response->assertStatus(201);
    $this->assertDatabaseHas('orders', ['company_id' => $company->id]);
}
```

- Always test with proper multi-tenant company context
- Test both success and failure scenarios
- Use `assertDatabaseHas` to confirm persistence
- Run `./vendor/bin/pint` before committing test files

---

## Error Handling

```php
try {
    $response = Http::timeout(30)->post($url, $data);
    if (!$response->successful()) {
        Log::error('API request failed', [
            'url' => $url,
            'status' => $response->status(),
            'company_id' => $company->id,  // Always include company_id in logs
        ]);
        return response()->json(['success' => false, 'message' => 'External API error'], 502);
    }
    return response()->json(['success' => true, 'data' => $response->json()]);
} catch (\Exception $e) {
    Log::error('Exception', ['message' => $e->getMessage(), 'company_id' => $company->id]);
    return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
}
```

HTTP status codes: `200`, `201`, `400`, `401`, `403`, `404`, `422`, `500`, `502`

---

## Filament Admin Panel

- Resources: `ClientResource`, `ProductResource`, `OrderResource`
- Tenant-aware: company scoping applied automatically
- Dark theme by default
- Custom middleware stack applies to Filament routes
- Navigation icons use `heroicon-o-*` prefix

---

## Docker Compose Services

| Service | Purpose |
|---|---|
| `laravel.test` | App container (PHP 8.4) |
| `mysql:8.4` | Primary database |
| `redis:alpine` | Cache / sessions |
| `meilisearch` | Full-text search |
| `mailpit` | Email testing |
| `selenium` | Browser automation |

---

## Environment Variables (Key ones)

```env
DB_CONNECTION=mysql
DB_DATABASE=carvasys

STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=

VITE_CLERK_PUBLISHABLE_KEY=   # Client SSO

CACHE_DRIVER=file             # Use redis in production
SESSION_DRIVER=file           # Use redis in production
```

---

## Extended Documentation

For deeper context on specific areas, see:

- `AGENTS.md` — Core development guidelines and code style reference
- `IMPLEMENTATION.md` — Phase 1 implementation summary and architecture decisions
- `docs/PRD-System-Overview.md` — Full business context and system design
- `.agent/skills/laravel-development.md` — Laravel patterns
- `.agent/skills/api-development.md` — API design conventions
- `.agent/skills/filament-resources.md` — Filament admin resource patterns
- `.agent/skills/multi-tenant-development.md` — Multi-tenancy patterns
- `.agent/skills/testing-strategy.md` — Testing approaches

---

## Phase 2 Roadmap

- Client dashboard (React/Inertia)
- Payment recording & reconciliation
- Stripe webhooks
- Email notification queue
- Advanced product filtering & cart persistence
- PDF invoice generation
- Mobile PWA
- WhatsApp / SMS notifications
