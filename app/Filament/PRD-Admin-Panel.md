# PRD: Admin Panel (Filament)

**Module:** Filament Admin Panel  
**Version:** 1.0  
**Last Updated:** February 4, 2026  
**Status:** Planned (Phase 2)

---

## 1. Module Overview

The Admin Panel provides a comprehensive interface for company administrators to manage their business operations using Filament 3.x. It supports multi-tenancy, allowing users to manage multiple companies.

### Key Features
- Multi-tenant company management
- Client management
- Product catalog administration
- Order tracking and management
- Transaction monitoring
- Fee configuration
- Notification management
- Reporting and analytics

---

## 2. Panel Configuration

### Panel ID
`client` - Separate panel for company admins

### Access URL
`/cliente/admin` - Admin panel base URL

### Authentication
- Guard: `client`
- Model: `App\Models\Client`
- Multi-tenancy via `HasTenants` interface

---

## 3. Resources

### 3.1 Client Resource

**Purpose:** Manage client accounts

**List View Columns:**
- Name
- Document (CPF/CNPJ)
- Email
- Phone
- Active status
- Last login
- Actions

**Form Fields:**
```php
TextInput::make('name')->required()
TextInput::make('document_number')->required()->mask()
TextInput::make('email')->email()
TextInput::make('phone')->tel()
Toggle::make('active')->default(true)
```

**Filters:**
- Active/Inactive
- Document type (CPF/CNPJ)
- Last login date range

**Actions:**
- Create new client
- Edit client
- Deactivate/Activate
- View login history
- Reset password

**Business Rules:**
- Document number must be unique
- Cannot delete clients with active transactions
- Deactivating client prevents login

---

### 3.2 Product Resource

**Purpose:** Manage product catalog

**List View Columns:**
- Image thumbnail
- Name
- Category
- Price
- Active status
- Featured
- Actions

**Form Fields:**
```php
TextInput::make('name')->required()
Textarea::make('description')
Select::make('category_id')->relationship('category', 'name')
TextInput::make('price')->numeric()->prefix('R$')
FileUpload::make('image')->image()->directory('products')
Toggle::make('active')->default(true)
Toggle::make('featured')->default(false)
```

**Filters:**
- Category
- Active/Inactive
- Featured
- Price range

**Bulk Actions:**
- Activate/Deactivate multiple
- Set as featured
- Delete multiple

**Business Rules:**
- Price must be positive
- Image max size: 2MB
- Featured products shown first in catalog

---

### 3.3 Category Resource

**Purpose:** Manage product categories

**List View Columns:**
- Name
- Description
- Product count
- Active status
- Actions

**Form Fields:**
```php
TextInput::make('name')->required()->unique()
Textarea::make('description')
Toggle::make('active')->default(true)
```

**Business Rules:**
- Cannot delete categories with products
- Name must be unique per company

---

### 3.4 Order Resource

**Purpose:** Track and manage orders

**List View Columns:**
- Order number (UUID)
- Client name
- Status badge
- Total amount
- Created date
- Actions

**Form Fields (Read-only for most):**
```php
Select::make('client_id')->relationship('client', 'name')->disabled()
Select::make('status')->options([
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
])
Repeater::make('items')->relationship('items')->schema([
    TextInput::make('product_name')->disabled(),
    TextInput::make('quantity')->disabled(),
    TextInput::make('unit_price')->disabled(),
    TextInput::make('total_amount')->disabled(),
])
Textarea::make('notes')
```

**Filters:**
- Status
- Client
- Date range
- Amount range

**Actions:**
- View order details
- Update status
- Print invoice (future)
- Send notification to client

**Status Transitions:**
```
pending → confirmed → processing → shipped → delivered
                                          ↓
                                     cancelled
```

**Business Rules:**
- Cannot edit confirmed orders
- Status must follow valid transitions
- Cancellation requires reason (future)

---

### 3.5 FavoredTransaction Resource

**Purpose:** Manage credit transactions (fiado)

**List View Columns:**
- Client name
- Product name
- Total amount
- Paid amount
- Remaining balance
- Created date
- Actions

**Form Fields:**
```php
Select::make('client_id')->relationship('client', 'name')->required()
Select::make('product_id')->relationship('product', 'name')
TextInput::make('name')->required()
TextInput::make('quantity')->numeric()->default(1)
TextInput::make('amount')->numeric()->prefix('R$')
TextInput::make('discounts')->numeric()->prefix('R$')
TextInput::make('favored_total')->numeric()->prefix('R$')->disabled()
TextInput::make('favored_paid_amount')->numeric()->prefix('R$')
Toggle::make('active')->default(true)
```

**Filters:**
- Client
- Product
- Fully paid / Outstanding
- Date range

**Actions:**
- Record payment
- View payment history
- Send payment reminder
- Mark as fully paid

**Business Rules:**
- `favored_paid_amount` cannot exceed `favored_total`
- Remaining balance auto-calculated
- Cannot delete transactions with payments

---

### 3.6 Fee Resource

**Purpose:** Configure fees and charges

**List View Columns:**
- Name
- Type (Fixed/Percentage)
- Amount
- Active status
- Actions

**Form Fields:**
```php
TextInput::make('name')->required()
Select::make('type')->options([
    'fixed' => 'Fixed Amount',
    'percentage' => 'Percentage',
])->required()
TextInput::make('amount')->numeric()->required()
Toggle::make('active')->default(true)
```

**Business Rules:**
- Percentage fees: 0-100
- Fixed fees: positive amounts
- Active fees applied to new orders

---

### 3.7 Notification Resource

**Purpose:** Manage client notifications

**List View Columns:**
- Client name
- Type badge
- Title
- Read status
- Created date
- Actions

**Form Fields:**
```php
Select::make('client_user_id')->relationship('client', 'name')->required()
Select::make('type')->options([
    'order_update' => 'Order Update',
    'payment_reminder' => 'Payment Reminder',
    'credit_warning' => 'Credit Warning',
    'announcement' => 'Announcement',
])->required()
TextInput::make('title')->required()
Textarea::make('description')
RichEditor::make('message')
TextInput::make('action_url')->url()
```

**Filters:**
- Type
- Read/Unread
- Client
- Date range

**Bulk Actions:**
- Mark as read
- Delete multiple

**Business Rules:**
- Cannot edit sent notifications
- Action URL must be valid

---

## 4. Dashboard

### Widgets

#### Stats Overview
- Total clients
- Active orders
- Outstanding credit
- Monthly revenue

#### Recent Orders
- Last 10 orders with status

#### Credit Summary
- Total credit extended
- Total collected
- Outstanding balance

#### Charts
- Sales trend (last 30 days)
- Top products
- Client activity

---

## 5. Navigation

### Main Menu Structure

```
Dashboard
├── Clients
├── Products
│   ├── Products
│   └── Categories
├── Orders
├── Credit
│   ├── Transactions
│   └── Fees
├── Notifications
└── Settings (future)
```

---

## 6. User Permissions (Future)

### Roles
- **Admin:** Full access to all resources
- **Manager:** View and edit, no delete
- **Viewer:** Read-only access

### Permissions
- `view_clients`
- `create_clients`
- `edit_clients`
- `delete_clients`
- (Similar for all resources)

---

## 7. Multi-Tenancy

### Tenant Switching

**UI Element:** Tenant switcher in top navigation

**Behavior:**
- Shows all companies user has access to
- Clicking switches active tenant
- All data filtered by selected tenant

**Implementation:**
```php
public function getTenants(Panel $panel): Collection
{
    return $this->companies()->get();
}

public function canAccessTenant(Model $tenant): bool
{
    return $this->companies()->where('companies.id', $tenant->id)->exists();
}
```

---

## 8. Customization

### Theme
- Primary color: Company brand color
- Dark mode support
- Custom logo per tenant

### Branding
```php
->brandName('CarvaSys')
->brandLogo(asset('images/logo.png'))
->brandLogoHeight('2rem')
->favicon(asset('images/favicon.png'))
```

---

## 9. Implementation Files

### Resources
- `app/Filament/Client/Resources/ClientResource.php`
- `app/Filament/Client/Resources/ProductResource.php`
- `app/Filament/Client/Resources/CategoryResource.php`
- `app/Filament/Client/Resources/OrderResource.php`
- `app/Filament/Client/Resources/FavoredTransactionResource.php`
- `app/Filament/Client/Resources/FeeResource.php`
- `app/Filament/Client/Resources/NotificationResource.php`

### Pages
- `app/Filament/Client/Pages/Dashboard.php`
- `app/Filament/Client/Pages/Settings.php` (future)

### Widgets
- `app/Filament/Client/Widgets/StatsOverview.php`
- `app/Filament/Client/Widgets/RecentOrders.php`
- `app/Filament/Client/Widgets/CreditSummary.php`
- `app/Filament/Client/Widgets/SalesChart.php`

---

## 10. Testing Strategy

### Resource Tests
```php
public function test_admin_can_view_clients()
{
    $admin = Client::factory()->create();
    $company = Company::factory()->create();
    $admin->companies()->attach($company);
    
    $this->actingAs($admin, 'client')
         ->get('/cliente/admin/clients')
         ->assertSuccessful();
}
```

---

## 11. Future Enhancements

### Phase 2
- [ ] Advanced reporting with charts
- [ ] Export to PDF/Excel
- [ ] Bulk import clients/products
- [ ] Email templates management
- [ ] Settings page

### Phase 3
- [ ] Role-based permissions
- [ ] Activity log
- [ ] API key management
- [ ] Webhook configuration
- [ ] Custom fields per tenant

---

## 12. References

- [Filament Documentation](https://filamentphp.com/docs)
- [Filament Multi-Tenancy](https://filamentphp.com/docs/panels/tenancy)
- [AGENTS.md](../../AGENTS.md)

---

**Document Owner:** Backend Team  
**Stakeholders:** Product, UX  
**Review Cycle:** Quarterly
