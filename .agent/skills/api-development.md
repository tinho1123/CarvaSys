---
name: API Development with Sanctum
description: Guidelines for building RESTful APIs with Laravel Sanctum authentication in CarvaSys
---

# API Development with Sanctum

This skill covers building RESTful APIs with Laravel Sanctum for the CarvaSys client portal.

## API Structure

### Route Organization

```php
// routes/api_client.php
use App\Http\Controllers\Api\Client\ProductController;
use App\Http\Controllers\Api\Client\OrderController;

Route::prefix('client')->middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::prefix('companies/{tenant}')->group(function () {
        // Products
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product:uuid}', [ProductController::class, 'show']);
        
        // Orders
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{order:uuid}', [OrderController::class, 'show']);
    });
    
    // Non-tenant routes
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notification:uuid}/read', [NotificationController::class, 'markAsRead']);
});
```

## Controller Pattern

### Standard API Controller

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
        // 1. Validate input
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:products_categories,id',
            'featured' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // 2. Build query with filters
        $query = Product::query()
            ->with('category')
            ->where('active', true);

        // Search filter
        if ($search = $validated['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $validated['category_id'] ?? null) {
            $query->where('category_id', $categoryId);
        }

        // Featured filter
        if (isset($validated['featured'])) {
            $query->where('featured', $validated['featured']);
        }

        // 3. Paginate results
        $products = $query->paginate($validated['per_page'] ?? 15);

        // 4. Return JSON response
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

    /**
     * Create new product (admin only)
     */
    public function store(Request $request, string $companyUuid): JsonResponse
    {
        // 1. Validate
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:products_categories,id',
            'image' => 'nullable|image|max:2048',
        ]);

        // 2. Handle file upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // 3. Create product
        $product = Product::create($validated);

        // 4. Return created resource
        return response()->json($product, 201);
    }

    /**
     * Update product
     */
    public function update(Request $request, string $companyUuid, string $productUuid): JsonResponse
    {
        $product = Product::where('uuid', $productUuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'active' => 'sometimes|boolean',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Delete product
     */
    public function destroy(string $companyUuid, string $productUuid): JsonResponse
    {
        $product = Product::where('uuid', $productUuid)->firstOrFail();
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
```

## Authentication with Sanctum

### Token Generation

```php
// Login endpoint
public function login(Request $request): JsonResponse
{
    $validated = $request->validate([
        'document' => 'required|string',
        'password' => 'required|string',
    ]);

    $client = Client::where('document_number', $validated['document'])->first();

    if (!$client || !Hash::check($validated['password'], $client->password)) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    // Create token
    $token = $client->createToken('carvaSys')->plainTextToken;

    return response()->json([
        'token' => $token,
        'client' => $client,
    ]);
}
```

### Token Usage

```bash
# Client request with token
curl -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     https://api.carvasys.com/api/client/products
```

### Token Revocation

```php
// Logout endpoint
public function logout(Request $request): JsonResponse
{
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Logged out successfully']);
}

// Revoke all tokens
public function revokeAll(Request $request): JsonResponse
{
    $request->user()->tokens()->delete();

    return response()->json(['message' => 'All tokens revoked']);
}
```

## Response Formats

### Success Response

```json
{
  "data": [
    {
      "uuid": "abc-123",
      "name": "Product Name",
      "price": "99.99"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3
  },
  "links": {
    "first": "/api/products?page=1",
    "last": "/api/products?page=3",
    "prev": null,
    "next": "/api/products?page=2"
  }
}
```

### Error Response

```json
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required"],
    "price": ["The price must be at least 0"]
  }
}
```

### Single Resource

```json
{
  "uuid": "abc-123",
  "name": "Product Name",
  "price": "99.99",
  "category": {
    "uuid": "cat-456",
    "name": "Category Name"
  }
}
```

## Validation

### Request Validation

```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:clients,email',
    'price' => 'required|numeric|min:0|max:999999.99',
    'quantity' => 'required|integer|min:1',
    'category_id' => 'required|exists:products_categories,id',
    'image' => 'nullable|image|mimes:jpg,png|max:2048',
    'items' => 'required|array|min:1',
    'items.*.product_uuid' => 'required|exists:products,uuid',
    'items.*.quantity' => 'required|integer|min:1',
]);
```

### Custom Validation Rules

```php
// Custom rule for CPF validation
$request->validate([
    'cpf' => ['required', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', new ValidCpf],
]);
```

## Pagination

### Laravel Pagination

```php
// Automatic pagination
$products = Product::paginate(15);

return response()->json($products);
```

### Custom Pagination

```php
$perPage = $request->input('per_page', 15);
$perPage = min($perPage, 100); // Max 100 items

$products = Product::paginate($perPage);
```

## Filtering and Searching

### Search Pattern

```php
$query = Product::query();

if ($search = $request->input('search')) {
    $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
    });
}

$products = $query->paginate(15);
```

### Multiple Filters

```php
$query = Product::query();

// Category filter
if ($categoryId = $request->input('category_id')) {
    $query->where('category_id', $categoryId);
}

// Price range filter
if ($minPrice = $request->input('min_price')) {
    $query->where('price', '>=', $minPrice);
}

if ($maxPrice = $request->input('max_price')) {
    $query->where('price', '<=', $maxPrice);
}

// Active filter
if ($request->has('active')) {
    $query->where('active', $request->boolean('active'));
}

$products = $query->paginate(15);
```

## Error Handling

### Try-Catch Pattern

```php
public function store(Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        Log::error('Product creation failed', [
            'error' => $e->getMessage(),
            'company_id' => session('selected_tenant_id'),
        ]);

        return response()->json([
            'message' => 'Internal server error',
        ], 500);
    }
}
```

### HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET/PUT/DELETE |
| 201 | Created | Resource created successfully |
| 204 | No Content | Successful DELETE with no response body |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Authenticated but no access |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |
| 502 | Bad Gateway | External API error |

## Rate Limiting

### Apply Rate Limiting

```php
// routes/api_client.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // 60 requests per minute
});
```

### Custom Rate Limits

```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

## File Uploads

### Image Upload

```php
public function uploadImage(Request $request): JsonResponse
{
    $request->validate([
        'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
    ]);

    $path = $request->file('image')->store('products', 'public');

    return response()->json([
        'path' => $path,
        'url' => Storage::url($path),
    ]);
}
```

## Testing APIs

### Feature Test

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    /** @test */
    public function client_can_list_products()
    {
        $client = Client::factory()->create();
        $company = Company::factory()->create();
        $client->companies()->attach($company);
        
        Product::factory()->count(5)->create(['company_id' => $company->id]);
        
        $token = $client->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/client/companies/{$company->uuid}/products");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['uuid', 'name', 'price']
                     ],
                     'meta'
                 ])
                 ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function unauthenticated_request_returns_401()
    {
        $company = Company::factory()->create();

        $response = $this->getJson("/api/client/companies/{$company->uuid}/products");

        $response->assertStatus(401);
    }
}
```

## Best Practices

### ✅ Do This

```php
// Use route model binding with UUID
Route::get('products/{product:uuid}', [ProductController::class, 'show']);

// Validate all inputs
$validated = $request->validate([...]);

// Use proper HTTP status codes
return response()->json($data, 201); // Created

// Log errors with context
Log::error('API error', ['company_id' => session('selected_tenant_id')]);

// Return consistent JSON structure
return response()->json(['data' => $products, 'meta' => $meta]);
```

### ❌ Don't Do This

```php
// Don't use auto-increment IDs in URLs
Route::get('products/{id}', ...); // Exposes sequential IDs

// Don't skip validation
$product = Product::create($request->all()); // Security risk!

// Don't return raw Eloquent models
return $product; // Inconsistent format

// Don't expose sensitive data
return response()->json($user); // May include password hash!
```

## References

- [Laravel Sanctum Docs](https://laravel.com/docs/sanctum)
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
- [RESTful API Design](https://restfulapi.net/)
