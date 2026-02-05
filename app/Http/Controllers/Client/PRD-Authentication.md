# PRD: Authentication & Multi-Tenancy

**Module:** Client Authentication  
**Version:** 1.0  
**Last Updated:** February 4, 2026  
**Status:** Implemented (Phase 1)

---

## 1. Module Overview

The Authentication & Multi-Tenancy module handles client login, company selection, and tenant resolution for the CarvaSys platform. It supports N:N client-company relationships, allowing a single client to access multiple companies.

### Key Features
- CPF/CNPJ-based authentication (Brazilian tax IDs)
- Multi-company support per client
- Account lockout after failed attempts
- Session-based tenant tracking
- Automatic company selection for single-company clients

---

## 2. Functional Requirements

### FR-1: Client Login
**Priority:** Critical  
**Status:** ✅ Implemented

**Description:**  
Clients authenticate using CPF/CNPJ and password (not email).

**Acceptance Criteria:**
- ✅ Login form accepts CPF or CNPJ with automatic formatting
- ✅ Password validation with minimum 8 characters
- ✅ Invalid credentials return appropriate error message
- ✅ Successful login redirects to company selection or dashboard
- ✅ Login attempts are tracked per client

**Business Rules:**
- CPF format: XXX.XXX.XXX-XX (11 digits)
- CNPJ format: XX.XXX.XXX/XXXX-XX (14 digits)
- Password must be hashed using bcrypt
- Session timeout: 2 hours of inactivity

---

### FR-2: Account Lockout
**Priority:** High  
**Status:** ✅ Implemented

**Description:**  
After 5 consecutive failed login attempts, the account is locked for 30 minutes.

**Acceptance Criteria:**
- ✅ Failed attempts increment `login_attempts` counter
- ✅ After 5 attempts, `locked_until` is set to now + 30 minutes
- ✅ Locked accounts cannot login until lockout expires
- ✅ Successful login resets `login_attempts` to 0
- ✅ Clear error message shown when account is locked

**Business Rules:**
- Lockout duration: 30 minutes
- Lockout threshold: 5 attempts
- Counter resets on successful login
- Lockout expires automatically (no admin intervention required)

---

### FR-3: Company Selection
**Priority:** Critical  
**Status:** ✅ Implemented

**Description:**  
After authentication, clients with access to multiple companies must select which company to access.

**Acceptance Criteria:**
- ✅ If client has 1 company → Auto-select and redirect to dashboard
- ✅ If client has N companies → Show selection modal
- ✅ Company selection stores `selected_tenant_id` in session
- ✅ Company list shows: logo, name, foundation date
- ✅ "Use another CPF" logout link available

**Business Rules:**
- Only active companies (`is_active=true` in pivot) are shown
- Company selection is required before accessing any tenant-scoped resource
- Changing company requires re-selection (logout/login or switch company feature)

**UI/UX:**
- Clean, mobile-friendly company selection screen
- Company cards with visual indicators
- Clear branding per company

---

### FR-4: Tenant Resolution
**Priority:** Critical  
**Status:** ✅ Implemented

**Description:**  
Middleware resolves the active tenant (company) for each request and applies global scopes.

**Acceptance Criteria:**
- ✅ Tenant resolved from: header → URL → session (in order)
- ✅ Validates client has access to company via `client_company` pivot
- ✅ Applies global scopes to: Transaction, FavoredTransaction, Product, Fee, Notification
- ✅ Redirects to company selection if no tenant found
- ✅ Returns 403 if client doesn't have access to requested company

**Business Rules:**
- Tenant resolution order: `X-Tenant-ID` header → `{tenant}` URL param → session
- All tenant-scoped models must have `company_id` foreign key
- Middleware applies to all `/cliente/*` and `/api/client/*` routes

---

### FR-5: Session Management
**Priority:** High  
**Status:** ✅ Implemented

**Description:**  
Manage client sessions with proper security and timeout handling.

**Acceptance Criteria:**
- ✅ Session stores: `selected_tenant_id`, `last_activity`
- ✅ Session timeout after 2 hours of inactivity
- ✅ Logout clears session and redirects to login
- ✅ "Remember me" option (future enhancement)

**Business Rules:**
- Session driver: database (for multi-server support)
- Session lifetime: 120 minutes
- Session regeneration on login (CSRF protection)

---

## 3. Non-Functional Requirements

### NFR-1: Security
- All passwords hashed with bcrypt (cost factor 10)
- CSRF protection on all forms
- Session fixation prevention (regenerate on login)
- Rate limiting: 10 login attempts per minute per IP
- SQL injection prevention via Eloquent ORM

### NFR-2: Performance
- Login response time < 300ms (p95)
- Company selection query < 100ms
- Tenant resolution < 50ms per request
- Session storage optimized (Redis recommended for production)

### NFR-3: Usability
- CPF/CNPJ auto-formatting on input
- Clear error messages in Portuguese
- Mobile-responsive login and selection screens
- Accessibility: WCAG 2.1 Level AA compliance

### NFR-4: Compliance
- LGPD-compliant session handling
- No third-party tracking cookies
- Audit log for login attempts (future)

---

## 4. User Flows

### Flow 1: Successful Login (Single Company)

```
1. Client visits /cliente/login
2. Enters CPF/CNPJ and password
3. Submits form
   ↓
4. AuthController validates credentials
5. Checks login attempts (not locked)
6. Authenticates client
   ↓
7. Queries client.companies()
8. Finds 1 active company
   ↓
9. Auto-selects company
10. Stores selected_tenant_id in session
11. Redirects to /cliente/dashboard
```

### Flow 2: Successful Login (Multiple Companies)

```
1. Client visits /cliente/login
2. Enters CPF/CNPJ and password
3. Submits form
   ↓
4. AuthController validates credentials
5. Authenticates client
   ↓
6. Queries client.companies()
7. Finds N active companies
   ↓
8. Redirects to /cliente/select-company
9. Shows company selection screen
   ↓
10. Client clicks on company card
11. POST /cliente/select-company/{companyUuid}
    ↓
12. Validates client has access
13. Stores selected_tenant_id in session
14. Redirects to /cliente/dashboard
```

### Flow 3: Failed Login (Account Lockout)

```
1. Client enters invalid credentials
2. AuthController increments login_attempts
   ↓
3. If login_attempts >= 5:
   - Set locked_until = now + 30 minutes
   - Reset login_attempts to 0
   - Return "Account locked" error
   ↓
4. If login_attempts < 5:
   - Return "Invalid credentials" error
   - Show remaining attempts
```

---

## 5. Data Model

### Client Model

```php
class Client extends AuthenticatableUser
{
    protected $fillable = [
        'uuid',
        'email',
        'password',
        'document_type',      // 'cpf' or 'cnpj'
        'document_number',    // Stored without formatting
        'name',
        'phone',
        'active',
        'last_login_at',
        'login_attempts',
        'locked_until',
        'preferences',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'preferences' => 'array',
    ];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'client_company')
            ->withPivot('is_active')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }
}
```

### Pivot Table: client_company

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

---

## 6. API Endpoints

### POST /cliente/login

**Description:** Authenticate client with CPF/CNPJ and password

**Request:**
```json
{
  "document": "123.456.789-00",
  "password": "securepassword123"
}
```

**Response (Success - Single Company):**
```json
{
  "success": true,
  "redirect": "/cliente/dashboard",
  "company": {
    "uuid": "abc-123",
    "name": "Empresa ABC"
  }
}
```

**Response (Success - Multiple Companies):**
```json
{
  "success": true,
  "redirect": "/cliente/select-company"
}
```

**Response (Error - Invalid Credentials):**
```json
{
  "success": false,
  "message": "CPF/CNPJ ou senha inválidos",
  "attempts_remaining": 3
}
```

**Response (Error - Account Locked):**
```json
{
  "success": false,
  "message": "Conta bloqueada. Tente novamente em 25 minutos.",
  "locked_until": "2026-02-04T22:00:00Z"
}
```

---

### POST /cliente/select-company/{companyUuid}

**Description:** Select active company for session

**Response (Success):**
```json
{
  "success": true,
  "redirect": "/cliente/dashboard",
  "company": {
    "uuid": "abc-123",
    "name": "Empresa ABC",
    "logo": "/storage/logos/abc.png"
  }
}
```

**Response (Error - No Access):**
```json
{
  "success": false,
  "message": "Você não tem acesso a esta empresa"
}
```

---

### POST /cliente/logout

**Description:** Logout client and clear session

**Response:**
```json
{
  "success": true,
  "redirect": "/cliente/login"
}
```

---

## 7. Business Rules

### BR-1: Document Validation
- CPF must be valid (check digit validation)
- CNPJ must be valid (check digit validation)
- Document stored without formatting (digits only)
- Document must be unique per client

### BR-2: Password Policy
- Minimum 8 characters
- Must contain at least 1 letter and 1 number (recommended)
- Hashed with bcrypt before storage
- Password reset via email (future)

### BR-3: Multi-Tenancy Isolation
- All queries must be scoped by `company_id`
- Client can only access data from authorized companies
- Middleware enforces tenant resolution on every request
- Global scopes automatically applied to tenant-scoped models

### BR-4: Session Security
- Session regenerated on login (prevent fixation)
- Session cleared on logout
- Session timeout after 2 hours inactivity
- CSRF token validated on all POST requests

---

## 8. Testing Strategy

### Unit Tests
- ✅ `ClientTest::test_can_create_client()`
- ✅ `ClientTest::test_validates_cpf_format()`
- ✅ `ClientTest::test_increments_login_attempts()`
- ✅ `ClientTest::test_locks_account_after_5_attempts()`
- ✅ `ClientTest::test_resets_attempts_on_success()`

### Feature Tests
- ✅ `AuthTest::test_client_can_login_with_cpf()`
- ✅ `AuthTest::test_client_cannot_login_with_invalid_credentials()`
- ✅ `AuthTest::test_account_locks_after_failed_attempts()`
- ✅ `AuthTest::test_auto_selects_single_company()`
- ✅ `AuthTest::test_shows_company_selection_for_multiple()`
- ✅ `AuthTest::test_tenant_resolution_from_session()`

---

## 9. Implementation Files

### Controllers
- `app/Http/Controllers/Client/AuthController.php`
  - `login()` - Authenticate via CPF/password
  - `selectCompany()` - Resolve tenant after auth
  - `showCompanySelection()` - Show company selection screen
  - `logout()` - Clear tenant and session

### Middleware
- `app/Http/Middleware/SetClientTenant.php`
  - Resolves tenant from header/URL/session
  - Validates client access via pivot
  - Applies global scopes

### Views
- `resources/views/client/auth/login.blade.php`
- `resources/views/client/select-company.blade.php`

### Routes
- `routes/web.php` (client routes)

---

## 10. Future Enhancements

### Phase 2
- [ ] "Remember me" functionality
- [ ] Password reset via email
- [ ] Two-factor authentication (2FA)
- [ ] Social login (Google, Facebook)
- [ ] Audit log for login attempts

### Phase 3
- [ ] Biometric authentication (mobile app)
- [ ] Single Sign-On (SSO)
- [ ] OAuth2 provider for third-party integrations

---

## 11. Dependencies

- Laravel Sanctum (authentication)
- Laravel Session (session management)
- Eloquent ORM (database queries)
- Blade templates (views)

---

## 12. References

- [AGENTS.md](../../../AGENTS.md) - Development guidelines
- [IMPLEMENTATION.md](../../../IMPLEMENTATION.md) - Implementation summary
- [Laravel Authentication Docs](https://laravel.com/docs/authentication)
- [Laravel Multi-Tenancy](https://tenancyforlaravel.com/)

---

**Document Owner:** Backend Team  
**Stakeholders:** Product, Security, UX  
**Review Cycle:** Quarterly
