---
name: Testing Strategy with PHPUnit
description: Comprehensive testing guidelines for CarvaSys using PHPUnit with unit and feature tests
---

# Testing Strategy with PHPUnit

This skill covers testing strategies for CarvaSys using PHPUnit 10.x with Laravel's testing utilities.

## Testing Philosophy

### Test Pyramid

```
        /\
       /  \      E2E Tests (Few)
      /____\
     /      \    Integration/Feature Tests (Some)
    /________\
   /          \  Unit Tests (Many)
  /____________\
```

- **Unit Tests:** Test individual methods and classes in isolation
- **Feature Tests:** Test complete user flows and API endpoints
- **E2E Tests:** Manual testing or browser automation (future)

## Running Tests

### Basic Commands

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Unit/UserTest.php

# Run specific test method
./vendor/bin/phpunit --filter test_can_create_user

# Run with coverage (requires Xdebug)
./vendor/bin/phpunit --coverage-html coverage

# Run only unit tests
./vendor/bin/phpunit tests/Unit

# Run only feature tests
./vendor/bin/phpunit tests/Feature
```

### Via Sail

```bash
./vendor/bin/sail test
./vendor/bin/sail test --filter test_name
```

## Unit Tests

### Unit Test Pattern

```php
<?php

namespace Tests\Unit;

use App\Models\FavoredTransaction;
use Tests\TestCase;

class FavoredTransactionTest extends TestCase
{
    /**
     * Test remaining balance calculation
     *
     * @test
     */
    public function it_calculates_remaining_balance_correctly()
    {
        // Arrange
        $transaction = FavoredTransaction::factory()->make([
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
        $transaction = FavoredTransaction::factory()->make([
            'favored_total' => 100.00,
            'favored_paid_amount' => 100.00,
        ]);

        $this->assertTrue($transaction->isFullyPaid());
    }

    /** @test */
    public function it_knows_when_not_fully_paid()
    {
        $transaction = FavoredTransaction::factory()->make([
            'favored_total' => 100.00,
            'favored_paid_amount' => 50.00,
        ]);

        $this->assertFalse($transaction->isFullyPaid());
    }
}
```

### Testing Relationships

```php
/** @test */
public function product_belongs_to_company()
{
    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);

    $this->assertInstanceOf(Company::class, $product->company);
    $this->assertEquals($company->id, $product->company->id);
}

/** @test */
public function company_has_many_products()
{
    $company = Company::factory()->create();
    Product::factory()->count(3)->create(['company_id' => $company->id]);

    $this->assertCount(3, $company->products);
}
```

### Testing Scopes

```php
/** @test */
public function active_scope_filters_active_products()
{
    Product::factory()->create(['active' => true]);
    Product::factory()->create(['active' => false]);

    $activeProducts = Product::active()->get();

    $this->assertCount(1, $activeProducts);
    $this->assertTrue($activeProducts->first()->active);
}
```

## Feature Tests

### API Feature Test Pattern

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;
    protected Company $company;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test client and company
        $this->client = Client::factory()->create();
        $this->company = Company::factory()->create();
        $this->client->companies()->attach($this->company);

        // Generate token
        $this->token = $this->client->createToken('test')->plainTextToken;
    }

    /** @test */
    public function client_can_list_products()
    {
        Product::factory()->count(5)->create(['company_id' => $this->company->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/client/companies/{$this->company->uuid}/products");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['uuid', 'name', 'price', 'category']
                     ],
                     'meta'
                 ])
                 ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function client_can_search_products()
    {
        Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Smartphone XYZ'
        ]);

        Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Laptop ABC'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/client/companies/{$this->company->uuid}/products?search=Smartphone");

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.name', 'Smartphone XYZ');
    }

    /** @test */
    public function unauthenticated_request_returns_401()
    {
        $response = $this->getJson("/api/client/companies/{$this->company->uuid}/products");

        $response->assertStatus(401);
    }

    /** @test */
    public function client_cannot_access_other_company_products()
    {
        $otherCompany = Company::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/client/companies/{$otherCompany->uuid}/products");

        $response->assertStatus(403);
    }
}
```

### Authentication Tests

```php
/** @test */
public function client_can_login_with_valid_credentials()
{
    $client = Client::factory()->create([
        'document_number' => '12345678900',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/cliente/login', [
        'document' => '12345678900',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['token', 'client']);
}

/** @test */
public function login_fails_with_invalid_credentials()
{
    $client = Client::factory()->create([
        'document_number' => '12345678900',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/cliente/login', [
        'document' => '12345678900',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
}

/** @test */
public function account_locks_after_5_failed_attempts()
{
    $client = Client::factory()->create([
        'document_number' => '12345678900',
        'password' => Hash::make('password123'),
    ]);

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/cliente/login', [
            'document' => '12345678900',
            'password' => 'wrongpassword',
        ]);
    }

    $client->refresh();

    $this->assertNotNull($client->locked_until);
    $this->assertTrue($client->locked_until->isFuture());
}
```

### Multi-Tenancy Tests

```php
/** @test */
public function queries_are_scoped_to_current_tenant()
{
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    Product::factory()->create(['company_id' => $company1->id, 'name' => 'Product 1']);
    Product::factory()->create(['company_id' => $company2->id, 'name' => 'Product 2']);

    // Set tenant
    session(['selected_tenant_id' => $company1->id]);

    // Apply global scope (normally done by middleware)
    Product::addGlobalScope('company', function ($query) {
        $query->where('company_id', session('selected_tenant_id'));
    });

    $products = Product::all();

    $this->assertCount(1, $products);
    $this->assertEquals('Product 1', $products->first()->name);
}
```

## Database Testing

### Using Factories

```php
// Create single model
$product = Product::factory()->create();

// Create with attributes
$product = Product::factory()->create([
    'name' => 'Custom Product',
    'price' => 99.99,
]);

// Create multiple
$products = Product::factory()->count(5)->create();

// Make without saving
$product = Product::factory()->make();
```

### Database Assertions

```php
/** @test */
public function product_can_be_created()
{
    $product = Product::factory()->create([
        'name' => 'Test Product',
    ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
    ]);
}

/** @test */
public function product_can_be_deleted()
{
    $product = Product::factory()->create();
    $product->delete();

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
}
```

### RefreshDatabase Trait

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase; // Migrates and rolls back after each test

    /** @test */
    public function test_example()
    {
        // Database is fresh for each test
    }
}
```

## Assertions

### Common Assertions

```php
// Equality
$this->assertEquals(expected, actual);
$this->assertNotEquals(expected, actual);

// Boolean
$this->assertTrue(condition);
$this->assertFalse(condition);

// Null
$this->assertNull(value);
$this->assertNotNull(value);

// Arrays
$this->assertCount(expectedCount, array);
$this->assertContains(needle, haystack);
$this->assertEmpty(array);

// Strings
$this->assertStringContainsString(needle, haystack);
$this->assertStringStartsWith(prefix, string);

// Exceptions
$this->expectException(Exception::class);
$this->expectExceptionMessage('Error message');
```

### HTTP Assertions

```php
// Status codes
$response->assertStatus(200);
$response->assertOk();
$response->assertCreated(); // 201
$response->assertNoContent(); // 204
$response->assertNotFound(); // 404
$response->assertForbidden(); // 403
$response->assertUnauthorized(); // 401

// JSON structure
$response->assertJsonStructure([
    'data' => [
        '*' => ['id', 'name', 'price']
    ],
    'meta'
]);

// JSON content
$response->assertJson([
    'name' => 'Product Name',
]);

$response->assertJsonPath('data.0.name', 'Product Name');

// JSON count
$response->assertJsonCount(5, 'data');
```

## Mocking

### Mocking External APIs

```php
use Illuminate\Support\Facades\Http;

/** @test */
public function it_handles_stripe_payment()
{
    Http::fake([
        'api.stripe.com/*' => Http::response([
            'id' => 'pi_123',
            'status' => 'succeeded',
        ], 200),
    ]);

    $response = $this->postJson('/api/payments/create-intent', [
        'amount' => 100.00,
    ]);

    $response->assertStatus(200);
}
```

### Mocking Events

```php
use Illuminate\Support\Facades\Event;

/** @test */
public function order_created_event_is_dispatched()
{
    Event::fake();

    $order = Order::factory()->create();

    Event::assertDispatched(OrderCreated::class, function ($event) use ($order) {
        return $event->order->id === $order->id;
    });
}
```

## Test Organization

### Naming Conventions

```php
// Method name describes what is being tested
public function test_user_can_create_product() {}

// Or use @test docblock
/** @test */
public function user_can_create_product() {}

// Descriptive names
public function test_validation_fails_when_price_is_negative() {}
```

### AAA Pattern (Arrange-Act-Assert)

```php
/** @test */
public function product_price_can_be_updated()
{
    // Arrange
    $product = Product::factory()->create(['price' => 100.00]);

    // Act
    $product->update(['price' => 150.00]);

    // Assert
    $this->assertEquals(150.00, $product->fresh()->price);
}
```

## Coverage Goals

### Target Coverage

- **Overall:** 70%+
- **Models:** 80%+
- **Controllers:** 70%+
- **Critical paths:** 90%+

### Generate Coverage Report

```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Pre-Commit Testing

### Required Checks

1. ✅ Run `./vendor/bin/pint` - Code formatting
2. ✅ Run `./vendor/bin/phpunit` - All tests pass
3. ✅ Manual functionality testing (when applicable)

### CI/CD Integration (Future)

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run tests
        run: ./vendor/bin/phpunit
```

## Best Practices

### ✅ Do This

```php
// Use factories for test data
$product = Product::factory()->create();

// Use RefreshDatabase for clean state
use RefreshDatabase;

// Test one thing per test
public function test_product_name_is_required() {}

// Use descriptive test names
public function test_client_cannot_access_other_company_data() {}

// Clean up after tests (RefreshDatabase does this)
```

### ❌ Don't Do This

```php
// Don't use real data
$product = Product::find(1); // May not exist!

// Don't test multiple things
public function test_everything() {} // Too broad!

// Don't skip assertions
public function test_product_creation() {
    Product::create([...]);
    // No assertions!
}

// Don't depend on test order
// Tests should be independent
```

## References

- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [AGENTS.md](../../../AGENTS.md)
