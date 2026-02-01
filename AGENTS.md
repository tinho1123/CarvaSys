# AGENTS.md - CarvaSys Development Guidelines

## Project Stack
- **Backend**: Laravel 10, PHP 8.1+, Filament 3.x, Laravel Sanctum
- **Frontend**: React 19.x, Inertia.js, Tailwind CSS v4, Vite 6.x
- **Database**: MySQL/MariaDB with Eloquent ORM
- **Testing**: PHPUnit 10.x
- **Dev Env**: Laravel Sail (Docker)

## Essential Commands

### Development Environment
```bash
# Sail (Recommended)
./vendor/bin/sail up -d                    # Start containers
./vendor/bin/sail down                      # Stop containers

# Dependencies
composer install                           # PHP deps
npm install                               # Node deps
./vendor/bin/sail composer install        # PHP deps via Sail
./vendor/bin/sail npm run dev             # Vite dev via Sail
```

### Code Quality & Testing
```bash
# Code Formatting (MANDATORY before commits)
./vendor/bin/pint

# Testing
./vendor/bin/phpunit                       # All tests
./vendor/bin/phpunit tests/Unit           # Unit tests only
./vendor/bin/phpunit tests/Feature        # Feature tests only
./vendor/bin/phpunit tests/Unit/UserTest.php  # Single file
./vendor/bin/phpunit --filter test_method_name  # Single method
```

### Laravel Commands
```bash
php artisan migrate                       # Run migrations
php artisan migrate:fresh --seed          # Fresh with seeding
php artisan serve                         # Start dev server
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

### Frontend Commands
```bash
npm run dev                               # Vite development server
npm run build                             # Production build
```

## Code Style Guidelines

### PHP/Laravel Standards
- **Indentation**: 4 spaces (.editorconfig enforced)
- **Encoding**: UTF-8, LF line endings
- **Formatter**: Laravel Pint (`./vendor/bin/pint`) - REQUIRED
- **PHP Version**: 8.1+ (8.4+ in Sail)

#### Import Order (Strict)
```php
<?php

namespace App\Models;

// 1. Framework imports
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 2. Package imports  
use Laravel\Sanctum\HasApiTokens;

// 3. App imports
use App\Models\User;
```

#### Naming Conventions
- Classes: `PascalCase` (User, TransactionResource)
- Methods: `camelCase` (getUserData, createTransaction) 
- Variables: `camelCase` ($userData, $transactionId)
- Constants: `UPPER_SNAKE_CASE` (API_VERSION, MAX_ATTEMPTS)
- Database tables: `snake_case` (users, transactions)
- UUID route keys: All models use `uuid` with `getRouteKeyName()`

#### Model Structure
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

use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
        ]);
    }
}
```

### JavaScript/React Standards
- **Framework**: React 19.x function components with hooks
- **State**: useState/useReducer for local state
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

    return <div className="p-6">Users Component</div>;
}
```

## Error Handling Guidelines
- Use try-catch for external API calls
- Return proper HTTP status codes (200, 400, 401, 403, 404, 422, 500, 502)
- Log errors with context including `company_id`
- Use Laravel's validation for form inputs
- Handle multi-tenant company context in all operations

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
- **Test Method Names**: Descriptive, `test_` prefix or `@test` docblock
- **Pattern**: Arrange-Act-Assert
- **Coverage**: Test both success and failure scenarios
- **Multi-tenancy**: Always test with proper company context
- **Pre-commit**: Run `./vendor/bin/pint` before committing test files

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

## Database & Multi-Tenancy

### Migration Pattern
```php
<?php

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

### Multi-Tenancy Requirements
- **All tables** must have `company_id` foreign key with cascade delete
- Current company: `auth()->user()->companies->first()->id`
- Models should auto-set `company_id` in boot method
- Filter all queries by `company_id` for security
- Filament routes use `{tenant}` parameter (company UUID)

## Security & Performance
- Use Laravel CSRF protection
- Validate all inputs with Laravel validation rules
- Use eager loading (`with()`) to prevent N+1 queries
- Never commit secrets/.env files to version control
- Filter queries by `company_id` for multi-tenant isolation

## Pre-commit Checklist
1. Run `./vendor/bin/pint` to fix all formatting issues
2. Run `./vendor/bin/phpunit` - tests must pass
3. Manual functionality testing (when applicable)
4. Verify no sensitive data committed
5. Confirm multi-tenant relationships properly filtered by `company_id`