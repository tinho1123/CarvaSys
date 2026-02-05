# PRD: CarvaSys - System Overview

**Version:** 1.0  
**Last Updated:** February 4, 2026  
**Status:** Phase 1 MVP - 80% Complete

---

## 1. Executive Summary

**CarvaSys** is a multi-tenant B2B credit management system (fiado/installment payment system) designed for small to medium businesses in Brazil. The platform enables companies to offer credit to their clients, track transactions, manage payments, and provide a modern digital experience through a client portal.

### Key Value Propositions

- **Multi-tenant architecture** - Single platform serving multiple companies
- **Credit management (Fiado)** - Track client debts and payment schedules
- **Client portal** - Self-service portal for clients to view balances, make orders, and pay debts
- **Payment integration** - Stripe integration for online payments
- **Admin panel** - Filament-based admin interface for company management

---

## 2. Business Context

### Target Market

- **Primary:** Small to medium retail businesses in Brazil
- **Secondary:** Wholesale distributors, service providers
- **Client base:** B2B relationships with recurring transactions

### Business Model

- Multi-tenant SaaS platform
- Each company (tenant) manages their own clients and transactions
- Clients can belong to multiple companies (N:N relationship)

---

## 3. System Architecture

### Technology Stack

#### Backend
- **Framework:** Laravel 10
- **PHP Version:** 8.1+ (8.4+ in Sail)
- **Admin Panel:** Filament 3.x
- **Authentication:** Laravel Sanctum (API tokens)
- **Database:** MySQL/MariaDB with Eloquent ORM

#### Frontend
- **Framework:** React 19.x
- **Bridge:** Inertia.js
- **Styling:** Tailwind CSS v4
- **Build Tool:** Vite 6.x

#### Development Environment
- **Container:** Laravel Sail (Docker)
- **Testing:** PHPUnit 10.x
- **Code Style:** Laravel Pint

#### External Services
- **Payment Gateway:** Stripe
- **Email:** (To be configured)
- **SMS:** (Future integration)

### Architecture Patterns

```
┌─────────────────────────────────────────────────────────┐
│                    Client Portal (React)                 │
│              Inertia.js + Tailwind CSS v4               │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                  API Layer (Sanctum)                     │
│         /api/client/* - Client Portal Endpoints         │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│              Multi-Tenant Middleware                     │
│         SetClientTenant - Scope Resolution              │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                  Business Logic Layer                    │
│    Controllers → Models → Database (Eloquent ORM)       │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                    MySQL Database                        │
│         Multi-tenant with company_id isolation          │
└─────────────────────────────────────────────────────────┘
```

---

## 4. Core Modules

### 4.1 Authentication & Multi-Tenancy
- CPF/CNPJ-based authentication (not email)
- N:N client-company relationships
- Company selection after authentication
- Account lockout after 5 failed attempts (30 min)
- Session-based tenant tracking

### 4.2 Client Management
- Client registration (CPF/CNPJ)
- N:N relationship with companies via `client_company` pivot
- Access control per company
- Client preferences and settings

### 4.3 Credit Management (Fiado)
- Track credit transactions
- Monitor outstanding balances
- Payment history
- Upcoming payment schedules
- Credit limit warnings

### 4.4 Product Catalog
- Product management per company
- Category organization
- Search and filtering
- Featured products
- Pricing and discounts

### 4.5 Order Management
- Order creation with line items
- Status tracking: pending → confirmed → processing → shipped → delivered
- Order history
- Automatic total calculations
- Cancellation support

### 4.6 Notifications
- In-app notification system
- Types: order_update, payment_reminder, credit_warning, announcement
- Read/unread status
- Unread counter
- Action URLs for deep linking

### 4.7 Payment Processing
- Stripe integration (PaymentIntent)
- Payment confirmation
- Payment history (Phase 2)
- Webhook handling (Phase 2)
- Transaction reconciliation

### 4.8 Admin Panel (Filament)
- Company management
- User management
- Product catalog management
- Transaction monitoring
- Reporting and analytics

---

## 5. User Roles & Permissions

### Admin Users (Filament Panel)
- **Super Admin:** Full system access, multi-company management
- **Company Admin:** Manage own company, users, products, clients
- **Company User:** View-only access, limited operations

### Client Users (Client Portal)
- **Client:** Access to own data across authorized companies
- **Permissions:** View products, create orders, view balances, make payments

---

## 6. Data Model Overview

### Core Entities

```
Company (Tenant)
├── Users (Admin)
├── Clients (N:N via client_company)
├── Products
├── ProductCategories
├── Orders
├── Transactions
├── FavoredTransactions (Credit)
├── Fees
└── Notifications

Client
├── Companies (N:N via client_company)
├── Orders
├── FavoredTransactions
└── Notifications
```

### Multi-Tenancy Strategy

- **Tenant Key:** `company_id` on all resources
- **Pivot Table:** `client_company` (client_id, company_id, is_active)
- **Scoping:** Global scopes applied via middleware
- **Isolation:** All queries filtered by `company_id`

---

## 7. API Structure

### Client Portal API (`/api/client`)

**Authentication:** Sanctum token-based

**Endpoints:**
- `GET /api/client/companies/{company}/products` - Product listing
- `GET /api/client/companies/{company}/orders` - Order history
- `POST /api/client/companies/{company}/orders` - Create order
- `GET /api/client/companies/{company}/client/credit-balance` - Credit status
- `GET /api/client/companies/{company}/client/transaction-history` - Transaction history
- `GET /api/client/notifications` - Notifications
- `POST /api/client/companies/{company}/payments/create-intent` - Payment intent
- `POST /api/client/companies/{company}/payments/confirm` - Confirm payment

---

## 8. Security & Compliance

### Security Measures
- CSRF protection (Laravel default)
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade/React escaping)
- Rate limiting on API endpoints
- Account lockout after failed login attempts
- Sanctum token authentication
- Multi-tenant data isolation

### LGPD Compliance (Brazilian Data Protection)
- Session-based tracking (no third-party cookies)
- User consent management (future)
- Data export capabilities (future)
- Right to deletion (future)

---

## 9. Performance Considerations

### Optimization Strategies
- Eager loading to prevent N+1 queries
- Database indexing on foreign keys
- Query result caching (future)
- CDN for static assets (future)
- Database query optimization

### Scalability
- Multi-tenant architecture supports horizontal scaling
- Stateless API design
- Background job processing (queues - future)

---

## 10. Development Workflow

### Code Quality Standards
- **Formatter:** Laravel Pint (mandatory before commits)
- **Testing:** PHPUnit with unit and feature tests
- **Naming:** PascalCase classes, camelCase methods, snake_case DB
- **Indentation:** 4 spaces (enforced by .editorconfig)

### Git Workflow
- Feature branches
- Pull request reviews
- Automated testing (future CI/CD)

### Pre-commit Checklist
1. Run `./vendor/bin/pint` for code formatting
2. Run `./vendor/bin/phpunit` - all tests must pass
3. Manual functionality testing
4. Verify no sensitive data committed
5. Confirm multi-tenant relationships filtered by `company_id`

---

## 11. Current Status (Phase 1 MVP)

### ✅ Completed
- Authentication refactor (CPF-based, multi-company)
- Tenant resolution middleware
- Company selection flow
- Notification system foundation
- Order management system
- Client portal API (5 controllers, 15+ endpoints)
- Stripe integration skeleton
- Database schema (notifications, orders, order_items)

### ⏳ In Progress
- Payment recording in database
- Email notifications
- React/Inertia components

### 📋 Planned (Phase 2)
- Client dashboard implementation
- Payment recording and reconciliation
- Webhook handling
- Email notification queue
- Advanced product filtering
- Cart persistence
- Mobile PWA
- PDF invoice generation
- SMS notifications
- Push notifications

---

## 12. Success Metrics

### Technical Metrics
- API response time < 200ms (p95)
- Database query count < 10 per request
- Test coverage > 70%
- Zero critical security vulnerabilities

### Business Metrics
- Client adoption rate
- Transaction volume per company
- Payment success rate
- Client portal engagement

---

## 13. Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Multi-tenant data leakage | Critical | Strict scoping, automated tests, code reviews |
| Payment processing failures | High | Stripe webhook handling, retry logic, monitoring |
| Performance degradation | Medium | Query optimization, caching, load testing |
| LGPD non-compliance | High | Legal review, consent management, data export |

---

## 14. Future Enhancements

### Short-term (3-6 months)
- Mobile app (React Native)
- Advanced reporting and analytics
- Automated payment reminders
- WhatsApp integration for notifications

### Long-term (6-12 months)
- Machine learning for credit risk assessment
- Multi-currency support
- International expansion
- Integration with accounting systems (e.g., Conta Azul)

---

## 15. References

- [AGENTS.md](../AGENTS.md) - Development guidelines
- [IMPLEMENTATION.md](../IMPLEMENTATION.md) - Implementation summary
- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [Stripe API Documentation](https://stripe.com/docs/api)

---

**Document Owner:** Development Team  
**Stakeholders:** Product, Engineering, Business  
**Review Cycle:** Monthly
