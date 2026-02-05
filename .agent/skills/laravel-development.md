---
name: Laravel Development with CarvaSys Patterns
description: Guidelines for developing Laravel applications following CarvaSys project standards and best practices
---

# Laravel Development with CarvaSys Patterns

This skill provides comprehensive guidelines for developing Laravel applications following the CarvaSys project's established patterns, conventions, and best practices.

## Technology Stack

- **Backend:** Laravel 10, PHP 8.1+
- **Admin Panel:** Filament 3.x
- **Frontend:** React 19.x, Inertia.js, Tailwind CSS v4
- **Database:** MySQL/MariaDB with Eloquent ORM
- **Testing:** PHPUnit 10.x
- **Dev Environment:** Laravel Sail (Docker)

## Code Style Standards

### PHP/Laravel Conventions

#### Indentation and Formatting
- **Indentation:** 4 spaces (enforced by .editorconfig)
- **Encoding:** UTF-8, LF line endings
- **Formatter:** Laravel Pint - **MANDATORY** before commits

```bash
# Run Pint before every commit
./vendor/bin/pint
```

#### Import Order (Strict)

```php
<?php

namespace App\Models;

// 1. Framework imports (alphabetical)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// 2. Package imports (alphabetical)
use Laravel\Sanctum\HasApiTokens;

// 3. App imports (alphabetical)
use App\Models\Company;
use App\Models\User;
```

#### Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| Classes | PascalCase | `UserController`, `TransactionResource` |
| Methods | camelCase | `getUserData()`, `createTransaction()` |
| Variables | camelCase | `$userData`, `$transactionId` |
| Constants | UPPER_SNAKE_CASE | `API_VERSION`, `MAX_ATTEMPTS` |
| Database tables | snake_case plural | `users`, `favored_transactions` |
| Database columns | snake_case | `company_id`, `created_at` |
| Route keys | UUID | All models use `uuid` via `getRouteKeyName()` |

### Model Structure Pattern

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FavoredTransaction extends Model
{
    use HasFactory;

    // 1. Fillable fields
    protected $fillable = [
        'uuid',
        'company_id',
        'client_id',
        'amount',
        'description',
    ];

    // 2. Casts
    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'active' => 'boolean',
    ];

    // 3. Route key
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // 4. Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // 5. Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // 6. Accessors/Mutators
    public function getRemainingBalanceAttribute(): float
    {
        return $this->favored_total - $this->favored_paid_amount;
    }

    // 7. Business logic methods
    public function isFullyPaid(): bool
    {
        return $this->favored_paid_amount >= $this->favored_total;
    }

    // 8. Boot method (if needed)
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (is_null($model->favored_total)) {
                $model->favored_total = $model->amount ?? 0;
            }
        });
    }
}
```

### Controller Structure Pattern

```php
<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List products with pagination and filters
     */
    public function index(Request $request, string $companyUuid): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:products_categories,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Product::query()
            ->with('category')
            ->where('active', true);

        if ($validated['search'] ?? null) {
            $query->where(function ($q) use ($validated) {
                $q->where('name', 'like', "%{$validated['search']}%")
                  ->orWhere('description', 'like', "%{$validated['search']}%");
            });
        }

        if ($validated['category_id'] ?? null) {
            $query->where('category_id', $validated['category_id']);
        }

        $products = $query->paginate($validated['per_page'] ?? 15);

        return response()->json($products);
    }

    /**
     * Get single product details
     */
    public function show(string $companyUuid, string $productUuid): JsonResponse
    {
        $product = Product::where('uuid', $productUuid)
            ->with('category')
            ->firstOrFail();

        return response()->json($product);
    }
}
```

## Multi-Tenancy Patterns

### Tenant Isolation

**All tenant-scoped models must:**
1. Have `company_id` foreign key
2. Apply global scopes via middleware
3. Validate tenant access in controllers

```php
// Middleware: SetClientTenant
Transaction::addGlobalScope('company', function ($query) {
    $query->where('company_id', session('selected_tenant_id'));
});
```

### Model Boot Method for Auto-Setting company_id

```php
protected static function booted(): void
{
    static::creating(function (self $model) {
        if (is_null($model->company_id)) {
            $model->company_id = session('selected_tenant_id');
        }
    });
}
```

## Error Handling Patterns

### API Error Responses

```php
try {
    $response = Http::timeout(30)->post($url, $data);
    
    if (!$response->successful()) {
        Log::error('API request failed', [
            'url' => $url,
            'status' => $response->status(),
            'company_id' => session('selected_tenant_id'),
        ]);
        
        return response()->json([
            'error' => 'External API error'
        ], 502);
    }
    
    return response()->json($response->json());
    
} catch (\Exception $e) {
    Log::error('API exception', [
        'message' => $e->getMessage(),
        'company_id' => session('selected_tenant_id'),
    ]);
    
    return response()->json([
        'error' => 'Internal server error'
    ], 500);
}
```

### HTTP Status Codes

| Code | Usage |
|------|-------|
| 200 | Successful GET/POST/PUT |
| 201 | Resource created |
| 400 | Bad request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not found |
| 422 | Validation failed |
| 500 | Server error |
| 502 | External API error |

## Database Patterns

### Migration Pattern

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'delivered'])->default('pending');
            $table->timestamps();
            
            $table->index(['company_id', 'client_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

### Key Migration Rules

1. **Always include:**
   - `id()` - Auto-increment primary key
   - `uuid()` - For route keys
   - `company_id` - For multi-tenancy
   - `timestamps()` - created_at, updated_at

2. **Foreign keys:**
   - Use `constrained()` for automatic constraint
   - Use `onDelete('cascade')` for tenant data
   - Use `onDelete('set null')` for optional references

3. **Indexes:**
   - Index all foreign keys
   - Index frequently queried columns
   - Create composite indexes for common query patterns

## Testing Patterns

### Unit Test Pattern

```php
<?php

namespace Tests\Unit;

use App\Models\FavoredTransaction;
use Tests\TestCase;

class FavoredTransactionTest extends TestCase
{
    /** @test */
    public function it_calculates_remaining_balance_correctly()
    {
        // Arrange
        $transaction = FavoredTransaction::factory()->create([
            'favored_total' => 100.00,
            'favored_paid_amount' => 30.00,
        ]);

        // Act
        $balance = $transaction->getRemainingBalance();

        // Assert
        $this->assertEquals(70.00, $balance);
    }

    /** @test */
    public function it_knows_when_fully_paid()
    {
        $transaction = FavoredTransaction::factory()->create([
            'favored_total' => 100.00,
            'favored_paid_amount' => 100.00,
        ]);

        $this->assertTrue($transaction->isFullyPaid());
    }
}
```

### Feature Test Pattern

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    /** @test */
    public function client_can_list_products()
    {
        // Arrange
        $client = Client::factory()->create();
        $company = Company::factory()->create();
        $client->companies()->attach($company);
        
        $token = $client->createToken('test')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/client/companies/{$company->uuid}/products");

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }
}
```

## Essential Commands

### Development

```bash
# Start Sail containers
./vendor/bin/sail up -d

# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

### Code Quality

```bash
# Format code (MANDATORY before commits)
./vendor/bin/pint

# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Unit/UserTest.php

# Run specific test method
./vendor/bin/phpunit --filter test_method_name
```

### Frontend

```bash
# Development server
npm run dev

# Production build
npm run build
```

## Pre-commit Checklist

1. ✅ Run `./vendor/bin/pint` - Code formatting
2. ✅ Run `./vendor/bin/phpunit` - All tests pass
3. ✅ Manual functionality testing
4. ✅ Verify no sensitive data committed
5. ✅ Confirm multi-tenant relationships filtered by `company_id`

## Common Pitfalls to Avoid

### ❌ Don't Do This

```php
// Missing company_id in query
$products = Product::all(); // Leaks data across tenants!

// Using guarded instead of fillable
protected $guarded = []; // Security risk!

// Not using UUID for routes
Route::get('/products/{id}', ...); // Exposes sequential IDs
```

### ✅ Do This Instead

```php
// Properly scoped query
$products = Product::where('company_id', session('selected_tenant_id'))->get();

// Use fillable whitelist
protected $fillable = ['name', 'price', 'description'];

// Use UUID for routes
Route::get('/products/{product:uuid}', ...);
```

## References

- [AGENTS.md](../../../AGENTS.md) - Full development guidelines
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
