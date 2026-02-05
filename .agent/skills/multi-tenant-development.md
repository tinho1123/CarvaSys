---
name: Multi-Tenant Development
description: Patterns and best practices for multi-tenant development in CarvaSys with company_id isolation
---

# Multi-Tenant Development

This skill covers multi-tenancy patterns used in CarvaSys, including tenant isolation, scoping, and security best practices.

## Multi-Tenancy Architecture

CarvaSys uses **database-level multi-tenancy** with `company_id` isolation. All tenant-scoped data is stored in the same database but filtered by `company_id`.

### Key Concepts

- **Tenant:** A company using the platform
- **Tenant Key:** `company_id` foreign key on all scoped tables
- **Tenant Resolution:** Determining the active tenant from session/header/URL
- **Tenant Scoping:** Automatically filtering queries by `company_id`

## Tenant-Scoped Models

### Model Requirements

All tenant-scoped models must:

1. Have `company_id` column
2. Include `company_id` in `$fillable`
3. Define `company()` relationship
4. Apply global scopes (via middleware)

### Example Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',  // Required for multi-tenancy
        'name',
        'price',
        'active',
    ];

    // Relationship to tenant
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Auto-set company_id on creation
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (is_null($model->company_id)) {
                $model->company_id = session('selected_tenant_id');
            }
        });
    }
}
```

## Tenant Resolution

### Middleware: SetClientTenant

The `SetClientTenant` middleware resolves the active tenant and applies global scopes.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetClientTenant
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Resolve tenant from: header → URL → session
        $tenantId = $this->resolveTenantId($request);
        
        // 2. Validate client has access to tenant
        if (!$this->validateAccess($tenantId)) {
            return redirect()->route('client.select-company');
        }
        
        // 3. Store in session
        session(['selected_tenant_id' => $tenantId]);
        
        // 4. Apply global scopes
        $this->applyGlobalScopes($tenantId);
        
        return $next($request);
    }

    protected function resolveTenantId(Request $request): ?int
    {
        // Priority: header → URL → session
        return $request->header('X-Tenant-ID')
            ?? $request->route('tenant')
            ?? session('selected_tenant_id');
    }

    protected function validateAccess(?int $tenantId): bool
    {
        if (!$tenantId) {
            return false;
        }

        return auth('client')->user()
            ->companies()
            ->where('companies.id', $tenantId)
            ->exists();
    }

    protected function applyGlobalScopes(int $tenantId): void
    {
        // Apply to all tenant-scoped models
        \App\Models\Product::addGlobalScope('company', function ($query) use ($tenantId) {
            $query->where('company_id', $tenantId);
        });

        \App\Models\Order::addGlobalScope('company', function ($query) use ($tenantId) {
            $query->where('company_id', $tenantId);
        });

        \App\Models\FavoredTransaction::addGlobalScope('company', function ($query) use ($tenantId) {
            $query->where('company_id', $tenantId);
        });

        // Add more models as needed
    }
}
```

### Tenant Resolution Priority

1. **HTTP Header:** `X-Tenant-ID: {company_id}`
2. **URL Parameter:** `/api/client/companies/{tenant}/...`
3. **Session:** `session('selected_tenant_id')`

## Global Scopes

### Automatic Scoping

Global scopes are applied via middleware to automatically filter queries:

```php
// Middleware applies this scope
Product::addGlobalScope('company', function ($query) {
    $query->where('company_id', session('selected_tenant_id'));
});

// Now all queries are automatically scoped
Product::all(); // Only products for current tenant
Product::where('active', true)->get(); // Still scoped!
```

### Removing Scopes (Admin Only)

```php
// Remove global scope for admin queries
Product::withoutGlobalScope('company')->get(); // All products across all tenants
```

## Client-Company Relationship

### N:N Pivot Table

Clients can belong to multiple companies via `client_company` pivot:

```sql
CREATE TABLE client_company (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_client_company (client_id, company_id)
);
```

### Model Relationship

```php
// Client model
public function companies(): BelongsToMany
{
    return $this->belongsToMany(Company::class, 'client_company')
        ->withPivot('is_active')
        ->wherePivot('is_active', true)
        ->withTimestamps();
}

// Company model
public function clients(): BelongsToMany
{
    return $this->belongsToMany(Client::class, 'client_company')
        ->withPivot('is_active')
        ->withTimestamps();
}
```

## Company Selection Flow

### After Authentication

```php
// AuthController
public function login(Request $request)
{
    // 1. Authenticate client
    $client = Client::where('document_number', $request->document)->first();
    
    if (!$client || !Hash::check($request->password, $client->password)) {
        return back()->withErrors(['document' => 'Invalid credentials']);
    }
    
    auth('client')->login($client);
    
    // 2. Check companies
    $companies = $client->companies;
    
    if ($companies->count() === 1) {
        // Auto-select single company
        session(['selected_tenant_id' => $companies->first()->id]);
        return redirect()->route('client.dashboard');
    }
    
    // Multiple companies - show selection
    return redirect()->route('client.select-company');
}

public function selectCompany(Request $request, string $companyUuid)
{
    $company = Company::where('uuid', $companyUuid)->firstOrFail();
    
    // Validate access
    if (!auth('client')->user()->canAccessTenant($company)) {
        abort(403, 'You do not have access to this company');
    }
    
    // Set tenant
    session(['selected_tenant_id' => $company->id]);
    
    return redirect()->route('client.dashboard');
}
```

## Database Migrations

### Tenant-Scoped Table Pattern

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Tenant key - REQUIRED
            $table->foreignId('company_id')
                ->constrained()
                ->onDelete('cascade');
            
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Index for tenant queries
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### Key Migration Rules

1. **Always include `company_id`** on tenant-scoped tables
2. **Use `constrained()->onDelete('cascade')`** for automatic cleanup
3. **Index `company_id`** for query performance
4. **Composite indexes** for common query patterns: `index(['company_id', 'client_id'])`

## API Tenant Resolution

### URL-Based Tenancy

```php
// routes/api_client.php
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::prefix('companies/{tenant}')->group(function () {
        Route::get('products', [ProductController::class, 'index']);
        Route::get('orders', [OrderController::class, 'index']);
    });
});
```

### Controller Validation

```php
public function index(Request $request, string $companyUuid)
{
    $company = Company::where('uuid', $companyUuid)->firstOrFail();
    
    // Validate client has access
    if (!auth('sanctum')->user()->canAccessTenant($company)) {
        return response()->json(['error' => 'Forbidden'], 403);
    }
    
    // Set tenant for this request
    session(['selected_tenant_id' => $company->id]);
    
    // Queries are now automatically scoped
    $products = Product::paginate(15);
    
    return response()->json($products);
}
```

## Security Best Practices

### ✅ Do This

```php
// Always validate tenant access
if (!auth()->user()->canAccessTenant($company)) {
    abort(403);
}

// Use global scopes for automatic filtering
Product::all(); // Automatically scoped

// Include company_id in validation
$validated = $request->validate([
    'name' => 'required',
    'company_id' => 'required|exists:companies,id',
]);

// Verify company_id matches current tenant
if ($validated['company_id'] !== session('selected_tenant_id')) {
    abort(403);
}
```

### ❌ Don't Do This

```php
// Don't skip tenant validation
$company = Company::find($id); // No access check!

// Don't trust client-provided company_id without validation
$product = Product::create($request->all()); // Security risk!

// Don't forget to scope queries
Product::withoutGlobalScope('company')->get(); // Leaks data!
```

## Testing Multi-Tenancy

### Test Pattern

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    /** @test */
    public function client_can_only_see_own_company_products()
    {
        // Arrange
        $client = Client::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $client->companies()->attach($company1);
        
        $product1 = Product::factory()->create(['company_id' => $company1->id]);
        $product2 = Product::factory()->create(['company_id' => $company2->id]);
        
        // Act
        $this->actingAs($client, 'client');
        session(['selected_tenant_id' => $company1->id]);
        
        $products = Product::all();
        
        // Assert
        $this->assertTrue($products->contains($product1));
        $this->assertFalse($products->contains($product2));
    }

    /** @test */
    public function client_cannot_access_unauthorized_company()
    {
        $client = Client::factory()->create();
        $company = Company::factory()->create();
        
        $this->actingAs($client, 'client')
             ->get("/api/client/companies/{$company->uuid}/products")
             ->assertStatus(403);
    }
}
```

## Common Pitfalls

### Data Leakage

```php
// ❌ BAD: Bypasses global scope
Product::withoutGlobalScope('company')->get();

// ✅ GOOD: Respects tenant isolation
Product::all();
```

### Missing Tenant Validation

```php
// ❌ BAD: No access check
$company = Company::find($request->company_id);

// ✅ GOOD: Validate access
$company = auth()->user()->companies()->findOrFail($request->company_id);
```

### Hardcoded company_id

```php
// ❌ BAD: Hardcoded value
Product::where('company_id', 1)->get();

// ✅ GOOD: Use session
Product::where('company_id', session('selected_tenant_id'))->get();

// ✅ BETTER: Use global scope
Product::all(); // Automatically scoped
```

## References

- [AGENTS.md](../../../AGENTS.md)
- [Laravel Multi-Tenancy Packages](https://tenancyforlaravel.com/)
- [Filament Multi-Tenancy](https://filamentphp.com/docs/panels/tenancy)
