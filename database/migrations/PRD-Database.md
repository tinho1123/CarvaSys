# PRD: Database Schema

**Module:** Database Structure & Migrations  
**Version:** 1.0  
**Last Updated:** February 4, 2026  
**Status:** Implemented (Phase 1)

---

## 1. Overview

This document defines the database schema for CarvaSys, including all tables, relationships, indexes, and constraints. The schema supports multi-tenancy with `company_id` isolation.

### Database Technology
- **RDBMS:** MySQL 8.0+ / MariaDB 10.6+
- **ORM:** Laravel Eloquent
- **Migrations:** Laravel migration system
- **Character Set:** utf8mb4 (full Unicode support)
- **Collation:** utf8mb4_unicode_ci

---

## 2. Core Tables

### 2.1 companies

**Purpose:** Tenant companies using the platform

```sql
CREATE TABLE companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    foundation_date DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_companies_uuid (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields:**
- `id` - Auto-increment primary key
- `uuid` - Globally unique identifier for routing
- `name` - Company name
- `foundation_date` - Company foundation date
- `created_at`, `updated_at` - Laravel timestamps

---

### 2.2 clients

**Purpose:** End-user clients who can access multiple companies

```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NULL,
    password VARCHAR(255) NOT NULL,
    document_type ENUM('cpf', 'cnpj') NOT NULL,
    document_number VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    preferences JSON NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_clients_uuid (uuid),
    INDEX idx_clients_document (document_number),
    INDEX idx_clients_email (email),
    INDEX idx_clients_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**
- `document_number` stored without formatting (digits only)
- `password` hashed with bcrypt
- `locked_until` set after 5 failed login attempts
- `preferences` stores JSON settings

---

### 2.3 client_company (Pivot)

**Purpose:** N:N relationship between clients and companies

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
    UNIQUE KEY unique_client_company (client_id, company_id),
    INDEX idx_client_company_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**
- `is_active` controls client access to company
- Cascade delete when client or company is deleted
- Unique constraint prevents duplicate relationships

---

### 2.4 users

**Purpose:** Admin users managing companies via Filament

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 2.5 companies_users (Pivot)

**Purpose:** N:N relationship between users and companies

```sql
CREATE TABLE companies_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_company_user (company_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. Product Tables

### 3.1 products_categories

```sql
CREATE TABLE products_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_categories_company (company_id),
    INDEX idx_categories_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3.2 products

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) NULL,
    active BOOLEAN DEFAULT TRUE,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES products_categories(id) ON DELETE SET NULL,
    INDEX idx_products_company (company_id),
    INDEX idx_products_category (category_id),
    INDEX idx_products_active (active),
    INDEX idx_products_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 4. Transaction Tables

### 4.1 favored_transactions

**Purpose:** Credit transactions (fiado)

```sql
CREATE TABLE favored_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    discounts DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    favored_total DECIMAL(10, 2) NOT NULL,
    favored_paid_amount DECIMAL(10, 2) DEFAULT 0,
    quantity INT DEFAULT 1,
    image VARCHAR(255) NULL,
    active BOOLEAN DEFAULT TRUE,
    category_name VARCHAR(255) NULL,
    client_name VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES products_categories(id) ON DELETE SET NULL,
    INDEX idx_favored_company_client (company_id, client_id),
    INDEX idx_favored_active (active),
    INDEX idx_favored_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**
- `favored_total` = total amount owed
- `favored_paid_amount` = amount paid so far
- Remaining balance = `favored_total` - `favored_paid_amount`

---

### 4.2 transactions

**Purpose:** General financial transactions

```sql
CREATE TABLE transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    description TEXT NULL,
    reference VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_transactions_company (company_id),
    INDEX idx_transactions_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 4.3 favored_debts

**Purpose:** Track debt summaries per client

```sql
CREATE TABLE favored_debts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    total_debt DECIMAL(10, 2) DEFAULT 0,
    total_paid DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_company_client_debt (company_id, client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 5. Order Tables

### 5.1 orders

```sql
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    fee_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    confirmed_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_orders_company_client (company_id, client_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Status Flow:**
```
pending → confirmed → processing → shipped → delivered
                                          ↓
                                     cancelled
```

---

### 5.2 order_items

```sql
CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    discount_percent DECIMAL(5, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**
- `product_name` denormalized for historical accuracy
- `total_amount` = (`unit_price` * `quantity`) - `discount_amount`

---

## 6. Notification Tables

### 6.1 notifications

```sql
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    client_user_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    type ENUM('order_update', 'payment_reminder', 'credit_warning', 'announcement') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    message TEXT NULL,
    action_url VARCHAR(255) NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (client_user_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_notifications_client_company (client_user_id, company_id),
    INDEX idx_notifications_client_read (client_user_id, read_at),
    INDEX idx_notifications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 7. Fee Tables

### 7.1 fees

```sql
CREATE TABLE fees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('fixed', 'percentage') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_fees_company (company_id),
    INDEX idx_fees_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 8. Laravel System Tables

### 8.1 personal_access_tokens (Sanctum)

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_tokenable (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 8.2 sessions

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 9. Multi-Tenancy Strategy

### Tenant Isolation

All tenant-scoped tables must have:
- `company_id BIGINT UNSIGNED NOT NULL`
- Foreign key constraint with CASCADE delete
- Index on `company_id`

**Scoped Tables:**
- products
- products_categories
- favored_transactions
- transactions
- orders
- fees
- notifications

### Global Scopes

Applied via middleware:
```php
Model::addGlobalScope('company', function ($query) {
    $query->where('company_id', session('selected_tenant_id'));
});
```

---

## 10. Indexes Strategy

### Performance Indexes

**Foreign Keys:** All foreign keys automatically indexed
**UUID Columns:** Indexed for routing lookups
**Status Columns:** Indexed for filtering
**Timestamps:** Indexed for sorting/filtering
**Composite Indexes:** For common query patterns

### Example Composite Indexes
```sql
INDEX idx_orders_company_client (company_id, client_id)
INDEX idx_favored_company_client (company_id, client_id)
INDEX idx_notifications_client_read (client_user_id, read_at)
```

---

## 11. Migration Files

### Migration Order

1. `create_companies.php`
2. `create_clients_table.php`
3. `create_users.php`
4. `create_companies_users.php`
5. `create_client_company_table.php`
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

---

## 12. Data Integrity

### Constraints

- **Foreign Keys:** CASCADE on delete for tenant data
- **Unique Constraints:** Prevent duplicate records
- **NOT NULL:** Required fields enforced at DB level
- **Default Values:** Sensible defaults for optional fields

### Validation

- **Application Level:** Laravel validation rules
- **Database Level:** Constraints and triggers (future)

---

## 13. Backup Strategy

### Recommendations

- **Daily backups:** Full database dump
- **Point-in-time recovery:** Binary log enabled
- **Retention:** 30 days minimum
- **Testing:** Monthly restore tests

---

## 14. Future Enhancements

### Phase 2
- [ ] Soft deletes on all models
- [ ] Audit tables (created_by, updated_by, deleted_by)
- [ ] Payment history table
- [ ] Webhook event log table

### Phase 3
- [ ] Read replicas for scaling
- [ ] Partitioning for large tables
- [ ] Archival strategy for old data

---

## 15. References

- [Laravel Migrations](https://laravel.com/docs/migrations)
- [MySQL Documentation](https://dev.mysql.com/doc/)

---

**Document Owner:** Database Team  
**Stakeholders:** Backend, DevOps  
**Review Cycle:** Quarterly
