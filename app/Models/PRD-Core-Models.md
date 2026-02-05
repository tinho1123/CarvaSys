# PRD: Core Models

**Module:** Data Models & Business Logic  
**Version:** 1.0  
**Last Updated:** February 4, 2026  
**Status:** Implemented (Phase 1)

---

## 1. Module Overview

The Core Models module defines the fundamental data structures and business logic for the CarvaSys platform. These models represent the primary entities and their relationships in the multi-tenant system.

### Core Entities
- **Client** - End users who purchase on credit
- **Company** - Tenants (businesses using the platform)
- **User** - Admin users managing companies
- **FavoredTransaction** - Credit transactions (fiado)
- **Product** - Items available for purchase
- **ProductsCategories** - Product categorization
- **Order** - Purchase orders
- **OrderItem** - Line items in orders
- **Transaction** - General financial transactions
- **Fee** - Fees and charges
- **Notification** - In-app notifications

---

## 2. Model Specifications

### 2.1 Client Model

**Purpose:** Represents end-user clients who can access multiple companies.

**File:** `app/Models/Client.php`

**Relationships:**
- `belongsToMany(Company)` via `client_company` pivot (N:N)
- `hasMany(Order)`
- `hasMany(FavoredTransaction)`
- `hasMany(Notification)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',                  // UUID for route keys
    'email',                 // Optional email
    'password',              // Hashed password
    'document_type',         // 'cpf' or 'cnpj'
    'document_number',       // Tax ID (digits only)
    'name',                  // Full name
    'phone',                 // Contact phone
    'active',                // Account status
    'last_login_at',         // Last login timestamp
    'login_attempts',        // Failed login counter
    'locked_until',          // Account lockout expiry
    'preferences',           // JSON preferences
];
```

**Key Methods:**
- `getRouteKeyName()` - Returns 'uuid' for routing
- `companies()` - N:N relationship with active companies
- `getTenants()` - Filament multi-tenancy support
- `canAccessTenant()` - Validate company access
- `validateLoginAttempts()` - Check if account is locked
- `incrementLoginAttempts()` - Increment failed attempts
- `resetLoginAttempts()` - Reset on successful login

**Business Rules:**
- UUID must be unique
- Document number must be unique
- Password must be hashed with bcrypt
- Account locks after 5 failed attempts for 30 minutes
- Only active clients can login

---

### 2.2 Company Model

**Purpose:** Represents tenant companies in the multi-tenant system.

**File:** `app/Models/Company.php`

**Relationships:**
- `belongsToMany(User)` via `companies_users` (N:N)
- `belongsToMany(Client)` via `client_company` (N:N)
- `hasMany(Transaction)`
- `hasMany(FavoredTransaction)`
- `hasMany(Product)`
- `hasMany(ProductsCategories)`
- `hasMany(Fee)`
- `hasMany(Order)`
- `hasMany(Notification)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',              // UUID for route keys
    'name',              // Company name
    'foundation_date',   // Foundation date
];
```

**Key Methods:**
- `getTenantKeyName()` - Returns 'id' for tenancy
- `getTenantKey()` - Returns company ID
- `users()` - Admin users managing this company
- `transactions()` - All transactions for this company
- `products()` - Product catalog

**Business Rules:**
- UUID must be unique
- Name is required
- All related data must have `company_id` foreign key

---

### 2.3 User Model

**Purpose:** Admin users who manage companies via Filament panel.

**File:** `app/Models/User.php`

**Relationships:**
- `belongsToMany(Company)` via `companies_users` (N:N)

**Key Fields:**
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'email_verified_at',
];
```

**Key Methods:**
- `companies()` - Companies this user can manage
- `canAccessPanel()` - Filament panel access control

**Business Rules:**
- Email must be unique
- Password must be hashed
- Must belong to at least one company

---

### 2.4 FavoredTransaction Model

**Purpose:** Tracks credit transactions (fiado) for clients.

**File:** `app/Models/FavoredTransaction.php`

**Relationships:**
- `belongsTo(Company)`
- `belongsTo(Client)`
- `belongsTo(Product)`
- `belongsTo(ProductsCategories, 'category_id')`

**Key Fields:**
```php
protected $fillable = [
    'uuid',
    'company_id',            // Tenant isolation
    'client_id',             // Client who owes
    'product_id',            // Product purchased
    'name',                  // Transaction name
    'description',           // Details
    'amount',                // Original amount
    'discounts',             // Discount amount
    'total_amount',          // Final amount
    'favored_total',         // Total owed
    'favored_paid_amount',   // Amount paid so far
    'quantity',              // Quantity purchased
    'image',                 // Product image
    'active',                // Transaction status
    'category_name',         // Category name (denormalized)
    'category_id',           // Category FK
    'client_name',           // Client name (denormalized)
];
```

**Key Methods:**
- `getRouteKeyName()` - Returns 'uuid'
- `getRemainingBalance()` - Calculate outstanding balance
- `isFullyPaid()` - Check if fully paid
- `booted()` - Auto-set defaults on creation

**Business Rules:**
- `favored_total` = `total_amount` if not specified
- `favored_paid_amount` defaults to 0
- Remaining balance = `favored_total` - `favored_paid_amount`
- Must be scoped by `company_id`

**Casts:**
```php
protected $casts = [
    'amount' => 'decimal:2',
    'discounts' => 'decimal:2',
    'total_amount' => 'decimal:2',
    'favored_total' => 'decimal:2',
    'favored_paid_amount' => 'decimal:2',
    'quantity' => 'integer',
    'active' => 'boolean',
];
```

---

### 2.5 Product Model

**Purpose:** Product catalog items per company.

**File:** `app/Models/Product.php`

**Relationships:**
- `belongsTo(Company)`
- `belongsTo(ProductsCategories, 'category_id')`
- `hasMany(FavoredTransaction)`
- `hasMany(OrderItem)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',
    'company_id',
    'category_id',
    'name',
    'description',
    'price',
    'image',
    'active',
    'featured',
];
```

**Key Methods:**
- `getRouteKeyName()` - Returns 'uuid'
- `category()` - Product category
- `company()` - Owning company

**Business Rules:**
- Must belong to a company
- Price must be positive
- Active products shown in catalog
- Featured products highlighted

**Casts:**
```php
protected $casts = [
    'price' => 'decimal:2',
    'active' => 'boolean',
    'featured' => 'boolean',
];
```

---

### 2.6 ProductsCategories Model

**Purpose:** Categorize products for organization and filtering.

**File:** `app/Models/ProductsCategories.php`

**Relationships:**
- `belongsTo(Company)`
- `hasMany(Product)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',
    'company_id',
    'name',
    'description',
    'active',
];
```

**Business Rules:**
- Must belong to a company
- Name must be unique per company
- Active categories shown in filters

---

### 2.7 Order Model

**Purpose:** Track customer purchase orders.

**File:** `app/Models/Order.php`

**Relationships:**
- `belongsTo(Company)`
- `belongsTo(Client)`
- `hasMany(OrderItem)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',
    'company_id',
    'client_id',
    'subtotal',
    'discount_amount',
    'fee_amount',
    'total_amount',
    'status',               // pending, confirmed, processing, shipped, delivered, cancelled
    'notes',
    'confirmed_at',
    'shipped_at',
    'delivered_at',
    'cancelled_at',
];
```

**Key Methods:**
- `getRouteKeyName()` - Returns 'uuid'
- `items()` - Order line items
- `recalculateTotal()` - Recalculate order totals

**Status Flow:**
```
pending → confirmed → processing → shipped → delivered
                                          ↓
                                     cancelled
```

**Business Rules:**
- Total = subtotal - discount + fees
- Status transitions must follow flow
- Timestamps track status changes
- Cannot modify confirmed orders

**Casts:**
```php
protected $casts = [
    'subtotal' => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'fee_amount' => 'decimal:2',
    'total_amount' => 'decimal:2',
    'confirmed_at' => 'datetime',
    'shipped_at' => 'datetime',
    'delivered_at' => 'datetime',
    'cancelled_at' => 'datetime',
];
```

---

### 2.8 OrderItem Model

**Purpose:** Line items within an order.

**File:** `app/Models/OrderItem.php`

**Relationships:**
- `belongsTo(Order)`
- `belongsTo(Product)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',
    'order_id',
    'product_id',
    'product_name',         // Denormalized for history
    'quantity',
    'unit_price',
    'discount_percent',
    'discount_amount',
    'total_amount',
];
```

**Key Methods:**
- `calculateTotal()` - Calculate line total with discount

**Business Rules:**
- Total = (unit_price * quantity) - discount_amount
- Product name stored for historical accuracy
- Quantity must be positive

**Casts:**
```php
protected $casts = [
    'quantity' => 'integer',
    'unit_price' => 'decimal:2',
    'discount_percent' => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'total_amount' => 'decimal:2',
];
```

---

### 2.9 Transaction Model

**Purpose:** General financial transactions.

**File:** `app/Models/Transaction.php`

**Relationships:**
- `belongsTo(Company)`
- `belongsTo(User)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',
    'company_id',
    'user_id',
    'amount',
    'type',                 // credit, debit
    'description',
    'reference',
];
```

**Business Rules:**
- Must be scoped by company_id
- Amount must be positive
- Type determines debit/credit

---

### 2.10 Fee Model

**Purpose:** Fees and charges configuration.

**File:** `app/Models/Fee.php`

**Relationships:**
- `belongsTo(Company)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',
    'company_id',
    'name',
    'type',                 // fixed, percentage
    'amount',
    'active',
];
```

**Business Rules:**
- Percentage fees: 0-100
- Fixed fees: positive amounts
- Active fees applied to orders

---

### 2.11 Notification Model

**Purpose:** In-app notifications for clients.

**File:** `app/Models/Notification.php`

**Relationships:**
- `belongsTo(Client, 'client_user_id')`
- `belongsTo(Company)`

**Key Fields:**
```php
protected $fillable = [
    'uuid',
    'client_user_id',
    'company_id',
    'type',                 // order_update, payment_reminder, credit_warning, announcement
    'title',
    'description',
    'message',
    'action_url',
    'read_at',
];
```

**Key Methods:**
- `markAsRead()` - Mark notification as read
- `markAsUnread()` - Mark as unread
- `isRead()` - Check read status

**Scopes:**
- `unread()` - Only unread notifications
- `read()` - Only read notifications
- `recent()` - Recent notifications (last 30 days)

**Business Rules:**
- Must be scoped by company_id
- Read status tracked via `read_at` timestamp
- Action URL for deep linking

**Casts:**
```php
protected $casts = [
    'read_at' => 'datetime',
];
```

---

## 3. Multi-Tenancy Strategy

### Global Scopes

Models with `company_id` should have global scopes applied via middleware:

```php
// SetClientTenant middleware
Transaction::addGlobalScope('company', function ($query) {
    $query->where('company_id', session('selected_tenant_id'));
});
```

**Scoped Models:**
- Transaction
- FavoredTransaction
- Product
- Fee
- Notification
- Order

---

## 4. UUID Route Keys

All models use UUID for route keys instead of auto-increment IDs:

```php
public function getRouteKeyName(): string
{
    return 'uuid';
}
```

**Benefits:**
- Non-sequential IDs (security)
- Globally unique identifiers
- Easier cross-system integration

---

## 5. Eloquent Conventions

### Naming Conventions
- **Model:** PascalCase (e.g., `FavoredTransaction`)
- **Table:** snake_case plural (e.g., `favored_transactions`)
- **Foreign Keys:** `{model}_id` (e.g., `company_id`)
- **Pivot Tables:** Alphabetical order (e.g., `client_company`)

### Fillable vs Guarded
- Use `$fillable` to whitelist mass-assignable fields
- Never use `$guarded = []` (security risk)

### Casts
- Use `$casts` for type conversion
- Common: `decimal:2`, `boolean`, `datetime`, `array`, `json`

---

## 6. Testing Strategy

### Model Tests
- ✅ Test relationships (hasMany, belongsTo, belongsToMany)
- ✅ Test scopes (unread, active, recent)
- ✅ Test methods (calculateTotal, isFullyPaid)
- ✅ Test casts (decimal, boolean, datetime)
- ✅ Test validation rules

### Example Test
```php
public function test_favored_transaction_calculates_remaining_balance()
{
    $transaction = FavoredTransaction::factory()->create([
        'favored_total' => 100.00,
        'favored_paid_amount' => 30.00,
    ]);

    $this->assertEquals(70.00, $transaction->getRemainingBalance());
}
```

---

## 7. Database Indexes

**Performance Optimization:**

```sql
-- Client indexes
CREATE INDEX idx_clients_document ON clients(document_number);
CREATE INDEX idx_clients_active ON clients(active);

-- Company indexes
CREATE INDEX idx_companies_uuid ON companies(uuid);

-- FavoredTransaction indexes
CREATE INDEX idx_favored_company_client ON favored_transactions(company_id, client_id);
CREATE INDEX idx_favored_active ON favored_transactions(active);

-- Order indexes
CREATE INDEX idx_orders_company_client ON orders(company_id, client_id);
CREATE INDEX idx_orders_status ON orders(status);

-- Notification indexes
CREATE INDEX idx_notifications_client_read ON notifications(client_user_id, read_at);
CREATE INDEX idx_notifications_company ON notifications(company_id);
```

---

## 8. Future Enhancements

### Phase 2
- [ ] Soft deletes on all models
- [ ] Audit trail (created_by, updated_by)
- [ ] Model observers for event handling
- [ ] Polymorphic relationships (attachments, comments)

### Phase 3
- [ ] Elasticsearch integration for search
- [ ] Redis caching for frequently accessed models
- [ ] Model versioning for audit history

---

## 9. References

- [AGENTS.md](../../AGENTS.md) - Development guidelines
- [Laravel Eloquent Docs](https://laravel.com/docs/eloquent)
- [Laravel Relationships](https://laravel.com/docs/eloquent-relationships)

---

**Document Owner:** Backend Team  
**Stakeholders:** Engineering, Product  
**Review Cycle:** Quarterly
