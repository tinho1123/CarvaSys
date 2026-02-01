# AGENTS.md - CarvaSys Development Guidelines

This file contains development guidelines for agentic coding agents working on CarvaSys Laravel project.

## Project Overview
CarvaSys is a Laravel 10 application with the following stack:
- Backend: Laravel 10, PHP 8.4, Filament 3.x
- Frontend: React 19.x, Inertia.js, Tailwind CSS v4, Vite 6.x
- Database: MySQL/MariaDB with Eloquent ORM
- Testing: PHPUnit 10.x
- Multi-tenancy with Laravel Sanctum
- Development: Laravel Sail (Docker)

## Build Commands

### Docker/Sail Commands (Recommended)
```bash
# Start development environment
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Execute commands inside container
./vendor/bin/sail php artisan migrate
./vendor/bin/sail npm run dev
./vendor/bin/sail composer install
```

### PHP/Composer Commands
```bash
# Install dependencies
composer install

# Run Laravel Pint for code formatting (REQUIRED before commits)
./vendor/bin/pint

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Fresh migrate with seeding
php artisan migrate:fresh --seed

# Start development server
php artisan serve

# Clear caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

### Node/NPM Commands
```bash
# Install frontend dependencies
npm install

# Start Vite development server
npm run dev

# Build for production
npm run build
```

### Testing Commands
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature

# Run single test file
./vendor/bin/phpunit tests/Unit/ExampleTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage

# Run specific test method
./vendor/bin/phpunit --filter test_method_name
```

## Code Style Guidelines

### PHP/Laravel Standards
- **Indentation**: 4 spaces (configured in .editorconfig)
- **File Encoding**: UTF-8
- **Line Endings**: LF (Unix style)
- **Code Formatter**: Laravel Pint (`./vendor/bin/pint`) - MANDATORY before commits
- **PHP Version**: 8.4+ (Laravel Sail runtime)

#### Import Organization
```php
<?php

namespace App\Models;

// Framework imports first
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Package imports
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;

// App imports last
use App\Models\Company;
```

#### Naming Conventions
- **Classes**: PascalCase (User, ProductCategory, TransactionResource)
- **Methods**: camelCase (getUserData, createTransaction)
- **Variables**: camelCase ($userData, $transactionId)
- **Constants**: UPPER_SNAKE_CASE (API_VERSION, MAX_ATTEMPTS)
- **Database Tables**: snake_case (users, product_categories)
- **UUID Keys**: All models use `uuid` as route key with `getRouteKeyName()` method

#### Model Guidelines
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_id',
        'user_id',
        'client_id', 
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

#### Filament Resources
```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuários';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true),
            ]);
    }
}
```

### JavaScript/React Standards
- **Framework**: React 19.x with function components and hooks
- **State**: useState, useReducer for local state
- **Data Fetching**: Axios with async/await
- **Styling**: Tailwind CSS v4 classes

```javascript
import { useState } from 'react';
import axios from 'axios';

export default function UserList() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(false);

    const fetchUsers = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/users');
            setUsers(response.data);
        } catch (error) {
            console.error('Failed to fetch users:', error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="p-6">
            <h1 className="text-2xl font-bold mb-4">Users</h1>
        </div>
    );
}
```

## Error Handling Guidelines

### PHP/Laravel
- Use try-catch blocks for external API calls
- Return proper HTTP status codes
- Log errors with context
- Use Laravel's validation for form input
- Handle multi-tenant company context properly

```php
try {
    $response = Http::timeout(30)->post($url, $data);
    
    if (!$response->successful()) {
        Log::error('API request failed', [
            'url' => $url,
            'status' => $response->status(),
            'company_id' => auth()->user()->companies->first()->id,
        ]);
        
        return response()->json(['error' => 'External API error'], 502);
    }
    
    return response()->json($response->json());
    
} catch (\Exception $e) {
    Log::error('API exception', [
        'message' => $e->getMessage(),
        'company_id' => auth()->user()->companies->first()->id,
    ]);
    
    return response()->json(['error' => 'Internal server error'], 500);
}
```

## Testing Guidelines

### PHPUnit Tests
- Use descriptive test method names
- Arrange-Act-Assert pattern
- Test both success and failure scenarios
- Run `./vendor/bin/pint` before committing test files
- Test multi-tenant functionality with proper company setup

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function it_can_create_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}
```

## Database Guidelines

### Migrations
- Use descriptive table and column names
- Include proper indexes for performance
- Use foreign key constraints
- Support multi-tenancy with `company_id` foreign keys

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }
};
```

## Multi-Tenancy Guidelines
- All tables must have `company_id` foreign key
- Use `auth()->user()->companies->first()->id` for current company
- Models should automatically set `company_id` in boot method
- Filament routes use `{tenant}` parameter (company ID)

## Security & Performance Guidelines
- Use Laravel's built-in CSRF protection
- Validate all user inputs
- Use eager loading to prevent N+1 queries
- Never commit secrets to version control
- Filter queries by company_id for multi-tenant security

## Pre-commit Checklist
1. Run `./vendor/bin/pint` to fix formatting issues
2. Run `./vendor/bin/phpunit` to ensure tests pass
3. Test functionality manually if applicable
4. Verify no sensitive data is committed
5. Ensure multi-tenant relationships are properly filtered